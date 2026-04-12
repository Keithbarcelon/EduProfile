<?php

namespace App\Services;

use App\Models\School;
use App\Models\TenantRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TenantRequestApprovalService
{
    public function __construct(private readonly TenantDatabaseProvisioner $databaseProvisioner)
    {
    }

    public function approve(TenantRequest $tenantRequest, User $reviewer, ?string $approvedDomain = null): School
    {
        if (! $tenantRequest->isPending()) {
            throw new InvalidArgumentException('Only pending tenant requests can be approved.');
        }

        $domain = $this->resolveDomainForApproval($tenantRequest, $approvedDomain);
        $this->ensureDomainIsAvailable($domain, $tenantRequest->id);

        $databaseName = $this->databaseProvisioner->generateUniqueDatabaseName($tenantRequest->tenant_name);

        $this->databaseProvisioner->createDatabase($databaseName);
        $this->databaseProvisioner->migrateTenantSchema($databaseName);

        $approvedAt = now();

        $school = DB::transaction(function () use ($tenantRequest, $reviewer, $domain, $databaseName, $approvedAt): School {
            $school = School::create([
                'name' => $tenantRequest->tenant_name,
                'school_type' => 'School',
                'address' => $tenantRequest->address,
                'email' => $tenantRequest->admin_email,
                'contact_number' => null,
                'plan_type' => $tenantRequest->plan_type,
                'plan_started_at' => optional($tenantRequest->plan_started_at)->toDateString(),
                'plan_due_at' => optional($tenantRequest->plan_due_at)->toDateString(),
                'plan_expiration_email' => $tenantRequest->plan_expiration_email,
                'signup_admin_name' => $tenantRequest->signup_admin_name,
                'tenant_domain' => $domain,
                'requested_tenant_domain' => $domain,
                'tenant_database' => $databaseName,
                'is_enabled' => true,
                'approval_status' => School::STATUS_APPROVED,
                'approved_at' => $approvedAt,
            ]);

            $tenantRequest->update([
                'status' => TenantRequest::STATUS_APPROVED,
                'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => $approvedAt,
                'rejection_reason' => null,
                'approved_school_id' => $school->id,
                'requested_tenant_domain' => $domain,
            ]);

            return $school;
        });

        try {
            $this->databaseProvisioner->seedTenantCoreData($databaseName, [
                'name' => $school->name,
                'school_type' => $school->school_type,
                'address' => $school->address,
                'email' => $school->email,
                'contact_number' => $school->contact_number,
                'plan_type' => $school->plan_type,
                'plan_started_at' => optional($school->plan_started_at)->toDateString(),
                'plan_due_at' => optional($school->plan_due_at)->toDateString(),
                'plan_expiration_email' => $school->plan_expiration_email,
                'signup_admin_name' => $school->signup_admin_name,
                'tenant_domain' => $school->tenant_domain,
                'is_enabled' => true,
            ], [
                'name' => $school->signup_admin_name,
                'email' => $school->email,
                'password' => (string) $tenantRequest->admin_password,
            ]);
        } catch (\Throwable $exception) {
            DB::transaction(function () use ($school, $tenantRequest): void {
                $school->delete();

                $tenantRequest->update([
                    'status' => TenantRequest::STATUS_PENDING,
                    'reviewed_by_user_id' => null,
                    'reviewed_at' => null,
                    'rejection_reason' => null,
                    'approved_school_id' => null,
                ]);
            });

            throw $exception;
        }

        return $school;
    }

    public function reject(TenantRequest $tenantRequest, User $reviewer, string $reason): TenantRequest
    {
        if (! $tenantRequest->isPending()) {
            throw new InvalidArgumentException('Only pending tenant requests can be rejected.');
        }

        $tenantRequest->update([
            'status' => TenantRequest::STATUS_REJECTED,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $tenantRequest->refresh();
    }

    private function resolveDomainForApproval(TenantRequest $tenantRequest, ?string $approvedDomain = null): string
    {
        $domain = $approvedDomain ?: $tenantRequest->requested_tenant_domain;

        if (! $domain) {
            return $this->databaseProvisioner->generateUniqueDomain($tenantRequest->tenant_name);
        }

        return str_ends_with($domain, '.localhost') ? $domain : $domain.'.localhost';
    }

    private function ensureDomainIsAvailable(string $domain, int $tenantRequestId): void
    {
        $existsInSchools = School::query()
            ->where(function (Builder $query) use ($domain): void {
                $query->where('tenant_domain', $domain)
                    ->orWhere('requested_tenant_domain', $domain);
            })
            ->exists();

        if ($existsInSchools) {
            throw new InvalidArgumentException('Selected domain is already in use.');
        }

        $reservedByOtherPendingRequest = TenantRequest::query()
            ->whereKeyNot($tenantRequestId)
            ->where('status', TenantRequest::STATUS_PENDING)
            ->where('requested_tenant_domain', $domain)
            ->exists();

        if ($reservedByOtherPendingRequest) {
            throw new InvalidArgumentException('Selected domain is already reserved by another pending request.');
        }
    }
}
