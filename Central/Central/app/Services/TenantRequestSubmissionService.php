<?php

namespace App\Services;

use App\Models\TenantRequest;

class TenantRequestSubmissionService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function submit(array $validated, ?string $submittedIp, ?string $submittedUserAgent): TenantRequest
    {
        $planStartedAt = now()->toDateString();
        $planDueAt = match ((string) $validated['plan_type']) {
            'premium' => now()->addYear()->toDateString(),
            default => now()->addMonth()->toDateString(),
        };

        return TenantRequest::create([
            'tenant_name' => $validated['tenant_name'],
            'address' => $validated['address'],
            'plan_type' => $validated['plan_type'],
            'plan_started_at' => $planStartedAt,
            'plan_due_at' => $planDueAt,
            'signup_admin_name' => $validated['signup_admin_name'],
            'admin_email' => $validated['admin_email'],
            'admin_password' => $validated['admin_password'],
            'plan_expiration_email' => $validated['plan_expiration_email'] ?? $validated['admin_email'],
            'requested_tenant_domain' => $validated['tenant_domain'] ?? null,
            'status' => TenantRequest::STATUS_PENDING,
            'submitted_ip' => $submittedIp,
            'submitted_user_agent' => $submittedUserAgent,
        ]);
    }
}
