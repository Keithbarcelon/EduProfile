<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $normalizedHost = preg_replace('/^www\./', '', $host) ?? $host;

        $isTenantDomain = School::query()
            ->where('tenant_domain', $host)
            ->orWhere('tenant_domain', $normalizedHost)
            ->exists();

        if ($isTenantDomain) {
            abort(404);
        }

        return $next($request);
    }
}
