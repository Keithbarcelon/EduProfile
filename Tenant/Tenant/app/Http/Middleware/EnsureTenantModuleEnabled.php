<?php

namespace App\Http\Middleware;

use App\Services\TenantCustomizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        if ($modules === []) {
            return $next($request);
        }

        $customization = app(TenantCustomizationService::class);

        foreach ($modules as $moduleKey) {
            if (! $customization->moduleEnabled($moduleKey, true)) {
                abort(403, 'This module is disabled for your tenant configuration.');
            }
        }

        return $next($request);
    }
}
