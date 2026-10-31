<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PatientLinkingController extends Controller
{
    /**
     * Display the Patient Account Verification & Record Linking dashboard for staff.
     */
    public function index(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        abort_unless(
            $currentUser->hasPermission('patients.link_account') || Gate::allows('create', Patient::class),
            403,
            'Unauthorized: You do not have permission to verify and link patient accounts.'
        );

        $userSearch = $request->query('user_search');
        $patientSearch = $request->query('patient_search');

        // Unlinked online patient accounts
        $unlinkedUsers = User::where('user_type', 'patient')
            ->whereDoesntHave('patient')
            ->when($userSearch, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(10, ['*'], 'users_page');

        // Clinical patient records pending account linkage
        $unlinkedPatients = Patient::whereNull('user_id')
            ->when($patientSearch, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('patient_code', 'like', "%{$term}%")
                        ->orWhere('nid_or_birth_cert', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(10, ['*'], 'patients_page');

        // Recently verified & linked accounts
        $recentlyLinked = Patient::whereNotNull('user_id')
            ->with(['user', 'verifiedBy'])
            ->latest('verified_at')
            ->take(10)
            ->get();

        return view('portal.patient-linking.index', compact(
            'unlinkedUsers',
            'unlinkedPatients',
            'recentlyLinked',
            'userSearch',
            'patientSearch'
        ));
    }

    /**
     * Staff verification action: link an authenticated patient user to a clinical patient record.
     */
    public function link(Request $request): RedirectResponse
    {
        /** @var User $staffUser */
        $staffUser = Auth::user();
        abort_unless(
            $staffUser->hasPermission('patients.link_account'),
            403,
            'Unauthorized: You do not have permission to verify and link patient accounts.'
        );

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'verification_method' => ['required', 'string', 'in:physical_in_person,nid_matched,phone_otp_verified,guardian_verified'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $patient = Patient::findOrFail($validated['patient_id']);

        // Security assertion 1: Must be a patient-type user account
        if ($user->user_type !== 'patient') {
            return back()->with('error', 'Cannot link clinical records to staff accounts.');
        }

        // Security assertion 2: Web account must not already be linked
        if ($user->patient) {
            return back()->with('error', "User account {$user->name} is already linked to patient code {$user->patient->patient_code}.");
        }

        // Security assertion 3: Patient clinical record must not already be linked
        if ($patient->user_id !== null) {
            return back()->with('error', "Clinical record {$patient->patient_code} is already linked to another portal account.");
        }

        DB::transaction(function () use ($user, $patient, $staffUser, $validated, $request) {
            $patient->update([
                'user_id' => $user->id,
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => $staffUser->id,
            ]);

            AuditLog::create([
                'user_id' => $staffUser->id,
                'event' => 'patient.account_linked_by_staff',
                'auditable_type' => Patient::class,
                'auditable_id' => $patient->id,
                'old_values' => [
                    'user_id' => null,
                    'is_verified' => false,
                ],
                'new_values' => [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_phone' => $user->phone,
                    'patient_code' => $patient->patient_code,
                    'verification_method' => $validated['verification_method'],
                    'verified_by_staff_id' => $staffUser->id,
                    'notes' => $validated['notes'] ?? null,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return back()->with('status', "Success: {$user->name} is now verified and linked to clinical record {$patient->patient_code}.");
    }

    /**
     * Staff action: safely unlink an account if requested or registered in error.
     */
    public function unlink(Request $request, int $patientId): RedirectResponse
    {
        /** @var User $staffUser */
        $staffUser = Auth::user();
        abort_unless(
            $staffUser->hasPermission('patients.link_account'),
            403,
            'Unauthorized: You do not have permission to unlink patient accounts.'
        );

        $patient = Patient::with('user')->findOrFail($patientId);

        if (!$patient->user_id) {
            return back()->with('error', 'This clinical record is not currently linked to any online account.');
        }

        $oldUserId = $patient->user_id;
        $userName = $patient->user?->name ?? 'User #' . $oldUserId;

        DB::transaction(function () use ($patient, $oldUserId, $userName, $staffUser, $request) {
            $patient->update([
                'user_id' => null,
                'is_verified' => false,
                'verified_at' => null,
                'verified_by' => null,
            ]);

            AuditLog::create([
                'user_id' => $staffUser->id,
                'event' => 'patient.account_unlinked_by_staff',
                'auditable_type' => Patient::class,
                'auditable_id' => $patient->id,
                'old_values' => [
                    'user_id' => $oldUserId,
                    'is_verified' => true,
                ],
                'new_values' => [
                    'user_id' => null,
                    'is_verified' => false,
                    'unlinked_by' => $staffUser->name,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return back()->with('status', "Patient record {$patient->patient_code} has been successfully unlinked from {$userName}.");
    }
}
