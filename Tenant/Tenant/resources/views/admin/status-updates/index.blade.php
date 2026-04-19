@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
    $categoryStyles = [
        'regular' => 'bg-sky-50 text-sky-700 border-sky-100',
        'affirmative' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'probation' => 'bg-amber-50 text-amber-700 border-amber-100',
    ];
@endphp
<x-layouts.admin :pageTitle="'Status Monitoring'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Status Monitoring</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="tenant-hero admin-soft-ring rounded-3xl px-6 py-6 text-white sm:px-8">
            <p class="tenant-hero-kicker text-xs font-semibold uppercase tracking-[0.2em]">Monitoring System</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Student Status Monitoring</h2>
            <p class="tenant-hero-body mt-2 max-w-2xl text-sm">Track current student standing across the tenant and review submitted status change requests.</p>
        </section>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach(['regular', 'affirmative', 'probation'] as $category)
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ ucfirst($category) }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($statusCategoryCounts[$category] ?? 0) }}</p>
            </div>
            @endforeach
        </div>

        <div class="admin-panel rounded-2xl bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Current Student Statuses</h2>
                    <p class="text-sm text-slate-500">Filter the tenant-wide status view by category.</p>
                </div>
                <form method="GET" action="{{ route('admin.status-updates.index') }}" class="flex flex-wrap items-center gap-3">
                    <select name="status_category" class="tenant-focus-ring rounded-lg border-slate-300 px-3 py-2 text-sm">
                        <option value="">All categories</option>
                        <option value="regular" @selected(request('status_category') === 'regular')>Regular</option>
                        <option value="affirmative" @selected(request('status_category') === 'affirmative')>Affirmative</option>
                        <option value="probation" @selected(request('status_category') === 'probation')>Probation</option>
                    </select>
                    <button type="submit" class="tenant-primary-btn rounded-lg px-4 py-2 text-sm font-medium transition-colors">Filter</button>
                    @if(request()->filled('status_category'))
                    <a href="{{ route('admin.status-updates.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-200">Clear</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Student</th>
                            <th class="px-6 py-4 text-left font-semibold">Department</th>
                            <th class="px-6 py-4 text-left font-semibold">Status Category</th>
                            <th class="px-6 py-4 text-left font-semibold">Academic Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800">{{ $student->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $student->student_id }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $student->department->name ?? 'Unassigned' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $categoryStyles[$student->status_category] ?? 'bg-slate-50 text-slate-700 border-slate-100' }}">
                                    {{ ucfirst($student->status_category) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ ucfirst($student->status) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">No students match the selected filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/30">
                {{ $students->links() }}
            </div>
            @endif
        </div>

        <div class="admin-panel flex flex-col rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-800">Status Change Requests</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Student</th>
                            <th class="px-6 py-4 text-left font-semibold">Change</th>
                            <th class="px-6 py-4 text-left font-semibold">Initiated By</th>
                            <th class="px-6 py-4 text-left font-semibold">Pending Role</th>
                            <th class="px-6 py-4 text-left font-semibold">Approval</th>
                            <th class="px-6 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($updates as $update)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800">{{ $update->student->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $update->student->student_id }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 line-through">{{ ucfirst($update->old_status ?? 'n/a') }}</span>
                                    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    <span class="tenant-primary-text font-medium">{{ ucfirst($update->new_status) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-700">{{ $update->initiator->name ?? 'System' }}</p>
                                <p class="text-[10px] uppercase text-slate-400">{{ $update->created_at->format('M d, Y h:i A') }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                @if($update->approval_status === 'pending' && $update->required_role_slug)
                                    {{ str($update->required_role_slug)->replace('_', ' ')->title() }}
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ ucfirst($update->approval_status) }}</td>
                            <td class="px-6 py-4 text-right">
                                @if($update->approval_status === 'pending')
                                    @can('approve', $update)
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.status-updates.approve', $update) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-emerald-700">Approve</button>
                                        </form>
                                        <button type="button" @click="$dispatch('open-modal', 'reject-{{ $update->id }}')" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">Reject</button>
                                    </div>

                                    <x-modal name="reject-{{ $update->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('admin.status-updates.reject', $update) }}" class="p-6">
                                            @csrf
                                            <h2 class="text-lg font-medium text-slate-900">Reject Status Update</h2>
                                            <p class="mt-1 text-sm text-slate-600">Provide the reason for rejecting this request.</p>
                                            <div class="mt-4">
                                                <textarea name="rejection_reason" required class="tenant-focus-ring w-full rounded-xl border-slate-300 shadow-sm"></textarea>
                                            </div>
                                            <div class="mt-6 flex justify-end gap-3">
                                                <button type="button" x-on:click="$dispatch('close')" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">Cancel</button>
                                                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Reject Request</button>
                                            </div>
                                        </form>
                                    </x-modal>
                                    @else
                                    <span class="text-xs text-slate-400">Awaiting review</span>
                                    @endcan
                                @else
                                <span class="text-xs text-slate-400">{{ $update->approver->name ?? 'Processed' }}</span>
                                @endif

                                @if(!empty($update->approval_audit))
                                <div class="mt-2 text-left">
                                    <details class="group inline-block text-xs text-slate-500">
                                        <summary class="cursor-pointer list-none font-medium text-slate-500 hover:text-slate-700">Audit Timeline ({{ count((array) $update->approval_audit) }})</summary>
                                        <div class="mt-2 max-h-36 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-2 text-[11px] text-slate-600">
                                            @foreach((array) $update->approval_audit as $audit)
                                                <p>
                                                    <span class="font-semibold">{{ str((string) ($audit['action'] ?? 'event'))->replace('_', ' ')->title() }}</span>
                                                    · {{ \Illuminate\Support\Carbon::parse((string) ($audit['at'] ?? now()))->format('M d, Y h:i A') }}
                                                    @if(!empty($audit['by_role']))
                                                        · {{ str((string) $audit['by_role'])->replace('_', ' ')->title() }}
                                                    @endif
                                                </p>
                                            @endforeach
                                        </div>
                                    </details>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">No status requests found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($updates->hasPages())
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/30">
                {{ $updates->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
