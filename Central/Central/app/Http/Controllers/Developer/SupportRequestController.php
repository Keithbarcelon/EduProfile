<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SupportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportRequestController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = (string) $request->input('status', '');
        $tenantFilter = (int) $request->input('tenant_id', 0);

        $supportRequests = SupportRequest::query()
            ->with('tenant:id,name,tenant_domain')
            ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
            ->when($tenantFilter > 0, fn ($query) => $query->where('tenant_id', $tenantFilter))
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        $tenants = School::query()->orderBy('name')->get(['id', 'name', 'tenant_domain']);

        $statusCounts = SupportRequest::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('developer.support-requests.index', [
            'supportRequests' => $supportRequests,
            'tenants' => $tenants,
            'statusFilter' => $statusFilter,
            'tenantFilter' => $tenantFilter,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function show(SupportRequest $supportRequest): View
    {
        $supportRequest->load('tenant:id,name,tenant_domain,tenant_database,email');

        return view('developer.support-requests.show', [
            'request' => $supportRequest,
        ]);
    }

    public function updateStatus(Request $request, SupportRequest $supportRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved'])],
        ]);

        $supportRequest->update(['status' => $validated['status']]);

        $returnPage = (int) $request->input('page', 1);

        return redirect()->route('developer.support-requests.index', ['page' => $returnPage])
            ->with('success', 'Support request status updated to '.ucfirst($validated['status']).'.');
    }

    public function destroy(SupportRequest $supportRequest): RedirectResponse
    {
        $supportRequest->delete();

        return redirect()->route('developer.support-requests.index')
            ->with('success', 'Support request deleted.');
    }
}
