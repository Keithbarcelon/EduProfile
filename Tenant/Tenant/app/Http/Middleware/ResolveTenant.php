<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\School;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * Resolves the tenant from the request domain and switches the database connection.
     * Also configures mail settings to use the tenant's domain.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $domain = strtolower($request->getHost());

        if ($request->is('register') || $request->routeIs('register') || $this->isLoginHubDomain($domain)) {
            return $next($request);
        }

        // Resolve tenant from domain
        $normalizedDomain = preg_replace('/^www\./', '', $domain) ?? $domain;

        $candidateDomains = array_unique(array_filter([
            $domain,
            $normalizedDomain,
            $this->mapLocalDomainAlias($domain),
            $this->mapLocalDomainAlias($normalizedDomain),
        ]));

        // Query central database to find tenant
        $centralTenant = DB::connection('central')
            ->table('schools')
            ->whereIn('tenant_domain', $candidateDomains)
            ->whereNotNull('tenant_database')
            ->first();

        if (!$centralTenant) {
            abort(404, 'Tenant not found');
        }

        $isAuthRoute = $this->isTenantAuthRoute($request);

        if ($centralTenant->plan_due_at && Carbon::parse((string) $centralTenant->plan_due_at)->isPast()) {
            DB::connection('central')
                ->table('schools')
                ->where('id', $centralTenant->id)
                ->update([
                    'is_enabled' => false,
                    'disabled_at' => now(),
                    'disable_reason' => 'Plan expired',
                ]);

            $centralTenant->is_enabled = false;
            $centralTenant->disable_reason = 'Plan expired';
        }

        // Update database configuration to use tenant's database
        config([
            'database.connections.mysql.database' => $centralTenant->tenant_database,
        ]);

        // Purge the connection to force reconnection with new database
        DB::purge('mysql');

        $tenant = School::query()
            ->where('tenant_database', $centralTenant->tenant_database)
            ->orWhere('tenant_domain', $centralTenant->tenant_domain)
            ->first();

        if (! $tenant) {
            abort(500, 'Tenant school record not found in tenant database');
        }

        // Configure mail settings for this tenant
        $this->configureMailForTenant($centralTenant, $domain);

        // Store tenant info in request for later use
        $request->attributes->set('tenant', $tenant);
        app()->instance('currentSchool', $tenant);

        if (! $centralTenant->is_enabled && ! $isAuthRoute) {
            $reason = trim((string) ($centralTenant->disable_reason ?? ''));
            $reasonText = $reason !== '' ? $reason : 'Tenant access was disabled by the administrator.';

            abort(403, 'This tenant is disabled. Reason: '.$reasonText);
        }

        return $next($request);
    }

    /**
     * Configure mail settings for the tenant.
     */
    private function configureMailForTenant($tenant, string $domain): void
    {
        // Use tenant's email if available, otherwise use a default from their domain
        $fromEmail = $tenant->email ?? "noreply@{$domain}";
        $fromName = $tenant->name ?? config('app.name');

        // Update mail configuration
        config([
            'mail.from.address' => $fromEmail,
            'mail.from.name' => $fromName,
        ]);

        // Configure mailer with tenant-specific settings
        Mail::alwaysFrom($fromEmail, $fromName);
    }

    /**
     * Map local development aliases between .local and .localhost domains.
     */
    private function mapLocalDomainAlias(string $domain): ?string
    {
        if (str_ends_with($domain, '.localhost')) {
            return preg_replace('/\.localhost$/', '.local', $domain) ?: null;
        }

        if (str_ends_with($domain, '.local')) {
            return preg_replace('/\.local$/', '.localhost', $domain) ?: null;
        }

        return null;
    }

    private function isLoginHubDomain(string $domain): bool
    {
        $hubDomain = strtolower(trim((string) env('LOGIN_HUB_DOMAIN', '')));

        if ($hubDomain === '') {
            return false;
        }

        return in_array($hubDomain, array_filter([
            $domain,
            preg_replace('/^www\./', '', $domain) ?: null,
            $this->mapLocalDomainAlias($domain),
        ]), true);
    }

    private function isTenantAuthRoute(Request $request): bool
    {
        return $request->is('login')
            || $request->is('logout')
            || $request->is('forgot-password')
            || $request->is('reset-password')
            || $request->is('reset-password/*')
            || $request->is('auth/transfer-login')
            || $request->routeIs('login')
            || $request->routeIs('logout')
            || $request->routeIs('password.*')
            || $request->routeIs('verification.*');
    }
}

