<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\ApproveTenantRequestActionRequest;
use App\Http\Requests\Developer\RejectTenantRequestActionRequest;
use App\Models\School;
use App\Models\TenantRequest;
use App\Notifications\SchoolRegistrationConfirmationNotification;
use App\Notifications\TenantRequestRejectedNotification;
use App\Services\TenantRequestApprovalService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use InvalidArgumentException;

class TenantRequestController extends Controller
{
    public function __construct(private readonly TenantRequestApprovalService $tenantRequestApprovalService)
    {
    }

    public function index(Request $request): View
    {
        $query = TenantRequest::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('tenant_name', 'like', "%{$search}%")
                    ->orWhere('admin_email', 'like', "%{$search}%")
                    ->orWhere('signup_admin_name', 'like', "%{$search}%")
                    ->orWhere('requested_tenant_domain', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $tenantRequests = $query
            ->with(['reviewer:id,name,email', 'approvedSchool:id,name,tenant_domain'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => TenantRequest::count(),
            'pending' => TenantRequest::where('status', TenantRequest::STATUS_PENDING)->count(),
            'approved' => TenantRequest::where('status', TenantRequest::STATUS_APPROVED)->count(),
            'rejected' => TenantRequest::where('status', TenantRequest::STATUS_REJECTED)->count(),
        ];

        return view('developer.tenant-requests.index', compact('tenantRequests', 'summary'));
    }

    public function show(TenantRequest $tenantRequest): View
    {
        $tenantRequest->load(['reviewer:id,name,email', 'approvedSchool:id,name,tenant_domain,tenant_database']);

        return view('developer.tenant-requests.show', compact('tenantRequest'));
    }

    public function approve(ApproveTenantRequestActionRequest $request, TenantRequest $tenantRequest): RedirectResponse
    {
        try {
            $school = $this->tenantRequestApprovalService->approve(
                $tenantRequest,
                $request->user(),
                $request->validated('tenant_domain')
            );
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            Log::error('Tenant request approval failed.', [
                'tenant_request_id' => $tenantRequest->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Tenant request could not be approved due to a provisioning error. Please retry or check logs.');
        }

        $this->sendApprovalNotifications($school, $tenantRequest->fresh());

        return redirect()->route('developer.tenant-requests.show', $tenantRequest)
            ->with('success', 'Tenant request approved. Tenant account and database were provisioned successfully.');
    }

    public function reject(RejectTenantRequestActionRequest $request, TenantRequest $tenantRequest): RedirectResponse
    {
        try {
            $tenantRequest = $this->tenantRequestApprovalService->reject(
                $tenantRequest,
                $request->user(),
                $request->validated('rejection_reason')
            );
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $this->sendRejectionNotification($tenantRequest);

        return redirect()->route('developer.tenant-requests.show', $tenantRequest)
            ->with('success', 'Tenant request rejected.');
    }

    private function sendApprovalNotifications(School $school, TenantRequest $tenantRequest): void
    {
        try {
            Notification::route('mail', $tenantRequest->admin_email)
                ->notify(new SchoolRegistrationConfirmationNotification(
                    $school,
                    $tenantRequest->signup_admin_name,
                    $tenantRequest->admin_email
                ));

            if ($tenantRequest->plan_expiration_email && $tenantRequest->plan_expiration_email !== $tenantRequest->admin_email) {
                Notification::route('mail', $tenantRequest->plan_expiration_email)
                    ->notify(new SchoolRegistrationConfirmationNotification(
                        $school,
                        $tenantRequest->signup_admin_name,
                        $tenantRequest->admin_email
                    ));
            }
        } catch (\Throwable $exception) {
            Log::warning('Tenant approval notification failed.', [
                'tenant_request_id' => $tenantRequest->id,
                'school_id' => $school->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendRejectionNotification(TenantRequest $tenantRequest): void
    {
        try {
            Notification::route('mail', $tenantRequest->admin_email)
                ->notify(new TenantRequestRejectedNotification($tenantRequest));
        } catch (\Throwable $exception) {
            Log::warning('Tenant rejection notification failed.', [
                'tenant_request_id' => $tenantRequest->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
