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

if (! function_exists('tenantBusinessDay')) {
    /**
     * Devuelve la fecha del día comercial para el datetime dado (o ahora si null).
     * Usa el horario de atención del tenant activo.
     */
    function tenantBusinessDay(?\Carbon\Carbon $dt = null): \Carbon\Carbon
    {
        $dt     = $dt ?? \Carbon\Carbon::now();
        $tenant = TenantHelper::current();

        if (!$tenant) {
            return $dt->copy()->startOfDay();
        }

        return $tenant->businessDayFor($dt);
    }
}

if (! function_exists('tenantBusinessDayRange')) {
    /**
     * Devuelve [inicio, fin] del día comercial para la fecha dada.
     * Usa el horario de atención del tenant activo.
     */
    function tenantBusinessDayRange(\Carbon\Carbon $date): array
    {
        $tenant = TenantHelper::current();

        if (!$tenant) {
            return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];
        }

        return $tenant->businessDayRange($date);
    }
}
