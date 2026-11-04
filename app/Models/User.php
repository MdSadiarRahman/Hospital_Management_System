<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_type',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return $this->roles->pluck('name')->intersect($role)->isNotEmpty();
        }

        return $this->roles->contains('name', $role);
    }

    public function hasAnyRole(string|array $roles): bool
    {
        return $this->hasRole(is_array($roles) ? $roles : func_get_args());
    }

    public function hasPermission(string|array $permission): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        if (is_array($permission)) {
            return $this->roles()
                ->whereHas('permissions', fn ($query) => $query->whereIn('name', $permission))
                ->exists();
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->exists();
    }

    public function isAdministrative(): bool
    {
        return $this->hasRole(['super_admin', 'administrator']);
    }

    public function isStaff(): bool
    {
        return $this->user_type === 'staff';
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function assignRole(Role|string $role): self
    {
        $roleModel = is_string($role) ? Role::where('name', $role)->firstOrFail() : $role;
        $this->roles()->syncWithoutDetaching([$roleModel->id]);
        $this->load('roles');

        return $this;
    }

    public function removeRole(Role|string $role): self
    {
        $roleModel = is_string($role) ? Role::where('name', $role)->first() : $role;
        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
            $this->load('roles');
        }

        return $this;
    }

    public function syncRoles(array $roles): self
    {
        $roleIds = Role::whereIn('name', $roles)
            ->orWhereIn('id', array_filter($roles, 'is_numeric'))
            ->pluck('id');

        $this->roles()->sync($roleIds);
        $this->load('roles');

        return $this;
    }

    public function scopeStaff($query)
    {
        return $query->where('user_type', 'staff');
    }

    public function scopePatient($query)
    {
        return $query->where('user_type', 'patient');
    }

    public function doctorSchedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }

    public function doctorAppointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function doctorConsultations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }

    public function doctorAdmissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Admission::class, 'attending_doctor_id');
    }

    public function doctorTreatmentPlans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TreatmentPlan::class, 'doctor_id');
    }
}
