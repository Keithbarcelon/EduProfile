<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

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

        // Handle administrative role aliases
        $effectiveRoles = $roles;
        if (in_array('tenant_admin', $roles)) {
            $effectiveRoles[] = 'admin';
        }

        if (! in_array($user->role, $effectiveRoles)) {
            abort(403, 'Unauthorized. You do not have the required role for this action.');
        }

        return $next($request);
    }
}
