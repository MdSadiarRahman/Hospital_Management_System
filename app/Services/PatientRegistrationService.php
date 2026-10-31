<?php

namespace App\Services;

use App\Exceptions\DuplicatePatientException;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\PatientContact;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientRegistrationService
{
    /**
     * Generate the next unique Patient ID in standard MCNH-YYYY-XXXX format.
     */
    public function generatePatientId(?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $prefix = "MCNH-{$year}-";

        // Query the latest sequential patient code for the given year
        $latestCode = Patient::where('patient_code', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(patient_code, 11) AS UNSIGNED) DESC')
            ->value('patient_code');

        if ($latestCode && preg_match('/^MCNH-\d{4}-(\d+)$/', $latestCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        $candidate = sprintf('%s%04d', $prefix, $nextNumber);

        // Ensure absolute collision safety
        while (Patient::where('patient_code', $candidate)->exists()) {
            $nextNumber++;
            $candidate = sprintf('%s%04d', $prefix, $nextNumber);
        }

        return $candidate;
    }

    /**
     * Detect potential duplicate patients matching phone, NID, email, or exact Name + Date of Birth.
     * Duplicate detection does NOT automatically merge records.
     */
    public function findPotentialDuplicates(array $criteria, ?int $excludePatientId = null): Collection
    {
        $phone = trim($criteria['phone'] ?? '');
        $nid = trim($criteria['nid_or_birth_cert'] ?? '');
        $name = trim($criteria['name'] ?? '');
        $dob = !empty($criteria['date_of_birth']) ? Carbon::parse($criteria['date_of_birth'])->toDateString() : null;
        $email = trim($criteria['email'] ?? '');

        // If no identifying search parameters were supplied, return empty collection
        if (!$phone && !$nid && (!$name || !$dob) && !$email) {
            return collect();
        }

        $query = Patient::with(['primaryContact', 'contacts']);

        if ($excludePatientId) {
            $query->where('id', '!=', $excludePatientId);
        }

        $query->where(function ($q) use ($phone, $nid, $name, $dob, $email) {
            $hasCondition = false;

            // 1. Phone matching
            if ($phone !== '') {
                $q->where('phone', $phone);
                $hasCondition = true;
            }

            // 2. NID or Birth Certificate matching
            if ($nid !== '') {
                if ($hasCondition) {
                    $q->orWhere('nid_or_birth_cert', $nid);
                } else {
                    $q->where('nid_or_birth_cert', $nid);
                    $hasCondition = true;
                }
            }

            // 3. Email matching
            if ($email !== '') {
                if ($hasCondition) {
                    $q->orWhere('email', $email);
                } else {
                    $q->where('email', $email);
                    $hasCondition = true;
                }
            }

            // 4. Exact Name and Date of Birth matching
            if ($name !== '' && $dob !== null) {
                if ($hasCondition) {
                    $q->orWhere(function ($subQ) use ($name, $dob) {
                        $subQ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                             ->whereDate('date_of_birth', $dob);
                    });
                } else {
                    $q->whereRaw('LOWER(name) = ?', [strtolower($name)])
                      ->whereDate('date_of_birth', $dob);
                }
            }
        });

        return $query->take(5)->get();
    }

    /**
     * Register a new patient in the hospital master registry inside a secure database transaction.
     *
     * @throws DuplicatePatientException when duplicates are detected and not explicitly confirmed
     */
    public function registerPatient(
        array $patientData,
        array $contactData,
        User $staffUser,
        bool $confirmNotDuplicate = false
    ): Patient {
        // Check for potential duplicate patients
        $duplicates = $this->findPotentialDuplicates($patientData);

        if ($duplicates->isNotEmpty() && !$confirmNotDuplicate) {
            throw new DuplicatePatientException($duplicates);
        }

        return DB::transaction(function () use ($patientData, $contactData, $staffUser, $duplicates, $confirmNotDuplicate) {
            // Automatically generate unique Patient ID if not manually passed
            if (empty($patientData['patient_code'])) {
                $patientData['patient_code'] = $this->generatePatientId();
            }

            // Default registration date
            if (empty($patientData['registration_date'])) {
                $patientData['registration_date'] = now()->toDateString();
            }

            // Create Master Patient record
            $patient = Patient::create([
                'patient_code' => $patientData['patient_code'],
                'user_id' => $patientData['user_id'] ?? null,
                'name' => trim($patientData['name']),
                'date_of_birth' => $patientData['date_of_birth'],
                'gender' => $patientData['gender'],
                'blood_group' => $patientData['blood_group'] ?? null,
                'phone' => trim($patientData['phone']),
                'email' => !empty($patientData['email']) ? strtolower(trim($patientData['email'])) : null,
                'nid_or_birth_cert' => !empty($patientData['nid_or_birth_cert']) ? trim($patientData['nid_or_birth_cert']) : null,
                'address' => $patientData['address'] ?? null,
                'registration_date' => $patientData['registration_date'],
                'is_verified' => false,
            ]);

            // Save Emergency Contact / Next of Kin if provided
            $savedContact = null;
            if (!empty($contactData['name']) && !empty($contactData['phone'])) {
                $savedContact = PatientContact::create([
                    'patient_id' => $patient->id,
                    'name' => trim($contactData['name']),
                    'relationship' => $contactData['relationship'] ?? 'Guardian',
                    'phone' => trim($contactData['phone']),
                    'is_primary' => true,
                ]);
            }

            // Record comprehensive audit history
            AuditLog::create([
                'user_id' => $staffUser->id,
                'event' => 'patient_registered',
                'auditable_type' => Patient::class,
                'auditable_id' => $patient->id,
                'old_values' => null,
                'new_values' => [
                    'patient_code' => $patient->patient_code,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                    'gender' => $patient->gender,
                    'date_of_birth' => $patient->date_of_birth->toDateString(),
                    'blood_group' => $patient->blood_group,
                    'registration_date' => $patient->registration_date->toDateString(),
                    'primary_contact' => $savedContact ? [
                        'name' => $savedContact->name,
                        'relationship' => $savedContact->relationship,
                        'phone' => $savedContact->phone,
                    ] : null,
                    'duplicate_warning_acknowledged' => $duplicates->isNotEmpty() && $confirmNotDuplicate,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            Log::info("Patient master record registered successfully: [{$patient->patient_code}] {$patient->name} by Staff User ID #{$staffUser->id}");

            return $patient->load(['primaryContact', 'contacts']);
        });
    }

    /**
     * Update an existing patient master record and emergency contact with audit trail.
     */
    public function updatePatient(
        Patient $patient,
        array $patientData,
        ?array $contactData,
        User $staffUser
    ): Patient {
        return DB::transaction(function () use ($patient, $patientData, $contactData, $staffUser) {
            $oldValues = [
                'name' => $patient->name,
                'date_of_birth' => $patient->date_of_birth?->toDateString(),
                'gender' => $patient->gender,
                'blood_group' => $patient->blood_group,
                'phone' => $patient->phone,
                'email' => $patient->email,
                'nid_or_birth_cert' => $patient->nid_or_birth_cert,
                'address' => $patient->address,
                'registration_date' => $patient->registration_date?->toDateString(),
            ];

            // Update master record
            $patient->update([
                'name' => trim($patientData['name']),
                'date_of_birth' => $patientData['date_of_birth'],
                'gender' => $patientData['gender'],
                'blood_group' => $patientData['blood_group'] ?? null,
                'phone' => trim($patientData['phone']),
                'email' => !empty($patientData['email']) ? strtolower(trim($patientData['email'])) : null,
                'nid_or_birth_cert' => !empty($patientData['nid_or_birth_cert']) ? trim($patientData['nid_or_birth_cert']) : null,
                'address' => $patientData['address'] ?? null,
                'registration_date' => $patientData['registration_date'] ?? $patient->registration_date,
            ]);

            // Update or create primary emergency contact
            if ($contactData && !empty($contactData['name']) && !empty($contactData['phone'])) {
                $primaryContact = $patient->primaryContact;
                if ($primaryContact) {
                    $primaryContact->update([
                        'name' => trim($contactData['name']),
                        'relationship' => $contactData['relationship'] ?? $primaryContact->relationship,
                        'phone' => trim($contactData['phone']),
                    ]);
                } else {
                    PatientContact::create([
                        'patient_id' => $patient->id,
                        'name' => trim($contactData['name']),
                        'relationship' => $contactData['relationship'] ?? 'Guardian',
                        'phone' => trim($contactData['phone']),
                        'is_primary' => true,
                    ]);
                }
            }

            $newValues = [
                'name' => $patient->name,
                'date_of_birth' => $patient->date_of_birth?->toDateString(),
                'gender' => $patient->gender,
                'blood_group' => $patient->blood_group,
                'phone' => $patient->phone,
                'email' => $patient->email,
                'nid_or_birth_cert' => $patient->nid_or_birth_cert,
                'address' => $patient->address,
                'registration_date' => $patient->registration_date?->toDateString(),
            ];

            // Record audit log
            AuditLog::create([
                'user_id' => $staffUser->id,
                'event' => 'patient_updated',
                'auditable_type' => Patient::class,
                'auditable_id' => $patient->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $patient->load(['primaryContact', 'contacts']);
        });
    }
}
