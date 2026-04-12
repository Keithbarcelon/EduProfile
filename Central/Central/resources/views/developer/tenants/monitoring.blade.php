<x-layouts.admin :pageTitle="'Tenant Monitoring'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.tenants.index') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Tenants</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Monitoring</span>
    </x-slot>

    <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-slate-700 dark:bg-slate-900"><p class="text-xs uppercase text-slate-500 dark:text-slate-300">Total</p><p class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $summary['total'] }}</p></div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 shadow-sm dark:border-amber-900/50 dark:bg-amber-900/20"><p class="text-xs uppercase text-amber-700 dark:text-amber-300">Pending</p><p class="mt-1 text-2xl font-black text-amber-900 dark:text-amber-100">{{ $summary['pending'] }}</p></div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-900/20"><p class="text-xs uppercase text-emerald-700 dark:text-emerald-300">Enabled</p><p class="mt-1 text-2xl font-black text-emerald-900 dark:text-emerald-100">{{ $summary['enabled'] }}</p></div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 shadow-sm dark:border-amber-900/50 dark:bg-amber-900/20"><p class="text-xs uppercase text-amber-700 dark:text-amber-300">Disabled</p><p class="mt-1 text-2xl font-black text-amber-900 dark:text-amber-100">{{ $summary['disabled'] }}</p></div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 shadow-sm dark:border-rose-900/50 dark:bg-rose-900/20"><p class="text-xs uppercase text-rose-700 dark:text-rose-300">Expired</p><p class="mt-1 text-2xl font-black text-rose-900 dark:text-rose-100">{{ $summary['expired'] }}</p></div>
        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 shadow-sm dark:border-cyan-900/50 dark:bg-cyan-900/20"><p class="text-xs uppercase text-cyan-700 dark:text-cyan-300">Expiring in {{ $days }}d</p><p class="mt-1 text-2xl font-black text-cyan-900 dark:text-cyan-100">{{ $summary['expiring'] }}</p></div>
        <div class="rounded-2xl border border-fuchsia-200 bg-fuchsia-50 px-4 py-3 shadow-sm dark:border-fuchsia-900/50 dark:bg-fuchsia-900/20"><p class="text-xs uppercase text-fuchsia-700 dark:text-fuchsia-300">Over Usage Limit</p><p class="mt-1 text-2xl font-black text-fuchsia-900 dark:text-fuchsia-100">{{ $summary['over_limit'] }}</p></div>
    </div>

    <div class="rounded-2xl bg-slate-900/70 shadow-xl border border-slate-700/70 backdrop-blur-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-700/80">
            <h2 class="text-base font-semibold text-slate-100">Monitoring Queue</h2>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('developer.tenants.sync-usage') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-cyan-100 hover:bg-cyan-200 text-cyan-700 text-sm rounded-lg transition-colors dark:bg-cyan-900/40 dark:hover:bg-cyan-900/60 dark:text-cyan-300">Sync Usage</button>
                </form>
                <form method="POST" action="{{ route('developer.tenants.sync-expired') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 text-sm rounded-lg transition-colors dark:bg-rose-900/40 dark:hover:bg-rose-900/60 dark:text-rose-300">Sync Expired</button>
                </form>
                <a href="{{ route('developer.tenants.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition-colors dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">Back to Tenants</a>
            </div>
        </div>

        <form method="GET" action="{{ route('developer.tenants.monitoring') }}"
              class="flex flex-wrap gap-3 px-6 py-3 bg-slate-900/40 border-b border-slate-700/80">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tenant, domain, database..."
                   class="flex-1 min-w-[220px] text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
            <select name="status" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                <option value="">All Status</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="enabled" @selected(request('status') === 'enabled')>Enabled</option>
                <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
            </select>
            <select name="health" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                <option value="">All Health</option>
                <option value="healthy" @selected(request('health') === 'healthy')>Healthy</option>
                <option value="expiring" @selected(request('health') === 'expiring')>Expiring</option>
                <option value="expired" @selected(request('health') === 'expired')>Expired</option>
            </select>
            <input type="number" min="1" max="30" name="days" value="{{ request('days', $days) }}" class="w-24 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2" title="Expiring window (days)">
            <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm rounded-lg transition-colors">Filter</button>
        </form>

        <div class="relative overflow-x-auto lg:overflow-visible">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Tenant</th>
                        <th class="px-6 py-3 text-left">Domain</th>
                        <th class="px-6 py-3 text-left">Plan</th>
                        <th class="px-6 py-3 text-left">Plan Due</th>
                        <th class="px-6 py-3 text-left">Storage Usage</th>
                        <th class="px-6 py-3 text-left">Bandwidth Usage</th>
                        <th class="px-6 py-3 text-left">Health</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Reminder Email</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/70">
                    @forelse($tenants as $tenant)
                        @php
                            $due = $tenant->plan_due_at;
                            $isExpired = $tenant->isSubscriptionExpired();
                            $isExpiring = $tenant->isSubscriptionExpiringWithinDays($days);
                        @endphp
                        <tr class="hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-3">
                                <p class="font-medium text-slate-100">{{ $tenant->name }}</p>
                                <p class="text-xs text-slate-400">{{ $tenant->tenant_database ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-3 text-slate-200">
                                @if($tenant->tenant_domain)
                                    {{ $tenant->tenant_domain }}
                                @elseif($tenant->requested_tenant_domain)
                                    <span class="text-amber-300">Requested: {{ $tenant->requested_tenant_domain }}</span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full bg-cyan-900/40 px-2.5 py-0.5 text-xs font-semibold uppercase text-cyan-300">{{ $tenant->plan_type }}</span>
                            </td>
                            <td class="px-6 py-3 text-slate-200">{{ optional($due)->format('M d, Y') ?? 'N/A' }}</td>
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
                            <td class="px-6 py-3">
                                @if($tenant->approval_status === 'pending')
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Pending</span>
                                @else
                                    @if($tenant->is_enabled)
                                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">Enabled</span>
                                    @else
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300">Disabled</span>
                                            @if($tenant->disable_reason)
                                                <p class="max-w-[220px] text-[11px] text-slate-400">Reason: {{ $tenant->disable_reason }}</p>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-200">{{ $tenant->plan_expiration_email ?? 'N/A' }}</td>
                            <td class="relative px-6 py-3 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <a href="{{ route('developer.tenants.show', $tenant) }}" class="inline-flex items-center rounded-lg bg-cyan-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-cyan-700">View</a>
                                    <a href="{{ route('developer.tenants.show', $tenant) }}" class="inline-flex items-center rounded-lg bg-violet-100 px-3.5 py-2 text-xs font-semibold text-violet-700 hover:bg-violet-200 dark:bg-violet-900/40 dark:text-violet-300 dark:hover:bg-violet-900/60">Usage</a>

                                    <details class="relative">
                                        <summary class="list-none cursor-pointer inline-flex items-center rounded-lg bg-slate-700 px-3 py-2 text-xs font-semibold text-slate-100 hover:bg-slate-600">
                                            More
                                        </summary>
                                        <div class="absolute right-0 top-full z-30 mt-2 w-48 rounded-xl border border-slate-700 bg-slate-900/95 p-2 shadow-xl">
                                            <form method="POST" action="{{ route('developer.tenants.send-reminder', $tenant) }}">
                                                @csrf
                                                <input type="hidden" name="days" value="{{ $days }}">
                                                <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-xs font-medium text-cyan-300 hover:bg-slate-800">Send Reminder</button>
                                            </form>

                                            @if($tenant->approval_status === 'pending')
                                                <form method="POST" action="{{ route('developer.tenants.approve', $tenant) }}" class="mt-1 border-t border-slate-700 pt-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-xs font-medium text-emerald-300 hover:bg-emerald-900/30">
                                                        Approve Tenant
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('developer.tenants.toggle-status', $tenant) }}" class="mt-1 border-t border-slate-700 pt-1" onsubmit="return handleTenantStatusToggle(this, {{ $tenant->is_enabled ? 'true' : 'false' }}, '{{ addslashes($tenant->name) }}');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="disable_reason" value="">
                                                    <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-xs font-medium {{ $tenant->is_enabled ? 'text-rose-300 hover:bg-rose-900/30' : 'text-emerald-300 hover:bg-emerald-900/30' }}">
                                                        {{ $tenant->is_enabled ? 'Disable Tenant' : 'Enable Tenant' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </details>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10 text-center text-slate-400">No tenants found for monitoring.</td>
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
