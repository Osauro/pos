<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'celular',
        'pin',
        'is_super_admin',
        'imagen',
    ];

    protected $hidden = [
        'pin',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_super_admin' => 'boolean',
        ];
    }

    // Auth

    public function getAuthPassword(): string
    {
        return $this->pin;
    }

    // Relaciones

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    // Tenant actual

    public function currentTenant(): ?Tenant
    {
        $tenantId = session('current_tenant_id');

        if (! $tenantId) {
            return null;
        }

        // Landlord puede acceder a cualquier tenant directamente
        if ($this->isSuperAdmin()) {
            return Tenant::find($tenantId);
        }

        return $this->tenants()
            ->wherePivot('is_active', true)
            ->find($tenantId);
    }

    public function roleInCurrentTenant(): ?string
    {
        $tenant = $this->currentTenant();

        return $tenant?->pivot->role;
    }

    public function switchTenant(int $tenantId): bool
    {
        // Landlord puede acceder a cualquier tenant
        if ($this->isSuperAdmin()) {
            $tenant = \App\Models\Tenant::find($tenantId);
            if (! $tenant) {
                return false;
            }
            session(['current_tenant_id' => $tenantId]);
            return true;
        }

        $tenant = $this->tenants()
            ->wherePivot('is_active', true)
            ->find($tenantId);

        if (! $tenant) {
            return false;
        }

        session(['current_tenant_id' => $tenantId]);

        return true;
    }

    // Permisos

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function canManageCurrentTenant(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roleInCurrentTenant() === 'admin';
    }
}
