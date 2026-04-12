@php
    $planModel = $plan ?? null;
    $fieldClass = 'w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-200';
    $featureRows = old('feature_keys')
        ? collect(old('feature_keys'))->map(function ($key, $index) {
            return [
                'feature_key' => old('feature_keys.'.$index),
                'feature_label' => old('feature_labels.'.$index),
                'feature_value' => old('feature_values.'.$index),
                'limit_value' => old('feature_limits.'.$index),
                'is_enabled' => in_array((string) $index, collect(old('feature_enabled', []))->map(fn ($val) => (string) $val)->all(), true),
            ];
        })
        : ($planModel?->features->map(fn ($feature) => [
            'feature_key' => $feature->feature_key,
            'feature_label' => $feature->feature_label,
            'feature_value' => $feature->feature_value,
            'limit_value' => $feature->limit_value,
            'is_enabled' => $feature->is_enabled,
        ]) ?? collect());

    if ($featureRows->isEmpty()) {
        $featureRows = collect([[
            'feature_key' => '',
            'feature_label' => '',
            'feature_value' => '',
            'limit_value' => '',
            'is_enabled' => true,
        ]]);
    }
@endphp

<div class="modular-plan-form space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="{{ $labelClass }}">Plan Name</label>
            <input type="text" name="name" value="{{ old('name', $planModel?->name) }}" required class="{{ $fieldClass }}">
        </div>
        <div>
            <label class="{{ $labelClass }}">Slug (optional)</label>
            <input type="text" name="slug" value="{{ old('slug', $planModel?->slug) }}" class="{{ $fieldClass }}">
        </div>
        <div>
            <label class="{{ $labelClass }}">Billing Cycle</label>
            <select name="billing_cycle" class="{{ $fieldClass }}">
                @foreach(['monthly', 'quarterly', 'yearly'] as $cycle)
                <option value="{{ $cycle }}" @selected(old('billing_cycle', $planModel?->billing_cycle ?? 'monthly') === $cycle)>{{ ucfirst($cycle) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $labelClass }}">Price</label>
            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $planModel?->price ?? 0) }}" class="{{ $fieldClass }}">
        </div>
    </div>

    <div>
        <label class="{{ $labelClass }}">Description</label>
        <textarea name="description" rows="2" class="{{ $fieldClass }}">{{ old('description', $planModel?->description) }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-200">
            <input type="checkbox" name="is_sale" value="1" @checked(old('is_sale', $planModel?->is_sale)) class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            Sale plan enabled
        </label>
        <div>
            <label class="{{ $labelClass }}">Sale Price</label>
            <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $planModel?->sale_price) }}" class="{{ $fieldClass }}">
        </div>
        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-200">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $planModel?->is_active ?? true)) class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            Plan active
        </label>
        <div>
            <label class="{{ $labelClass }}">Sale Starts At</label>
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($planModel?->starts_at)->format('Y-m-d\\TH:i')) }}" class="{{ $fieldClass }}">
        </div>
        <div>
            <label class="{{ $labelClass }}">Sale Ends At</label>
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($planModel?->ends_at)->format('Y-m-d\\TH:i')) }}" class="{{ $fieldClass }}">
        </div>
    </div>

    <div>
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Plan Features</h3>
            <button type="button" id="add-feature-row" class="rounded-lg bg-cyan-100 px-3 py-1.5 text-xs font-semibold text-cyan-700 hover:bg-cyan-200">Add Feature</button>
        </div>

        <div id="feature-rows" class="space-y-3">
            @foreach($featureRows as $index => $row)
                <div class="feature-row grid grid-cols-1 gap-3 rounded-xl border border-slate-700 bg-slate-900/80 p-3 lg:grid-cols-5">
                    <input type="text" name="feature_keys[]" value="{{ $row['feature_key'] }}" placeholder="feature_key" class="{{ $fieldClass }}">
                    <input type="text" name="feature_labels[]" value="{{ $row['feature_label'] }}" placeholder="Feature label" class="{{ $fieldClass }}">
                    <input type="text" name="feature_values[]" value="{{ $row['feature_value'] }}" placeholder="Value (optional)" class="{{ $fieldClass }}">
                    <input type="number" min="0" name="feature_limits[]" value="{{ $row['limit_value'] }}" placeholder="Limit" class="{{ $fieldClass }}">
                    <div class="flex items-center justify-between gap-2">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-200">
                            <input type="checkbox" name="feature_enabled[]" value="{{ $index }}" @checked($row['is_enabled']) class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                            Enabled
                        </label>
                        <button type="button" class="remove-feature-row rounded-md bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-200">Remove</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <a href="{{ route('developer.tenants.plan-management', ['tab' => 'modular']) }}" class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-slate-800">Cancel</a>
        <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">{{ $submitLabel ?? 'Save Plan' }}</button>
    </div>
</div>

<script>
    (function () {
        const rowsContainer = document.getElementById('feature-rows');
        const addButton = document.getElementById('add-feature-row');

        if (!rowsContainer || !addButton) {
            return;
        }

        const bindRemoveButtons = () => {
            rowsContainer.querySelectorAll('.remove-feature-row').forEach((button) => {
                button.onclick = () => {
                    const rows = rowsContainer.querySelectorAll('.feature-row');
                    if (rows.length <= 1) {
                        return;
                    }
                    button.closest('.feature-row')?.remove();
                };
            });
        };

        addButton.addEventListener('click', () => {
            const index = rowsContainer.querySelectorAll('.feature-row').length;
            const row = document.createElement('div');
            row.className = 'feature-row grid grid-cols-1 gap-3 rounded-xl border border-slate-700 bg-slate-900/80 p-3 lg:grid-cols-5';
            row.innerHTML = `
                <input type="text" name="feature_keys[]" placeholder="feature_key" class="w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500">
                <input type="text" name="feature_labels[]" placeholder="Feature label" class="w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500">
                <input type="text" name="feature_values[]" placeholder="Value (optional)" class="w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500">
                <input type="number" min="0" name="feature_limits[]" placeholder="Limit" class="w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500">
                <div class="flex items-center justify-between gap-2">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-200">
                        <input type="checkbox" name="feature_enabled[]" value="${index}" checked class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                        Enabled
                    </label>
                    <button type="button" class="remove-feature-row rounded-md bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-200">Remove</button>
                </div>
            `;
            rowsContainer.appendChild(row);
            bindRemoveButtons();
        });

        bindRemoveButtons();
    })();
</script>

<style>
    .modular-plan-form input:not([type="checkbox"]):not([type="radio"]),
    .modular-plan-form select,
    .modular-plan-form textarea {
        background-color: #0b1220 !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
        color-scheme: dark;
    }

    .modular-plan-form input:focus,
    .modular-plan-form select:focus,
    .modular-plan-form textarea:focus {
        border-color: #06b6d4 !important;
        box-shadow: 0 0 0 1px rgba(6, 182, 212, 0.45) !important;
    }

    .modular-plan-form input::placeholder,
    .modular-plan-form textarea::placeholder {
        color: #94a3b8 !important;
    }

    .modular-plan-form select option {
        background-color: #0b1220;
        color: #e2e8f0;
    }
</style>
