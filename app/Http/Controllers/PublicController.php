<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Common bed categories with transparent tariffs.
     */
    protected array $bedCategories = [
        [
            'category' => 'General Inpatient Ward',
            'code' => 'GEN',
            'beds' => '4 Beds (GEN-01 to GEN-04)',
            'rate' => 800.00,
            'amenities' => 'Oxygen line, 24/7 nursing duty, attendant seating, ceiling fan',
            'care_level' => 'Standard Inpatient Observation',
        ],
        [
            'category' => 'Gynecology & Maternity Ward',
            'code' => 'GYN',
            'beds' => '2 Beds (GYN-01, GYN-02)',
            'rate' => 1200.00,
            'amenities' => 'Post-op observation, female nursing staff, privacy curtains, baby bassinet access',
            'care_level' => 'Maternal & Neonatal Postpartum',
        ],
        [
            'category' => 'Surgical Inpatient Ward',
            'code' => 'SUR',
            'beds' => '2 Beds (SUR-01, SUR-02)',
            'rate' => 1500.00,
            'amenities' => 'Post-operative monitoring, dressing care, surgeon rounds, adjustable backrest',
            'care_level' => 'Post-Surgical Intermediate Care',
        ],
        [
            'category' => 'Emergency Acute Observation',
            'code' => 'EM',
            'beds' => '2 Beds (EM-01, EM-02)',
            'rate' => 1500.00,
            'amenities' => 'Emergency monitor, immediate triage, central oxygen support, crash cart access',
            'care_level' => 'High-Dependency Acute Stabilization',
        ],
    ];

    /**
     * Retrieve global clinic settings from database with safe defaults.
     */
    protected function getClinicSettings(): array
    {
        return [
            'clinicName' => SystemSetting::get('clinic_name', 'Meghna Clinic and Nursing Home'),
            'clinicAddress' => SystemSetting::get('clinic_address', 'Shohid Minar Mor, Dinajpur, Bangladesh'),
            'clinicHotline' => SystemSetting::get('clinic_hotline', '+8801700000000'),
            'totalBedCapacity' => (int) SystemSetting::get('total_bed_capacity', 10),
        ];
    }

    /**
     * Display the public website homepage.
     */
    public function index(): View
    {
        $settings = $this->getClinicSettings();

        // Fetch active clinical departments
        $departments = Department::where('is_active', true)
            ->withCount('beds')
            ->orderBy('is_clinical', 'desc')
            ->orderBy('name')
            ->get();

        // Fetch verified doctors with their profiles
        $doctors = User::where('user_type', 'staff')
            ->whereHas('roles', fn ($query) => $query->where('name', 'doctor'))
            ->with(['staffProfile.department'])
            ->get();

        // Fetch beds summary
        $beds = Bed::with('department')->get();
        $availableBedsCount = Bed::where('status', 'available')->count();
        $bedCategories = $this->bedCategories;

        return view('public.index', array_merge($settings, compact(
            'departments',
            'doctors',
            'beds',
            'availableBedsCount',
            'bedCategories'
        )));
    }

    /**
     * Display the About Us page.
     */
    public function about(): View
    {
        $settings = $this->getClinicSettings();

        $departmentsCount = Department::where('is_active', true)->count();
        $doctorsCount = User::where('user_type', 'staff')
            ->whereHas('roles', fn ($query) => $query->where('name', 'doctor'))
            ->count();
        $nursesCount = User::where('user_type', 'staff')
            ->whereHas('roles', fn ($query) => $query->where('name', 'nurse'))
            ->count();
        $totalStaffEstimate = 14; // Verified organizational headcount

        return view('public.about', array_merge($settings, compact(
            'departmentsCount',
            'doctorsCount',
            'nursesCount',
            'totalStaffEstimate'
        )));
    }

    /**
     * Display the Departments listing page.
     */
    public function departments(): View
    {
        $settings = $this->getClinicSettings();

        $departments = Department::where('is_active', true)
            ->withCount('beds')
            ->with(['staffProfiles.user.roles'])
            ->orderBy('is_clinical', 'desc')
            ->orderBy('name')
            ->get();

        $beds = Bed::with('department')->get();
        $availableBedsCount = Bed::where('status', 'available')->count();

        return view('public.departments', array_merge($settings, compact(
            'departments',
            'beds',
            'availableBedsCount'
        )));
    }

    /**
     * Display the Department Detail page.
     */
    public function departmentDetail(string $id): View
    {
        $settings = $this->getClinicSettings();

        $department = Department::where('id', $id)
            ->orWhere('code', strtoupper($id))
            ->with(['beds', 'staffProfiles.user.roles'])
            ->firstOrFail();

        // Doctors associated with this department
        $departmentDoctors = User::where('user_type', 'staff')
            ->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->whereHas('staffProfile', fn ($q) => $q->where('department_id', $department->id))
            ->with('staffProfile')
            ->get();

        // Fallback doctors in case of emergency coverage
        if ($departmentDoctors->isEmpty()) {
            $departmentDoctors = User::where('user_type', 'staff')
                ->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
                ->with('staffProfile')
                ->take(2)
                ->get();
        }

        $allDepartments = Department::where('is_active', true)
            ->where('id', '!=', $department->id)
            ->get();

        return view('public.department-detail', array_merge($settings, compact(
            'department',
            'departmentDoctors',
            'allDepartments'
        )));
    }

    /**
     * Display the Doctors directory page.
     */
    public function doctors(): View
    {
        $settings = $this->getClinicSettings();

        $doctors = User::where('user_type', 'staff')
            ->whereHas('roles', fn ($query) => $query->where('name', 'doctor'))
            ->with(['staffProfile.department'])
            ->get();

        $nurses = User::where('user_type', 'staff')
            ->whereHas('roles', fn ($query) => $query->where('name', 'nurse'))
            ->with(['staffProfile.department'])
            ->get();

        return view('public.doctors', array_merge($settings, compact(
            'doctors',
            'nurses'
        )));
    }

    /**
     * Display individual Doctor Profile page.
     */
    public function doctorProfile(int $id): View
    {
        $settings = $this->getClinicSettings();

        $doctor = User::where('user_type', 'staff')
            ->whereHas('roles', fn ($query) => $query->where('name', 'doctor'))
            ->with(['staffProfile.department', 'roles'])
            ->findOrFail($id);

        $otherDoctors = User::where('user_type', 'staff')
            ->where('id', '!=', $doctor->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'doctor'))
            ->with(['staffProfile.department'])
            ->take(2)
            ->get();

        return view('public.doctor-profile', array_merge($settings, compact(
            'doctor',
            'otherDoctors'
        )));
    }

    /**
     * Display the Healthcare Services page.
     */
    public function services(): View
    {
        $settings = $this->getClinicSettings();
        $bedCategories = $this->bedCategories;
        $totalBeds = Bed::count();
        $availableBeds = Bed::where('status', 'available')->count();

        return view('public.services', array_merge($settings, compact(
            'bedCategories',
            'totalBeds',
            'availableBeds'
        )));
    }

    /**
     * Display the Contact Us & Location page.
     */
    public function contact(): View
    {
        $settings = $this->getClinicSettings();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('public.contact', array_merge($settings, compact('departments')));
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy(): View
    {
        $settings = $this->getClinicSettings();

        return view('public.privacy', $settings);
    }
}
