<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function getTitleAttribute(): string
    {
        return $this->display_name ?? ucfirst($this->name);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function givePermission(Permission|string $permission): self
    {
        $permModel = is_string($permission) ? Permission::where('name', $permission)->firstOrFail() : $permission;
        $this->permissions()->syncWithoutDetaching([$permModel->id]);
        $this->load('permissions');

        return $this;
    }

    public function revokePermission(Permission|string $permission): self
    {
        $permModel = is_string($permission) ? Permission::where('name', $permission)->first() : $permission;
        if ($permModel) {
            $this->permissions()->detach($permModel->id);
            $this->load('permissions');
        }

        return $this;
    }

    public function syncPermissions(array $permissions): self
    {
        $permIds = Permission::whereIn('name', $permissions)
            ->orWhereIn('id', array_filter($permissions, 'is_numeric'))
            ->pluck('id');

        $this->permissions()->sync($permIds);
        $this->load('permissions');

        return $this;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions->contains('name', $permission);
    }
}
