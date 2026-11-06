<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\LabOrder;
use App\Models\LabReport;
use App\Models\NursingNote;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\TreatmentPlan;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DoctorDashboardService
{
    /**
     * Compile complete doctor clinical dashboard payload.
     *
     * @param User $currentUser Authenticated user
     * @param array $filters Query parameters (date, doctor_id, search q)
     * @return array
     */
    public function getDoctorDashboardData(User $currentUser, array $filters = []): array
    {
        $targetDoctor = $this->resolveTargetDoctor($currentUser, $filters);
        $selectedDate = !empty($filters['date']) ? Carbon::parse($filters['date'])->toDateString() : today()->toDateString();
        $searchTerm = !empty($filters['q']) ? trim($filters['q']) : null;

        // 1. Doctor Profile & Chamber Schedules
        $targetDoctor->loadMissing(['staffProfile.department', 'doctorSchedules.department']);
        $allDoctors = $currentUser->hasRole(['super_admin', 'administrator'])
            ? User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->with('staffProfile.department')->get()
            : collect([$targetDoctor]);

        // 2. Today's Scheduled Appointments
        $todayAppointmentsQuery = Appointment::where('doctor_id', $targetDoctor->id)
            ->whereDate('appointment_date', $selectedDate)
            ->with(['patient', 'department'])
            ->orderBy('appointment_time', 'asc');

        if ($searchTerm) {
            $todayAppointmentsQuery->where(function (Builder $q) use ($searchTerm) {
                $q->where('serial_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('patient', fn($pq) => $pq->where('name', 'like', "%{$searchTerm}%")->orWhere('patient_code', 'like', "%{$searchTerm}%"));
            });
        }
        $todayAppointments = $todayAppointmentsQuery->get();

        // 3. Consultation Queue (Waiting & In-Progress Visits)
        $consultationQueue = $this->getConsultationQueue($targetDoctor, $selectedDate, $searchTerm);

        // 4. Inpatient Ward Care — Active Admissions (under this doctor's care)
        $activeAdmissionsQuery = Admission::where('attending_doctor_id', $targetDoctor->id)
            ->where('status', 'admitted')
            ->with([
                'patient',
                'currentBedAssignment.bed.department',
                'department',
                'nursingNotes' => fn($q) => $q->latest('recorded_at')->take(1),
                'treatmentPlans',
            ])
            ->orderBy('admission_date', 'desc');

        if ($searchTerm) {
            $activeAdmissionsQuery->where(function (Builder $q) use ($searchTerm) {
                $q->where('admission_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('patient', fn($pq) => $pq->where('name', 'like', "%{$searchTerm}%")->orWhere('patient_code', 'like', "%{$searchTerm}%"));
            });
        }
        $activeAdmissions = $activeAdmissionsQuery->get();

        // 5. Assigned Patients (Distinct active patients in inpatient care, recent visits, and appointments)
        $assignedPatients = $this->getAssignedPatients($targetDoctor, $searchTerm);

        // 6. Pending Diagnostic Test Reports for Review
        $pendingLabReports = $this->getPendingLabReports($targetDoctor, $searchTerm);

        // 7. Recent Consultations Ledger
        $recentConsultations = Consultation::where('doctor_id', $targetDoctor->id)
            ->with([
                'visit.patient',
                'diagnoses',
                'prescription.items',
            ])
            ->latest('created_at')
            ->take(8)
            ->get();

        // 8. Treatment Follow-ups (Due within next 7 days, today, or overdue)
        $treatmentFollowUps = $this->getTreatmentFollowUps($targetDoctor);

        // 9. Real Database Statistics / KPI Cards
        $metrics = [
            'today_appointments' => $todayAppointments->count(),
            'today_confirmed' => $todayAppointments->where('status', 'confirmed')->count(),
            'today_completed' => $todayAppointments->where('status', 'completed')->count(),
            'waiting_queue' => $consultationQueue->count(),
            'emergency_cases' => $consultationQueue->where('triage_level', 'emergency')->count(),
            'active_admissions' => $activeAdmissions->count(),
            'pending_lab_reviews' => $pendingLabReports->count(),
            'assigned_patients_count' => $assignedPatients->total(),
            'follow_ups_due' => $treatmentFollowUps->count(),
        ];

        // 10. Quick Patient Search Results (if searchTerm provided and not matching appointments)
        $patientSearchResults = $searchTerm ? $this->searchPatients($searchTerm, $targetDoctor) : collect();

        return [
            'doctor' => $targetDoctor,
            'allDoctors' => $allDoctors,
            'selectedDate' => $selectedDate,
            'searchTerm' => $searchTerm,
            'metrics' => $metrics,
            'todayAppointments' => $todayAppointments,
            'consultationQueue' => $consultationQueue,
            'activeAdmissions' => $activeAdmissions,
            'assignedPatients' => $assignedPatients,
            'pendingLabReports' => $pendingLabReports,
            'recentConsultations' => $recentConsultations,
            'treatmentFollowUps' => $treatmentFollowUps,
            'patientSearchResults' => $patientSearchResults,
        ];
    }

    /**
     * Resolve target doctor based on current user role and request filters.
     */
    protected function resolveTargetDoctor(User $currentUser, array $filters = []): User
    {
        if ($currentUser->hasRole('doctor') && empty($filters['doctor_id'])) {
            return $currentUser;
        }

        if ($currentUser->hasRole(['super_admin', 'administrator'])) {
            if (!empty($filters['doctor_id'])) {
                $doctor = User::where('id', $filters['doctor_id'])
                    ->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
                    ->first();
                if ($doctor) {
                    return $doctor;
                }
            }

            // Fallback to logged-in user if doctor, or first doctor in database
            if ($currentUser->hasRole('doctor')) {
                return $currentUser;
            }

            $firstDoctor = User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first();
            if ($firstDoctor) {
                return $firstDoctor;
            }
        }

        return $currentUser;
    }

    /**
     * Query today's active consultation queue.
     */
    public function getConsultationQueue(User $doctor, string $date, ?string $searchTerm = null): Collection
    {
        $query = Visit::whereDate('visit_date', $date)
            ->whereIn('status', ['waiting', 'in_consultation'])
            ->where(function (Builder $q) use ($doctor) {
                // Visits linked to appointments for this doctor
                $q->whereHas('appointment', fn($aq) => $aq->where('doctor_id', $doctor->id))
                  // OR visits that already have a draft consultation by this doctor
                  ->orWhereHas('consultation', fn($cq) => $cq->where('doctor_id', $doctor->id)->where('status', 'draft'));

                // OR if doctor is attached to Emergency, show unassigned emergency triage visits
                if ($doctor->staffProfile?->department?->code === 'EMERG') {
                    $q->orWhere(fn($eq) => $eq->where('visit_type', 'emergency')->whereNull('appointment_id'));
                }
            })
            ->with([
                'patient',
                'appointment.department',
                'consultation.diagnoses',
            ]);

        if ($searchTerm) {
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('visit_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('patient', fn($pq) => $pq->where('name', 'like', "%{$searchTerm}%")->orWhere('patient_code', 'like', "%{$searchTerm}%"));
            });
        }

        // Triage prioritization: critical/emergency > priority > normal, then earliest arrival
        return $query->get()->sortBy(function (Visit $visit) {
            $triageScore = match ($visit->triage_level) {
                'critical', 'emergency' => 1,
                'priority' => 2,
                default => 3,
            };
            return sprintf('%d-%s', $triageScore, $visit->visit_date->format('YmdHis'));
        })->values();
    }

    /**
     * Query distinct patients assigned to this doctor.
     */
    public function getAssignedPatients(User $doctor, ?string $searchTerm = null, int $perPage = 10)
    {
        $admissionPatientIds = Admission::where('attending_doctor_id', $doctor->id)
            ->where('status', 'admitted')
            ->pluck('patient_id');

        $appointmentPatientIds = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', '>=', today()->subDays(30))
            ->pluck('patient_id');

        $consultationPatientIds = Visit::whereHas('consultation', fn($q) => $q->where('doctor_id', $doctor->id)->whereDate('created_at', '>=', today()->subDays(90)))
            ->pluck('patient_id');

        $allPatientIds = $admissionPatientIds->merge($appointmentPatientIds)
            ->merge($consultationPatientIds)
            ->filter()
            ->unique();

        $query = Patient::whereIn('id', $allPatientIds)
            ->with([
                'admissions' => fn($q) => $q->where('status', 'admitted')->with('currentBedAssignment.bed'),
                'appointments' => fn($q) => $q->where('doctor_id', $doctor->id)->latest('appointment_date')->take(1),
                'visits' => fn($q) => $q->latest('visit_date')->take(1),
            ])
            ->orderBy('name', 'asc');

        if ($searchTerm) {
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('patient_code', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'assigned_page');
    }

    /**
     * Query diagnostic test reports awaiting doctor review.
     */
    public function getPendingLabReports(User $doctor, ?string $searchTerm = null): Collection
    {
        $query = LabReport::where('review_status', 'pending_review')
            ->where(function (Builder $q) use ($doctor) {
                // Ordered by this doctor during a consultation
                $q->whereHas('orderItem.labOrder.consultation', fn($cq) => $cq->where('doctor_id', $doctor->id))
                  // OR for an inpatient currently admitted under this doctor
                  ->orWhereHas('orderItem.labOrder.patient.admissions', fn($aq) => $aq->where('attending_doctor_id', $doctor->id)->where('status', 'admitted'));
            })
            ->with([
                'orderItem.labOrder.patient',
                'orderItem.labOrder.consultation',
                'orderItem.labTest',
            ])
            ->orderBy('created_at', 'desc');

        if ($searchTerm) {
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('findings_summary', 'like', "%{$searchTerm}%")
                  ->orWhereHas('orderItem.labOrder', fn($oq) => $oq->where('order_number', 'like', "%{$searchTerm}%"))
                  ->orWhereHas('orderItem.labOrder.patient', fn($pq) => $pq->where('name', 'like', "%{$searchTerm}%")->orWhere('patient_code', 'like', "%{$searchTerm}%"));
            });
        }

        return $query->get();
    }

    /**
     * Query upcoming or overdue treatment follow-ups authored by this doctor.
     */
    public function getTreatmentFollowUps(User $doctor, int $limit = 8): Collection
    {
        $prescriptions = Prescription::whereHas('consultation', fn($q) => $q->where('doctor_id', $doctor->id))
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '>=', today()->subDays(14))
            ->whereDate('follow_up_date', '<=', today()->addDays(30))
            ->with([
                'consultation.visit.patient',
                'consultation.diagnoses',
                'items',
            ])
            ->orderBy('follow_up_date', 'asc')
            ->take($limit)
            ->get();

        return $prescriptions->map(function (Prescription $prescription) {
            $followUpDate = Carbon::parse($prescription->follow_up_date);
            $daysDiff = today()->diffInDays($followUpDate, false);

            if ($followUpDate->isToday()) {
                $statusLabel = 'Due Today';
                $statusBadge = 'danger';
            } elseif ($daysDiff < 0) {
                $statusLabel = abs($daysDiff) . ' days overdue';
                $statusBadge = 'warning';
            } else {
                $statusLabel = 'In ' . $daysDiff . ' days';
                $statusBadge = 'teal';
            }

            return [
                'prescription' => $prescription,
                'patient' => $prescription->consultation?->visit?->patient,
                'diagnoses' => $prescription->consultation?->diagnoses ?? collect(),
                'follow_up_date' => $followUpDate,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
                'advice' => $prescription->advice,
            ];
        });
    }

    /**
     * Search patient directory for quick lookup.
     */
    public function searchPatients(string $term, User $doctor): Collection
    {
        return Patient::where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('patient_code', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        })
        ->with([
            'admissions' => fn($q) => $q->where('status', 'admitted')->with('currentBedAssignment.bed'),
            'visits' => fn($q) => $q->latest('visit_date')->take(1),
        ])
        ->take(10)
        ->get();
    }

    /**
     * Retrieve complete clinical timeline & medical history for a patient.
     */
    public function getPatientClinicalHistory(Patient $patient, User $actor): array
    {
        // 1. Visits with Consultations, Prescriptions, Diagnoses
        $visits = Visit::where('patient_id', $patient->id)
            ->with([
                'consultation.doctor.staffProfile',
                'consultation.diagnoses',
                'consultation.prescription.items',
                'appointment.department',
                'admissions.currentBedAssignment.bed',
            ])
            ->orderBy('visit_date', 'desc')
            ->get();

        // 2. Admissions & Bed Stays History
        $admissions = Admission::where('patient_id', $patient->id)
            ->with([
                'attendingDoctor.staffProfile',
                'department',
                'bedAssignments.bed',
                'treatmentPlans.records',
                'dischargeSummary',
            ])
            ->orderBy('admission_date', 'desc')
            ->get();

        // 3. Diagnostic Lab Orders & Reports
        $labOrders = LabOrder::where('patient_id', $patient->id)
            ->with([
                'items.labTest',
                'items.report.reviewedBy',
                'consultation.doctor',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. Nursing Vitals History
        $vitalsHistory = NursingNote::whereHas('admission', fn($q) => $q->where('patient_id', $patient->id))
            ->with(['nurse', 'admission'])
            ->orderBy('recorded_at', 'desc')
            ->take(20)
            ->get();

        // 5. Prescriptions History
        $prescriptions = Prescription::whereHas('consultation.visit', fn($q) => $q->where('patient_id', $patient->id))
            ->with(['consultation.doctor', 'consultation.diagnoses', 'items'])
            ->latest('created_at')
            ->get();

        return [
            'patient' => $patient,
            'visits' => $visits,
            'admissions' => $admissions,
            'labOrders' => $labOrders,
            'vitalsHistory' => $vitalsHistory,
            'prescriptions' => $prescriptions,
        ];
    }

    /**
     * Doctor reviews and marks a diagnostic test report verified.
     */
    public function reviewLabReport(LabReport $labReport, User $doctor, ?string $findingsSummary = null, ?string $clinicalNotes = null): LabReport
    {
        return DB::transaction(function () use ($labReport, $doctor, $findingsSummary, $clinicalNotes) {
            $lockedReport = LabReport::where('id', $labReport->id)->lockForUpdate()->firstOrFail();

            $updatedSummary = $findingsSummary ?: $lockedReport->findings_summary;
            if ($clinicalNotes) {
                $updatedSummary = ($updatedSummary ? $updatedSummary . "\n\n" : '') . "[Dr. {$doctor->name} Review]: " . $clinicalNotes;
            }

            $lockedReport->update([
                'findings_summary' => $updatedSummary,
                'report_status' => 'reviewed',
                'review_status' => 'reviewed',
                'reviewed_by' => $doctor->id,
                'reviewed_at' => now(),
                'doctor_notes' => $clinicalNotes,
            ]);

            \App\Models\LabReportHistory::create([
                'lab_report_id' => $lockedReport->id,
                'action' => 'doctor_reviewed',
                'changed_by' => $doctor->id,
                'previous_status' => 'received',
                'new_status' => 'reviewed',
                'notes' => "Medically reviewed by Dr. {$doctor->name}." . ($clinicalNotes ? " Remarks: {$clinicalNotes}" : ''),
                'metadata' => [
                    'doctor_name' => $doctor->name,
                    'doctor_notes' => $clinicalNotes,
                ],
            ]);

            // Audit Trail
            AuditLog::create([
                'user_id' => $doctor->id,
                'event' => 'lab_report.reviewed',
                'auditable_type' => LabReport::class,
                'auditable_id' => $lockedReport->id,
                'old_values' => ['review_status' => 'pending_review'],
                'new_values' => [
                    'review_status' => 'reviewed',
                    'reviewed_by' => $doctor->id,
                    'reviewed_at' => now()->toDateTimeString(),
                    'clinical_notes' => $clinicalNotes,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedReport;
        });
    }
}
