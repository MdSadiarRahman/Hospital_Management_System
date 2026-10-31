<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreEmergencyContactRequest;
use App\Http\Requests\Patient\UpdatePatientPasswordRequest;
use App\Http\Requests\Patient\UpdatePatientProfileRequest;
use App\Models\AuditLog;
use App\Models\PatientContact;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PatientProfileController extends Controller
{
    /**
     * Display the authenticated patient's profile & security settings.
     */
    public function show(Request $request): View
    {
        $user = Auth::user();

        // Load clinical patient profile if linked
        $patient = $user->patient?->load(['contacts', 'verifiedBy']);
        $isLinked = !is_null($patient);

        return view('patient.profile', compact('user', 'patient', 'isLinked'));
    }

    /**
     * Update the patient's account & demographic details.
     */
    public function update(UpdatePatientProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $oldUserValues = $user->only(['name', 'email', 'phone']);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $patient = $user->patient;
        if ($patient) {
            $patient->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth ?? $patient->date_of_birth,
                'gender' => $request->gender ?? $patient->gender,
                'blood_group' => $request->blood_group ?? $patient->blood_group,
                'nid_or_birth_cert' => $request->nid_or_birth_cert ?? $patient->nid_or_birth_cert,
            ]);
        }

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'patient.profile_updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => $oldUserValues,
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_linked' => !is_null($patient),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('patient.profile')->with('status', 'Your profile details have been successfully updated.');
    }

    /**
     * Store a new emergency contact for the linked patient record.
     */
    public function storeEmergencyContact(StoreEmergencyContactRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $patient = $user->patient;

        if (!$patient) {
            return redirect()->route('patient.profile')->with('error', 'Emergency contacts can only be added once your account is physically linked to your clinical chart at the clinic reception desk.');
        }

        $isPrimary = $request->boolean('is_primary');

        if ($isPrimary) {
            $patient->contacts()->update(['is_primary' => false]);
        }

        $contact = $patient->contacts()->create([
            'name' => $request->name,
            'relationship' => $request->relationship,
            'phone' => $request->phone,
            'is_primary' => $isPrimary,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'patient.emergency_contact_added',
            'auditable_type' => PatientContact::class,
            'auditable_id' => $contact->id,
            'old_values' => null,
            'new_values' => [
                'patient_code' => $patient->patient_code,
                'contact_name' => $contact->name,
                'relationship' => $contact->relationship,
                'phone' => $contact->phone,
                'is_primary' => $contact->is_primary,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('patient.profile')->with('status', 'Emergency contact saved successfully.');
    }

    /**
     * Delete an emergency contact belonging to the patient.
     */
    public function destroyEmergencyContact(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $patient = $user->patient;

        $contact = PatientContact::findOrFail($id);

        // Ownership enforcement: patient can only delete their own emergency contact
        if (!$patient || $contact->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access: You can only manage your own emergency contacts.');
        }

        $oldValues = $contact->only(['name', 'relationship', 'phone', 'is_primary']);
        $contact->delete();

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'patient.emergency_contact_deleted',
            'auditable_type' => PatientContact::class,
            'auditable_id' => $id,
            'old_values' => $oldValues,
            'new_values' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('patient.profile')->with('status', 'Emergency contact removed.');
    }

    /**
     * Change authenticated patient password.
     */
    public function updatePassword(UpdatePatientPasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'patient.password_changed',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => null,
            'new_values' => ['action' => 'password_reset_via_portal'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('patient.profile')->with('status', 'Your password has been changed successfully.');
    }
}
