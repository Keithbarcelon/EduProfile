@php
    $planModel = $plan ?? null;
    $isCreateMode = $planModel === null;
    $fieldClass = 'w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-200';
    $oldFeatureKeys = old('feature_keys');
    $featureRows = collect();

    if (is_array($oldFeatureKeys)) {
        foreach ($oldFeatureKeys as $index => $key) {
            $featureRows->put((int) $index, [
                'feature_key' => old('feature_keys.'.$index),
                'feature_label' => old('feature_labels.'.$index),
                'feature_value' => old('feature_values.'.$index),
                'limit_value' => old('feature_limits.'.$index),
                'is_enabled' => in_array((string) old('feature_enabled.'.$index, '0'), ['1', 'true', 'yes', 'on'], true),
            ]);
        }
    } elseif ($planModel) {
        $featureRows = $planModel->features
            ->values()
            ->mapWithKeys(fn ($feature, $index) => [
                $index => [
                    'feature_key' => $feature->feature_key,
                    'feature_label' => $feature->feature_label,
                    'feature_value' => $feature->feature_value,
                    'limit_value' => $feature->limit_value,
                    'is_enabled' => (bool) $feature->is_enabled,
                ],
            ]);
    }

    if ($featureRows->isEmpty()) {
        $featureRows = collect([
            0 => [
                'feature_key' => 'students_limit',
                'feature_label' => 'Students Limit',
                'feature_value' => '',
                'limit_value' => '300',
                'is_enabled' => true,
            ],
            1 => [
                'feature_key' => 'admin_users_limit',
                'feature_label' => 'Admin/Faculty Users Limit',
                'feature_value' => '',
                'limit_value' => '5',
                'is_enabled' => true,
            ],
        ]);
    }

    $featurePresets = [
        'basic' => [
            'identity' => [
                'name' => 'Basic',
                'billing_cycle' => 'monthly',
                'price' => '499.00',
                'description' => 'Starter package for small schools.',
            ],
            'features' => [
                ['feature_key' => 'students_limit', 'feature_label' => 'Students Limit', 'feature_value' => '', 'limit_value' => 300, 'is_enabled' => true],
                ['feature_key' => 'admin_users_limit', 'feature_label' => 'Admin/Faculty Users Limit', 'feature_value' => '', 'limit_value' => 5, 'is_enabled' => true],
                ['feature_key' => 'status_monitoring', 'feature_label' => 'Status Monitoring', 'feature_value' => 'Affirmative & Probation', 'limit_value' => '', 'is_enabled' => true],
                ['feature_key' => 'reports_access', 'feature_label' => 'Reports', 'feature_value' => 'Basic reports', 'limit_value' => '', 'is_enabled' => true],
            ],
        ],
        'standard' => [
            'identity' => [
                'name' => 'Standard',
                'billing_cycle' => 'monthly',
                'price' => '1299.00',
                'description' => 'Balanced package for growing schools.',
            ],
            'features' => [
                ['feature_key' => 'students_limit', 'feature_label' => 'Students Limit', 'feature_value' => '', 'limit_value' => 1500, 'is_enabled' => true],
                ['feature_key' => 'admin_users_limit', 'feature_label' => 'Admin/Faculty Users Limit', 'feature_value' => '', 'limit_value' => 20, 'is_enabled' => true],
                ['feature_key' => 'status_monitoring', 'feature_label' => 'Status Monitoring', 'feature_value' => 'Advanced monitoring dashboard', 'limit_value' => '', 'is_enabled' => true],
                ['feature_key' => 'data_export', 'feature_label' => 'Data Export', 'feature_value' => 'PDF/Excel', 'limit_value' => '', 'is_enabled' => true],
            ],
        ],
        'premium' => [
            'identity' => [
                'name' => 'Premium',
                'billing_cycle' => 'monthly',
                'price' => '2499.00',
                'description' => 'Advanced package for large institutions.',
            ],
            'features' => [
                ['feature_key' => 'students_limit', 'feature_label' => 'Students Limit', 'feature_value' => 'Unlimited', 'limit_value' => '', 'is_enabled' => true],
                ['feature_key' => 'admin_users_limit', 'feature_label' => 'Admin/Faculty Users Limit', 'feature_value' => 'Unlimited', 'limit_value' => '', 'is_enabled' => true],
                ['feature_key' => 'status_monitoring', 'feature_label' => 'Status Monitoring', 'feature_value' => 'Advanced analytics dashboard', 'limit_value' => '', 'is_enabled' => true],
                ['feature_key' => 'custom_branding', 'feature_label' => 'Custom Branding', 'feature_value' => 'Enabled', 'limit_value' => '', 'is_enabled' => true],
                ['feature_key' => 'automated_backups', 'feature_label' => 'Automated Backups', 'feature_value' => 'Enabled', 'limit_value' => '', 'is_enabled' => true],
            ],
        ],
    ];

    $nextRowIndex = ((int) $featureRows->keys()->max()) + 1;
    $initialPresetKey = old('preset_key', $planModel?->preset_key);
@endphp

<div class="modular-plan-form space-y-6">
    <div class="rounded-xl border border-cyan-900/40 bg-cyan-900/15 px-4 py-3">
        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-300">Plan Builder</p>
        <p class="mt-1 text-xs text-cyan-100/90">Use starter presets, then refine limits/features for your modular offer.</p>
    </div>

    <input type="hidden" id="preset_key" name="preset_key" value="{{ $initialPresetKey }}">

    @if($isCreateMode)
    <div class="rounded-xl border border-indigo-900/40 bg-indigo-900/20 p-4">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-indigo-200">Section 1: Plan Source</h3>
        <p class="mt-1 text-xs text-indigo-100/80">Create from preset (Basic, Standard, Premium) or start blank custom.</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <button type="button" class="source-preset-btn rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-200" data-preset="basic">Basic Preset</button>
            <button type="button" class="source-preset-btn rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-200" data-preset="standard">Standard Preset</button>
            <button type="button" class="source-preset-btn rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-200" data-preset="premium">Premium Preset</button>
            <button type="button" id="start-blank-plan" class="rounded-lg bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-300">Start Blank Custom</button>
        </div>
    </div>
    @endif

    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-300">Section 2: Plan Identity</h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="{{ $labelClass }}">Plan Name</label>
            <input type="text" id="plan_name" name="name" value="{{ old('name', $planModel?->name) }}" required class="{{ $fieldClass }}">
        </div>
        <div>
            <label class="{{ $labelClass }}">Slug (optional)</label>
            <input type="text" id="plan_slug" name="slug" value="{{ old('slug', $planModel?->slug) }}" class="{{ $fieldClass }}">
            <p class="mt-1 text-xs text-slate-400">Auto-generated from plan name if empty.</p>
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
    </div>

    <div>
        <label class="{{ $labelClass }}">Description</label>
        <textarea name="description" rows="2" class="{{ $fieldClass }}">{{ old('description', $planModel?->description) }}</textarea>
    </div>

    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-300">Section 4: Temporary Promotion</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-200">
            <input type="checkbox" id="is_sale_toggle" name="is_sale" value="1" @checked(old('is_sale', $planModel?->is_sale)) class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            Sale plan enabled
        </label>
        <div id="sale_price_wrap">
            <label class="{{ $labelClass }}">Sale Price</label>
            <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $planModel?->sale_price) }}" class="{{ $fieldClass }}">
        </div>
        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-200">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $planModel?->is_active ?? true)) class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            Plan active
        </label>
        <div id="sale_starts_wrap">
            <label class="{{ $labelClass }}">Sale Starts At</label>
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($planModel?->starts_at)->format('Y-m-d\\TH:i')) }}" class="{{ $fieldClass }}">
        </div>
        <div id="sale_ends_wrap">
            <label class="{{ $labelClass }}">Sale Ends At</label>
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($planModel?->ends_at)->format('Y-m-d\\TH:i')) }}" class="{{ $fieldClass }}">
        </div>
        </div>
    </div>

    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-300">Section 3: Plan Features and Limits</h3>
        <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-2">
            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-300">Included Modules / Features</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" class="quick-feature-btn rounded-md bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700" data-key="students_module" data-label="Students Module" data-value="Enabled">Students Module</button>
                    <button type="button" class="quick-feature-btn rounded-md bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700" data-key="documents_module" data-label="Documents Module" data-value="Enabled">Documents Module</button>
                    <button type="button" class="quick-feature-btn rounded-md bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700" data-key="status_monitoring" data-label="Status Monitoring" data-value="Enabled">Status Monitoring</button>
                    <button type="button" class="quick-feature-btn rounded-md bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700" data-key="reports_access" data-label="Reports" data-value="Enabled">Reports</button>
                    <button type="button" class="quick-feature-btn rounded-md bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700" data-key="custom_branding" data-label="Custom Branding" data-value="Enabled">Custom Branding</button>
                    <button type="button" class="quick-feature-btn rounded-md bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700" data-key="data_export" data-label="Data Export" data-value="Enabled">Data Export</button>
                </div>
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-cyan-300">Usage Limits</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" class="quick-limit-btn rounded-md bg-cyan-100 px-2.5 py-1 text-[11px] font-semibold text-cyan-700" data-key="students_limit" data-label="Students Limit" data-limit="300">Students Limit</button>
                    <button type="button" class="quick-limit-btn rounded-md bg-cyan-100 px-2.5 py-1 text-[11px] font-semibold text-cyan-700" data-key="admin_users_limit" data-label="Admin/Faculty Users Limit" data-limit="5">Admin/Faculty Users Limit</button>
                    <button type="button" class="quick-limit-btn rounded-md bg-cyan-100 px-2.5 py-1 text-[11px] font-semibold text-cyan-700" data-key="storage_limit_mb" data-label="Storage Limit (MB)" data-limit="10240">Storage Limit</button>
                    <button type="button" class="quick-limit-btn rounded-md bg-cyan-100 px-2.5 py-1 text-[11px] font-semibold text-cyan-700" data-key="bandwidth_limit_mb" data-label="Bandwidth Limit (MB)" data-limit="51200">Bandwidth Limit</button>
                </div>
            </div>
        </div>

        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Feature Rows</h3>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="feature-preset-btn rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-200" data-preset="basic">Basic Preset</button>
                <button type="button" class="feature-preset-btn rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-200" data-preset="standard">Standard Preset</button>
                <button type="button" class="feature-preset-btn rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-200" data-preset="premium">Premium Preset</button>
                <button type="button" id="add-feature-row" class="rounded-lg bg-cyan-100 px-3 py-1.5 text-xs font-semibold text-cyan-700 hover:bg-cyan-200">Add Feature</button>
                <button type="button" id="clear-feature-rows" class="rounded-lg bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-300">Clear</button>
            </div>
        </div>
        <p class="mb-3 text-xs text-slate-400">Use unique feature keys (letters, numbers, ., _, -). Limits are optional for non-quantitative features.</p>

        <div id="feature-rows" class="space-y-3">
            @foreach($featureRows as $index => $row)
                <div class="feature-row grid grid-cols-1 gap-3 rounded-xl border border-slate-700 bg-slate-900/80 p-3 lg:grid-cols-5" data-row-index="{{ $index }}">
                    <input type="text" name="feature_keys[{{ $index }}]" value="{{ $row['feature_key'] }}" placeholder="feature_key" class="{{ $fieldClass }}">
                    <input type="text" name="feature_labels[{{ $index }}]" value="{{ $row['feature_label'] }}" placeholder="Feature label" class="{{ $fieldClass }}">
                    <input type="text" name="feature_values[{{ $index }}]" value="{{ $row['feature_value'] }}" placeholder="Value (optional)" class="{{ $fieldClass }}">
                    <input type="number" min="0" name="feature_limits[{{ $index }}]" value="{{ $row['limit_value'] }}" placeholder="Limit" class="{{ $fieldClass }}">
                    <div class="flex items-center justify-between gap-2">
                        <label class="inline-flex items-center gap-2 text-xs text-slate-200">
                            <input type="hidden" name="feature_enabled[{{ $index }}]" value="0">
                            <input type="checkbox" name="feature_enabled[{{ $index }}]" value="1" @checked($row['is_enabled']) class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
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
        const clearButton = document.getElementById('clear-feature-rows');
        const sourcePresetButtons = Array.from(document.querySelectorAll('.source-preset-btn'));
        const startBlankButton = document.getElementById('start-blank-plan');
        const presetButtons = Array.from(document.querySelectorAll('.feature-preset-btn'));
        const quickFeatureButtons = Array.from(document.querySelectorAll('.quick-feature-btn'));
        const quickLimitButtons = Array.from(document.querySelectorAll('.quick-limit-btn'));
        const nameInput = document.getElementById('plan_name');
        const slugInput = document.getElementById('plan_slug');
        const billingCycleInput = document.querySelector('select[name="billing_cycle"]');
        const priceInput = document.querySelector('input[name="price"]');
        const descriptionInput = document.querySelector('textarea[name="description"]');
        const presetKeyInput = document.getElementById('preset_key');
        const saleToggle = document.getElementById('is_sale_toggle');
        const salePriceWrap = document.getElementById('sale_price_wrap');
        const saleStartsWrap = document.getElementById('sale_starts_wrap');
        const saleEndsWrap = document.getElementById('sale_ends_wrap');
        const featurePresets = @json($featurePresets);
        let nextRowIndex = {{ $nextRowIndex }};
        let slugEditedManually = Boolean((slugInput?.value || '').trim());

        if (!rowsContainer || !addButton) {
            return;
        }

        function slugify(value) {
            return String(value || '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s._-]/g, '')
                .replace(/[\s]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^[-._]+|[-._]+$/g, '');
        }

        function syncSaleVisibility() {
            const isSale = Boolean(saleToggle?.checked);
            [salePriceWrap, saleStartsWrap, saleEndsWrap].forEach((el) => {
                if (!el) {
                    return;
                }

                el.classList.toggle('opacity-50', !isSale);
                el.classList.toggle('pointer-events-none', !isSale);
            });
        }

        function createFeatureRow(index, data = {}) {
            const row = document.createElement('div');
            row.className = 'feature-row grid grid-cols-1 gap-3 rounded-xl border border-slate-700 bg-slate-900/80 p-3 lg:grid-cols-5';
            row.dataset.rowIndex = String(index);
            row.innerHTML = `
                <input type="text" name="feature_keys[${index}]" value="${(data.feature_key || '').replace(/"/g, '&quot;')}" placeholder="feature_key" class="w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500">
                <input type="text" name="feature_labels[${index}]" value="${(data.feature_label || '').replace(/"/g, '&quot;')}" placeholder="Feature label" class="w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500">
                <input type="text" name="feature_values[${index}]" value="${(data.feature_value || '').replace(/"/g, '&quot;')}" placeholder="Value (optional)" class="w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500">
                <input type="number" min="0" name="feature_limits[${index}]" value="${(data.limit_value ?? '')}" placeholder="Limit" class="w-full rounded-xl border border-slate-700 !bg-slate-900 !text-slate-100 px-3 py-2 text-sm placeholder:!text-slate-400 focus:border-cyan-500 focus:ring-cyan-500">
                <div class="flex items-center justify-between gap-2">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-200">
                        <input type="hidden" name="feature_enabled[${index}]" value="0">
                        <input type="checkbox" name="feature_enabled[${index}]" value="1" ${(data.is_enabled ?? true) ? 'checked' : ''} class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                        Enabled
                    </label>
                    <button type="button" class="remove-feature-row rounded-md bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-200">Remove</button>
                </div>
            `;
            return row;
        }

        function appendFeature(data = {}) {
            const row = createFeatureRow(nextRowIndex++, data);
            rowsContainer.appendChild(row);
            bindRemoveButtons();
        }

        function applyPreset(presetKey, options = { applyIdentity: true }) {
            const preset = featurePresets[presetKey] || {};
            const presetFeatures = Array.isArray(preset.features) ? preset.features : [];

            if (presetFeatures.length === 0) {
                return;
            }

            if (presetKeyInput) {
                presetKeyInput.value = presetKey;
            }

            if (options.applyIdentity !== false && nameInput && billingCycleInput && priceInput && descriptionInput) {
                const identity = featurePresets[presetKey]?.identity || {};
                if (identity.name) {
                    nameInput.value = identity.name;
                }
                if (identity.billing_cycle) {
                    billingCycleInput.value = identity.billing_cycle;
                }
                if (identity.price !== undefined) {
                    priceInput.value = identity.price;
                }
                if (identity.description !== undefined) {
                    descriptionInput.value = identity.description;
                }
                if (slugInput && !slugEditedManually) {
                    slugInput.value = slugify(nameInput.value);
                }
            }

            rowsContainer.innerHTML = '';
            presetFeatures.forEach((item) => {
                appendFeature(item);
            });
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
            appendFeature();
        });

        clearButton?.addEventListener('click', () => {
            rowsContainer.innerHTML = '';
            appendFeature();

            if (presetKeyInput) {
                presetKeyInput.value = '';
            }
        });

        presetButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const presetKey = button.dataset.preset || '';
                applyPreset(presetKey, { applyIdentity: false });
            });
        });

        sourcePresetButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const presetKey = button.dataset.preset || '';
                applyPreset(presetKey, { applyIdentity: true });
            });
        });

        startBlankButton?.addEventListener('click', () => {
            rowsContainer.innerHTML = '';
            appendFeature();

            if (nameInput) {
                nameInput.value = '';
            }
            if (slugInput) {
                slugInput.value = '';
            }
            if (billingCycleInput) {
                billingCycleInput.value = 'monthly';
            }
            if (priceInput) {
                priceInput.value = '0';
            }
            if (descriptionInput) {
                descriptionInput.value = '';
            }
            if (presetKeyInput) {
                presetKeyInput.value = '';
            }
            slugEditedManually = false;
        });

        quickFeatureButtons.forEach((button) => {
            button.addEventListener('click', () => {
                appendFeature({
                    feature_key: button.dataset.key || '',
                    feature_label: button.dataset.label || '',
                    feature_value: button.dataset.value || 'Enabled',
                    limit_value: '',
                    is_enabled: true,
                });
            });
        });

        quickLimitButtons.forEach((button) => {
            button.addEventListener('click', () => {
                appendFeature({
                    feature_key: button.dataset.key || '',
                    feature_label: button.dataset.label || '',
                    feature_value: '',
                    limit_value: button.dataset.limit || '',
                    is_enabled: true,
                });
            });
        });

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', () => {
                if (slugEditedManually) {
                    return;
                }

                slugInput.value = slugify(nameInput.value);
            });

            slugInput.addEventListener('input', () => {
                slugEditedManually = slugInput.value.trim() !== '';
            });
        }

        saleToggle?.addEventListener('change', syncSaleVisibility);
        syncSaleVisibility();

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
