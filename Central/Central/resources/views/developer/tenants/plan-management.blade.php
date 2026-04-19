<x-layouts.admin :pageTitle="'Plan Management'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.tenants.index') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Tenants</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Plan Management</span>
    </x-slot>

    @php
        $activeTab = request('tab', 'tenant');
        $activeTab = in_array($activeTab, ['tenant', 'modular'], true) ? $activeTab : 'tenant';
    @endphp

    <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 shadow-sm dark:border-cyan-900/50 dark:bg-cyan-900/20">
            <p class="text-xs uppercase tracking-wider text-cyan-700 dark:text-cyan-300">Total Tenants</p>
            <p class="mt-1 text-2xl font-black text-cyan-900 dark:text-cyan-100">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 shadow-sm dark:border-cyan-900/50 dark:bg-cyan-900/20">
            <p class="text-xs uppercase tracking-wider text-cyan-700 dark:text-cyan-300">Expiring in 30 Days</p>
            <p class="mt-1 text-2xl font-black text-cyan-900 dark:text-cyan-100">{{ $summary['expiring30'] }}</p>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 shadow-sm dark:border-rose-900/50 dark:bg-rose-900/20">
            <p class="text-xs uppercase tracking-wider text-rose-700 dark:text-rose-300">Expired</p>
            <p class="mt-1 text-2xl font-black text-rose-900 dark:text-rose-100">{{ $summary['expired'] }}</p>
        </div>
        <div class="rounded-2xl border border-fuchsia-200 bg-fuchsia-50 px-4 py-3 shadow-sm dark:border-fuchsia-900/50 dark:bg-fuchsia-900/20">
            <p class="text-xs uppercase tracking-wider text-fuchsia-700 dark:text-fuchsia-300">Over Usage Limit</p>
            <p class="mt-1 text-2xl font-black text-fuchsia-900 dark:text-fuchsia-100">{{ $summary['over_limit'] }}</p>
        </div>
    </div>

    <div class="mb-5 rounded-2xl border border-slate-700/70 bg-slate-900/70 p-3 shadow-lg">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button"
                class="plan-tab-toggle rounded-xl px-4 py-2 text-sm font-semibold transition-colors"
                data-target="tenant-tab"
                data-tab="tenant">
                Tenant Billing Operations
            </button>
            <button type="button"
                class="plan-tab-toggle rounded-xl px-4 py-2 text-sm font-semibold transition-colors"
                data-target="modular-tab"
                data-tab="modular">
                Modular Plan Catalog
            </button>
        </div>
    </div>

    <section id="tenant-tab" class="plan-tab-section space-y-5" data-tab="tenant" @if($activeTab !== 'tenant') hidden @endif>
        <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
            @foreach($planCatalog as $key => $plan)
            <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-4 shadow-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-100">{{ $plan['label'] }}</h3>
                    <span class="rounded-full bg-cyan-900/40 px-2.5 py-0.5 text-xs font-semibold uppercase text-cyan-300">{{ $key }}</span>
                </div>
                <div class="mt-3 space-y-1 text-xs text-slate-300">
                    <p>Students: {{ $plan['student_limit'] ? 'Up to '.number_format($plan['student_limit']) : 'Unlimited' }}</p>
                    <p>Admin/Faculty Users: {{ $plan['user_limit'] ? 'Up to '.number_format($plan['user_limit']) : 'Unlimited' }}</p>
                </div>
                <ul class="mt-3 space-y-1 text-xs text-slate-400">
                    @foreach($plan['features'] as $feature)
                    <li>- {{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        <div class="mb-5 rounded-2xl border border-cyan-900/40 bg-cyan-900/15 p-4 text-sm text-cyan-100">
            <p class="font-semibold uppercase tracking-wider text-cyan-300">Billing Options</p>
            <p class="mt-1">Monthly subscription</p>
            <p>Annual subscription (discounted rate)</p>
            <p>Free trial: 7 or 14 days</p>
        </div>

        <div class="rounded-2xl bg-slate-900/70 shadow-xl border border-slate-700/70 backdrop-blur-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-700/80">
                <h2 class="text-base font-semibold text-slate-100">Tenant Plans</h2>
                <a href="{{ route('developer.tenants.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition-colors dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">Back to Tenants</a>
            </div>

            <form method="GET" action="{{ route('developer.tenants.plan-management') }}"
                class="flex flex-wrap gap-3 px-6 py-3 bg-slate-900/40 border-b border-slate-700/80">
                <input type="hidden" name="tab" value="tenant">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tenant, domain, admin, email..."
                    class="flex-1 min-w-[220px] text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                <select name="plan_type" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                    <option value="">All Plans</option>
                    <option value="basic" @selected(request('plan_type') === 'basic')>Basic</option>
                    <option value="standard" @selected(request('plan_type') === 'standard')>Standard</option>
                    <option value="premium" @selected(request('plan_type') === 'premium')>Premium</option>
                </select>
                <select name="status" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                    <option value="">All Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="enabled" @selected(request('status') === 'enabled')>Enabled</option>
                    <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm rounded-lg transition-colors">Filter</button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Tenant</th>
                            <th class="px-6 py-3 text-left">Current Plan</th>
                            <th class="px-6 py-3 text-left">Plan Limits</th>
                            <th class="px-6 py-3 text-left">Billing</th>
                            <th class="px-6 py-3 text-left">Period</th>
                            <th class="px-6 py-3 text-left">Reminder Email</th>
                            <th class="px-6 py-3 text-left">Storage</th>
                            <th class="px-6 py-3 text-left">Bandwidth</th>
                            <th class="px-6 py-3 text-left">Health</th>
                            <th class="px-6 py-3 text-right">Quick Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/70">
                        @forelse($tenants as $tenant)
                            @php
                                $isExpired = $tenant->isSubscriptionExpired();
                                $isExpiring = $tenant->isSubscriptionExpiringWithinDays(30);
                            @endphp
                            <tr class="hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-3">
                                    <p class="font-medium text-slate-100">{{ $tenant->name }}</p>
                                    @if($tenant->tenant_domain)
                                        <p class="text-xs text-slate-400">{{ $tenant->tenant_domain }}</p>
                                    @elseif($tenant->requested_tenant_domain)
                                        <p class="text-xs text-amber-300">Requested: {{ $tenant->requested_tenant_domain }}</p>
                                    @else
                                        <p class="text-xs text-slate-400">N/A</p>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center rounded-full bg-cyan-100 px-2.5 py-0.5 text-xs font-semibold uppercase text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300">{{ $tenant->plan_type }}</span>
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-300">
                                    @php $spec = $tenant->planSpec(); @endphp
                                    <p>Students: {{ $spec['student_limit'] ? number_format($spec['student_limit']) : 'Unlimited' }}</p>
                                    <p>Users: {{ $spec['user_limit'] ? number_format($spec['user_limit']) : 'Unlimited' }}</p>
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-300">
                                    <p class="uppercase">{{ $tenant->billing_cycle ?? 'monthly' }}</p>
                                    <p>Trial: {{ $tenant->trial_ends_at ? optional($tenant->trial_ends_at)->format('M d, Y') : 'None' }}</p>
                                </td>
                                <td class="px-6 py-3 text-slate-200">
                                    <p>{{ optional($tenant->plan_started_at)->format('M d, Y') ?? 'N/A' }}</p>
                                    <p class="text-xs text-slate-400">to {{ optional($tenant->plan_due_at)->format('M d, Y') ?? 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-3 text-slate-200">{{ $tenant->plan_expiration_email ?? 'N/A' }}</td>
                                <td class="px-6 py-3 min-w-[180px]">
                                    @php
                                        $limits = $tenant->planLimits();
                                        $storagePercent = min(100, $tenant->storageUsagePercent());
                                    @endphp
                                    <p class="text-xs text-slate-200">{{ number_format((float) $tenant->storage_used_mb, 2) }} MB / {{ number_format((float) $limits['storage_mb'], 0) }} MB</p>
                                    <div class="mt-1 h-1.5 rounded-full bg-slate-700">
                                        <div class="h-1.5 rounded-full {{ $tenant->storageUsagePercent() > 100 ? 'bg-rose-400' : 'bg-cyan-400' }}" style="width: {{ $storagePercent }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 min-w-[180px]">
                                    @php $bandwidthPercent = min(100, $tenant->bandwidthUsagePercent()); @endphp
                                    <p class="text-xs text-slate-200">{{ number_format((float) $tenant->bandwidth_used_mb, 2) }} MB / {{ number_format((float) $limits['bandwidth_mb'], 0) }} MB</p>
                                    <div class="mt-1 h-1.5 rounded-full bg-slate-700">
                                        <div class="h-1.5 rounded-full {{ $tenant->bandwidthUsagePercent() > 100 ? 'bg-rose-400' : 'bg-emerald-400' }}" style="width: {{ $bandwidthPercent }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    @if($isExpired)
                                        <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">Expired</span>
                                    @elseif($isExpiring)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Expiring</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Healthy</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('developer.tenants.extend-plan', $tenant) }}" class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="days" value="30">
                                            <button type="submit" class="px-3 py-1.5 text-xs rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">+30 days</button>
                                        </form>
                                        <form method="POST" action="{{ route('developer.tenants.send-reminder', $tenant) }}" class="inline-flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="days" value="7">
                                            <button type="submit" class="px-3 py-1.5 text-xs rounded bg-cyan-100 text-cyan-700 hover:bg-cyan-200 dark:bg-cyan-900/40 dark:text-cyan-300 dark:hover:bg-cyan-900/60">Send Reminder</button>
                                        </form>
                                        <a href="{{ route('developer.tenants.show', $tenant) }}#subscription" class="px-3 py-1.5 text-xs rounded bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60">Edit Plan</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-slate-400">No tenants found for plan management.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tenants->hasPages())
                <div class="px-6 py-4 border-t border-slate-700/80">
                    {{ $tenants->links() }}
                </div>
            @endif
        </div>
    </section>

    <section id="modular-tab" class="plan-tab-section space-y-5" data-tab="modular" @if($activeTab !== 'modular') hidden @endif>
        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-5 py-4 shadow-sm dark:border-cyan-900/40 dark:bg-cyan-900/20">
            <h2 class="text-base font-semibold text-cyan-800 dark:text-cyan-200">Modular Plan Catalog</h2>
            <p class="mt-1 text-sm text-cyan-700/90 dark:text-cyan-300">Create, edit, and assign dynamic plans with configurable feature sets from the same workspace.</p>
        </div>

        <div class="rounded-2xl bg-slate-900/70 shadow-xl border border-slate-700/70 backdrop-blur-sm overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-700/80 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-100">Plan Catalog</h3>
                <a href="{{ route('developer.plans.create') }}" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Create Plan</a>
            </div>

            <form method="GET" action="{{ route('developer.tenants.plan-management') }}" class="flex flex-wrap gap-3 px-6 py-3 bg-slate-900/40 border-b border-slate-700/80">
                <input type="hidden" name="tab" value="modular">
                <input type="text" name="plan_search" value="{{ request('plan_search') }}" placeholder="Search plan name, slug, description..." class="flex-1 min-w-[220px] text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                <select name="plan_state" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                    <option value="">All States</option>
                    <option value="active" @selected(request('plan_state') === 'active')>Active</option>
                    <option value="inactive" @selected(request('plan_state') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm rounded-lg transition-colors">Filter</button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50 text-xs uppercase tracking-wider text-slate-300">
                        <tr>
                            <th class="px-6 py-3 text-left">Plan</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Billing</th>
                            <th class="px-6 py-3 text-left">Features</th>
                            <th class="px-6 py-3 text-left">Tenants</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/70">
                        @forelse($plans as $plan)
                        <tr class="hover:bg-slate-800/50">
                            <td class="px-6 py-3">
                                <p class="font-semibold text-slate-100">{{ $plan->name }}</p>
                                <p class="text-xs text-slate-400">{{ $plan->slug }}</p>
                                @if($plan->description)
                                <p class="mt-1 text-xs text-slate-400">{{ $plan->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($plan->is_system_preset)
                                    <span class="inline-flex rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">System Preset</span>
                                    @if($plan->preset_key)
                                        <p class="mt-1 text-xs uppercase text-slate-400">{{ $plan->preset_key }}</p>
                                    @endif
                                @else
                                    <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-semibold text-slate-700">Custom Plan</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-slate-200">{{ ucfirst($plan->billing_cycle) }}</p>
                                <p class="text-xs text-slate-400">Price: {{ number_format((float) $plan->price, 2) }}</p>
                                @if($plan->is_sale)
                                    <p class="text-xs text-amber-300">Sale: {{ number_format((float) $plan->sale_price, 2) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-200">{{ $plan->features_count }}</td>
                            <td class="px-6 py-3 text-slate-200">{{ $plan->tenant_plans_count }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('developer.plans.edit', $plan) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">View</a>
                                    <a href="{{ route('developer.plans.edit', $plan) }}" class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200">Edit</a>

                                    <form method="POST" action="{{ route('developer.plans.duplicate', $plan) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-violet-100 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-200">Duplicate</button>
                                    </form>

                                    <form method="POST" action="{{ route('developer.plans.set-active', $plan) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $plan->is_active ? '0' : '1' }}">
                                        <button type="submit" class="rounded-lg {{ $plan->is_active ? 'bg-slate-200 text-slate-700 hover:bg-slate-300' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }} px-3 py-1.5 text-xs font-semibold">
                                            {{ $plan->is_active ? 'Archive' : 'Activate' }}
                                        </button>
                                    </form>

                                    @if($schools->isNotEmpty())
                                    <details class="group rounded-lg border border-slate-700 bg-slate-900 text-left">
                                        <summary class="cursor-pointer list-none rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-cyan-700">Assign</summary>
                                        <form method="POST" action="{{ route('developer.plans.assign', $plan) }}" class="mt-2 grid w-72 gap-2 p-2">
                                            @csrf
                                            <label class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Tenant</label>
                                            <select name="school_id" class="rounded-lg border border-slate-600 bg-slate-800 px-2 py-1.5 text-xs text-slate-100">
                                                @foreach($schools as $school)
                                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                                @endforeach
                                            </select>

                                            <label class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Status</label>
                                            <select name="status" class="rounded-lg border border-slate-600 bg-slate-800 px-2 py-1.5 text-xs text-slate-100">
                                                <option value="active">Active</option>
                                                <option value="pending">Pending</option>
                                                <option value="canceled">Canceled</option>
                                            </select>

                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Starts</label>
                                                    <input type="date" name="starts_at" value="{{ now()->toDateString() }}" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-2 py-1.5 text-xs text-slate-100">
                                                </div>
                                                <div>
                                                    <label class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Ends</label>
                                                    <input type="date" name="ends_at" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-2 py-1.5 text-xs text-slate-100">
                                                </div>
                                            </div>

                                            <button type="submit" class="mt-1 rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-cyan-700">Save Assignment</button>
                                        </form>
                                    </details>
                                    @endif

                                    @if(! $plan->is_system_preset)
                                    <form method="POST" action="{{ route('developer.plans.destroy', $plan) }}" onsubmit="return confirm('Delete plan {{ addslashes($plan->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200">Delete</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">No plans found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($plans->hasPages())
            <div class="border-t border-slate-700/80 px-6 py-4">
                {{ $plans->links() }}
            </div>
            @endif
        </div>
    </section>

    <script>
        (function () {
            const tabButtons = document.querySelectorAll('.plan-tab-toggle');
            const tabSections = document.querySelectorAll('.plan-tab-section');
            const activeClasses = ['bg-cyan-600', 'text-white', 'shadow-sm'];
            const idleClasses = ['bg-slate-800', 'text-slate-300'];

            function applyButtonState(targetTab) {
                tabButtons.forEach((button) => {
                    const isActive = button.dataset.tab === targetTab;
                    button.classList.remove(...activeClasses, ...idleClasses);
                    button.classList.add(...(isActive ? activeClasses : idleClasses));
                });
            }

            function showTab(tabName, updateUrl = false) {
                tabSections.forEach((section) => {
                    section.hidden = section.dataset.tab !== tabName;
                });

                applyButtonState(tabName);

                if (updateUrl) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tabName);
                    window.history.replaceState({}, '', url);
                }
            }

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    showTab(button.dataset.tab, true);
                });
            });

            showTab(@json($activeTab));
        })();
    </script>
</x-layouts.admin>
