<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $user = $request->user();
            $path = $request->path();

            // Redirecciones dinámicas basadas en rol
            $rolePrefix = '';
            if ($user->hasAnyRole(['Super Admin', 'Admin'])) $rolePrefix = 'admin';
            elseif ($user->hasRole('Manager')) $rolePrefix = 'manager';
            elseif ($user->hasRole('Vendedor')) $rolePrefix = 'sales';
            elseif ($user->hasRole('Presupuestador')) $rolePrefix = 'budget';
            elseif ($user->hasRole('Viewer')) $rolePrefix = 'viewer';

            // Si no hay prefijo (usuario sin rol), dejar continuar al dashboard base
            if (empty($rolePrefix)) {
                return $next($request);
            }

            if ($path === 'dashboard') {
                return redirect()->route("$rolePrefix.dashboard");
            }

            if ($path === 'sales/import') {
                return redirect()->route("$rolePrefix.sales.import");
            }

            if ($path === 'clients/resolve') {
                return redirect()->route("$rolePrefix.clients.resolve");
            }
        }

        return $next($request);
    }
}
