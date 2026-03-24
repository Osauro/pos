<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLandlord
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! isLandlord()) {
            abort(403, 'Acceso restringido al administrador del sistema.');
        }

        return $next($request);
    }
}
