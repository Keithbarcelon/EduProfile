<x-layouts.admin :pageTitle="'Support Requests'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <span>Developer</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Support Requests</span>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

    <!-- Status Summary Cards -->
    <div class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-4">
        <a href="{{ route('developer.support-requests.index') }}" class="rounded-2xl border border-cyan-100 bg-gradient-to-br from-white to-cyan-50 px-5 py-4 shadow-sm dark:border-cyan-900/50 dark:from-slate-900 dark:to-slate-800 hover:shadow-md {{ request('status') == '' ? 'ring-2 ring-cyan-400' : '' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700 dark:text-cyan-300">Total Requests</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ collect($statusCounts)->sum() ?? 0 }}</p>
        </a>
        <a href="{{ route('developer.support-requests.index', ['status' => 'open']) }}" class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 px-5 py-4 shadow-sm dark:border-amber-900/50 dark:from-slate-900 dark:to-slate-800 hover:shadow-md {{ request('status') == 'open' ? 'ring-2 ring-amber-400' : '' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Open</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $statusCounts['open'] ?? 0 }}</p>
        </a>
        <a href="{{ route('developer.support-requests.index', ['status' => 'in_progress']) }}" class="rounded-2xl border border-blue-100 bg-gradient-to-br from-white to-blue-50 px-5 py-4 shadow-sm dark:border-blue-900/50 dark:from-slate-900 dark:to-slate-800 hover:shadow-md {{ request('status') == 'in_progress' ? 'ring-2 ring-blue-400' : '' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">In Progress</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $statusCounts['in_progress'] ?? 0 }}</p>
        </a>
        <a href="{{ route('developer.support-requests.index', ['status' => 'resolved']) }}" class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50 px-5 py-4 shadow-sm dark:border-emerald-900/50 dark:from-slate-900 dark:to-slate-800 hover:shadow-md {{ request('status') == 'resolved' ? 'ring-2 ring-emerald-400' : '' }}">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Resolved</p>
            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">{{ $statusCounts['resolved'] ?? 0 }}</p>
        </a>
    </div>

    <!-- Filter Section -->
    <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 backdrop-blur-sm p-4">
        <form method="GET" action="{{ route('developer.support-requests.index') }}" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-64">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Filter by Tenant</label>
                <select name="tenant_id" class="w-full rounded-lg border-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->name }} ({{ $tenant->tenant_domain }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-cyan-600 hover:bg-cyan-700 px-4 py-2 text-sm font-semibold text-white transition-colors">
                    Filter
                </button>
                @if(request('status') || request('tenant_id'))
                    <a href="{{ route('developer.support-requests.index') }}" class="rounded-lg bg-slate-700 hover:bg-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 shadow-xl backdrop-blur-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/50 text-xs text-slate-300 uppercase tracking-wider border-b border-slate-700/80">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold">Tenant</th>
                        <th class="px-6 py-3 text-left font-semibold">Subject</th>
                        <th class="px-6 py-3 text-left font-semibold">Status</th>
                        <th class="px-6 py-3 text-left font-semibold">Submitted</th>
                        <th class="px-6 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/70">
                    @forelse($supportRequests as $request)
                        <tr class="hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-100">{{ $request->tenant->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400">{{ $request->tenant->tenant_domain ?? '' }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-sm text-slate-200 max-w-xs truncate">{{ $request->subject }}</div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                    @if($request->status === 'open') bg-amber-500/20 text-amber-300 border border-amber-500/40
                                    @elseif($request->status === 'in_progress') bg-blue-500/20 text-blue-300 border border-blue-500/40
                                    @elseif($request->status === 'resolved') bg-emerald-500/20 text-emerald-300 border border-emerald-500/40
                                    @else bg-gray-500/20 text-gray-300 border border-gray-500/40
                                    @endif
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-xs text-slate-400">
                                {{ $request->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-right">
                                <a href="{{ route('developer.support-requests.show', $request) }}" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold transition-colors">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                No support requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($supportRequests->hasPages())
            <div class="border-t border-slate-700/80 px-6 py-4 bg-slate-900/50">
                {{ $supportRequests->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
