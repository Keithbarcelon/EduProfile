<?php

namespace App\Http\Middleware;

use App\Services\TenantCustomizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantFeatureEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        if ($features === []) {
            return $next($request);
        }

        $customization = app(TenantCustomizationService::class);

        foreach ($features as $featureKey) {
            if (! $customization->featureActive($featureKey, false)) {
                abort(403, 'This feature is currently disabled for your tenant configuration.');
            }
        }

        return $next($request);
    }
}
