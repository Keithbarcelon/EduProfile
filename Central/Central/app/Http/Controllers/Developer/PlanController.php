<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\School;
use App\Services\PlanManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function __construct(private readonly PlanManagementService $planManagementService)
    {
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('developer.tenants.plan-management', ['tab' => 'modular']);
    }

    public function create(): View
    {
        return view('developer.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePlan($request);

        $this->planManagementService->createPlan($validated);

        return redirect()->route('developer.tenants.plan-management', ['tab' => 'modular'])
            ->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan): View
    {
        return view('developer.plans.edit', [
            'plan' => $plan->load('features'),
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $this->validatePlan($request, $plan);

        $this->planManagementService->updatePlan($plan, $validated);

        return redirect()->route('developer.tenants.plan-management', ['tab' => 'modular'])
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('developer.tenants.plan-management', ['tab' => 'modular'])
            ->with('success', 'Plan deleted successfully.');
    }

    public function assign(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', Rule::exists('schools', 'id')],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'pending', 'expired', 'canceled'])],
        ]);

        $school = School::query()->findOrFail((int) $validated['school_id']);

        $this->planManagementService->assignPlanToTenant($school, $plan, $validated);

        return redirect()->route('developer.tenants.plan-management', ['tab' => 'modular'])
            ->with('success', 'Plan assigned to tenant successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePlan(Request $request, ?Plan $plan = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'quarterly', 'yearly'])],
            'price' => ['required', 'numeric', 'min:0'],
            'is_sale' => ['nullable', 'boolean'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'feature_keys' => ['nullable', 'array'],
            'feature_keys.*' => ['nullable', 'string', 'max:100'],
            'feature_labels' => ['nullable', 'array'],
            'feature_labels.*' => ['nullable', 'string', 'max:120'],
            'feature_values' => ['nullable', 'array'],
            'feature_values.*' => ['nullable', 'string', 'max:255'],
            'feature_limits' => ['nullable', 'array'],
            'feature_limits.*' => ['nullable', 'integer', 'min:0'],
            'feature_enabled' => ['nullable', 'array'],
            'feature_enabled.*' => ['nullable', 'integer'],
        ]);
    }
}
