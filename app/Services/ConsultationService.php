<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Diagnosis;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\TreatmentPlan;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;

class ConsultationService
{
    /**
     * Create a new consultation for a patient visit.
     *
     * @param Visit $visit
     * @param User $doctor
     * @param array $data
     * @return Consultation
     */
    public function createConsultation(Visit $visit, User $doctor, array $data): Consultation
    {
        return DB::transaction(function () use ($visit, $doctor, $data) {
            $status = $data['status'] ?? 'draft';
            $isFinalizing = $status === 'finalized';

            // 1. Create Consultation Record
            $consultation = Consultation::create([
                'visit_id' => $visit->id,
                'doctor_id' => $doctor->id,
                'chief_complaints' => $data['chief_complaints'],
                'clinical_notes' => $data['clinical_notes'] ?? null,
                'status' => $status,
                'finalized_at' => $isFinalizing ? now() : null,
            ]);

            // 2. Attach Diagnoses
            if (!empty($data['diagnoses']) && is_array($data['diagnoses'])) {
                foreach ($data['diagnoses'] as $diag) {
                    if (!empty($diag['condition_name'])) {
                        $consultation->diagnoses()->create([
                            'condition_name' => trim($diag['condition_name']),
                            'icd_code' => !empty($diag['icd_code']) ? trim($diag['icd_code']) : null,
                            'diagnosis_type' => $diag['diagnosis_type'] ?? 'provisional',
                        ]);
                    }
                }
            }

            // 3. Create Prescription if advice or medications provided
            $hasAdvice = !empty($data['advice']) || !empty($data['follow_up_date']);
            $hasMeds = !empty($data['medications']) && is_array($data['medications']);

            if ($hasAdvice || $hasMeds) {
                $advice = $data['advice'] ?? '';
                if (!empty($data['follow_up_instructions'])) {
                    $advice .= ($advice ? "\n" : '') . 'Follow-up instructions: ' . trim($data['follow_up_instructions']);
                }

                $prescription = $consultation->prescription()->create([
                    'advice' => $advice ?: 'Rest and maintain hydration.',
                    'follow_up_date' => !empty($data['follow_up_date']) ? Carbon::parse($data['follow_up_date'])->toDateString() : null,
                ]);

                if ($hasMeds) {
                    foreach ($data['medications'] as $med) {
                        if (!empty($med['medicine_name'])) {
                            $prescription->items()->create([
                                'medicine_name' => trim($med['medicine_name']),
                                'dosage' => trim($med['dosage'] ?? '1 standard dose'),
                                'frequency' => trim($med['frequency'] ?? 'Once daily'),
                                'duration' => trim($med['duration'] ?? '3 days'),
                                'instructions' => !empty($med['instructions']) ? trim($med['instructions']) : 'Take as directed',
                            ]);
                        }
                    }
                }
            }

            // 4. Handle Inpatient Treatment Plan if patient is admitted
            if (!empty($data['inpatient_treatment_plan']) && $visit->admissions()->where('status', 'admitted')->exists()) {
                $admission = $visit->admissions()->where('status', 'admitted')->latest()->first();
                if ($admission) {
                    TreatmentPlan::create([
                        'admission_id' => $admission->id,
                        'doctor_id' => $doctor->id,
                        'plan_title' => 'Consultation Clinical Plan (' . now()->format('d M Y') . ')',
                        'details' => trim($data['inpatient_treatment_plan']),
                        'status' => 'active',
                    ]);
                }
            }

            // 5. Update Visit & Appointment Status
            if ($isFinalizing) {
                // Outpatient visits transition to completed; inpatient visits remain admitted
                if ($visit->status !== 'admitted') {
                    $visit->update(['status' => 'completed']);
                }

                if ($visit->appointment && $visit->appointment->status !== 'completed') {
                    $visit->appointment->update(['status' => 'completed']);
                }
            } else {
                if ($visit->status === 'waiting') {
                    $visit->update(['status' => 'in_consultation']);
                }
            }

            // 6. Record Audit Log
            AuditLog::create([
                'user_id' => $doctor->id,
                'event' => 'created',
                'auditable_type' => Consultation::class,
                'auditable_id' => $consultation->id,
                'old_values' => null,
                'new_values' => [
                    'status' => $consultation->status,
                    'chief_complaints' => $consultation->chief_complaints,
                    'diagnoses_count' => $consultation->diagnoses()->count(),
                    'has_prescription' => $consultation->prescription()->exists(),
                    'created_by' => $doctor->name,
                ],
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => Request::userAgent() ?? 'Antigravity-HMS',
                'created_at' => now(),
            ]);

            return $consultation->load(['diagnoses', 'prescription.items', 'visit.patient', 'doctor']);
        });
    }

    /**
     * Update an existing consultation (draft edit or finalized correction).
     *
     * @param Consultation $consultation
     * @param User $actor
     * @param array $data
     * @param string|null $correctionReason
     * @return Consultation
     */
    public function updateConsultation(Consultation $consultation, User $actor, array $data, ?string $correctionReason = null): Consultation
    {
        return DB::transaction(function () use ($consultation, $actor, $data, $correctionReason) {
            $isAlreadyFinalized = $consultation->status === 'finalized';
            $targetStatus = $data['status'] ?? $consultation->status;
            $isTransitioningToFinalized = !$isAlreadyFinalized && $targetStatus === 'finalized';

            if ($isAlreadyFinalized && empty($correctionReason) && empty($data['correction_reason'])) {
                throw new InvalidArgumentException('A reason for clinical record correction is mandatory for finalized consultations.');
            }

            $effectiveReason = $correctionReason ?? ($data['correction_reason'] ?? null);

            // Snapshot old values for medical audit trail
            $oldSnapshot = [
                'status' => $consultation->status,
                'chief_complaints' => $consultation->chief_complaints,
                'clinical_notes' => $consultation->clinical_notes,
                'finalized_at' => $consultation->finalized_at?->toIso8601String(),
                'diagnoses' => $consultation->diagnoses->map(fn($d) => [
                    'condition_name' => $d->condition_name,
                    'icd_code' => $d->icd_code,
                    'type' => $d->diagnosis_type,
                ])->toArray(),
                'advice' => $consultation->prescription?->advice,
                'follow_up_date' => $consultation->prescription?->follow_up_date?->toDateString(),
                'medications' => $consultation->prescription?->items->map(fn($i) => [
                    'name' => $i->medicine_name,
                    'dosage' => $i->dosage,
                    'frequency' => $i->frequency,
                    'duration' => $i->duration,
                ])->toArray(),
            ];

            // 1. Update Consultation Attributes
            $consultation->chief_complaints = $data['chief_complaints'];
            $consultation->clinical_notes = $data['clinical_notes'] ?? null;
            $consultation->status = $targetStatus;

            if ($isTransitioningToFinalized) {
                $consultation->finalized_at = now();
            }

            $consultation->save();

            // 2. Sync Diagnoses
            if (isset($data['diagnoses']) && is_array($data['diagnoses'])) {
                $consultation->diagnoses()->delete();
                foreach ($data['diagnoses'] as $diag) {
                    if (!empty($diag['condition_name'])) {
                        $consultation->diagnoses()->create([
                            'condition_name' => trim($diag['condition_name']),
                            'icd_code' => !empty($diag['icd_code']) ? trim($diag['icd_code']) : null,
                            'diagnosis_type' => $diag['diagnosis_type'] ?? 'provisional',
                        ]);
                    }
                }
            }

            // 3. Sync Prescription & Items
            $hasAdvice = !empty($data['advice']) || !empty($data['follow_up_date']);
            $hasMeds = !empty($data['medications']) && is_array($data['medications']);

            if ($hasAdvice || $hasMeds) {
                $advice = $data['advice'] ?? '';
                if (!empty($data['follow_up_instructions'])) {
                    $advice .= ($advice ? "\n" : '') . 'Follow-up instructions: ' . trim($data['follow_up_instructions']);
                }

                $prescription = $consultation->prescription ?: $consultation->prescription()->create([
                    'advice' => $advice ?: 'Rest and maintain hydration.',
                    'follow_up_date' => !empty($data['follow_up_date']) ? Carbon::parse($data['follow_up_date'])->toDateString() : null,
                ]);

                $prescription->update([
                    'advice' => $advice ?: ($prescription->advice ?: 'Rest and maintain hydration.'),
                    'follow_up_date' => !empty($data['follow_up_date']) ? Carbon::parse($data['follow_up_date'])->toDateString() : $prescription->follow_up_date,
                ]);

                if (isset($data['medications']) && is_array($data['medications'])) {
                    $prescription->items()->delete();
                    foreach ($data['medications'] as $med) {
                        if (!empty($med['medicine_name'])) {
                            $prescription->items()->create([
                                'medicine_name' => trim($med['medicine_name']),
                                'dosage' => trim($med['dosage'] ?? '1 standard dose'),
                                'frequency' => trim($med['frequency'] ?? 'Once daily'),
                                'duration' => trim($med['duration'] ?? '3 days'),
                                'instructions' => !empty($med['instructions']) ? trim($med['instructions']) : 'Take as directed',
                            ]);
                        }
                    }
                }
            }

            // 4. Inpatient Treatment Plan
            if (!empty($data['inpatient_treatment_plan']) && $consultation->visit?->admissions()->where('status', 'admitted')->exists()) {
                $admission = $consultation->visit->admissions()->where('status', 'admitted')->latest()->first();
                if ($admission) {
                    TreatmentPlan::create([
                        'admission_id' => $admission->id,
                        'doctor_id' => $actor->id,
                        'plan_title' => 'Consultation Plan Update (' . now()->format('d M Y') . ')',
                        'details' => trim($data['inpatient_treatment_plan']),
                        'status' => 'active',
                    ]);
                }
            }

            // 5. Update Visit & Appointment Status if newly finalized
            if ($isTransitioningToFinalized) {
                $visit = $consultation->visit;
                if ($visit && $visit->status !== 'admitted') {
                    $visit->update(['status' => 'completed']);
                }
                if ($visit && $visit->appointment && $visit->appointment->status !== 'completed') {
                    $visit->appointment->update(['status' => 'completed']);
                }
            }

            // Snapshot new values for audit
            $consultation->refresh();
            $newSnapshot = [
                'status' => $consultation->status,
                'chief_complaints' => $consultation->chief_complaints,
                'clinical_notes' => $consultation->clinical_notes,
                'finalized_at' => $consultation->finalized_at?->toIso8601String(),
                'diagnoses' => $consultation->diagnoses->map(fn($d) => [
                    'condition_name' => $d->condition_name,
                    'icd_code' => $d->icd_code,
                    'type' => $d->diagnosis_type,
                ])->toArray(),
                'advice' => $consultation->prescription?->advice,
                'follow_up_date' => $consultation->prescription?->follow_up_date?->toDateString(),
                'medications' => $consultation->prescription?->items->map(fn($i) => [
                    'name' => $i->medicine_name,
                    'dosage' => $i->dosage,
                    'frequency' => $i->frequency,
                    'duration' => $i->duration,
                ])->toArray(),
            ];

            if ($effectiveReason) {
                $newSnapshot['correction_reason'] = $effectiveReason;
            }

            $event = $isTransitioningToFinalized ? 'finalized' : ($isAlreadyFinalized ? 'corrected' : 'updated');

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => $event,
                'auditable_type' => Consultation::class,
                'auditable_id' => $consultation->id,
                'old_values' => $oldSnapshot,
                'new_values' => $newSnapshot,
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => Request::userAgent() ?? 'Antigravity-HMS',
                'created_at' => now(),
            ]);

            return $consultation->load(['diagnoses', 'prescription.items', 'visit.patient', 'doctor']);
        });
    }

    /**
     * Finalize a draft consultation.
     *
     * @param Consultation $consultation
     * @param User $actor
     * @return Consultation
     */
    public function finalizeConsultation(Consultation $consultation, User $actor): Consultation
    {
        if ($consultation->status === 'finalized') {
            return $consultation;
        }

        if ($consultation->diagnoses()->count() === 0) {
            throw new InvalidArgumentException('At least one clinical diagnosis must be recorded before finalization.');
        }

        return DB::transaction(function () use ($consultation, $actor) {
            $oldStatus = $consultation->status;

            $consultation->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);

            $visit = $consultation->visit;
            if ($visit && $visit->status !== 'admitted') {
                $visit->update(['status' => 'completed']);
            }
            if ($visit && $visit->appointment && $visit->appointment->status !== 'completed') {
                $visit->appointment->update(['status' => 'completed']);
            }

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => 'finalized',
                'auditable_type' => Consultation::class,
                'auditable_id' => $consultation->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => [
                    'status' => 'finalized',
                    'finalized_at' => now()->toIso8601String(),
                    'finalized_by' => $actor->name,
                ],
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => Request::userAgent() ?? 'Antigravity-HMS',
                'created_at' => now(),
            ]);

            return $consultation->refresh();
        });
    }

    /**
     * Build rich clinical consultation dossier including patient history, past visits, and lab orders.
     *
     * @param Consultation $consultation
     * @return array
     */
    public function getConsultationDossier(Consultation $consultation): array
    {
        $consultation->loadMissing([
            'visit.patient.contacts',
            'visit.patient.admissions.currentBedAssignment.bed.department',
            'visit.appointment',
            'doctor.staffProfile.department',
            'diagnoses',
            'prescription.items',
            'labOrders.items.labTestCatalog',
            'labOrders.reports',
            'auditLogs.user',
        ]);

        $patient = $consultation->visit?->patient;

        // Past Consultations for this patient (excluding current one)
        $pastConsultations = $patient
            ? Consultation::whereHas('visit', fn($q) => $q->where('patient_id', $patient->id))
                ->where('id', '!=', $consultation->id)
                ->with(['doctor', 'diagnoses', 'prescription'])
                ->latest('created_at')
                ->take(5)
                ->get()
            : collect();

        // Past Visits for this patient
        $pastVisits = $patient
            ? $patient->visits()
                ->where('id', '!=', $consultation->visit_id)
                ->with('consultation.diagnoses')
                ->latest('visit_date')
                ->take(5)
                ->get()
            : collect();

        // Admissions History
        $admissionsHistory = $patient
            ? $patient->admissions()
                ->with(['currentBedAssignment.bed', 'department', 'dischargeSummary'])
                ->latest('admission_date')
                ->take(3)
                ->get()
            : collect();

        return [
            'consultation' => $consultation,
            'patient' => $patient,
            'visit' => $consultation->visit,
            'doctor' => $consultation->doctor,
            'diagnoses' => $consultation->diagnoses,
            'prescription' => $consultation->prescription,
            'medications' => $consultation->prescription?->items ?? collect(),
            'labOrders' => $consultation->labOrders,
            'auditLogs' => $consultation->auditLogs,
            'pastConsultations' => $pastConsultations,
            'pastVisits' => $pastVisits,
            'admissionsHistory' => $admissionsHistory,
        ];
    }

    /**
     * Get paginated and filtered consultation list.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getConsultationsList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Consultation::query()
            ->with([
                'visit.patient',
                'doctor.staffProfile.department',
                'diagnoses',
                'prescription.items',
            ])
            ->latest('created_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (!empty($filters['q'])) {
            $query->search($filters['q']);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}

