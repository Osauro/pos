<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Super admin sin tenant activo: redirigir al panel landlord
        if ($user->isSuperAdmin() && ! session('current_tenant_id')) {
            return redirect()->route('admin.dashboard');
        }

        // Usuario sin tenant activo: intentar auto-asignar el primero disponible
        if (! session('current_tenant_id')) {
            $tenant = $user->tenants()->wherePivot('is_active', true)->first();

            if (! $tenant) {
                auth()->logout();
                return redirect()->route('login')->with('error', 'No tienes un negocio asignado.');
            }

            $user->switchTenant($tenant->id);
        }

        $tenant = $user->currentTenant();

        if (! $tenant) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Negocio no encontrado.');
        }

        if (! $tenant->isActivo()) {
            // Permitir acceso a la página de suscripción para mostrar el QR de pago
            if ($request->routeIs('suscripcion')) {
                return $next($request);
            }
            return redirect()->route('suscripcion');
        }

        return $next($request);
    }
}
