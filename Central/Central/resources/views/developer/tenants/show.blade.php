<x-layouts.admin :pageTitle="'Tenant Usage Dashboard'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.tenants.index') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Tenants</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Usage Dashboard</span>
    </x-slot>

    @php
        $limits = $tenant->planLimits();
        $storagePercentRaw = $tenant->storageUsagePercent();
        $bandwidthPercentRaw = $tenant->bandwidthUsagePercent();
        $storagePercent = min(100, $storagePercentRaw);
        $bandwidthPercent = min(100, $bandwidthPercentRaw);

        $planDurationDays = $tenant->plan_started_at && $tenant->plan_due_at
            ? max(1, $tenant->plan_started_at->diffInDays($tenant->plan_due_at))
            : 30;

        $daysUsed = $tenant->plan_started_at
            ? max(1, $tenant->plan_started_at->diffInDays(now()))
            : 1;

        $daysRemaining = $tenant->plan_due_at
            ? now()->startOfDay()->diffInDays($tenant->plan_due_at->startOfDay(), false)
            : null;

        $projectedStorageAtDue = round(((float) $tenant->storage_used_mb / $daysUsed) * $planDurationDays, 2);
        $projectedBandwidthAtDue = round(((float) $tenant->bandwidth_used_mb / $daysUsed) * $planDurationDays, 2);
        $currentTrialDays = ($tenant->trial_ends_at && $tenant->plan_started_at)
            ? max(0, $tenant->plan_started_at->diffInDays($tenant->trial_ends_at, false))
            : 0;

        $months = collect(range(5, 0))->map(fn ($offset) => now()->subMonths($offset)->format('M'))->push(now()->format('M'))->values();

        $storageSeries = collect(range(1, 6))->map(function ($step) use ($tenant) {
            return round(((float) $tenant->storage_used_mb) * ($step / 6), 2);
        })->prepend(0)->values();

        $bandwidthSeries = collect(range(1, 6))->map(function ($step) use ($tenant) {
            return round(((float) $tenant->bandwidth_used_mb) * ($step / 6), 2);
        })->prepend(0)->values();
    @endphp

    <div class="space-y-5">
        <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5 shadow-xl backdrop-blur-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-100">{{ $tenant->name }}</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        @if($tenant->tenant_domain)
                            {{ $tenant->tenant_domain }}
                        @elseif($tenant->requested_tenant_domain)
                            Requested: {{ $tenant->requested_tenant_domain }}
                        @else
                            No domain configured
                        @endif
                    </p>
                    <div class="mt-2 inline-flex items-center gap-2">
                        <span class="rounded-full bg-cyan-900/40 px-2.5 py-0.5 text-xs font-semibold uppercase text-cyan-300">{{ $tenant->plan_type }}</span>
                        <span class="rounded-full bg-indigo-900/40 px-2.5 py-0.5 text-xs font-semibold uppercase text-indigo-300">{{ $tenant->billing_cycle ?? 'monthly' }}</span>
                        @if($tenant->approval_status === 'pending')
                            <span class="rounded-full bg-amber-900/40 px-2.5 py-0.5 text-xs font-semibold text-amber-300">Pending Approval</span>
                        @else
                            <span class="rounded-full {{ $tenant->is_enabled ? 'bg-emerald-900/40 text-emerald-300' : 'bg-rose-900/40 text-rose-300' }} px-2.5 py-0.5 text-xs font-semibold">{{ $tenant->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                        @endif
                    </div>
                    @if(! $tenant->is_enabled && $tenant->disable_reason)
                        <p class="mt-2 text-xs text-rose-300">Disable reason: {{ $tenant->disable_reason }}</p>
                    @endif
                    @if($tenant->disabled_at)
                        <p class="mt-1 text-xs text-slate-400">Disabled at: {{ $tenant->disabled_at->format('M d, Y h:i A') }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('developer.tenants.edit', $tenant) }}" class="px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 text-sm">Edit</a>
                    <a href="{{ route('developer.tenants.index') }}" class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-sm">Back</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-cyan-900/50 bg-cyan-900/20 px-4 py-3">
                <p class="text-xs uppercase tracking-wider text-cyan-300">Storage Used</p>
                <p class="mt-1 text-2xl font-black text-cyan-100">{{ number_format((float) $tenant->storage_used_mb, 2) }} MB</p>
                <p class="text-xs text-cyan-300/80">of {{ number_format((float) $limits['storage_mb'], 0) }} MB limit</p>
            </div>
            <div class="rounded-2xl border border-emerald-900/50 bg-emerald-900/20 px-4 py-3">
                <p class="text-xs uppercase tracking-wider text-emerald-300">Bandwidth Used</p>
                <p class="mt-1 text-2xl font-black text-emerald-100">{{ number_format((float) $tenant->bandwidth_used_mb, 2) }} MB</p>
                <p class="text-xs text-emerald-300/80">of {{ number_format((float) $limits['bandwidth_mb'], 0) }} MB limit</p>
            </div>
            <div class="rounded-2xl border border-violet-900/50 bg-violet-900/20 px-4 py-3">
                <p class="text-xs uppercase tracking-wider text-violet-300">Plan Remaining</p>
                <p class="mt-1 text-2xl font-black text-violet-100">{{ $daysRemaining === null ? 'N/A' : $daysRemaining }}</p>
                <p class="text-xs text-violet-300/80">days before due date</p>
            </div>
            <div class="rounded-2xl border {{ $tenant->isOverUsageLimit() ? 'border-rose-900/60 bg-rose-900/20' : 'border-slate-700 bg-slate-800/60' }} px-4 py-3">
                <p class="text-xs uppercase tracking-wider {{ $tenant->isOverUsageLimit() ? 'text-rose-300' : 'text-slate-300' }}">Usage Risk</p>
                <p class="mt-1 text-2xl font-black {{ $tenant->isOverUsageLimit() ? 'text-rose-100' : 'text-slate-100' }}">{{ $tenant->isOverUsageLimit() ? 'Over Limit' : 'Within Limit' }}</p>
                <p class="text-xs {{ $tenant->isOverUsageLimit() ? 'text-rose-300/80' : 'text-slate-300/80' }}">Auto-evaluated from current plan caps</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5 xl:col-span-1">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Usage Gauges</h3>
                <div class="mt-4 space-y-4">
                    <div class="rounded-xl border border-slate-700 bg-slate-800/50 p-4">
                        <p class="text-xs font-semibold uppercase text-cyan-300">Storage</p>
                        <div class="mt-3 flex items-center gap-4">
                            <div class="relative h-20 w-20 rounded-full" style="background: conic-gradient(#22d3ee {{ $storagePercent }}%, #334155 0%);">
                                <div class="absolute inset-2 rounded-full bg-slate-900"></div>
                                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-slate-100">{{ number_format($storagePercentRaw, 1) }}%</span>
                            </div>
                            <div>
                                <p class="text-sm text-slate-100">{{ number_format((float) $tenant->storage_used_mb, 2) }} MB used</p>
                                <p class="text-xs text-slate-400">Limit: {{ number_format((float) $limits['storage_mb'], 0) }} MB</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-700 bg-slate-800/50 p-4">
                        <p class="text-xs font-semibold uppercase text-emerald-300">Bandwidth</p>
                        <div class="mt-3 flex items-center gap-4">
                            <div class="relative h-20 w-20 rounded-full" style="background: conic-gradient(#34d399 {{ $bandwidthPercent }}%, #334155 0%);">
                                <div class="absolute inset-2 rounded-full bg-slate-900"></div>
                                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-slate-100">{{ number_format($bandwidthPercentRaw, 1) }}%</span>
                            </div>
                            <div>
                                <p class="text-sm text-slate-100">{{ number_format((float) $tenant->bandwidth_used_mb, 2) }} MB used</p>
                                <p class="text-xs text-slate-400">Limit: {{ number_format((float) $limits['bandwidth_mb'], 0) }} MB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5 xl:col-span-2">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Projected Usage Trend</h3>
                <p class="mt-1 text-xs text-slate-400">Trend based on current consumption rate across this plan cycle.</p>
                <div class="mt-4 overflow-x-auto">
                    <svg viewBox="0 0 760 240" class="h-56 w-full min-w-[720px]">
                        <rect x="0" y="0" width="760" height="240" fill="#0f172a" rx="12" />
                        @for($i = 0; $i <= 4; $i++)
                            <line x1="60" y1="{{ 40 + ($i * 40) }}" x2="720" y2="{{ 40 + ($i * 40) }}" stroke="#1e293b" stroke-width="1" />
                        @endfor

                        @php
                            $maxValue = max((float) ($limits['storage_mb'] * 0.5), (float) ($limits['bandwidth_mb'] * 0.2), (float) $storageSeries->max(), (float) $bandwidthSeries->max(), 1);
                            $storagePoints = [];
                            $bandwidthPoints = [];

                            foreach ($storageSeries as $idx => $value) {
                                $x = 60 + ($idx * 110);
                                $y = 200 - (($value / $maxValue) * 160);
                                $storagePoints[] = $x . ',' . round($y, 2);
                            }

                            foreach ($bandwidthSeries as $idx => $value) {
                                $x = 60 + ($idx * 110);
                                $y = 200 - (($value / $maxValue) * 160);
                                $bandwidthPoints[] = $x . ',' . round($y, 2);
                            }
                        @endphp

                        <polyline fill="none" stroke="#22d3ee" stroke-width="3" points="{{ implode(' ', $storagePoints) }}" />
                        <polyline fill="none" stroke="#34d399" stroke-width="3" points="{{ implode(' ', $bandwidthPoints) }}" />

                        @foreach($storageSeries as $idx => $value)
                            <circle cx="{{ 60 + ($idx * 110) }}" cy="{{ 200 - (($value / $maxValue) * 160) }}" r="3" fill="#22d3ee" />
                            <circle cx="{{ 60 + ($idx * 110) }}" cy="{{ 200 - (($bandwidthSeries[$idx] / $maxValue) * 160) }}" r="3" fill="#34d399" />
                            <text x="{{ 60 + ($idx * 110) - 10 }}" y="220" fill="#94a3b8" font-size="10">{{ $months[$idx] }}</text>
                        @endforeach

                        <text x="70" y="24" fill="#22d3ee" font-size="11">Storage</text>
                        <text x="150" y="24" fill="#34d399" font-size="11">Bandwidth</text>
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5 xl:col-span-2">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Allocation vs Consumption</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-300">
                            <span>Storage</span>
                            <span>{{ number_format((float) $tenant->storage_used_mb, 2) }} / {{ number_format((float) $limits['storage_mb'], 0) }} MB</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-700">
                            <div class="h-2 rounded-full {{ $storagePercentRaw > 100 ? 'bg-rose-400' : 'bg-cyan-400' }}" style="width: {{ $storagePercent }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-300">
                            <span>Bandwidth</span>
                            <span>{{ number_format((float) $tenant->bandwidth_used_mb, 2) }} / {{ number_format((float) $limits['bandwidth_mb'], 0) }} MB</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-700">
                            <div class="h-2 rounded-full {{ $bandwidthPercentRaw > 100 ? 'bg-rose-400' : 'bg-emerald-400' }}" style="width: {{ $bandwidthPercent }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-700 bg-slate-800/40 p-3">
                            <p class="text-xs uppercase tracking-wider text-slate-400">Projected Storage At Due</p>
                            <p class="mt-1 text-lg font-bold text-slate-100">{{ number_format($projectedStorageAtDue, 2) }} MB</p>
                        </div>
                        <div class="rounded-xl border border-slate-700 bg-slate-800/40 p-3">
                            <p class="text-xs uppercase tracking-wider text-slate-400">Projected Bandwidth At Due</p>
                            <p class="mt-1 text-lg font-bold text-slate-100">{{ number_format($projectedBandwidthAtDue, 2) }} MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Quick Usage Update</h3>
                <form method="POST" action="{{ route('developer.tenants.update-usage', $tenant) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="storage_used_mb" class="mb-1 block text-xs text-slate-300">Storage Used (MB)</label>
                        <input id="storage_used_mb" name="storage_used_mb" type="number" min="0" step="0.01" value="{{ number_format((float) $tenant->storage_used_mb, 2, '.', '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100 focus:border-cyan-500 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label for="bandwidth_used_mb" class="mb-1 block text-xs text-slate-300">Bandwidth Used (MB)</label>
                        <input id="bandwidth_used_mb" name="bandwidth_used_mb" type="number" min="0" step="0.01" value="{{ number_format((float) $tenant->bandwidth_used_mb, 2, '.', '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100 focus:border-cyan-500 focus:ring-cyan-500">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Save Usage</button>
                </form>
                <p class="mt-3 text-xs text-slate-400">Last refreshed: {{ optional($tenant->usage_refreshed_at)->format('M d, Y h:i A') ?? 'Not synced yet' }}</p>
            </div>
        </div>

        <div id="subscription" class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-6 shadow-xl">
            <h3 class="mb-3 text-base font-semibold text-slate-100">Subscription Update</h3>
            <form method="POST" action="{{ route('developer.tenants.update-subscription', $tenant) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf
                @method('PATCH')

                <div>
                    <label for="plan_type" class="mb-1 block text-sm font-medium text-slate-200">Plan</label>
                    <select id="plan_type" name="plan_type" required
                            class="w-full rounded-lg border border-slate-600 bg-slate-800 text-white focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="basic" @selected(old('plan_type', $tenant->plan_type) === 'basic')>Basic</option>
                        <option value="standard" @selected(old('plan_type', $tenant->plan_type) === 'standard')>Standard</option>
                        <option value="premium" @selected(old('plan_type', $tenant->plan_type) === 'premium')>Premium</option>
                    </select>
                    @error('plan_type') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="plan_expiration_email" class="mb-1 block text-sm font-medium text-slate-200">Reminder Email</label>
                    <input id="plan_expiration_email" name="plan_expiration_email" type="email" value="{{ old('plan_expiration_email', $tenant->plan_expiration_email) }}" required
                           class="w-full rounded-lg border border-slate-600 bg-slate-800 text-white focus:border-cyan-500 focus:ring-cyan-500">
                    @error('plan_expiration_email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="billing_cycle" class="mb-1 block text-sm font-medium text-slate-200">Billing Cycle</label>
                    <select id="billing_cycle" name="billing_cycle" required
                            class="w-full rounded-lg border border-slate-600 bg-slate-800 text-white focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="monthly" @selected(old('billing_cycle', $tenant->billing_cycle ?? 'monthly') === 'monthly')>Monthly subscription</option>
                        <option value="annual" @selected(old('billing_cycle', $tenant->billing_cycle ?? 'monthly') === 'annual')>Annual subscription (discounted)</option>
                    </select>
                    @error('billing_cycle') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="free_trial_days" class="mb-1 block text-sm font-medium text-slate-200">Free Trial</label>
                    <select id="free_trial_days" name="free_trial_days"
                            class="w-full rounded-lg border border-slate-600 bg-slate-800 text-white focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="0" @selected((string) old('free_trial_days', (string) $currentTrialDays) === '0')>No trial</option>
                        <option value="7" @selected((string) old('free_trial_days', (string) $currentTrialDays) === '7')>7 days</option>
                        <option value="14" @selected((string) old('free_trial_days', (string) $currentTrialDays) === '14')>14 days</option>
                    </select>
                    @error('free_trial_days') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="plan_started_at" class="mb-1 block text-sm font-medium text-slate-200">Plan Start Date</label>
                    <input id="plan_started_at" name="plan_started_at" type="date" value="{{ old('plan_started_at', optional($tenant->plan_started_at)->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border border-slate-600 bg-slate-800 text-white focus:border-cyan-500 focus:ring-cyan-500">
                    @error('plan_started_at') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="plan_due_at" class="mb-1 block text-sm font-medium text-slate-200">Plan Due Date</label>
                    <input id="plan_due_at" name="plan_due_at" type="date" value="{{ old('plan_due_at', optional($tenant->plan_due_at)->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border border-slate-600 bg-slate-800 text-white focus:border-cyan-500 focus:ring-cyan-500">
                    @error('plan_due_at') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2 flex items-center justify-end">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">Save Subscription</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
