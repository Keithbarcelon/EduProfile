<?php

namespace App\Http\Middleware;

use App\Models\Status;
use App\Support\StudentStatusRules;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentStatusRoleRestrictions
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $statusId = (int) $request->input('status_id');
        if ($statusId <= 0) {
            return $next($request);
        }

        if (! Schema::hasTable('statuses')) {
            abort(503, 'Status management tables are not yet available for this tenant. Please run tenant migrations.');
        }

        $status = Status::query()->find($statusId);
        if (! $status) {
            abort(422, 'Selected status is invalid.');
        }

        if (! StudentStatusRules::canRoleAssignStatus((string) $user->role, (string) $status->name)) {
            abort(403, 'Your role is not allowed to assign this status.');
        }

        return $next($request);
    }
}
