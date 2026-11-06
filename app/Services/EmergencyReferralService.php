<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\Referral;
use App\Models\ReferralHistory;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmergencyReferralService
{
    public const DEFAULT_TERTIARY_HOSPITAL = 'M. Abdur Rahim Medical College Hospital, Dinajpur';

    public const COMMON_DESTINATIONS = [
        'M. Abdur Rahim Medical College Hospital, Dinajpur',
        'Dinajpur 250 Bed General Hospital (Sadar Hospital)',
        'Rangpur Medical College Hospital (RpMCH)',
        'National Institute of Cardiovascular Diseases (NICVD), Dhaka',
        'National Institute of Neurosciences & Hospital (NINS), Dhaka',
        'Other Specialized Facility',
    ];

    /**
     * Generate unique Referral number (e.g. REF-20261107-0001).
     */
    public function generateReferralNumber(): string
    {
        $datePrefix = 'REF-' . date('Ymd') . '-';

        $lastReferral = Referral::query()
            ->where('referral_number', 'like', $datePrefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($lastReferral && preg_match('/' . preg_quote($datePrefix, '/') . '(\d{4})$/', $lastReferral->referral_number, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        } else {
            $nextSeq = 1;
        }

        return $datePrefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create and authorize an emergency referral record.
     */
    public function createEmergencyReferral(array $data, User $authorizingDoctor): Referral
    {
        return DB::transaction(function () use ($data, $authorizingDoctor) {
            // 1. Resolve Patient
            $patientId = $data['patient_id'] ?? null;
            $admissionId = $data['admission_id'] ?? null;
            $visitId = $data['visit_id'] ?? null;

            if ($admissionId) {
                $admission = Admission::with('patient')->findOrFail($admissionId);
                $patientId = $admission->patient_id;
                $visitId = $visitId ?? $admission->visit_id;
            } elseif ($visitId) {
                $visit = Visit::with('patient')->findOrFail($visitId);
                $patientId = $visit->patient_id;
            }

            if (!$patientId) {
                throw ValidationException::withMessages([
                    'patient_id' => 'Emergency referral must be linked to an identified patient record.',
                ]);
            }

            $referralNumber = $this->generateReferralNumber();

            // Extract vital signs snapshot
            $vitalsSnapshot = [
                'bp_systolic' => $data['blood_pressure_systolic'] ?? null,
                'bp_diastolic' => $data['blood_pressure_diastolic'] ?? null,
                'heart_rate' => $data['heart_rate'] ?? null,
                'respiratory_rate' => $data['respiratory_rate'] ?? null,
                'temperature' => $data['temperature_celsius'] ?? null,
                'spo2' => $data['spo2_percent'] ?? null,
                'consciousness_level' => $data['consciousness_level'] ?? 'alert',
                'gcs_score' => $data['gcs_score'] ?? null,
                'airway_status' => $data['airway_status'] ?? 'patent',
            ];

            $referralType = $data['referral_type'] ?? 'icu_ccu_emergency';
            $requiresIcu = (bool) ($data['requires_icu'] ?? ($referralType === 'icu_ccu_emergency'));
            $isCritical = (bool) ($data['is_critical'] ?? true);

            $referral = Referral::create([
                'referral_number' => $referralNumber,
                'patient_id' => $patientId,
                'visit_id' => $visitId,
                'admission_id' => $admissionId,
                'referral_type' => $referralType,
                'is_critical' => $isCritical,
                'requires_icu' => $requiresIcu,
                'facility_name' => $data['facility_name'] ?? self::DEFAULT_TERTIARY_HOSPITAL,
                'destination_department' => $data['destination_department'] ?? ($requiresIcu ? 'Intensive Care Unit (ICU)' : 'Emergency & Trauma Center'),
                'receiving_doctor_contact' => $data['receiving_doctor_contact'] ?? null,
                'clinical_reason' => $data['clinical_reason'],
                'emergency_assessment' => $data['emergency_assessment'] ?? null,
                'interventions_given' => $data['interventions_given'] ?? null,
                'vitals_snapshot' => $vitalsSnapshot,
                'referral_notes' => $data['referral_notes'] ?? null,
                'transport_arranged' => (bool) ($data['transport_arranged'] ?? false),
                'transport_type' => $data['transport_type'] ?? null,
                'ambulance_driver_contact' => $data['ambulance_driver_contact'] ?? null,
                'accompanying_staff' => $data['accompanying_staff'] ?? null,
                'oxygen_support_transit' => (bool) ($data['oxygen_support_transit'] ?? false),
                'status' => 'initiated',
                'authorized_by' => $authorizingDoctor->id,
                'referred_at' => isset($data['referred_at']) ? Carbon::parse($data['referred_at']) : now(),
            ]);

            // Initial audit history
            ReferralHistory::create([
                'referral_id' => $referral->id,
                'action' => 'initiated',
                'changed_by' => $authorizingDoctor->id,
                'previous_status' => null,
                'new_status' => 'initiated',
                'notes' => "Emergency referral authorized to {$referral->facility_name}. Clinical Reason: {$referral->clinical_reason}",
                'metadata' => [
                    'facility_name' => $referral->facility_name,
                    'is_critical' => $referral->is_critical,
                    'requires_icu' => $referral->requires_icu,
                ],
            ]);

            AuditLog::create([
                'user_id' => $authorizingDoctor->id,
                'event' => 'emergency_referral_created',
                'auditable_type' => Referral::class,
                'auditable_id' => $referral->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => null,
                'new_values' => $referral->only(['referral_number', 'facility_name', 'referral_type', 'is_critical', 'status']),
            ]);

            return $referral->fresh(['patient', 'visit', 'admission', 'authorizedBy', 'histories']);
        });
    }

    /**
     * Dispatch patient in transit via ambulance.
     */
    public function dispatchInTransit(Referral $referral, array $data, User $actor): Referral
    {
        return DB::transaction(function () use ($referral, $data, $actor) {
            if (!$referral->canBeDispatched()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot dispatch patient with referral status '{$referral->status}'.",
                ]);
            }

            $prevStatus = $referral->status;

            $referral->update([
                'status' => 'in_transit',
                'transport_arranged' => true,
                'transport_type' => $data['transport_type'] ?? $referral->transport_type,
                'ambulance_driver_contact' => $data['ambulance_driver_contact'] ?? $referral->ambulance_driver_contact,
                'accompanying_staff' => $data['accompanying_staff'] ?? $referral->accompanying_staff,
                'oxygen_support_transit' => (bool) ($data['oxygen_support_transit'] ?? $referral->oxygen_support_transit),
            ]);

            ReferralHistory::create([
                'referral_id' => $referral->id,
                'action' => 'dispatched_in_transit',
                'changed_by' => $actor->id,
                'previous_status' => $prevStatus,
                'new_status' => 'in_transit',
                'notes' => "Patient dispatched in transit to {$referral->facility_name} via {$referral->transport_type}. Accompanying: {$referral->accompanying_staff}.",
            ]);

            return $referral->fresh();
        });
    }

    /**
     * Confirm successful handover and admission at tertiary facility.
     */
    public function completeTransfer(Referral $referral, array $data, User $actor): Referral
    {
        return DB::transaction(function () use ($referral, $data, $actor) {
            if (!$referral->canBeTransferred()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot complete transfer with status '{$referral->status}'.",
                ]);
            }

            $prevStatus = $referral->status;
            $transferredAt = isset($data['transferred_at']) ? Carbon::parse($data['transferred_at']) : now();

            $referral->update([
                'status' => 'transferred',
                'transferred_at' => $transferredAt,
                'referral_notes' => ($referral->referral_notes ? $referral->referral_notes . "\n" : '') . ($data['handover_notes'] ?? 'Handover confirmed at destination center.'),
            ]);

            // If patient was admitted at Meghna Clinic, update admission status to referred_external and release bed!
            if ($referral->admission_id) {
                $admission = $referral->admission;
                if ($admission && $admission->status === 'admitted') {
                    $admission->update([
                        'status' => 'referred_external',
                        'discharge_date' => $transferredAt,
                        'notes' => ($admission->notes ? $admission->notes . "\n" : '') . "Referred externally to {$referral->facility_name} (Ref: {$referral->referral_number}).",
                    ]);

                    // Release bed if occupied
                    $currentBedAssignment = $admission->currentBedAssignment;
                    if ($currentBedAssignment) {
                        $currentBedAssignment->update(['released_at' => $transferredAt]);
                        $bed = $currentBedAssignment->bed;
                        if ($bed) {
                            $bed->update(['status' => 'cleaning']);
                        }
                    }
                }
            }

            ReferralHistory::create([
                'referral_id' => $referral->id,
                'action' => 'transferred',
                'changed_by' => $actor->id,
                'previous_status' => $prevStatus,
                'new_status' => 'transferred',
                'notes' => "Clinical handover completed at {$referral->facility_name} on {$transferredAt->format('d M Y, h:i A')}.",
            ]);

            return $referral->fresh();
        });
    }

    /**
     * Cancel an emergency referral.
     */
    public function cancelReferral(Referral $referral, string $reason, User $actor): Referral
    {
        return DB::transaction(function () use ($referral, $reason, $actor) {
            if (!$referral->canBeCancelled()) {
                throw ValidationException::withMessages([
                    'status' => "Cannot cancel a referral with status '{$referral->status}'.",
                ]);
            }

            $prevStatus = $referral->status;

            $referral->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => $actor->id,
            ]);

            ReferralHistory::create([
                'referral_id' => $referral->id,
                'action' => 'cancelled',
                'changed_by' => $actor->id,
                'previous_status' => $prevStatus,
                'new_status' => 'cancelled',
                'notes' => "Referral cancelled by {$actor->name}. Reason: {$reason}",
            ]);

            return $referral->fresh();
        });
    }

    /**
     * Update clinical emergency assessment details.
     */
    public function updateAssessment(Referral $referral, array $data, User $actor): Referral
    {
        return DB::transaction(function () use ($referral, $data, $actor) {
            $vitals = $referral->vitals_snapshot ?? [];

            if (isset($data['blood_pressure_systolic'])) $vitals['bp_systolic'] = $data['blood_pressure_systolic'];
            if (isset($data['blood_pressure_diastolic'])) $vitals['bp_diastolic'] = $data['blood_pressure_diastolic'];
            if (isset($data['heart_rate'])) $vitals['heart_rate'] = $data['heart_rate'];
            if (isset($data['respiratory_rate'])) $vitals['respiratory_rate'] = $data['respiratory_rate'];
            if (isset($data['temperature_celsius'])) $vitals['temperature'] = $data['temperature_celsius'];
            if (isset($data['spo2_percent'])) $vitals['spo2'] = $data['spo2_percent'];
            if (isset($data['consciousness_level'])) $vitals['consciousness_level'] = $data['consciousness_level'];

            $referral->update([
                'emergency_assessment' => $data['emergency_assessment'] ?? $referral->emergency_assessment,
                'interventions_given' => $data['interventions_given'] ?? $referral->interventions_given,
                'vitals_snapshot' => $vitals,
                'referral_notes' => $data['referral_notes'] ?? $referral->referral_notes,
            ]);

            ReferralHistory::create([
                'referral_id' => $referral->id,
                'action' => 'assessment_updated',
                'changed_by' => $actor->id,
                'notes' => 'Emergency assessment and bedside interventions updated by ' . $actor->name,
            ]);

            return $referral->fresh();
        });
    }

    /**
     * Compute KPI statistics for Emergency Referral Workbench.
     */
    public function getStatistics(): array
    {
        $today = Carbon::today();

        return [
            'total_today' => Referral::query()->whereDate('referred_at', $today)->count(),
            'active_in_transit' => Referral::query()->where('status', 'in_transit')->count(),
            'icu_ccu_total' => Referral::query()->where(fn($q) => $q->where('requires_icu', true)->orWhere('referral_type', 'icu_ccu_emergency'))->count(),
            'critical_total' => Referral::query()->where('is_critical', true)->whereNotIn('status', ['cancelled'])->count(),
            'transferred_today' => Referral::query()->where('status', 'transferred')->whereDate('transferred_at', $today)->count(),
            'cancelled_total' => Referral::query()->where('status', 'cancelled')->count(),
        ];
    }
}
