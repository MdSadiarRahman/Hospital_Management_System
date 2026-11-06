<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\BedStatusHistory;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\MedicationAdministration;
use App\Models\NursingNote;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\ShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class NurseDashboardService
{
    /**
     * Compile complete Nurse Ward Station dashboard payload.
     *
     * @param User $currentUser Authenticated user
     * @param array $filters Query parameters (department_id, shift, q)
     * @return array
     */
    public function getNurseDashboardData(User $currentUser, array $filters = []): array
    {
        $targetNurse = $this->resolveTargetNurse($currentUser, $filters);
        $selectedDeptId = !empty($filters['department_id']) && $filters['department_id'] !== 'all'
            ? (int) $filters['department_id']
            : null;
        $selectedShift = !empty($filters['shift']) && in_array($filters['shift'], ['morning', 'evening', 'night'])
            ? $filters['shift']
            : null;
        $searchTerm = !empty($filters['q']) ? trim($filters['q']) : null;

        // 1. Nurse Profile & Current Shift Determination
        $targetNurse->loadMissing(['staffProfile.department', 'staffProfile.departments']);
        $shiftInfo = $this->resolveShiftInformation($targetNurse);

        // 2. Clinical Departments for Filtering
        $clinicalDepartments = Department::where('is_clinical', true)->orderBy('name')->get();

        // 3. 10-Bed Inpatient Ward Bedside Monitor
        $coreBedNumbers = ['EM-01', 'EM-02', 'GYN-01', 'GYN-02', 'SUR-01', 'SUR-02', 'GEN-01', 'GEN-02', 'GEN-03', 'GEN-04'];
        $wardBedsQuery = Bed::query()
            ->with([
                'department',
                'currentAssignment.admission.patient',
                'currentAssignment.admission.attendingDoctor.staffProfile',
                'currentAssignment.admission.nursingNotes' => fn($q) => $q->latest('recorded_at')->take(1),
            ]);

        if ($selectedDeptId) {
            $wardBedsQuery->where('department_id', $selectedDeptId);
        } else {
            // Prioritize the 10 core beds or first 10 inpatient beds
            $wardBedsQuery->whereIn('bed_number', $coreBedNumbers)
                ->orderByRaw("FIELD(bed_number, 'EM-01', 'EM-02', 'GYN-01', 'GYN-02', 'SUR-01', 'SUR-02', 'GEN-01', 'GEN-02', 'GEN-03', 'GEN-04')");
        }

        $wardBeds = $wardBedsQuery->get();
        if ($wardBeds->isEmpty()) {
            $wardBeds = Bed::with([
                'department',
                'currentAssignment.admission.patient',
                'currentAssignment.admission.attendingDoctor.staffProfile',
                'currentAssignment.admission.nursingNotes' => fn($q) => $q->latest('recorded_at')->take(1),
            ])->orderBy('bed_number')->take(10)->get();
        }

        // Ward Bed Summary Metrics
        $totalWardBeds = $wardBeds->count();
        $occupiedBedsCount = $wardBeds->where('status', 'occupied')->count();
        $availableBedsCount = $wardBeds->where('status', 'available')->count();
        $cleaningBedsCount = $wardBeds->where('status', 'cleaning')->count();
        $maintenanceBedsCount = $wardBeds->where('status', 'maintenance')->count();
        $occupancyPercentage = $totalWardBeds > 0 ? (int) round(($occupiedBedsCount / $totalWardBeds) * 100) : 0;

        // 4. Assigned Inpatients & Active Admissions
        $activeAdmissionsQuery = Admission::where('status', 'admitted')
            ->with([
                'patient.contacts',
                'department',
                'attendingDoctor.staffProfile',
                'currentBedAssignment.bed.department',
                'nursingNotes' => fn($q) => $q->latest('recorded_at')->take(5),
                'medicationAdministrations' => fn($q) => $q->latest('administered_at')->take(5),
                'treatmentPlans' => fn($q) => $q->where('status', 'active'),
                'visit.consultation.prescription.items',
            ])
            ->orderBy('admission_date', 'desc');

        if ($selectedDeptId) {
            $activeAdmissionsQuery->where('department_id', $selectedDeptId);
        }

        if ($searchTerm) {
            $activeAdmissionsQuery->where(function (Builder $q) use ($searchTerm) {
                $q->where('admission_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('patient', function (Builder $pq) use ($searchTerm) {
                      $pq->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('patient_code', 'like', "%{$searchTerm}%")
                        ->orWhere('phone', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('currentBedAssignment.bed', function (Builder $bq) use ($searchTerm) {
                      $bq->where('bed_number', 'like', "%{$searchTerm}%")
                        ->orWhere('room_number', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $activeAdmissions = $activeAdmissionsQuery->get();

        // 5. Recent Bedside Vitals Ledger across Inpatient Ward
        $recentVitalsQuery = NursingNote::with([
            'nurse.staffProfile',
            'admission.patient',
            'admission.currentBedAssignment.bed',
            'admission.department',
        ])
        ->orderBy('recorded_at', 'desc');

        if ($selectedShift) {
            $recentVitalsQuery->where('shift', $selectedShift);
        }

        if ($selectedDeptId) {
            $recentVitalsQuery->whereHas('admission', fn($q) => $q->where('department_id', $selectedDeptId));
        }

        $recentVitals = $recentVitalsQuery->take(15)->get();

        // 6. Recent Medication Administration Records (MAR) Ledger
        $recentMarQuery = MedicationAdministration::with([
            'nurse.staffProfile',
            'admission.patient',
            'admission.currentBedAssignment.bed',
            'prescriptionItem',
        ])
        ->orderBy('administered_at', 'desc');

        if ($selectedDeptId) {
            $recentMarQuery->whereHas('admission', fn($q) => $q->where('department_id', $selectedDeptId));
        }

        $recentMedicationAdministrations = $recentMarQuery->take(15)->get();

        // 7. Today's MAR Doses Statistics
        $todayMarDosesCount = MedicationAdministration::whereDate('administered_at', today())->count();

        // 8. Pending Nursing Tasks & Critical Alerts
        $pendingTasks = $this->calculatePendingTasks($activeAdmissions, $wardBeds);

        // 9. All Nurses for Supervisor Switcher (if Super Admin or Admin)
        $allNurses = $currentUser->hasRole(['super_admin', 'administrator'])
            ? User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->with('staffProfile.department')->get()
            : collect([$targetNurse]);

        // 10. Summary Metrics Card Data (Bento Grid)
        $metrics = [
            'total_inpatients' => $activeAdmissions->count(),
            'occupied_beds' => $occupiedBedsCount,
            'total_beds' => $totalWardBeds,
            'occupancy_percentage' => $occupancyPercentage,
            'available_beds' => $availableBedsCount,
            'cleaning_beds' => $cleaningBedsCount,
            'maintenance_beds' => $maintenanceBedsCount,
            'vitals_due_count' => count($pendingTasks['vitals_due']),
            'medications_due_count' => count($pendingTasks['medications_due']),
            'critical_alerts_count' => count($pendingTasks['abnormal_vitals']),
            'today_mar_doses' => $todayMarDosesCount,
            'current_shift_name' => $shiftInfo['shift_label'],
            'current_shift_type' => $shiftInfo['shift_type'],
            'current_shift_hours' => $shiftInfo['hours'],
            'on_duty_doctor' => $shiftInfo['on_duty_doctor'],
        ];

        return [
            'nurse' => $targetNurse,
            'allNurses' => $allNurses,
            'shiftInfo' => $shiftInfo,
            'clinicalDepartments' => $clinicalDepartments,
            'selectedDeptId' => $selectedDeptId,
            'selectedShift' => $selectedShift,
            'searchTerm' => $searchTerm,
            'wardBeds' => $wardBeds,
            'activeAdmissions' => $activeAdmissions,
            'recentVitals' => $recentVitals,
            'recentMedicationAdministrations' => $recentMedicationAdministrations,
            'pendingTasks' => $pendingTasks,
            'metrics' => $metrics,
        ];
    }

    /**
     * Record patient bedside vital signs and nursing observation note.
     */
    public function recordVitals(Admission $admission, array $data, User $nurse): NursingNote
    {
        if ($admission->status !== 'admitted') {
            throw new InvalidArgumentException("Cannot record bedside vitals for non-active admission #{$admission->admission_number}.");
        }

        return DB::transaction(function () use ($admission, $data, $nurse) {
            $shift = !empty($data['shift']) ? $data['shift'] : $this->getCurrentShiftType();
            $recordedAt = !empty($data['recorded_at']) ? Carbon::parse($data['recorded_at']) : now();
            $tempUnit = !empty($data['temperature_unit']) ? $data['temperature_unit'] : '°F';
            $weightUnit = !empty($data['weight_unit']) ? $data['weight_unit'] : 'kg';

            $note = new NursingNote([
                'admission_id' => $admission->id,
                'nurse_id' => $nurse->id,
                'shift' => $shift,
                'temperature' => isset($data['temperature']) && $data['temperature'] !== '' ? (float) $data['temperature'] : null,
                'temperature_unit' => $tempUnit,
                'blood_pressure' => !empty($data['blood_pressure']) ? trim($data['blood_pressure']) : null,
                'blood_pressure_unit' => 'mmHg',
                'pulse_rate' => isset($data['pulse_rate']) && $data['pulse_rate'] !== '' ? (int) $data['pulse_rate'] : null,
                'pulse_rate_unit' => 'bpm',
                'respiratory_rate' => isset($data['respiratory_rate']) && $data['respiratory_rate'] !== '' ? (int) $data['respiratory_rate'] : null,
                'respiratory_rate_unit' => 'breaths/min',
                'spo2' => isset($data['spo2']) && $data['spo2'] !== '' ? (float) $data['spo2'] : null,
                'spo2_unit' => '%',
                'weight' => isset($data['weight']) && $data['weight'] !== '' ? (float) $data['weight'] : null,
                'weight_unit' => $weightUnit,
                'observations' => trim($data['observations']),
                'recorded_at' => $recordedAt,
            ]);

            // Flag unusual values for authorized clinical review without automated medical diagnosis
            $clinicalFlags = $note->evaluateClinicalFlags();
            if (!empty($clinicalFlags)) {
                $note->is_flagged_abnormal = true;
                $note->clinical_flags = $clinicalFlags;
                $note->clinical_review_status = 'flagged_for_review';
            } else {
                $note->is_flagged_abnormal = false;
                $note->clinical_flags = null;
                $note->clinical_review_status = 'normal';
            }

            $note->save();

            // Audit Trail
            AuditLog::create([
                'user_id' => $nurse->id,
                'event' => 'nursing_vitals.recorded',
                'auditable_type' => NursingNote::class,
                'auditable_id' => $note->id,
                'old_values' => null,
                'new_values' => [
                    'admission_id' => $admission->id,
                    'admission_number' => $admission->admission_number,
                    'patient_id' => $admission->patient_id,
                    'shift' => $note->shift,
                    'temperature' => $note->temperature,
                    'blood_pressure' => $note->blood_pressure,
                    'pulse_rate' => $note->pulse_rate,
                    'respiratory_rate' => $note->respiratory_rate,
                    'spo2' => $note->spo2,
                    'weight' => $note->weight,
                    'is_flagged_abnormal' => $note->is_flagged_abnormal,
                    'clinical_flags' => $note->clinical_flags,
                    'recorded_at' => $note->recorded_at->toDateTimeString(),
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $note;
        });
    }

    /**
     * Authorized clinical physician / supervisor reviews a flagged nursing observation.
     */
    public function reviewObservation(NursingNote $note, User $reviewer, ?string $reviewNotes = null): NursingNote
    {
        return DB::transaction(function () use ($note, $reviewer, $reviewNotes) {
            $lockedNote = NursingNote::where('id', $note->id)->lockForUpdate()->firstOrFail();

            $lockedNote->update([
                'clinical_review_status' => 'reviewed',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $reviewNotes ? trim($reviewNotes) : null,
            ]);

            AuditLog::create([
                'user_id' => $reviewer->id,
                'event' => 'nursing_observation.reviewed',
                'auditable_type' => NursingNote::class,
                'auditable_id' => $lockedNote->id,
                'old_values' => ['clinical_review_status' => 'flagged_for_review'],
                'new_values' => [
                    'clinical_review_status' => 'reviewed',
                    'reviewed_by' => $reviewer->id,
                    'reviewed_at' => now()->toDateTimeString(),
                    'review_notes' => $reviewNotes,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedNote;
        });
    }

    /**
     * Retrieve patient vital trends and historical analytics for an admission.
     */
    public function getPatientVitalTrends(Admission $admission): array
    {
        $admission->loadMissing([
            'patient',
            'department',
            'attendingDoctor.staffProfile',
            'currentBedAssignment.bed',
        ]);

        $notes = NursingNote::where('admission_id', $admission->id)
            ->with(['nurse.staffProfile', 'reviewedBy.staffProfile'])
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Statistical trends computation
        $temps = $notes->pluck('temperature')->filter()->values();
        $pulses = $notes->pluck('pulse_rate')->filter()->values();
        $spo2s = $notes->pluck('spo2')->filter()->values();
        $resps = $notes->pluck('respiratory_rate')->filter()->values();
        $weights = $notes->pluck('weight')->filter()->values();

        // Systolic and Diastolic series
        $systolics = collect();
        $diastolics = collect();
        foreach ($notes as $n) {
            if ($n->blood_pressure && str_contains($n->blood_pressure, '/')) {
                $bpParts = explode('/', $n->blood_pressure);
                $systolics->push((int) trim($bpParts[0]));
                $diastolics->push((int) trim($bpParts[1]));
            }
        }

        $latest = $notes->last();

        $stats = [
            'total_observations' => $notes->count(),
            'flagged_observations' => $notes->where('is_flagged_abnormal', true)->count(),
            'latest' => $latest,
            'temperature' => [
                'current' => $latest?->temperature,
                'min' => $temps->min(),
                'max' => $temps->max(),
                'avg' => $temps->isNotEmpty() ? round($temps->avg(), 1) : null,
                'unit' => $latest?->temperature_unit ?? '°F',
            ],
            'pulse' => [
                'current' => $latest?->pulse_rate,
                'min' => $pulses->min(),
                'max' => $pulses->max(),
                'avg' => $pulses->isNotEmpty() ? (int) round($pulses->avg()) : null,
                'unit' => 'bpm',
            ],
            'spo2' => [
                'current' => $latest?->spo2,
                'min' => $spo2s->min(),
                'max' => $spo2s->max(),
                'avg' => $spo2s->isNotEmpty() ? round($spo2s->avg(), 1) : null,
                'unit' => '%',
            ],
            'respiratory' => [
                'current' => $latest?->respiratory_rate,
                'min' => $resps->min(),
                'max' => $resps->max(),
                'avg' => $resps->isNotEmpty() ? (int) round($resps->avg()) : null,
                'unit' => 'breaths/min',
            ],
            'blood_pressure' => [
                'current' => $latest?->blood_pressure,
                'sys_max' => $systolics->max(),
                'sys_min' => $systolics->min(),
                'dia_max' => $diastolics->max(),
                'dia_min' => $diastolics->min(),
                'unit' => 'mmHg',
            ],
            'weight' => [
                'current' => $latest?->weight,
                'min' => $weights->min(),
                'max' => $weights->max(),
                'unit' => 'kg',
            ],
        ];

        return [
            'admission' => $admission,
            'patient' => $admission->patient,
            'notes' => $notes->sortByDesc('recorded_at'),
            'timelineNotes' => $notes, // chronological for charts
            'stats' => $stats,
        ];
    }

    /**
     * Record a medication administration event (eMAR).
     */
    public function recordMedicationAdministration(Admission $admission, array $data, User $nurse): MedicationAdministration
    {
        if ($admission->status !== 'admitted') {
            throw new InvalidArgumentException("Cannot record medication administration for non-active admission #{$admission->admission_number}.");
        }

        return DB::transaction(function () use ($admission, $data, $nurse) {
            $prescriptionItem = null;
            $prescriptionId = !empty($data['prescription_id']) ? (int) $data['prescription_id'] : null;

            if (!empty($data['prescription_item_id'])) {
                $prescriptionItem = PrescriptionItem::with('prescription.consultation.visit')->find($data['prescription_item_id']);
                if ($prescriptionItem) {
                    $prescription = $prescriptionItem->prescription;
                    if ($prescription && $prescription->status !== 'approved') {
                        throw new InvalidArgumentException("Cannot administer medication from an unapproved doctor prescription (status: {$prescription->status}).");
                    }
                    if ($prescription) {
                        $prescriptionId = $prescription->id;
                    }
                    if (empty($data['medicine_name'])) {
                        $data['medicine_name'] = $prescriptionItem->medicine_name;
                    }
                }
            }

            $administeredAt = !empty($data['administered_at']) ? Carbon::parse($data['administered_at']) : now();
            $shift = !empty($data['scheduled_shift']) ? $data['scheduled_shift'] : $this->getShiftFromTime($administeredAt);
            $status = $data['status'] ?? 'administered';

            // Prevent duplicate administration records where applicable
            if ($status === 'administered' && $prescriptionItem) {
                $isPrn = str_contains(strtoupper($prescriptionItem->frequency ?? ''), 'PRN') 
                    || str_contains(strtoupper($prescriptionItem->instructions ?? ''), 'PRN')
                    || str_contains(strtoupper($prescriptionItem->frequency ?? ''), 'SOS');

                if (!$isPrn) {
                    $existing = MedicationAdministration::where('admission_id', $admission->id)
                        ->where('prescription_item_id', $prescriptionItem->id)
                        ->where('status', 'administered')
                        ->where('scheduled_shift', $shift)
                        ->whereDate('administered_at', $administeredAt->toDateString())
                        ->exists();

                    if ($existing) {
                        $shiftLabel = ucfirst($shift);
                        throw new InvalidArgumentException("Medication '{$data['medicine_name']}' has already been recorded as administered for the {$shiftLabel} shift on {$administeredAt->format('d M Y')}. If correcting a dose, use the amendment option.");
                    }
                }
            }

            $mar = MedicationAdministration::create([
                'admission_id' => $admission->id,
                'prescription_id' => $prescriptionId,
                'prescription_item_id' => $prescriptionItem?->id,
                'medicine_name' => trim($data['medicine_name']),
                'dose_given' => trim($data['dose_given']),
                'route' => $data['route'] ?? 'Oral',
                'scheduled_time' => !empty($data['scheduled_time']) ? Carbon::parse($data['scheduled_time']) : null,
                'scheduled_shift' => $shift,
                'nurse_id' => $nurse->id,
                'status' => $status,
                'withheld_reason' => !empty($data['withheld_reason']) ? trim($data['withheld_reason']) : null,
                'remarks' => !empty($data['remarks']) ? trim($data['remarks']) : null,
                'administered_at' => $administeredAt,
            ]);

            // Audit Trail
            AuditLog::create([
                'user_id' => $nurse->id,
                'event' => 'medication.administered',
                'auditable_type' => MedicationAdministration::class,
                'auditable_id' => $mar->id,
                'old_values' => null,
                'new_values' => [
                    'admission_id' => $admission->id,
                    'admission_number' => $admission->admission_number,
                    'prescription_item_id' => $mar->prescription_item_id,
                    'medicine_name' => $mar->medicine_name,
                    'dose_given' => $mar->dose_given,
                    'route' => $mar->route,
                    'scheduled_shift' => $mar->scheduled_shift,
                    'status' => $mar->status,
                    'withheld_reason' => $mar->withheld_reason,
                    'remarks' => $mar->remarks,
                    'administered_at' => $mar->administered_at->toDateTimeString(),
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $mar;
        });
    }

    /**
     * Maintain correction history when an authorized nurse/doctor amends an eMAR dose record.
     */
    public function correctMedicationAdministration(MedicationAdministration $mar, array $data, User $actor): MedicationAdministration
    {
        return DB::transaction(function () use ($mar, $data, $actor) {
            $lockedMar = MedicationAdministration::where('id', $mar->id)->lockForUpdate()->firstOrFail();

            $oldValues = [
                'dose_given' => $lockedMar->dose_given,
                'route' => $lockedMar->route,
                'status' => $lockedMar->status,
                'withheld_reason' => $lockedMar->withheld_reason,
                'remarks' => $lockedMar->remarks,
                'administered_at' => $lockedMar->administered_at?->toDateTimeString(),
            ];

            $administeredAt = !empty($data['administered_at']) ? Carbon::parse($data['administered_at']) : $lockedMar->administered_at;

            $lockedMar->update([
                'dose_given' => trim($data['dose_given']),
                'route' => $data['route'] ?? $lockedMar->route,
                'status' => $data['status'],
                'withheld_reason' => !empty($data['withheld_reason']) ? trim($data['withheld_reason']) : null,
                'remarks' => !empty($data['remarks']) ? trim($data['remarks']) : null,
                'administered_at' => $administeredAt,
                'is_corrected' => true,
                'correction_reason' => trim($data['correction_reason']),
                'corrected_by' => $actor->id,
                'corrected_at' => now(),
            ]);

            $newValues = [
                'dose_given' => $lockedMar->dose_given,
                'route' => $lockedMar->route,
                'status' => $lockedMar->status,
                'withheld_reason' => $lockedMar->withheld_reason,
                'remarks' => $lockedMar->remarks,
                'administered_at' => $lockedMar->administered_at?->toDateTimeString(),
                'correction_reason' => $lockedMar->correction_reason,
                'corrected_by' => $actor->id,
                'corrected_at' => now()->toDateTimeString(),
            ];

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'medication.corrected',
                'auditable_type' => MedicationAdministration::class,
                'auditable_id' => $lockedMar->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedMar;
        });
    }

    /**
     * Retrieve patient eMAR schedule and daily flowsheet across 8-hour shifts.
     */
    public function getPatientMarSchedule(Admission $admission, ?string $targetDate = null): array
    {
        $admission->loadMissing([
            'patient',
            'department',
            'attendingDoctor.staffProfile',
            'currentBedAssignment.bed.department',
        ]);

        $date = $targetDate ? Carbon::parse($targetDate)->startOfDay() : today()->startOfDay();

        // Authorized (approved) prescriptions only
        $approvedPrescriptions = Prescription::where('status', 'approved')
            ->where(function (Builder $q) use ($admission) {
                if ($admission->visit_id) {
                    $q->whereHas('consultation', fn($cq) => $cq->where('visit_id', $admission->visit_id));
                }
                $q->orWhereHas('consultation.visit', fn($vq) => $vq->where('patient_id', $admission->patient_id));
            })
            ->with(['consultation.doctor.staffProfile', 'consultation.diagnoses', 'items'])
            ->latest('created_at')
            ->get();

        // Administrations recorded for the selected date
        $dayAdministrations = MedicationAdministration::where('admission_id', $admission->id)
            ->whereDate('administered_at', $date)
            ->with(['nurse.staffProfile', 'correctedBy.staffProfile', 'prescriptionItem'])
            ->orderBy('administered_at', 'asc')
            ->get();

        // Build schedule grid across 3 shifts
        $scheduledItems = [];
        $totalScheduledSlots = 0;
        $administeredCount = 0;
        $withheldCount = 0;
        $missedCount = 0;
        $refusedCount = 0;

        foreach ($approvedPrescriptions as $prescription) {
            foreach ($prescription->items as $item) {
                $shifts = $this->parseFrequencyToShifts($item->frequency);
                $itemSlots = [];

                foreach (['morning', 'evening', 'night'] as $shiftKey) {
                    $isScheduled = in_array($shiftKey, $shifts);
                    if ($isScheduled) {
                        $totalScheduledSlots++;
                    }

                    $record = $dayAdministrations->first(function ($adm) use ($item, $shiftKey) {
                        return $adm->prescription_item_id == $item->id && $adm->scheduled_shift === $shiftKey;
                    });

                    if ($record) {
                        if ($record->status === 'administered') $administeredCount++;
                        elseif ($record->status === 'withheld') $withheldCount++;
                        elseif ($record->status === 'missed') $missedCount++;
                        elseif ($record->status === 'refused') $refusedCount++;
                    }

                    $itemSlots[$shiftKey] = [
                        'is_scheduled' => $isScheduled,
                        'record' => $record,
                        'status' => $record ? $record->status : ($isScheduled ? 'due' : 'not_scheduled'),
                    ];
                }

                $scheduledItems[] = [
                    'prescription' => $prescription,
                    'item' => $item,
                    'shifts' => $shifts,
                    'slots' => $itemSlots,
                ];
            }
        }

        $pendingCount = max(0, $totalScheduledSlots - ($administeredCount + $withheldCount + $missedCount + $refusedCount));

        // Full administration history for this admission
        $history = MedicationAdministration::where('admission_id', $admission->id)
            ->with(['nurse.staffProfile', 'correctedBy.staffProfile', 'prescriptionItem', 'prescription'])
            ->orderBy('administered_at', 'desc')
            ->get();

        return [
            'admission' => $admission,
            'patient' => $admission->patient,
            'targetDate' => $date,
            'approvedPrescriptions' => $approvedPrescriptions,
            'scheduledItems' => $scheduledItems,
            'stats' => [
                'total_scheduled' => $totalScheduledSlots,
                'administered' => $administeredCount,
                'withheld' => $withheldCount,
                'missed' => $missedCount,
                'refused' => $refusedCount,
                'pending' => $pendingCount,
                'total_history' => $history->count(),
            ],
            'history' => $history,
        ];
    }

    /**
     * Map clinical frequency strings to ward shift windows.
     */
    public function parseFrequencyToShifts(?string $frequency): array
    {
        if (!$frequency) {
            return ['morning'];
        }

        $f = strtoupper(trim($frequency));

        // Format 1-0-1 or 1+0+1
        if (preg_match('/^(\d)\s*[-+]\s*(\d)\s*[-+]\s*(\d)$/', $f, $matches)) {
            $shifts = [];
            if ((int)$matches[1] > 0) $shifts[] = 'morning';
            if ((int)$matches[2] > 0) $shifts[] = 'evening';
            if ((int)$matches[3] > 0) $shifts[] = 'night';
            return !empty($shifts) ? $shifts : ['morning'];
        }

        // Format 1-0 or 1-1
        if (preg_match('/^(\d)\s*[-+]\s*(\d)$/', $f, $matches)) {
            $shifts = [];
            if ((int)$matches[1] > 0) $shifts[] = 'morning';
            if ((int)$matches[2] > 0) $shifts[] = 'night';
            return !empty($shifts) ? $shifts : ['morning'];
        }

        return match ($f) {
            'TDS', 'TID' => ['morning', 'evening', 'night'],
            'BD', 'BID' => ['morning', 'night'],
            'OD', 'QD' => ['morning'],
            'QDS', 'QID' => ['morning', 'evening', 'night'],
            'PRN', 'SOS' => ['morning', 'evening', 'night'],
            'STAT' => ['morning'],
            default => ['morning', 'night'],
        };
    }

    /**
     * Determine shift window from timestamp.
     */
    public function getShiftFromTime(Carbon $time): string
    {
        $hour = (int) $time->format('H');
        if ($hour >= 8 && $hour < 16) {
            return 'morning';
        } elseif ($hour >= 16 && $hour < 24) {
            return 'evening';
        } else {
            return 'night';
        }
    }

    /**
     * Master Inpatient eMAR Ledger across Ward.
     */
    public function getMarLedger(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MedicationAdministration::with([
            'admission.patient',
            'admission.currentBedAssignment.bed',
            'admission.department',
            'nurse.staffProfile',
            'correctedBy.staffProfile',
            'prescriptionItem',
            'prescription',
        ])
        ->filter($filters)
        ->recent();

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Update bed cleaning / sanitization readiness status.
     */
    public function updateBedReadiness(Bed $bed, string $newStatus, User $user, ?string $notes = null): Bed
    {
        return DB::transaction(function () use ($bed, $newStatus, $user, $notes) {
            $lockedBed = Bed::where('id', $bed->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $lockedBed->status;

            if ($oldStatus === 'occupied' && $newStatus === 'available') {
                throw new InvalidArgumentException("Cannot mark an occupied bed as available without discharging or transferring the patient first.");
            }

            $lockedBed->update(['status' => $newStatus]);

            BedStatusHistory::create([
                'bed_id' => $lockedBed->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => $user->id,
                'reason' => 'Nursing ward bed readiness update',
                'notes' => $notes,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'bed.readiness_updated',
                'auditable_type' => Bed::class,
                'auditable_id' => $lockedBed->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => [
                    'status' => $newStatus,
                    'notes' => $notes,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            return $lockedBed;
        });
    }

    /**
     * Retrieve complete patient admission nursing dossier (Read-Only clinical records).
     */
    public function getAdmissionNursingDossier(Admission $admission, User $nurse): array
    {
        $admission->loadMissing([
            'patient.contacts',
            'department',
            'attendingDoctor.staffProfile',
            'currentBedAssignment.bed.department',
            'bedAssignments.bed',
            'nursingNotes.nurse.staffProfile',
            'medicationAdministrations.nurse.staffProfile',
            'medicationAdministrations.prescriptionItem',
            'treatmentPlans.records',
        ]);

        // Approved Prescriptions for this Inpatient (Read-Only)
        $approvedPrescriptions = Prescription::where('status', 'approved')
            ->where(function (Builder $q) use ($admission) {
                if ($admission->visit_id) {
                    $q->whereHas('consultation', fn($cq) => $cq->where('visit_id', $admission->visit_id));
                }
                $q->orWhereHas('consultation.visit', fn($vq) => $vq->where('patient_id', $admission->patient_id));
            })
            ->with(['consultation.doctor.staffProfile', 'consultation.diagnoses', 'items'])
            ->latest('created_at')
            ->get();

        // Latest Vitals Snapshot
        $latestVitals = $admission->nursingNotes->sortByDesc('recorded_at')->first();

        return [
            'admission' => $admission,
            'patient' => $admission->patient,
            'bed' => $admission->currentBed,
            'latestVitals' => $latestVitals,
            'nursingNotes' => $admission->nursingNotes->sortByDesc('recorded_at'),
            'medicationAdministrations' => $admission->medicationAdministrations->sortByDesc('administered_at'),
            'treatmentPlans' => $admission->treatmentPlans,
            'approvedPrescriptions' => $approvedPrescriptions,
        ];
    }

    /**
     * Search active inpatients for quick bedside lookup.
     */
    public function searchInpatients(string $term, User $nurse): Collection
    {
        return Admission::where('status', 'admitted')
            ->where(function (Builder $q) use ($term) {
                $q->where('admission_number', 'like', "%{$term}%")
                  ->orWhereHas('patient', function (Builder $pq) use ($term) {
                      $pq->where('name', 'like', "%{$term}%")
                        ->orWhere('patient_code', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                  })
                  ->orWhereHas('currentBedAssignment.bed', function (Builder $bq) use ($term) {
                      $bq->where('bed_number', 'like', "%{$term}%")
                        ->orWhere('room_number', 'like', "%{$term}%");
                  });
            })
            ->with([
                'patient',
                'department',
                'currentBedAssignment.bed',
                'attendingDoctor',
                'nursingNotes' => fn($q) => $q->latest('recorded_at')->take(1),
            ])
            ->take(10)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve target nurse account.
     */
    protected function resolveTargetNurse(User $currentUser, array $filters = []): User
    {
        if ($currentUser->hasRole(['super_admin', 'administrator']) && !empty($filters['nurse_id'])) {
            $candidate = User::where('id', $filters['nurse_id'])
                ->whereHas('roles', fn($q) => $q->where('name', 'nurse'))
                ->first();

            if ($candidate) {
                return $candidate;
            }
        }

        if ($currentUser->hasRole('nurse')) {
            return $currentUser;
        }

        // Fallback for admin or supervisor viewing ward station
        $defaultNurse = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->first();

        return $defaultNurse ?? $currentUser;
    }

    /**
     * Resolve shift details, schedules, and on-duty escalation contacts.
     */
    protected function resolveShiftInformation(User $nurse): array
    {
        $today = today()->toDateString();
        $currentHour = (int) now()->format('H');

        $activeShiftType = match (true) {
            $currentHour >= 8 && $currentHour < 16 => 'morning',
            $currentHour >= 16 && $currentHour < 24 => 'evening',
            default => 'night',
        };

        // Check assigned ShiftSchedule in DB
        $todaySchedule = null;
        if ($nurse->staffProfile) {
            $todaySchedule = ShiftSchedule::where('staff_profile_id', $nurse->staffProfile->id)
                ->where('shift_date', $today)
                ->first();
        }

        $shiftType = $todaySchedule?->shift_type ?? $activeShiftType;

        $hours = match ($shiftType) {
            'morning' => '08:00 AM – 04:00 PM',
            'evening' => '04:00 PM – 12:00 AM',
            'night' => '12:00 AM – 08:00 AM',
            default => '08:00 AM – 04:00 PM',
        };

        $shiftLabel = match ($shiftType) {
            'morning' => 'Morning Shift (8 AM – 4 PM)',
            'evening' => 'Evening Shift (4 PM – 12 AM)',
            'night' => 'Night Shift (12 AM – 8 AM)',
            default => ucfirst($shiftType),
        };

        // On-duty Emergency Doctor for this shift
        $dayName = strtolower(now()->format('l'));
        $onDutyDoctorSchedule = DoctorSchedule::where('day_of_week', $dayName)
            ->where('is_active', true)
            ->with(['doctor.staffProfile', 'department'])
            ->first();

        $onDutyDoctorName = $onDutyDoctorSchedule?->doctor?->name
            ?? User::whereHas('roles', fn($q) => $q->where('name', 'doctor'))->first()?->name
            ?? 'Dr. On-Duty Emergency Officer';

        // Upcoming shifts for this nurse
        $upcomingShifts = collect();
        if ($nurse->staffProfile) {
            $upcomingShifts = ShiftSchedule::where('staff_profile_id', $nurse->staffProfile->id)
                ->where('shift_date', '>=', $today)
                ->with('department')
                ->orderBy('shift_date', 'asc')
                ->take(7)
                ->get();
        }

        // Peer nurses on duty today
        $peerNurses = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))
            ->where('id', '!=', $nurse->id)
            ->with('staffProfile')
            ->take(5)
            ->get();

        return [
            'shift_type' => $shiftType,
            'shift_label' => $shiftLabel,
            'hours' => $hours,
            'today_schedule' => $todaySchedule,
            'on_duty_doctor' => $onDutyDoctorName,
            'upcoming_shifts' => $upcomingShifts,
            'peer_nurses' => $peerNurses,
            'date_formatted' => now()->format('l, d F Y'),
        ];
    }

    /**
     * Compute current shift slug.
     */
    protected function getCurrentShiftType(): string
    {
        $hour = (int) now()->format('H');

        return match (true) {
            $hour >= 8 && $hour < 16 => 'morning',
            $hour >= 16 && $hour < 24 => 'evening',
            default => 'night',
        };
    }

    /**
     * Calculate pending nursing tasks and clinical alerts.
     */
    protected function calculatePendingTasks(Collection $activeAdmissions, Collection $wardBeds): array
    {
        $vitalsDue = [];
        $medicationsDue = [];
        $abnormalVitals = [];
        $unassignedAdmissions = [];

        $eightHoursAgo = now()->subHours(8);

        foreach ($activeAdmissions as $admission) {
            // 1. Bed assignment check
            if (!$admission->currentBedAssignment) {
                $unassignedAdmissions[] = [
                    'admission' => $admission,
                    'patient' => $admission->patient,
                    'reason' => 'Patient admitted but bed is pending assignment',
                ];
            }

            // 2. Vitals check (last recorded > 8 hours ago or never)
            $latestVital = $admission->nursingNotes->first();
            if (!$latestVital || $latestVital->recorded_at->lt($eightHoursAgo)) {
                $vitalsDue[] = [
                    'admission' => $admission,
                    'patient' => $admission->patient,
                    'bed' => $admission->currentBed,
                    'last_checked' => $latestVital ? $latestVital->recorded_at->diffForHumans() : 'Never recorded',
                    'hours_overdue' => $latestVital ? (int) $latestVital->recorded_at->diffInHours(now()) : 8,
                ];
            }

            // 3. Clinical abnormal vitals alert
            if ($latestVital && $latestVital->hasAbnormalVitals()) {
                $abnormalVitals[] = [
                    'admission' => $admission,
                    'patient' => $admission->patient,
                    'bed' => $admission->currentBed,
                    'vital' => $latestVital,
                    'is_fever' => $latestVital->isFever(),
                    'is_hypoxia' => $latestVital->isHypoxia(),
                    'is_tachycardia' => $latestVital->isTachycardia(),
                    'is_bradycardia' => $latestVital->isBradycardia(),
                ];
            }

            // 4. Approved prescriptions needing medication administration
            $activeRx = $admission->visit?->consultation?->prescription;
            if ($activeRx && $activeRx->status === 'approved' && $activeRx->items->isNotEmpty()) {
                // Check if any medication was not administered today
                $todayMeds = $admission->medicationAdministrations
                    ->filter(fn($m) => $m->administered_at && $m->administered_at->isToday());

                $medicationsDue[] = [
                    'admission' => $admission,
                    'patient' => $admission->patient,
                    'bed' => $admission->currentBed,
                    'prescription' => $activeRx,
                    'prescribed_items_count' => $activeRx->items->count(),
                    'administered_today_count' => $todayMeds->count(),
                ];
            }
        }

        // 5. Beds requiring cleaning
        $cleaningBeds = $wardBeds->where('status', 'cleaning')->values();

        return [
            'vitals_due' => $vitalsDue,
            'medications_due' => $medicationsDue,
            'abnormal_vitals' => $abnormalVitals,
            'unassigned_admissions' => $unassignedAdmissions,
            'cleaning_beds' => $cleaningBeds,
        ];
    }
}
