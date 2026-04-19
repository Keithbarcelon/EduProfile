<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class TrackBandwidthUsage
{
    /**
     * Handle an incoming request.
     *
     * Records total request and response bytes into the central usage table,
     * aggregated per tenant and date.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $tenant = $request->attributes->get('tenant');

        if (! $tenant instanceof School) {
            return $response;
        }

        $tenantDatabase = trim((string) $tenant->tenant_database);

        if ($tenantDatabase === '') {
            return $response;
        }

        $totalBytes = $this->resolveRequestBytes($request) + $this->resolveResponseBytes($response);

        if ($totalBytes <= 0) {
            return $response;
        }

        $this->storeUsage($tenantDatabase, (string) ($tenant->tenant_domain ?? ''), $totalBytes);

        return $response;
    }

    private function resolveRequestBytes(Request $request): int
    {
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);

        if ($contentLength > 0) {
            return $contentLength;
        }

        return strlen((string) $request->getContent());
    }

    private function resolveResponseBytes(Response $response): int
    {
        $contentLength = (int) $response->headers->get('Content-Length', '0');

        if ($contentLength > 0) {
            return $contentLength;
        }

        if ($response instanceof BinaryFileResponse) {
            $file = $response->getFile();

            if ($file !== null && $file->isFile()) {
                return (int) $file->getSize();
            }
        }

        return strlen((string) $response->getContent());
    }

    private function storeUsage(string $tenantDatabase, string $tenantDomain, int $totalBytes): void
    {
        try {
            DB::connection('central')->statement(
                'INSERT INTO tenant_bandwidth_metrics
                    (tenant_database, tenant_domain, usage_date, total_bytes, request_count, last_recorded_at, created_at, updated_at)
                 VALUES (?, ?, CURDATE(), ?, 1, NOW(), NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    total_bytes = total_bytes + VALUES(total_bytes),
                    request_count = request_count + 1,
                    tenant_domain = COALESCE(NULLIF(VALUES(tenant_domain), ""), tenant_domain),
                    last_recorded_at = NOW(),
                    updated_at = NOW()',
                [$tenantDatabase, $tenantDomain, $totalBytes]
            );
        } catch (Throwable $exception) {
            // Keep tenant requests resilient even if telemetry write fails.
            Log::warning('Failed to record bandwidth usage metric.', [
                'tenant_database' => $tenantDatabase,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
