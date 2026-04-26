<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $requiredRoles = collect($roles)
            ->map(fn (string $role): string => str_replace('-', '_', strtolower(trim($role))))
            ->filter(fn (string $role): bool => $role !== '')
            ->values()
            ->all();

        if (in_array('tenant_admin', $requiredRoles, true) && ! in_array('admin', $requiredRoles, true)) {
            $requiredRoles[] = 'admin';
        }

        if (in_array('admin', $requiredRoles, true) && ! in_array('tenant_admin', $requiredRoles, true)) {
            $requiredRoles[] = 'tenant_admin';
        }

        if (! $user->hasAnyRoleSlug($requiredRoles)) {
            abort(403, 'Unauthorized. You do not have the required role for this action.');
        }

        return $next($request);
    }
}
