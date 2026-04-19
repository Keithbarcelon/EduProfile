<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\School;
use App\Services\PlanManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
        if ($plan->is_system_preset) {
            return redirect()->route('developer.tenants.plan-management', ['tab' => 'modular'])
                ->with('error', 'System preset plans cannot be deleted. Archive it instead.');
        }

        $plan->delete();

        return redirect()->route('developer.tenants.plan-management', ['tab' => 'modular'])
            ->with('success', 'Plan deleted successfully.');
    }

    public function duplicate(Plan $plan): RedirectResponse
    {
        $copy = $this->planManagementService->duplicatePlan($plan->load('features'));

        return redirect()->route('developer.plans.edit', $copy)
            ->with('success', 'Plan duplicated. Review and save your changes.');
    }

    public function setActive(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $isActive = (bool) $validated['is_active'];

        $this->planManagementService->setPlanActive($plan, $isActive);

        return redirect()->route('developer.tenants.plan-management', ['tab' => 'modular'])
            ->with('success', $isActive ? 'Plan activated successfully.' : 'Plan archived successfully.');
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
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'preset_key' => ['nullable', Rule::in(['basic', 'standard', 'premium'])],
            'slug' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'quarterly', 'yearly'])],
            'price' => ['required', 'numeric', 'min:0'],
            'is_sale' => ['nullable', 'boolean'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price', 'required_if:is_sale,1', 'exclude_unless:is_sale,1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'feature_keys' => ['nullable', 'array'],
            'feature_keys.*' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'feature_labels' => ['nullable', 'array'],
            'feature_labels.*' => ['nullable', 'string', 'max:120'],
            'feature_values' => ['nullable', 'array'],
            'feature_values.*' => ['nullable', 'string', 'max:255'],
            'feature_limits' => ['nullable', 'array'],
            'feature_limits.*' => ['nullable', 'integer', 'min:0'],
            'feature_enabled' => ['nullable', 'array'],
            'feature_enabled.*' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $featureKeys = (array) $request->input('feature_keys', []);
            $featureLabels = (array) $request->input('feature_labels', []);
            $normalizedKeys = [];
            $validFeatureCount = 0;

            foreach ($featureKeys as $index => $rawKey) {
                $normalizedKey = Str::of((string) $rawKey)
                    ->trim()
                    ->lower()
                    ->replace(' ', '_')
                    ->replaceMatches('/[^a-z0-9_.-]/', '')
                    ->toString();

                $label = trim((string) ($featureLabels[$index] ?? ''));

                if ($normalizedKey === '' && $label === '') {
                    continue;
                }

                if ($normalizedKey === '') {
                    $validator->errors()->add('feature_keys.'.$index, 'A feature key is required when label/value is provided.');
                    continue;
                }

                if ($label === '') {
                    $validator->errors()->add('feature_labels.'.$index, 'A feature label is required for each feature key.');
                }

                if (isset($normalizedKeys[$normalizedKey])) {
                    $validator->errors()->add('feature_keys.'.$index, 'Feature keys must be unique per plan.');
                }

                $normalizedKeys[$normalizedKey] = true;
                $validFeatureCount++;
            }

            if ($validFeatureCount === 0) {
                $validator->errors()->add('feature_keys', 'Add at least one valid feature with key and label.');
            }
        });

        return $validator->validate();
    }
}
