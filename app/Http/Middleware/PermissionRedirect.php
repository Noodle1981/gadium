<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionRedirect
{
    /**
     * Handle an incoming request.
     * Redirects authenticated users to their appropriate landing page based on permissions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $user = $request->user();
            $path = $request->path();

            // Only redirect from generic paths
            if ($path === 'dashboard' || $path === 'app' || $path === '/' || $path === '') {
                return $this->redirectToLandingPage($user);
            }
        }

        return $next($request);
    }

    /**
     * Determine the appropriate landing page based on user permissions.
     * Priority order matches the most common user needs.
     */
    protected function redirectToLandingPage($user)
    {
        // Dashboard access (Admin/Manager level)
        if ($user->can('view_dashboards')) {
            return redirect()->route('app.dashboard');
        }

        // Module-specific access (specialized roles)
        if ($user->can('view_sales')) {
            return redirect()->route('app.sales.index');
        }

        if ($user->can('view_budgets')) {
            return redirect()->route('app.budgets.index');
        }

        if ($user->can('view_hours')) {
            return redirect()->route('app.hours.index');
        }

        if ($user->can('view_purchases')) {
            return redirect()->route('app.purchases.index');
        }

        if ($user->can('view_staff_satisfaction')) {
            return redirect()->route('app.staff-satisfaction.surveys');
        }

        if ($user->can('view_client_satisfaction')) {
            return redirect()->route('app.client-satisfaction.index');
        }

        if ($user->can('view_boards')) {
            return redirect()->route('app.boards.index');
        }

        if ($user->can('view_automation')) {
            return redirect()->route('app.automation.index');
        }

        if ($user->can('view_production')) {
            return redirect()->route('app.production.index');
        }

        if ($user->can('view_hr')) {
            return redirect()->route('app.hr.index');
        }

        // Fallback to profile if no module permissions
        return redirect()->route('app.profile');
    }
}
