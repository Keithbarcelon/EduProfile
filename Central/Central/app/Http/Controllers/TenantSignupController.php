<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantSignupRequest;
use App\Notifications\TenantRequestReceivedNotification;
use App\Services\TenantRequestSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class TenantSignupController extends Controller
{
    public function __construct(private readonly TenantRequestSubmissionService $tenantRequestSubmissionService)
    {
    }

    public function create(): View
    {
        return view('tenants.signup');
    }

    public function store(StoreTenantSignupRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $tenantRequest = $this->tenantRequestSubmissionService->submit(
            $validated,
            $request->ip(),
            $request->userAgent()
        );

        try {
            Notification::route('mail', $validated['admin_email'])
                ->notify(new TenantRequestReceivedNotification($tenantRequest));

            $reminderEmail = $validated['plan_expiration_email'] ?? null;
            if ($reminderEmail && $reminderEmail !== $validated['admin_email']) {
                Notification::route('mail', $reminderEmail)
                    ->notify(new TenantRequestReceivedNotification($tenantRequest));
            }
        } catch (\Throwable $exception) {
            Log::warning('Tenant request notification failed, continuing without blocking signup flow.', [
                'tenant_request_id' => $tenantRequest->id,
                'admin_email' => $validated['admin_email'],
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('tenant-signup.create')
                ->with('success', 'Tenant request submitted successfully. Central admin approval is required before account creation and tenant database provisioning.')
                ->with('tenant_requested_domain', $tenantRequest->requested_tenant_domain);
    }
}
