<x-layouts.admin :pageTitle="'Tenant Management'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <span>Developer</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Tenants</span>
    </x-slot>

    <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-cyan-100 bg-gradient-to-br from-white to-cyan-50 px-5 py-4 shadow-sm dark:border-cyan-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700 dark:text-cyan-300">Tenant Records</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $summary['total'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Current filter scope</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 px-5 py-4 shadow-sm dark:border-amber-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Pending</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $summary['pending'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Needs approval</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50 px-5 py-4 shadow-sm dark:border-emerald-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Enabled</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $summary['enabled'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Approved and active</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 px-5 py-4 shadow-sm dark:border-slate-700/70 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Disabled</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $summary['disabled'] }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Approved but inactive</p>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-900/70 shadow-xl border border-slate-700/70 backdrop-blur-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-700/80">
            <div>
                <h2 class="text-base font-semibold text-slate-100">Tenant Directory</h2>
                <p class="mt-1 text-xs text-slate-400">One unified screen for pending, enabled, and disabled tenants.</p>
            </div>
            <a href="{{ route('developer.tenants.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Tenant
            </a>
        </div>

        <form method="GET" action="{{ route('developer.tenants.index') }}"
              class="flex flex-wrap gap-3 px-6 py-3 bg-slate-900/40 border-b border-slate-700/80">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tenant, address, admin, database..."
                   class="flex-1 min-w-[220px] text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
            <select name="plan_type"
                    class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                <option value="">All Plans</option>
                <option value="basic" @selected(request('plan_type') === 'basic')>Basic</option>
                <option value="standard" @selected(request('plan_type') === 'standard')>Standard</option>
                <option value="premium" @selected(request('plan_type') === 'premium')>Premium</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm rounded-lg transition-colors">Filter</button>
            @if(request()->hasAny(['search','plan_type']))
            <a href="{{ route('developer.tenants.index') }}"
               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg transition-colors">Clear</a>
            @endif
        </form>

        <div class="flex flex-wrap items-center gap-2 px-6 py-3 border-b border-slate-700/80 bg-slate-900/30">
            <a href="#pending-section" class="inline-flex items-center rounded-full border border-amber-400/40 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-200 hover:bg-amber-500/20">Pending ({{ $summary['pending'] }})</a>
            <a href="#enabled-section" class="inline-flex items-center rounded-full border border-emerald-400/40 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-200 hover:bg-emerald-500/20">Enabled ({{ $summary['enabled'] }})</a>
            <a href="#disabled-section" class="inline-flex items-center rounded-full border border-rose-400/40 bg-rose-500/10 px-3 py-1 text-xs font-semibold text-rose-200 hover:bg-rose-500/20">Disabled ({{ $summary['disabled'] }})</a>
        </div>

        <div id="pending-section" class="px-6 py-4 border-b border-slate-700/80">
            <h3 class="text-sm font-semibold text-amber-300">Pending Tenants</h3>
            <p class="mt-1 text-xs text-slate-400">New tenant records waiting for approval and domain activation.</p>
        </div>

        <div class="relative overflow-x-auto lg:overflow-visible border-b border-slate-700/80">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Tenant</th>
                        <th class="px-6 py-3 text-left">Plan</th>
                        <th class="px-6 py-3 text-left">Admin Signup</th>
                        <th class="px-6 py-3 text-left">Expiration Email</th>
                        <th class="px-6 py-3 text-left">Domain</th>
                        <th class="px-6 py-3 text-left">Database</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/70">
                    @forelse($pendingTenants as $tenant)
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-3">
                            <p class="font-medium text-slate-100">{{ $tenant->name }}</p>
                            <p class="text-xs text-slate-400">{{ $tenant->address }}</p>
                        </td>
                        <td class="px-6 py-3">
                            <p class="text-slate-200 uppercase font-semibold">{{ $tenant->plan_type }}</p>
                            <p class="text-xs text-slate-400">{{ optional($tenant->plan_started_at)->format('M d, Y') ?? 'N/A' }} - {{ optional($tenant->plan_due_at)->format('M d, Y') ?? 'N/A' }}</p>
                        </td>
                        <td class="px-6 py-3 text-slate-200">{{ $tenant->signup_admin_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-slate-200">{{ $tenant->plan_expiration_email ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-xs text-slate-300">{{ $tenant->requested_tenant_domain ?? $tenant->tenant_domain ?? 'N/A' }}</td>
                        <td class="px-6 py-3 font-mono text-xs text-slate-300">{{ $tenant->tenant_database ?? 'N/A' }}</td>
                        <td class="relative px-6 py-3 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('developer.tenants.show', $tenant) }}" class="inline-flex items-center rounded-lg bg-cyan-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-cyan-700">View</a>
                                <form method="POST" action="{{ route('developer.tenants.approve', $tenant) }}" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('developer.tenants.reject', $tenant) }}" class="inline-block" onsubmit="return handleTenantReject(this, '{{ addslashes($tenant->name) }}');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="rejection_reason" value="">
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-rose-700">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400">No pending tenants found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pendingTenants->hasPages())
        <div class="px-6 py-4 border-b border-slate-700/80">
            {{ $pendingTenants->links() }}
        </div>
        @endif

        <div id="enabled-section" class="px-6 py-4 border-b border-slate-700/80">
            <h3 class="text-sm font-semibold text-emerald-300">Enabled Tenants</h3>
            <p class="mt-1 text-xs text-slate-400">Approved tenants currently active and accessible.</p>
        </div>

        <div class="relative overflow-x-auto lg:overflow-visible border-b border-slate-700/80">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Tenant</th>
                        <th class="px-6 py-3 text-left">Plan</th>
                        <th class="px-6 py-3 text-left">Admin Signup</th>
                        <th class="px-6 py-3 text-left">Expiration Email</th>
                        <th class="px-6 py-3 text-left">Domain</th>
                        <th class="px-6 py-3 text-left">Database</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/70">
                    @forelse($enabledTenants as $tenant)
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-3">
                            <p class="font-medium text-slate-100">{{ $tenant->name }}</p>
                            <p class="text-xs text-slate-400">{{ $tenant->address }}</p>
                        </td>
                        <td class="px-6 py-3">
                            <p class="text-slate-200 uppercase font-semibold">{{ $tenant->plan_type }}</p>
                            <p class="text-xs text-slate-400">{{ optional($tenant->plan_started_at)->format('M d, Y') ?? 'N/A' }} - {{ optional($tenant->plan_due_at)->format('M d, Y') ?? 'N/A' }}</p>
                        </td>
                        <td class="px-6 py-3 text-slate-200">{{ $tenant->signup_admin_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-slate-200">{{ $tenant->plan_expiration_email ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-xs text-slate-300">
                            {{ $tenant->tenant_domain ?? $tenant->requested_tenant_domain ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-3 font-mono text-xs text-slate-300">{{ $tenant->tenant_database ?? 'N/A' }}</td>
                        <td class="relative px-6 py-3 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('developer.tenants.show', $tenant) }}" class="inline-flex items-center rounded-lg bg-cyan-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-cyan-700">View</a>
                                <a href="{{ route('developer.tenants.edit', $tenant) }}" class="inline-flex items-center rounded-lg bg-amber-100 px-3.5 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60">Edit</a>

                                <details class="relative">
                                    <summary class="list-none cursor-pointer inline-flex items-center rounded-lg bg-slate-700 px-3 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-600">
                                        More
                                    </summary>
                                    <div class="absolute right-0 top-full z-30 mt-2 w-44 rounded-xl border border-slate-700 bg-slate-900/95 p-2 shadow-xl">
                                        <a href="{{ route('developer.tenants.show', $tenant) }}#subscription" class="block rounded-md px-3 py-2 text-xs font-medium text-blue-300 hover:bg-slate-800">Plan Settings</a>
                                        <a href="{{ route('developer.tenants.customization.edit', $tenant) }}" class="mt-1 block rounded-md px-3 py-2 text-xs font-medium text-cyan-300 hover:bg-slate-800">Customization</a>

                                        <form method="POST" action="{{ route('developer.tenants.toggle-status', $tenant) }}" class="mt-1" onsubmit="return handleTenantStatusToggle(this, true, '{{ addslashes($tenant->name) }}');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="disable_reason" value="">
                                            <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-xs font-medium text-rose-300 hover:bg-rose-900/30">
                                                Disable Tenant
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('developer.tenants.destroy', $tenant) }}"
                                              onsubmit="return confirm('Delete tenant {{ addslashes($tenant->name) }}? This only removes central record.')"
                                              class="mt-1 border-t border-slate-700 pt-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-xs font-medium text-rose-300 hover:bg-rose-900/30">Delete Tenant</button>
                                        </form>
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400">No enabled tenants found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($enabledTenants->hasPages())
        <div class="px-6 py-4 border-b border-slate-700/80">
            {{ $enabledTenants->links() }}
        </div>
        @endif

        <div id="disabled-section" class="px-6 py-4 border-b border-slate-700/80">
            <h3 class="text-sm font-semibold text-rose-300">Disabled Tenants</h3>
            <p class="mt-1 text-xs text-slate-400">Approved tenants currently blocked from access.</p>
        </div>

        <div class="relative overflow-x-auto lg:overflow-visible">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Tenant</th>
                        <th class="px-6 py-3 text-left">Plan</th>
                        <th class="px-6 py-3 text-left">Admin Signup</th>
                        <th class="px-6 py-3 text-left">Expiration Email</th>
                        <th class="px-6 py-3 text-left">Domain</th>
                        <th class="px-6 py-3 text-left">Database</th>
                        <th class="px-6 py-3 text-left">Disable Reason</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/70">
                    @forelse($disabledTenants as $tenant)
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-3">
                            <p class="font-medium text-slate-100">{{ $tenant->name }}</p>
                            <p class="text-xs text-slate-400">{{ $tenant->address }}</p>
                        </td>
                        <td class="px-6 py-3">
                            <p class="text-slate-200 uppercase font-semibold">{{ $tenant->plan_type }}</p>
                            <p class="text-xs text-slate-400">{{ optional($tenant->plan_started_at)->format('M d, Y') ?? 'N/A' }} - {{ optional($tenant->plan_due_at)->format('M d, Y') ?? 'N/A' }}</p>
                        </td>
                        <td class="px-6 py-3 text-slate-200">{{ $tenant->signup_admin_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-slate-200">{{ $tenant->plan_expiration_email ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-xs text-slate-300">{{ $tenant->tenant_domain ?? $tenant->requested_tenant_domain ?? 'N/A' }}</td>
                        <td class="px-6 py-3 font-mono text-xs text-slate-300">{{ $tenant->tenant_database ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-xs text-rose-200">{{ $tenant->disable_reason ?? 'N/A' }}</td>
                        <td class="relative px-6 py-3 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('developer.tenants.show', $tenant) }}" class="inline-flex items-center rounded-lg bg-cyan-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-cyan-700">View</a>
                                <form method="POST" action="{{ route('developer.tenants.toggle-status', $tenant) }}" class="inline-block" onsubmit="return handleTenantStatusToggle(this, false, '{{ addslashes($tenant->name) }}');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="disable_reason" value="">
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Enable</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-slate-400">No disabled tenants found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($disabledTenants->hasPages())
        <div class="px-6 py-4 border-t border-slate-700/80">
            {{ $disabledTenants->links() }}
        </div>
        @endif
    </div>

    <div id="tenantActionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 px-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-2xl">
            <h4 id="tenantActionTitle" class="text-base font-semibold text-slate-100">Confirm action</h4>
            <p id="tenantActionMessage" class="mt-2 text-sm text-slate-300"></p>
            <div id="tenantActionInputWrap" class="mt-4 hidden">
                <label id="tenantActionInputLabel" for="tenantActionInput" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-400"></label>
                <input id="tenantActionInput" type="text" class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                <p id="tenantActionInputError" class="mt-1 hidden text-xs text-rose-300"></p>
            </div>
            <div class="mt-5 flex items-center justify-end gap-2">
                <button id="tenantActionCancel" type="button" class="rounded-lg border border-slate-600 px-3.5 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-800">Cancel</button>
                <button id="tenantActionConfirm" type="button" class="rounded-lg bg-cyan-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-cyan-700">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let tenantActionState = null;

        function getTenantActionElements() {
            return {
                modal: document.getElementById('tenantActionModal'),
                title: document.getElementById('tenantActionTitle'),
                message: document.getElementById('tenantActionMessage'),
                inputWrap: document.getElementById('tenantActionInputWrap'),
                inputLabel: document.getElementById('tenantActionInputLabel'),
                input: document.getElementById('tenantActionInput'),
                inputError: document.getElementById('tenantActionInputError'),
                cancel: document.getElementById('tenantActionCancel'),
                confirm: document.getElementById('tenantActionConfirm'),
            };
        }

        function closeTenantActionModal() {
            const els = getTenantActionElements();
            if (!els.modal) {
                return;
            }

            els.modal.classList.add('hidden');
            els.modal.classList.remove('flex');
            tenantActionState = null;
        }

        function openTenantActionModal(config) {
            const els = getTenantActionElements();
            if (!els.modal) {
                return false;
            }

            tenantActionState = config;
            els.title.textContent = config.title;
            els.message.textContent = config.message;
            els.confirm.textContent = config.confirmText || 'Confirm';
            els.inputError.classList.add('hidden');
            els.inputError.textContent = '';

            if (config.input) {
                els.inputWrap.classList.remove('hidden');
                els.inputLabel.textContent = config.input.label;
                els.input.placeholder = config.input.placeholder || '';
                els.input.value = config.input.defaultValue || '';
                setTimeout(() => els.input.focus(), 0);
            } else {
                els.inputWrap.classList.add('hidden');
                els.input.value = '';
            }

            els.modal.classList.remove('hidden');
            els.modal.classList.add('flex');
            return true;
        }

        function handleTenantStatusToggle(form, isCurrentlyEnabled, tenantName) {
            if (!isCurrentlyEnabled) {
                const opened = openTenantActionModal({
                    title: 'Enable tenant',
                    message: 'Enable tenant "' + tenantName + '"?',
                    confirmText: 'Enable',
                    onConfirm: () => form.submit(),
                });

                return opened ? false : confirm('Enable tenant "' + tenantName + '"?');
            }

            const opened = openTenantActionModal({
                title: 'Disable tenant',
                message: 'Provide a required reason before disabling "' + tenantName + '".',
                confirmText: 'Disable',
                input: {
                    label: 'Disable reason',
                    placeholder: 'Plan expired',
                    defaultValue: 'Plan expired',
                },
                onConfirm: (value) => {
                    const trimmed = value.trim();
                    if (!trimmed) {
                        return 'Disable reason is required.';
                    }

                    const input = form.querySelector('input[name="disable_reason"]');
                    if (input) {
                        input.value = trimmed;
                    }

                    form.submit();
                    return null;
                },
            });

            if (!opened) {
                const reason = prompt('Disable reason for "' + tenantName + '" (required):', 'Plan expired');
                if (reason === null || reason.trim() === '') {
                    return false;
                }
                const input = form.querySelector('input[name="disable_reason"]');
                if (input) {
                    input.value = reason.trim();
                }
                return true;
            }

            return false;
        }

        function handleTenantReject(form, tenantName) {
            const opened = openTenantActionModal({
                title: 'Reject pending tenant',
                message: 'Reject "' + tenantName + '"? This removes the pending record from central list.',
                confirmText: 'Reject',
                input: {
                    label: 'Reason (optional)',
                    placeholder: 'Incomplete requirements',
                    defaultValue: 'Incomplete requirements',
                },
                onConfirm: (value) => {
                    const input = form.querySelector('input[name="rejection_reason"]');
                    if (input) {
                        input.value = value.trim();
                    }

                    form.submit();
                    return null;
                },
            });

            if (!opened) {
                const reason = prompt('Reason for rejecting "' + tenantName + '" (optional):', 'Incomplete requirements');
                if (reason === null) {
                    return false;
                }
                const input = form.querySelector('input[name="rejection_reason"]');
                if (input) {
                    input.value = reason.trim();
                }
                return confirm('Reject pending tenant "' + tenantName + '"? This removes the pending record from central list.');
            }

            return false;
        }

        (function attachTenantActionModalHandlers() {
            const els = getTenantActionElements();
            if (!els.modal) {
                return;
            }

            els.cancel.addEventListener('click', closeTenantActionModal);
            els.modal.addEventListener('click', function (event) {
                if (event.target === els.modal) {
                    closeTenantActionModal();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && tenantActionState) {
                    closeTenantActionModal();
                }
            });

            els.confirm.addEventListener('click', function () {
                if (!tenantActionState || typeof tenantActionState.onConfirm !== 'function') {
                    closeTenantActionModal();
                    return;
                }

                const value = els.input ? els.input.value : '';
                const error = tenantActionState.onConfirm(value);

                if (typeof error === 'string' && error.length > 0) {
                    els.inputError.textContent = error;
                    els.inputError.classList.remove('hidden');
                    return;
                }

                closeTenantActionModal();
            });
        })();
    </script>
</x-layouts.admin>
