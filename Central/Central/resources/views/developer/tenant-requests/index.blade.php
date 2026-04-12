<x-layouts.admin :pageTitle="'Tenant Requests'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <span>Developer</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Tenant Requests</span>
    </x-slot>

    <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="rounded-2xl border border-cyan-100 bg-gradient-to-br from-white to-cyan-50 px-5 py-4 shadow-sm dark:border-cyan-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700 dark:text-cyan-300">Total Requests</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 px-5 py-4 shadow-sm dark:border-amber-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Pending</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $summary['pending'] }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50 px-5 py-4 shadow-sm dark:border-emerald-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Approved</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $summary['approved'] }}</p>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-white to-rose-50 px-5 py-4 shadow-sm dark:border-rose-900/50 dark:from-slate-900 dark:to-slate-800">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-300">Rejected</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $summary['rejected'] }}</p>
        </div>
    </div>

    <div class="rounded-2xl bg-slate-900/70 shadow-xl border border-slate-700/70 backdrop-blur-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-700/80">
            <h2 class="text-base font-semibold text-slate-100">Approval Queue</h2>
            <a href="{{ route('developer.tenants.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 text-sm font-semibold rounded-xl transition-colors border border-cyan-200 dark:bg-cyan-900/30 dark:border-cyan-800 dark:text-cyan-300 dark:hover:bg-cyan-900/50">
                Back to Tenants
            </a>
        </div>

        <form method="GET" action="{{ route('developer.tenant-requests.index') }}" class="flex flex-wrap gap-3 px-6 py-3 bg-slate-900/40 border-b border-slate-700/80">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search school, email, admin, domain..." class="flex-1 min-w-[220px] text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
            <select name="status" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2">
                <option value="">All Status</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm rounded-lg transition-colors">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('developer.tenant-requests.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg transition-colors">Clear</a>
            @endif
        </form>

        <div class="relative overflow-x-auto lg:overflow-visible">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">School</th>
                        <th class="px-6 py-3 text-left">Admin</th>
                        <th class="px-6 py-3 text-left">Plan</th>
                        <th class="px-6 py-3 text-left">Requested Domain</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Reviewed</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/70">
                    @forelse($tenantRequests as $tenantRequest)
                        <tr class="hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-3">
                                <p class="font-medium text-slate-100">{{ $tenantRequest->tenant_name }}</p>
                                <p class="text-xs text-slate-400">{{ $tenantRequest->address }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-slate-200">{{ $tenantRequest->signup_admin_name }}</p>
                                <p class="text-xs text-slate-400">{{ $tenantRequest->admin_email }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-slate-200 uppercase font-semibold">{{ $tenantRequest->plan_type }}</p>
                                <p class="text-xs text-slate-400">{{ optional($tenantRequest->plan_started_at)->format('M d, Y') ?? 'N/A' }} - {{ optional($tenantRequest->plan_due_at)->format('M d, Y') ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-3 text-xs text-slate-300">{{ $tenantRequest->requested_tenant_domain ?? 'Auto-generate on approval' }}</td>
                            <td class="px-6 py-3">
                                @if($tenantRequest->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                @elseif($tenantRequest->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Approved</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700">Rejected</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-slate-300">
                                @if($tenantRequest->reviewed_at)
                                    {{ $tenantRequest->reviewed_at->format('M d, Y h:i A') }}
                                    <p class="text-slate-400">{{ $tenantRequest->reviewer?->name ?? 'System' }}</p>
                                @else
                                    Not reviewed
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('developer.tenant-requests.show', $tenantRequest) }}" class="inline-flex items-center rounded-lg bg-cyan-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-cyan-700">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">No tenant requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenantRequests->hasPages())
            <div class="px-6 py-4 border-t border-slate-700/80">
                {{ $tenantRequests->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
