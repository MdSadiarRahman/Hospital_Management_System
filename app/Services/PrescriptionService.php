<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;

class PrescriptionService
{
    /**
     * Create a new prescription linked to a consultation.
     *
     * @param Consultation $consultation
     * @param array $data
     * @param User $doctor
     * @return Prescription
     */
    public function createPrescription(Consultation $consultation, array $data, User $doctor): Prescription
    {
        return DB::transaction(function () use ($consultation, $data, $doctor) {
            $isApproved = ($data['status'] ?? 'draft') === 'approved' || $consultation->status === 'finalized';
            $status = $isApproved ? 'approved' : 'draft';

            $prescriptionNumber = Prescription::generatePrescriptionNumber();

            $prescription = Prescription::create([
                'prescription_number' => $prescriptionNumber,
                'consultation_id' => $consultation->id,
                'advice' => $data['advice'] ?? null,
                'follow_up_date' => !empty($data['follow_up_date']) ? Carbon::parse($data['follow_up_date'])->toDateString() : null,
                'status' => $status,
                'approved_at' => $isApproved ? now() : null,
                'approved_by' => $isApproved ? $doctor->id : null,
            ]);

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (!empty($item['medicine_name'])) {
                        $prescription->items()->create([
                            'medicine_name' => trim($item['medicine_name']),
                            'dosage' => trim($item['dosage'] ?? '1 standard dose'),
                            'frequency' => trim($item['frequency'] ?? 'Once daily'),
                            'duration' => trim($item['duration'] ?? '3 days'),
                            'instructions' => !empty($item['instructions']) ? trim($item['instructions']) : null,
                        ]);
                    }
                }
            }

            // Create Audit Log
            AuditLog::create([
                'user_id' => $doctor->id,
                'event' => 'created',
                'auditable_type' => Prescription::class,
                'auditable_id' => $prescription->id,
                'old_values' => null,
                'new_values' => [
                    'prescription_number' => $prescription->prescription_number,
                    'status' => $prescription->status,
                    'items_count' => $prescription->items()->count(),
                    'consultation_id' => $consultation->id,
                    'doctor_name' => $doctor->name,
                ],
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => Request::userAgent() ?? 'Antigravity-HMS',
                'created_at' => now(),
            ]);

            return $prescription->load(['items', 'consultation.visit.patient', 'consultation.doctor']);
        });
    }

    /**
     * Update an existing prescription (draft edit or approved correction).
     *
     * @param Prescription $prescription
     * @param array $data
     * @param User $actor
     * @param string|null $correctionReason
     * @return Prescription
     */
    public function updatePrescription(Prescription $prescription, array $data, User $actor, ?string $correctionReason = null): Prescription
    {
        return DB::transaction(function () use ($prescription, $data, $actor, $correctionReason) {
            $isAlreadyApproved = $prescription->status === 'approved';
            $targetStatus = $data['status'] ?? $prescription->status;
            $isTransitioningToApproved = !$isAlreadyApproved && $targetStatus === 'approved';

            if ($isAlreadyApproved && empty($correctionReason) && empty($data['correction_reason'])) {
                throw new InvalidArgumentException('A documented clinical reason is mandatory to amend an approved prescription.');
            }

            $effectiveReason = $correctionReason ?? ($data['correction_reason'] ?? null);

            // Snapshot old values
            $oldSnapshot = [
                'prescription_number' => $prescription->prescription_number,
                'status' => $prescription->status,
                'advice' => $prescription->advice,
                'follow_up_date' => $prescription->follow_up_date?->toDateString(),
                'items' => $prescription->items->map(fn($i) => [
                    'name' => $i->medicine_name,
                    'dosage' => $i->dosage,
                    'frequency' => $i->frequency,
                    'duration' => $i->duration,
                    'instructions' => $i->instructions,
                ])->toArray(),
            ];

            // Ensure prescription number exists
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = Prescription::generatePrescriptionNumber();
            }

            $prescription->advice = $data['advice'] ?? null;
            $prescription->follow_up_date = !empty($data['follow_up_date']) ? Carbon::parse($data['follow_up_date'])->toDateString() : null;
            $prescription->status = $targetStatus;

            if ($isTransitioningToApproved) {
                $prescription->approved_at = now();
                $prescription->approved_by = $actor->id;
            }

            $prescription->save();

            // Sync items
            if (isset($data['items']) && is_array($data['items'])) {
                $prescription->items()->delete();
                foreach ($data['items'] as $item) {
                    if (!empty($item['medicine_name'])) {
                        $prescription->items()->create([
                            'medicine_name' => trim($item['medicine_name']),
                            'dosage' => trim($item['dosage'] ?? '1 standard dose'),
                            'frequency' => trim($item['frequency'] ?? 'Once daily'),
                            'duration' => trim($item['duration'] ?? '3 days'),
                            'instructions' => !empty($item['instructions']) ? trim($item['instructions']) : null,
                        ]);
                    }
                }
            }

            $prescription->refresh();

            // Snapshot new values
            $newSnapshot = [
                'prescription_number' => $prescription->prescription_number,
                'status' => $prescription->status,
                'advice' => $prescription->advice,
                'follow_up_date' => $prescription->follow_up_date?->toDateString(),
                'items' => $prescription->items->map(fn($i) => [
                    'name' => $i->medicine_name,
                    'dosage' => $i->dosage,
                    'frequency' => $i->frequency,
                    'duration' => $i->duration,
                    'instructions' => $i->instructions,
                ])->toArray(),
            ];

            if ($effectiveReason) {
                $newSnapshot['correction_reason'] = $effectiveReason;
            }

            $event = $isTransitioningToApproved ? 'approved' : ($isAlreadyApproved ? 'corrected' : 'updated');

            AuditLog::create([
                'user_id' => $actor->id,
                'event' => $event,
                'auditable_type' => Prescription::class,
                'auditable_id' => $prescription->id,
                'old_values' => $oldSnapshot,
                'new_values' => $newSnapshot,
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => Request::userAgent() ?? 'Antigravity-HMS',
                'created_at' => now(),
            ]);

            return $prescription->load(['items', 'consultation.visit.patient', 'consultation.doctor']);
        });
    }

    /**
     * Approve/authorize a draft prescription.
     *
     * @param Prescription $prescription
     * @param User $doctor
     * @return Prescription
     */
    public function approvePrescription(Prescription $prescription, User $doctor): Prescription
    {
        if ($prescription->status === 'approved') {
            return $prescription;
        }

        if ($prescription->items()->count() === 0) {
            throw new InvalidArgumentException('At least one medicine must be prescribed before approving the prescription.');
        }

        return DB::transaction(function () use ($prescription, $doctor) {
            if (empty($prescription->prescription_number)) {
                $prescription->prescription_number = Prescription::generatePrescriptionNumber();
            }

            $prescription->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $doctor->id,
            ]);

            AuditLog::create([
                'user_id' => $doctor->id,
                'event' => 'approved',
                'auditable_type' => Prescription::class,
                'auditable_id' => $prescription->id,
                'old_values' => ['status' => 'draft'],
                'new_values' => [
                    'status' => 'approved',
                    'prescription_number' => $prescription->prescription_number,
                    'approved_at' => now()->toIso8601String(),
                    'approved_by' => $doctor->name,
                ],
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => Request::userAgent() ?? 'Antigravity-HMS',
                'created_at' => now(),
            ]);

            return $prescription->refresh();
        });
    }

    /**
     * Build complete dossier for displaying or printing a prescription.
     *
     * @param Prescription $prescription
     * @return array
     */
    public function getPrescriptionDossier(Prescription $prescription): array
    {
        $prescription->loadMissing([
            'items',
            'consultation.doctor.staffProfile.department',
            'consultation.diagnoses',
            'consultation.visit.patient.contacts',
            'consultation.visit.admission.currentBedAssignment.bed',
            'approvedBy',
            'auditLogs.user',
        ]);

        $patient = $prescription->consultation?->visit?->patient;
        $doctor = $prescription->consultation?->doctor;

        // Historical prescriptions for this patient
        $patientHistory = $patient
            ? Prescription::whereHas('consultation.visit', fn($q) => $q->where('patient_id', $patient->id))
                ->where('id', '!=', $prescription->id)
                ->with(['items', 'consultation.doctor'])
                ->latest('created_at')
                ->take(5)
                ->get()
            : collect();

        return [
            'prescription' => $prescription,
            'items' => $prescription->items,
            'consultation' => $prescription->consultation,
            'patient' => $patient,
            'doctor' => $doctor,
            'diagnoses' => $prescription->consultation?->diagnoses ?? collect(),
            'approvedBy' => $prescription->approvedBy,
            'auditLogs' => $prescription->auditLogs,
            'patientHistory' => $patientHistory,
        ];
    }

    /**
     * Get paginated prescriptions list for staff portal.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPrescriptionsList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Prescription::query()
            ->with([
                'items',
                'consultation.visit.patient',
                'consultation.doctor.staffProfile.department',
                'consultation.diagnoses',
            ])
            ->latest('created_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['doctor_id'])) {
            $query->whereHas('consultation', fn($q) => $q->where('doctor_id', $filters['doctor_id']));
        }

        if (!empty($filters['patient_id'])) {
            $query->whereHas('consultation.visit', fn($q) => $q->where('patient_id', $filters['patient_id']));
        }

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (!empty($filters['q'])) {
            $query->search($filters['q']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get prescriptions belonging to a specific patient.
     *
     * @param Patient $patient
     * @param bool $approvedOnly If true, only authorized/approved prescriptions are visible (Patient Portal rule)
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPatientPrescriptions(Patient $patient, bool $approvedOnly = true, int $perPage = 15): LengthAwarePaginator
    {
        $query = Prescription::query()
            ->whereHas('consultation.visit', fn($q) => $q->where('patient_id', $patient->id))
            ->with([
                'items',
                'consultation.doctor.staffProfile.department',
                'consultation.diagnoses',
                'consultation.visit',
            ])
            ->latest('created_at');

        if ($approvedOnly) {
            $query->where('status', 'approved');
        }

        return $query->paginate($perPage)->withQueryString();
    }
}

