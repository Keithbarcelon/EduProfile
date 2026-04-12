<x-layouts.admin :pageTitle="'Tenant Management'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <span>Developer</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Tenants</span>
    </x-slot>

    @php
        $tenantItems = $tenants->getCollection();
        $pendingOnPage = $tenantItems->where('approval_status', 'pending')->count();
        $approvedOnPage = $tenantItems->where('approval_status', 'approved')->count();
        $enabledOnPage = $tenantItems->where('approval_status', 'approved')->where('is_enabled', true)->count();
        $disabledOnPage = $tenantItems->where('approval_status', 'approved')->where('is_enabled', false)->count();
    @endphp

    <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="rounded-2xl border border-cyan-100 bg-gradient-to-br from-white to-cyan-50 px-5 py-4 shadow-sm dark:border-cyan-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700 dark:text-cyan-300">Tenant Records</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $tenants->total() }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Across all pages</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 px-5 py-4 shadow-sm dark:border-amber-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Pending Approval</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $pendingOnPage }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">In current page</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50 px-5 py-4 shadow-sm dark:border-emerald-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Approved</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $approvedOnPage }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">In current page</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 px-5 py-4 shadow-sm dark:border-slate-700/70 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Access State</p>
            <p class="mt-2 text-lg font-black text-slate-900 dark:text-slate-100">{{ $enabledOnPage }} Enabled / {{ $disabledOnPage }} Disabled</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Approved tenants only</p>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-900/70 shadow-xl border border-slate-700/70 backdrop-blur-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-700/80">
            <div>
                <h2 class="text-base font-semibold text-slate-100">All Tenants</h2>
                <p class="mt-1 text-xs text-slate-400">Use the sidebar for plan modules, support tickets, and monitoring tools.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('developer.tenant-requests.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-sm font-semibold rounded-xl transition-colors border border-amber-200 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-300 dark:hover:bg-amber-900/50">
                    Request Queue
                </a>
                <a href="{{ route('developer.tenants.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Tenant
                </a>
            </div>
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
            <select name="status"
                    class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                <option value="">All Status</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="enabled" @selected(request('status') === 'enabled')>Enabled</option>
                <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm rounded-lg transition-colors">Filter</button>
            @if(request()->hasAny(['search','plan_type','status']))
            <a href="{{ route('developer.tenants.index') }}"
               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg transition-colors">Clear</a>
            @endif
        </form>

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
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/70">
                    @forelse($tenants as $tenant)
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
                            @if($tenant->tenant_domain)
                                {{ $tenant->tenant_domain }}
                            @elseif($tenant->requested_tenant_domain)
                                <span class="text-amber-300">Requested: {{ $tenant->requested_tenant_domain }}</span>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-3 font-mono text-xs text-slate-300">{{ $tenant->tenant_database ?? 'N/A' }}</td>
                        <td class="px-6 py-3">
                            @if($tenant->approval_status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending Approval</span>
                            @else
                                <div class="flex flex-col items-start gap-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700">Approved</span>
                                    @if($tenant->is_enabled)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Enabled</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Disabled</span>
                                        @if($tenant->disable_reason)
                                            <p class="max-w-[220px] text-[11px] text-slate-400">Reason: {{ $tenant->disable_reason }}</p>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </td>
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

                                        @if($tenant->approval_status === 'pending')
                                            <form method="POST" action="{{ route('developer.tenants.approve', $tenant) }}" class="mt-1">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-xs font-medium text-emerald-300 hover:bg-emerald-900/30">
                                                    Approve and Activate Domain
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('developer.tenants.toggle-status', $tenant) }}" class="mt-1" onsubmit="return handleTenantStatusToggle(this, {{ $tenant->is_enabled ? 'true' : 'false' }}, '{{ addslashes($tenant->name) }}');">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="disable_reason" value="">
                                                <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-xs font-medium {{ $tenant->is_enabled ? 'text-rose-300 hover:bg-rose-900/30' : 'text-emerald-300 hover:bg-emerald-900/30' }}">
                                                    {{ $tenant->is_enabled ? 'Disable Tenant' : 'Enable Tenant' }}
                                                </button>
                                            </form>
                                        @endif

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
                        <td colspan="8" class="px-6 py-10 text-center text-slate-400">No tenants found.</td>
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

    <script>
        function handleTenantStatusToggle(form, isCurrentlyEnabled, tenantName) {
            if (!isCurrentlyEnabled) {
                return confirm('Enable tenant "' + tenantName + '"?');
            }

            const reason = prompt('Disable reason for "' + tenantName + '" (required):', 'Plan expired');

            if (reason === null) {
                return false;
            }

            const trimmed = reason.trim();

            if (trimmed.length === 0) {
                alert('Disable reason is required.');
                return false;
            }

            const input = form.querySelector('input[name="disable_reason"]');
            if (input) {
                input.value = trimmed;
            }

            return true;
        }
    </script>
</x-layouts.admin>
