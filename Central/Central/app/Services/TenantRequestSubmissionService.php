<?php

namespace App\Services;

use App\Models\School;

class TenantRequestSubmissionService
{
    public function __construct(private readonly TenantDatabaseProvisioner $databaseProvisioner)
    {
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function submit(array $validated, ?string $submittedIp, ?string $submittedUserAgent): School
    {
        $planStartedAt = now()->toDateString();
        $planDueAt = match ((string) $validated['plan_type']) {
            'premium' => now()->addYear()->toDateString(),
            default => now()->addMonth()->toDateString(),
        };

        $tenantName = (string) $validated['tenant_name'];
        $databaseName = $this->databaseProvisioner->generateUniqueDatabaseName($tenantName);

        return School::create([
            'name' => $tenantName,
            'school_type' => 'School',
            'email' => $validated['admin_email'],
            'contact_number' => null,
            'approval_status' => School::STATUS_PENDING,
            'is_enabled' => false,
            'tenant_domain' => null,
            'tenant_database' => $databaseName,
            'approved_at' => null,
            'address' => $validated['address'],
            'plan_type' => $validated['plan_type'],
            'plan_started_at' => $planStartedAt,
            'plan_due_at' => $planDueAt,
            'signup_admin_name' => $validated['signup_admin_name'],
            'signup_admin_password' => $validated['admin_password'],
            'plan_expiration_email' => $validated['plan_expiration_email'] ?? $validated['admin_email'],
            'requested_tenant_domain' => $validated['tenant_domain'] ?? null,
        ]);
    }
}
