<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\PatientAuthController;
use App\Http\Controllers\Patient\PatientAppointmentController;
use App\Http\Controllers\Patient\PatientDashboardController;
use App\Http\Controllers\Patient\PatientProfileController;
use App\Http\Controllers\PublicAppointmentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Staff\AdmissionController;
use App\Http\Controllers\Staff\AppointmentController;
use App\Http\Controllers\Staff\BedController;
use App\Http\Controllers\Staff\DepartmentController;
use App\Http\Controllers\Staff\DoctorScheduleController;
use App\Http\Controllers\Staff\PatientLinkingController;
use App\Http\Controllers\Staff\PatientMasterController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Staff\VisitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/departments', [PublicController::class, 'departments'])->name('departments');
Route::get('/departments/{id}', [PublicController::class, 'departmentDetail'])->name('departments.show');
Route::get('/doctors', [PublicController::class, 'doctors'])->name('doctors');
Route::get('/doctors/{id}', [PublicController::class, 'doctorProfile'])->name('doctors.show');
Route::get('/services', [PublicController::class, 'services'])->name('services');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::get('/privacy-policy', [PublicController::class, 'privacy'])->name('privacy');

// Public Doctor Appointment Booking & Availability APIs
Route::post('/appointments', [PublicAppointmentController::class, 'store'])->name('appointments.store');
Route::get('/api/appointments/slots', [PublicAppointmentController::class, 'getAvailableSlots'])->name('api.appointments.slots');
Route::get('/api/appointments/doctors', [PublicAppointmentController::class, 'getDoctorsByDepartment'])->name('api.appointments.doctors');

Route::get('/components', function () {
    return view('components-demo');
})->name('components.demo');

Route::get('/design-system', function () {
    return view('design-system.index');
})->name('design-system');


/*
|--------------------------------------------------------------------------
| Staff Portal Authentication Routes
|--------------------------------------------------------------------------
*/
// Canonical login route alias required by Laravel auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

Route::prefix('portal')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('portal.login');
    Route::post('/login', [AuthController::class, 'login'])->name('portal.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('portal.logout');
});

// Universal logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| Patient Self-Care Portal Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('patient')->name('patient.')->group(function () {
    Route::get('/login', [PatientAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [PatientAuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [PatientAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [PatientAuthController::class, 'register'])->name('register.submit');
    Route::post('/logout', [PatientAuthController::class, 'logout'])->name('logout');
    
    // Notice for self-registered patients pending physical identity verification
    Route::get('/unlinked-notice', [PatientAuthController::class, 'unlinkedNotice'])
        ->middleware(['auth', 'patient'])
        ->name('unlinked');
});


/*
|--------------------------------------------------------------------------
| Password Reset Routes
|--------------------------------------------------------------------------
*/
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');


/*
|--------------------------------------------------------------------------
| Protected Staff Portal Routes (auth + staff + role middleware)
|--------------------------------------------------------------------------
*/
Route::prefix('portal')->middleware(['auth', 'staff'])->name('portal.')->group(function () {
    // Role-aware dashboard dispatcher
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Administrator Control Center
    Route::get('/admin', function () {
        return view('previews.admin');
    })->middleware('role:super_admin,administrator')->name('admin');

    // Doctor Clinical Workstation
    Route::get('/doctor', function () {
        return view('previews.doctor');
    })->middleware('role:doctor')->name('doctor');

    // Nurse Ward Station
    Route::get('/nurse', function () {
        return view('previews.nurse');
    })->middleware('role:nurse')->name('nurse');

    // Reception & Patient Intake Desk
    Route::get('/reception', function () {
        return view('previews.reception');
    })->middleware('role:receptionist')->name('reception');

    // Billing & Cashier Desk
    Route::get('/billing', function () {
        return view('previews.billing');
    })->middleware('role:billing_staff')->name('billing');

    // Diagnostic Laboratory Workstation
    Route::get('/lab', function () {
        return view('previews.lab');
    })->middleware('role:lab_staff')->name('lab');

    // OT & Inpatient Ward Station
    Route::get('/ot-ward', function () {
        return view('previews.nurse');
    })->middleware('role:ot_ward_staff,nurse')->name('ot-ward');

    // Staff Patient Account Verification & Record Linking
    Route::get('/patient-linking', [PatientLinkingController::class, 'index'])
        ->middleware('permission:patients.link_account')
        ->name('patient-linking.index');
    Route::post('/patient-linking', [PatientLinkingController::class, 'link'])
        ->middleware('permission:patients.link_account')
        ->name('patient-linking.store');
    Route::post('/patient-linking/{id}/unlink', [PatientLinkingController::class, 'unlink'])
        ->middleware('permission:patients.link_account')
        ->name('patient-linking.unlink');

    // Patient Registration & Master Registry (Receptionist & Administrators)
    Route::get('/patients', [PatientMasterController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientMasterController::class, 'create'])->name('patients.create');
    Route::post('/patients/check-duplicates', [PatientMasterController::class, 'checkDuplicates'])->name('patients.check-duplicates');
    Route::post('/patients', [PatientMasterController::class, 'store'])->name('patients.store');
    Route::get('/patients/{patient}', [PatientMasterController::class, 'show'])->name('patients.show');
    Route::get('/patients/{patient}/medical-record', [PatientMasterController::class, 'medicalRecord'])->name('patients.medical-record');
    Route::get('/patients/{patient}/edit', [PatientMasterController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{patient}', [PatientMasterController::class, 'update'])->name('patients.update');
    Route::get('/patients/{patient}/print-card', [PatientMasterController::class, 'printCard'])->name('patients.print-card');

    // Doctor Consultation Schedules (Receptionist, Doctors, & Administrators)
    Route::get('/doctor-schedules', [DoctorScheduleController::class, 'index'])->name('doctor-schedules.index');
    Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store'])->name('doctor-schedules.store');
    Route::put('/doctor-schedules/{doctorSchedule}', [DoctorScheduleController::class, 'update'])->name('doctor-schedules.update');
    Route::delete('/doctor-schedules/{doctorSchedule}', [DoctorScheduleController::class, 'destroy'])->name('doctor-schedules.destroy');

    // Appointment Management (Receptionist, Doctors, & Staff)
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{appointment}/status', [AppointmentController::class, 'markStatus'])->name('appointments.status');
    Route::get('/appointments/{appointment}/print-slip', [AppointmentController::class, 'printSlip'])->name('appointments.print-slip');

    // Patient Visit Management & Emergency Triage Intake
    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
    Route::get('/visits/create', [VisitController::class, 'create'])->name('visits.create');
    Route::post('/visits', [VisitController::class, 'store'])->name('visits.store');
    Route::get('/visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
    Route::get('/visits/{visit}/edit', [VisitController::class, 'edit'])->name('visits.edit');
    Route::put('/visits/{visit}', [VisitController::class, 'update'])->name('visits.update');
    Route::delete('/visits/{visit}', [VisitController::class, 'destroy'])->name('visits.destroy');
    Route::get('/visits/{visit}/print-token', [VisitController::class, 'printToken'])->name('visits.print-token');

    // Department & Ward Management (Super Admin & Administrator)
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{department}', [DepartmentController::class, 'show'])->name('departments.show');
    Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::post('/departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    // Department Staff Assignment & Transfer
    Route::post('/departments/{department}/staff', [DepartmentController::class, 'assignStaff'])->name('departments.staff.assign');
    Route::delete('/departments/{department}/staff/{staffProfile}', [DepartmentController::class, 'removeStaff'])->name('departments.staff.remove');

    // Department Bed Configuration & Tariffs
    Route::post('/departments/{department}/beds', [DepartmentController::class, 'storeBed'])->name('departments.beds.store');
    Route::put('/departments/{department}/beds/{bed}', [DepartmentController::class, 'updateBed'])->name('departments.beds.update');
    Route::delete('/departments/{department}/beds/{bed}', [DepartmentController::class, 'deleteBed'])->name('departments.beds.destroy');

    // Inpatient Bed Management & Ward Operations
    Route::get('/beds', [BedController::class, 'index'])->name('beds.index');
    Route::get('/beds/occupancy', [BedController::class, 'occupancy'])->name('beds.occupancy');
    Route::get('/beds/transfers', [BedController::class, 'transferHistory'])->name('beds.transfers');
    Route::get('/beds/create', [BedController::class, 'create'])->name('beds.create');
    Route::post('/beds', [BedController::class, 'store'])->name('beds.store');
    Route::get('/beds/{bed}', [BedController::class, 'show'])->name('beds.show');
    Route::get('/beds/{bed}/edit', [BedController::class, 'edit'])->name('beds.edit');
    Route::put('/beds/{bed}', [BedController::class, 'update'])->name('beds.update');
    Route::post('/beds/{bed}/status', [BedController::class, 'updateStatus'])->name('beds.update-status');
    Route::post('/beds/{bed}/transfer', [BedController::class, 'transferOccupant'])->name('beds.transfer-occupant');
    Route::post('/beds/{bed}/complete-cleaning', [BedController::class, 'completeCleaning'])->name('beds.complete-cleaning');
    Route::post('/beds/{bed}/complete-maintenance', [BedController::class, 'completeMaintenance'])->name('beds.complete-maintenance');
    Route::delete('/beds/{bed}', [BedController::class, 'destroy'])->name('beds.destroy');

    // Inpatient Admissions & Bed Allocation
    Route::get('/admissions', [AdmissionController::class, 'index'])->name('admissions.index');
    Route::get('/admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
    Route::post('/admissions', [AdmissionController::class, 'store'])->name('admissions.store');
    Route::get('/admissions/{admission}', [AdmissionController::class, 'show'])->name('admissions.show');
    Route::post('/admissions/{admission}/transfer-bed', [AdmissionController::class, 'transferBed'])->name('admissions.transfer-bed');
    Route::post('/admissions/{admission}/cancel', [AdmissionController::class, 'cancel'])->name('admissions.cancel');
    Route::get('/api/admissions/available-beds', [AdmissionController::class, 'getAvailableBedsApi'])->name('api.admissions.available-beds');
    Route::get('/api/admissions/patient/{patient}/visits', [AdmissionController::class, 'getPatientVisitsApi'])->name('api.admissions.patient-visits');

    // Staff Management, Allocation, & 8-Hour Shift Scheduling
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/shifts', [StaffController::class, 'shifts'])->name('staff.shifts');
    Route::post('/staff/shifts', [StaffController::class, 'scheduleShift'])->name('staff.schedule-shift');
    Route::delete('/staff/shifts/{shiftSchedule}', [StaffController::class, 'deleteShift'])->name('staff.delete-shift');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staffProfile}', [StaffController::class, 'show'])->name('staff.show');
    Route::get('/staff/{staffProfile}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staffProfile}', [StaffController::class, 'update'])->name('staff.update');
    Route::post('/staff/{staffProfile}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
});


/*
|--------------------------------------------------------------------------
| Protected Patient Portal Routes (auth + patient middleware)
|--------------------------------------------------------------------------
*/
Route::prefix('patient')->middleware(['auth', 'patient'])->name('patient.')->group(function () {
    // Patient Self-Care Dashboard (accessible to both linked and unlinked patients)
    Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');

    // Patient Profile & Settings (accessible to both linked and unlinked patients)
    Route::get('/profile', [PatientProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [PatientProfileController::class, 'update'])->name('profile.update');
    Route::post('/emergency-contacts', [PatientProfileController::class, 'storeEmergencyContact'])->name('emergency-contacts.store');
    Route::delete('/emergency-contacts/{id}', [PatientProfileController::class, 'destroyEmergencyContact'])->name('emergency-contacts.destroy');
    Route::put('/password', [PatientProfileController::class, 'updatePassword'])->name('password.update');

    // Patient Appointments (accessible to all registered patients)
    Route::get('/appointments', [PatientAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [PatientAppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/reschedule', [PatientAppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/cancel', [PatientAppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/appointments/{appointment}/print-slip', [PatientAppointmentController::class, 'printSlip'])->name('appointments.print-slip');

    // Notifications
    Route::post('/notifications/{id}/read', [PatientDashboardController::class, 'markNotificationAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [PatientDashboardController::class, 'markAllNotificationsAsRead'])->name('notifications.read-all');

    // Sensitive Clinical Records: strictly protected by EnsurePatientLinked middleware
    Route::middleware('patient.linked')->group(function () {
        Route::get('/prescriptions', function () {
            return redirect()->route('patient.dashboard', '#prescriptions-section');
        })->name('prescriptions');
        Route::get('/prescriptions/{id}', [PatientDashboardController::class, 'showPrescription'])->name('prescriptions.show');

        Route::get('/lab-reports', function () {
            return redirect()->route('patient.dashboard', '#reports-section');
        })->name('lab-reports');
        Route::get('/lab-reports/{id}/download', [PatientDashboardController::class, 'downloadLabReport'])->name('lab-reports.download');

        Route::get('/inpatient-stays', function () {
            return redirect()->route('patient.dashboard', '#admission-section');
        })->name('inpatient-stays');

        Route::get('/bills', function () {
            return redirect()->route('patient.dashboard', '#billing-section');
        })->name('bills');
    });
});


/*
|--------------------------------------------------------------------------
| Application Layout Previews
|--------------------------------------------------------------------------
*/
Route::prefix('previews')->name('previews.')->group(function () {
    Route::get('/', function () {
        return view('previews.index');
    })->name('index');

    Route::get('/public', [PublicController::class, 'index'])->name('public');

    Route::get('/admin', function () {
        return view('previews.admin');
    })->name('admin');

    Route::get('/doctor', function () {
        return view('previews.doctor');
    })->name('doctor');

    Route::get('/nurse', function () {
        return view('previews.nurse');
    })->name('nurse');

    Route::get('/reception', function () {
        return view('previews.reception');
    })->name('reception');

    Route::get('/billing', function () {
        return view('previews.billing');
    })->name('billing');

    Route::get('/lab', function () {
        return view('previews.lab');
    })->name('lab');

    Route::get('/patient', function () {
        return view('previews.patient');
    })->name('patient');
});
