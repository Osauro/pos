<?php

use App\Helpers\TenantHelper;
use App\Models\Tenant;

if (! function_exists('currentTenantId')) {
    function currentTenantId(): ?int
    {
        return TenantHelper::currentId();
    }
}

if (! function_exists('currentTenant')) {
    function currentTenant(): ?Tenant
    {
        return TenantHelper::current();
    }
}

if (! function_exists('switchTenant')) {
    function switchTenant(int $tenantId): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->switchTenant($tenantId);
    }
}

if (! function_exists('isLandlord')) {
    function isLandlord(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->isSuperAdmin();
    }
}

if (! function_exists('isTenantAdmin')) {
    function isTenantAdmin(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        if (isLandlord()) {
            return true;
        }

        return auth()->user()->roleInCurrentTenant() === 'admin';
    }
}

if (! function_exists('isOperador')) {
    function isOperador(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->roleInCurrentTenant() === 'operador';
    }
}

if (! function_exists('canManageTenant')) {
    function canManageTenant(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->canManageCurrentTenant();
    }
}
