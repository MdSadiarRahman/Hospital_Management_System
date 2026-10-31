<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VisitService
{
    /**
     * Generate the next unique Visit Number for a given date in format VIS-YYYYMMDD-XXXX.
     */
    public function generateVisitNumber(?Carbon $date = null): string
    {
        $targetDate = $date ? $date->copy() : now();
        $datePrefix = 'VIS-' . $targetDate->format('Ymd') . '-';

        // Query highest existing sequence for that day
        $lastVisit = Visit::where('visit_number', 'like', $datePrefix . '%')
            ->orderByRaw('CAST(SUBSTRING(visit_number, 14) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->first();

        $nextSequence = 1;
        if ($lastVisit && preg_match('/^VIS-\d{8}-(\d+)$/', $lastVisit->visit_number, $matches)) {
            $nextSequence = (int)$matches[1] + 1;
        }

        $formattedSeq = str_pad((string)$nextSequence, 4, '0', STR_PAD_LEFT);
        $candidate = $datePrefix . $formattedSeq;

        // Collision safety guard
        while (Visit::where('visit_number', $candidate)->exists()) {
            $nextSequence++;
            $formattedSeq = str_pad((string)$nextSequence, 4, '0', STR_PAD_LEFT);
            $candidate = $datePrefix . $formattedSeq;
        }

        return $candidate;
    }

    /**
     * Create a new patient visit wrapped in a database transaction with audit logging.
     *
     * @param array $data
     * @param User $staffUser
     * @return Visit
     */
    public function createVisit(array $data, User $staffUser): Visit
    {
        return DB::transaction(function () use ($data, $staffUser) {
            $patient = Patient::findOrFail($data['patient_id']);

            $visitDate = !empty($data['visit_date']) 
                ? Carbon::parse($data['visit_date']) 
                : now();

            $visitNumber = $data['visit_number'] ?? $this->generateVisitNumber($visitDate);

            // Double-check visit number uniqueness
            if (Visit::where('visit_number', $visitNumber)->exists()) {
                $visitNumber = $this->generateVisitNumber($visitDate);
            }

            $visit = Visit::create([
                'patient_id' => $patient->id,
                'appointment_id' => $data['appointment_id'] ?? null,
                'visit_number' => $visitNumber,
                'visit_date' => $visitDate,
                'visit_type' => $data['visit_type'] ?? 'regular',
                'triage_level' => $data['triage_level'] ?? 'normal',
                'status' => $data['status'] ?? 'waiting',
            ]);

            // If an appointment was linked, mark it as completed or in progress
            if (!empty($data['appointment_id'])) {
                $appointment = $visit->appointment;
                if ($appointment && $appointment->status === 'pending') {
                    $appointment->update(['status' => 'confirmed']);
                }
            }

            // If initial chief complaints / clinical notes are provided at intake, create initial consultation draft
            if (!empty($data['chief_complaints'])) {
                Consultation::create([
                    'visit_id' => $visit->id,
                    'doctor_id' => $data['doctor_id'] ?? $staffUser->id,
                    'chief_complaints' => $data['chief_complaints'],
                    'clinical_notes' => $data['clinical_notes'] ?? null,
                    'status' => 'draft',
                ]);
            }

            // Create immutable audit history
            AuditLog::create([
                'user_id' => $staffUser->id,
                'event' => 'visit_created',
                'auditable_type' => Visit::class,
                'auditable_id' => $visit->id,
                'old_values' => null,
                'new_values' => [
                    'visit_number' => $visit->visit_number,
                    'patient_id' => $patient->id,
                    'patient_code' => $patient->patient_code,
                    'visit_type' => $visit->visit_type,
                    'triage_level' => $visit->triage_level,
                    'status' => $visit->status,
                    'visit_date' => $visit->visit_date->toIso8601String(),
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Hospital-System/1.0',
            ]);

            return $visit->fresh(['patient', 'appointment', 'consultation']);
        });
    }

    /**
     * Update visit record, enforcing patient immutability and maintaining audit history.
     *
     * @param Visit $visit
     * @param array $data
     * @param User $staffUser
     * @return Visit
     */
    public function updateVisit(Visit $visit, array $data, User $staffUser): Visit
    {
        return DB::transaction(function () use ($visit, $data, $staffUser) {
            // Business rule: Visits must remain linked to the same patient
            if (isset($data['patient_id']) && (int)$data['patient_id'] !== (int)$visit->patient_id) {
                throw new InvalidArgumentException('Visits cannot be transferred or reassigned to a different patient.');
            }

            $oldValues = [
                'visit_type' => $visit->visit_type,
                'triage_level' => $visit->triage_level,
                'status' => $visit->status,
                'visit_date' => $visit->visit_date?->toIso8601String(),
            ];

            $updatePayload = [];
            if (isset($data['visit_type'])) {
                $updatePayload['visit_type'] = $data['visit_type'];
            }
            if (isset($data['triage_level'])) {
                $updatePayload['triage_level'] = $data['triage_level'];
            }
            if (isset($data['status'])) {
                $updatePayload['status'] = $data['status'];
            }
            if (!empty($data['visit_date'])) {
                $updatePayload['visit_date'] = Carbon::parse($data['visit_date']);
            }

            $visit->update($updatePayload);

            // Audit log
            AuditLog::create([
                'user_id' => $staffUser->id,
                'event' => 'visit_updated',
                'auditable_type' => Visit::class,
                'auditable_id' => $visit->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'visit_type' => $visit->visit_type,
                    'triage_level' => $visit->triage_level,
                    'status' => $visit->status,
                    'visit_date' => $visit->visit_date?->toIso8601String(),
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Hospital-System/1.0',
            ]);

            return $visit->fresh(['patient', 'appointment', 'consultation', 'admissions']);
        });
    }

    /**
     * Get aggregated clinical overview metrics for a patient.
     *
     * @param Patient $patient
     * @return array
     */
    public function getPatientClinicalMetrics(Patient $patient): array
    {
        $totalVisits = $patient->visits()->count();
        $regularVisits = $patient->visits()->where('visit_type', 'regular')->count();
        $emergencyVisits = $patient->visits()->where('visit_type', 'emergency')->count();
        $followUpVisits = $patient->visits()->where('visit_type', 'follow_up')->count();

        $admissionsCount = $patient->admissions()->count();
        $activeAdmission = $patient->admissions()->where('status', 'admitted')->with('currentBedAssignment.bed.department')->first();

        // Count consultations across patient visits
        $consultationsCount = Consultation::whereIn('visit_id', $patient->visits()->pluck('id'))->count();

        // Count prescriptions through consultations
        $prescriptionsCount = DB::table('prescriptions')
            ->join('consultations', 'prescriptions.consultation_id', '=', 'consultations.id')
            ->join('visits', 'consultations.visit_id', '=', 'visits.id')
            ->where('visits.patient_id', $patient->id)
            ->count();

        // Count lab orders & reports
        $labOrdersCount = $patient->labOrders()->count();

        // Count discharge summaries through admissions
        $dischargesCount = DB::table('discharge_summaries')
            ->join('admissions', 'discharge_summaries.admission_id', '=', 'admissions.id')
            ->where('admissions.patient_id', $patient->id)
            ->count();

        return [
            'total_visits' => $totalVisits,
            'regular_visits' => $regularVisits,
            'emergency_visits' => $emergencyVisits,
            'follow_up_visits' => $followUpVisits,
            'admissions_count' => $admissionsCount,
            'active_admission' => $activeAdmission,
            'consultations_count' => $consultationsCount,
            'prescriptions_count' => $prescriptionsCount,
            'lab_orders_count' => $labOrdersCount,
            'discharges_count' => $dischargesCount,
        ];
    }
}
