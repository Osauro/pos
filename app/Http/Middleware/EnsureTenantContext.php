<?php

namespace App\Http\Middleware;

use App\Helpers\TenantHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está autenticado y aún no hay tenant en sesión,
        // lo cargamos automáticamente desde su propio registro
        if (Auth::check() && !TenantHelper::currentId()) {
            $usuario = Auth::user();

            // Landlord (is_owner = true) no necesita tenant en sesión
            if ($usuario->is_owner) {
                return $next($request);
            }

            if ($usuario->tenant_id) {
                TenantHelper::set($usuario->tenant_id);
            } else {
                // Usuario sin tenant asignado → redirigir a crear tienda
                return redirect()->route('crear-tienda');
            }
        }

        // Si no está autenticado ni hay tenant, dejar pasar
        // (el middleware 'auth' ya maneja la redirección al login)
        return $next($request);
    }
}

