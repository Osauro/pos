<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder');
        }

        if (!in_array(auth()->user()->tipo, ['admin', 'user'])) {
            abort(403, 'No tiene permisos para acceder a esta sección');
        }

        return $next($request);
    }
}
