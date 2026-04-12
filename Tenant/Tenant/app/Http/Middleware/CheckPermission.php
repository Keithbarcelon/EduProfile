<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (empty($permissions)) {
            return $next($request);
        }

        if (! $user->hasAnyPermission($permissions)) {
            abort(403, 'Unauthorized. You do not have the required permission for this action.');
        }

        return $next($request);
    }
}
