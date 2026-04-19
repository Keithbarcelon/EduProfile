<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\School;
use App\Models\TenantPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlanManagementService
{
    public function duplicatePlan(Plan $plan): Plan
    {
        return DB::transaction(function () use ($plan): Plan {
            $payload = [
                'name' => $plan->name.' Copy',
                'slug' => $this->resolveUniqueSlug($plan->slug.'-copy'),
                'description' => $plan->description,
                'billing_cycle' => $plan->billing_cycle,
                'price' => $plan->price,
                'is_sale' => (bool) $plan->is_sale,
                'sale_price' => $plan->sale_price,
                'starts_at' => $plan->starts_at,
                'ends_at' => $plan->ends_at,
                'is_active' => false,
                'is_system_preset' => false,
                'preset_key' => null,
            ];

            $copy = Plan::create($payload);

            $featureRows = $plan->features()->get()->map(fn ($feature) => [
                'feature_key' => $feature->feature_key,
                'feature_label' => $feature->feature_label,
                'feature_value' => $feature->feature_value,
                'limit_value' => $feature->limit_value,
                'is_enabled' => (bool) $feature->is_enabled,
            ])->all();

            if (! empty($featureRows)) {
                $copy->features()->createMany($featureRows);
            }

            return $copy->fresh(['features']);
        });
    }

    public function setPlanActive(Plan $plan, bool $isActive): Plan
    {
        $plan->update([
            'is_active' => $isActive,
        ]);

        return $plan->fresh();
    }

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
            TenantPlan::query()
                ->where('school_id', $school->id)
                ->whereIn('status', ['active', 'pending'])
                ->update([
                    'status' => 'expired',
                    'ends_at' => now()->toDateString(),
                ]);

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
        $isSale = (bool) ($validated['is_sale'] ?? false);
        $presetKey = trim((string) ($validated['preset_key'] ?? ''));
        $isPresetCreate = $plan === null && in_array($presetKey, ['basic', 'standard', 'premium'], true);

        return [
            'name' => $validated['name'],
            'slug' => $this->resolveUniqueSlug((string) ($validated['slug'] ?? $validated['name']), $plan),
            'description' => $validated['description'] ?? null,
            'billing_cycle' => $validated['billing_cycle'],
            'price' => $validated['price'],
            'is_sale' => $isSale,
            'sale_price' => $isSale ? ($validated['sale_price'] ?? null) : null,
            'starts_at' => $isSale ? ($validated['starts_at'] ?? null) : null,
            'ends_at' => $isSale ? ($validated['ends_at'] ?? null) : null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'is_system_preset' => $plan?->is_system_preset ?? $isPresetCreate,
            'preset_key' => $plan?->preset_key ?? ($isPresetCreate ? $presetKey : null),
        ];
    }

    private function syncFeatures(Plan $plan, array $validated): void
    {
        $keys = $validated['feature_keys'] ?? [];
        $labels = $validated['feature_labels'] ?? [];
        $values = $validated['feature_values'] ?? [];
        $limits = $validated['feature_limits'] ?? [];
        $enabledRows = collect($validated['feature_enabled'] ?? [])
            ->map(fn ($value) => in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true))
            ->all();

        $rows = [];
        $usedKeys = [];

        foreach ($keys as $index => $key) {
            $featureKey = Str::of((string) $key)
                ->trim()
                ->lower()
                ->replace(' ', '_')
                ->replaceMatches('/[^a-z0-9_.-]/', '')
                ->toString();
            $featureLabel = trim((string) ($labels[$index] ?? ''));

            if ($featureKey === '' || $featureLabel === '' || in_array($featureKey, $usedKeys, true)) {
                continue;
            }

            $usedKeys[] = $featureKey;

            $rows[] = [
                'feature_key' => $featureKey,
                'feature_label' => $featureLabel,
                'feature_value' => trim((string) ($values[$index] ?? '')) ?: null,
                'limit_value' => ($limits[$index] ?? null) !== null && $limits[$index] !== '' ? (int) $limits[$index] : null,
                'is_enabled' => (bool) ($enabledRows[$index] ?? false),
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
