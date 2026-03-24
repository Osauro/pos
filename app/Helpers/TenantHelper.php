<?php

namespace App\Helpers;

use App\Models\Tenant;

class TenantHelper
{
    public static function currentId(): ?int
    {
        if (auth()->check()) {
            return auth()->user()->currentTenant()?->id;
        }

        return session('current_tenant_id');
    }

    public static function current(): ?Tenant
    {
        if (auth()->check()) {
            return auth()->user()->currentTenant();
        }

        $id = session('current_tenant_id');
        return $id ? Tenant::find($id) : null;
    }

    public static function set(int $tenantId): void
    {
        session(['current_tenant_id' => $tenantId]);
    }

    public static function clear(): void
    {
        session()->forget('current_tenant_id');
    }
}
