<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\School;
use App\Models\TenantPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlanManagementService
{
    public function createPlan(array $validated): Plan
    {
        return DB::transaction(function () use ($validated): Plan {
            $plan = Plan::create($this->planPayload($validated));
            $this->syncFeatures($plan, $validated);

            return $plan->fresh(['features']);
        });
    }

    public function updatePlan(Plan $plan, array $validated): Plan
    {
        return DB::transaction(function () use ($plan, $validated): Plan {
            $plan->update($this->planPayload($validated, $plan));
            $this->syncFeatures($plan, $validated);

            return $plan->fresh(['features']);
        });
    }

    public function assignPlanToTenant(School $school, Plan $plan, array $validated): TenantPlan
    {
        return DB::transaction(function () use ($school, $plan, $validated): TenantPlan {
            $assignment = TenantPlan::create([
                'school_id' => $school->id,
                'plan_id' => $plan->id,
                'starts_at' => $validated['starts_at'] ?? now()->toDateString(),
                'ends_at' => $validated['ends_at'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'metadata' => [
                    'assigned_by' => auth()->id(),
                ],
            ]);

            // Keep legacy plan fields synchronized while migrating to modular plan tables.
            $school->update([
                'plan_type' => Str::lower($plan->slug),
                'plan_started_at' => $assignment->starts_at,
                'plan_due_at' => $assignment->ends_at,
            ]);

            return $assignment;
        });
    }

    private function planPayload(array $validated, ?Plan $plan = null): array
    {
        return [
            'name' => $validated['name'],
            'slug' => $this->resolveUniqueSlug((string) ($validated['slug'] ?? $validated['name']), $plan),
            'description' => $validated['description'] ?? null,
            'billing_cycle' => $validated['billing_cycle'],
            'price' => $validated['price'],
            'is_sale' => (bool) ($validated['is_sale'] ?? false),
            'sale_price' => $validated['sale_price'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ];
    }

    private function syncFeatures(Plan $plan, array $validated): void
    {
        $keys = $validated['feature_keys'] ?? [];
        $labels = $validated['feature_labels'] ?? [];
        $values = $validated['feature_values'] ?? [];
        $limits = $validated['feature_limits'] ?? [];
        $enabledRows = collect($validated['feature_enabled'] ?? [])->map(fn ($index) => (int) $index)->all();

        $rows = [];

        foreach ($keys as $index => $key) {
            $featureKey = trim((string) $key);
            $featureLabel = trim((string) ($labels[$index] ?? ''));

            if ($featureKey === '' || $featureLabel === '') {
                continue;
            }

            $rows[] = [
                'feature_key' => $featureKey,
                'feature_label' => $featureLabel,
                'feature_value' => trim((string) ($values[$index] ?? '')) ?: null,
                'limit_value' => ($limits[$index] ?? null) !== null && $limits[$index] !== '' ? (int) $limits[$index] : null,
                'is_enabled' => in_array((int) $index, $enabledRows, true),
            ];
        }

        $plan->features()->delete();

        if (! empty($rows)) {
            $plan->features()->createMany($rows);
        }
    }

    private function resolveUniqueSlug(string $source, ?Plan $plan = null): string
    {
        $base = Str::slug($source);
        $base = $base !== '' ? $base : 'plan';
        $slug = $base;
        $counter = 1;

        while (
            Plan::query()
                ->where('slug', $slug)
                ->when($plan, fn ($query) => $query->whereKeyNot($plan->id))
                ->exists()
        ) {
            $counter++;
            $slug = $base.'-'.$counter;
        }

        return $slug;
    }
}
