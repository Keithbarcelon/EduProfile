<x-layouts.admin :pageTitle="'Version Management'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.dashboard') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Developer</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Version Management</span>
    </x-slot>

    <div class="mx-auto w-full max-w-[1400px] space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
        @endif

        <section class="rounded-3xl border border-cyan-400/30 bg-gradient-to-r from-slate-900 via-slate-800 to-cyan-950 px-6 py-6 text-white shadow-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Release Operations</p>
            <h2 class="mt-2 text-2xl font-bold">Release and Tenant Update Monitoring</h2>
            <p class="mt-2 max-w-3xl text-sm text-cyan-100/85">Track active versions, tenant update checks, and support escalations in one control plane.</p>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <article class="rounded-2xl border border-cyan-400/30 bg-cyan-500/10 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-cyan-300">Active Version</p>
                    <p class="mt-1 text-lg font-bold">{{ $activeVersion?->version ?? 'N/A' }}</p>
                </article>
                <article class="rounded-2xl border border-indigo-400/30 bg-indigo-500/10 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-indigo-300">Tenant Update Records</p>
                    <p class="mt-1 text-lg font-bold">{{ $tenantUpdates->total() }}</p>
                </article>
                <article class="rounded-2xl border border-amber-400/30 bg-amber-500/10 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-amber-200">Open Requests</p>
                    <p class="mt-1 text-lg font-bold">{{ $supportRequests->where('status', 'open')->count() }}</p>
                </article>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-6 shadow-xl backdrop-blur-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-100">Add Version</h3>
                    <p class="mt-1 text-xs text-slate-400">Create release metadata and optionally set it as the active version.</p>
                </div>
                <form method="POST" action="{{ route('developer.version-management.sync-github') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow hover:bg-indigo-700">Fetch Latest from GitHub</button>
                </form>
            </div>

            <form method="POST" action="{{ route('developer.version-management.versions.store') }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-6">
                @csrf
                <input type="text" name="version" value="{{ old('version') }}" required placeholder="v1.0.1" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100 md:col-span-1">
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Release notes summary" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100 md:col-span-3">
                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-200 md:col-span-1">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active'))>
                    Set active
                </label>
                <button type="submit" class="rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-700 md:col-span-1">Add Version</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-700/70 bg-slate-900/70 shadow-xl backdrop-blur-sm">
            <div class="border-b border-slate-700/80 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-100">Version List</h3>
                <p class="mt-1 text-xs text-slate-400">Saved releases and activation controls.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-300">
                        <tr>
                            <th class="px-6 py-3 text-left">Version</th>
                            <th class="px-6 py-3 text-left">Notes</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/70">
                        @forelse($versions as $version)
                            <tr class="bg-slate-900/25 hover:bg-slate-800/50">
                                <td class="px-6 py-3 font-semibold text-slate-100">{{ $version->version }}</td>
                                <td class="px-6 py-3 text-slate-300">{{ $version->notes ?: 'No notes' }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $version->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $version->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @if(! $version->is_active)
                                    <form method="POST" action="{{ route('developer.version-management.versions.activate', $version) }}" class="inline-flex">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-cyan-700">Set Active</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400">No versions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($versions->hasPages())
                <div class="border-t border-slate-700/80 px-6 py-4">{{ $versions->links() }}</div>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-700/70 bg-slate-900/70 shadow-xl backdrop-blur-sm">
            <div class="border-b border-slate-700/80 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-100">Tenant Update Status</h3>
                <p class="mt-1 text-xs text-slate-400">Monitor tenant version posture and acknowledgement history.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-300">
                        <tr>
                            <th class="px-6 py-3 text-left">Tenant</th>
                            <th class="px-6 py-3 text-left">Current</th>
                            <th class="px-6 py-3 text-left">Latest Seen</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Last Checked</th>
                            <th class="px-6 py-3 text-left">Acknowledged</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/70">
                        @forelse($tenantUpdateRows as $row)
                            @php $update = $row['model']; @endphp
                            <tr class="bg-slate-900/25 hover:bg-slate-800/50">
                                <td class="px-6 py-3 text-slate-100">
                                    <p class="font-semibold">{{ $update->tenant?->name ?? 'Unknown Tenant' }}</p>
                                    <p class="text-xs text-slate-400">{{ $update->tenant?->tenant_domain ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-3 text-slate-200">{{ $update->current_version }}</td>
                                <td class="px-6 py-3 text-slate-200">{{ $row['visible_latest'] ?: 'N/A' }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $row['update_available'] ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $row['update_available'] ? 'Update Available' : 'Up To Date' }}</span>
                                </td>
                                <td class="px-6 py-3 text-slate-300">{{ $update->last_checked_at?->format('M d, Y h:i A') ?? 'Never' }}</td>
                                <td class="px-6 py-3 text-slate-300">{{ $update->acknowledged_at?->format('M d, Y h:i A') ?? 'Not yet' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400">No tenant update records found yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tenantUpdates->hasPages())
                <div class="border-t border-slate-700/80 px-6 py-4">{{ $tenantUpdates->links() }}</div>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-700/70 bg-slate-900/70 shadow-xl backdrop-blur-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-700/80 px-6 py-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-100">Support Requests</h3>
                    <p class="mt-1 text-xs text-slate-400">Track tenant-reported concerns and update request status.</p>
                </div>
                <form method="GET" action="{{ route('developer.version-management.index') }}" class="flex items-center gap-2">
                    <select name="request_status" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-xs text-slate-100">
                        <option value="">All</option>
                        <option value="open" @selected($requestStatusFilter === 'open')>Open</option>
                        <option value="in_progress" @selected($requestStatusFilter === 'in_progress')>In Progress</option>
                        <option value="resolved" @selected($requestStatusFilter === 'resolved')>Resolved</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white hover:bg-cyan-700">Filter</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-950/60 text-xs uppercase tracking-wider text-slate-300">
                        <tr>
                            <th class="px-6 py-3 text-left">Tenant</th>
                            <th class="px-6 py-3 text-left">Subject</th>
                            <th class="px-6 py-3 text-left">Message</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/70">
                        @forelse($supportRequests as $supportRequest)
                            <tr class="bg-slate-900/25 hover:bg-slate-800/50">
                                <td class="px-6 py-3 text-slate-100">{{ $supportRequest->tenant?->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-3 text-slate-200">{{ $supportRequest->subject }}</td>
                                <td class="px-6 py-3 text-slate-300">{{ \Illuminate\Support\Str::limit($supportRequest->message, 120) }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $supportRequest->status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : ($supportRequest->status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-700') }}">{{ str_replace('_', ' ', $supportRequest->status) }}</span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <form method="POST" action="{{ route('developer.version-management.support-requests.status', $supportRequest) }}" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="support_requests_page" value="{{ request('support_requests_page', 1) }}">
                                        <select name="status" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-xs text-slate-100">
                                            <option value="open" @selected($supportRequest->status === 'open')>Open</option>
                                            <option value="in_progress" @selected($supportRequest->status === 'in_progress')>In Progress</option>
                                            <option value="resolved" @selected($supportRequest->status === 'resolved')>Resolved</option>
                                        </select>
                                        <button type="submit" class="rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-cyan-700">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400">No support requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($supportRequests->hasPages())
                <div class="border-t border-slate-700/80 px-6 py-4">{{ $supportRequests->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.admin>
