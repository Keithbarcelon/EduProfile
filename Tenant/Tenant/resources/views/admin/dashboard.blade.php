@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
    $hasFullDashboardAccess = \App\Enums\UserRole::isAdmin(auth()->user()->role);
    $statusPalette = [
        'regular' => 'bg-sky-50 text-sky-700',
        'affirmative' => 'bg-emerald-50 text-emerald-700',
        'probation' => 'bg-amber-50 text-amber-700',
    ];
@endphp
<x-layouts.admin :pageTitle="'Dashboard'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <span class="text-slate-600">Overview</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
    <section class="tenant-hero admin-soft-ring rounded-3xl px-6 py-6 text-white sm:px-8 shadow-xl shadow-indigo-900/20">
        <p class="tenant-hero-kicker text-xs font-semibold uppercase tracking-[0.2em]">Tenant Overview</p>
        <h2 class="admin-display mt-2 text-2xl font-bold">{{ $roleLabel }} Dashboard</h2>
        <p class="tenant-hero-body mt-2 max-w-2xl text-sm">Monitor enrollment trends, check recent records, and manage student data from one focused workspace.</p>
    </section>

    @if($hasFullDashboardAccess && ($widgetVisibility['overview_cards'] ?? true))
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="admin-panel rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
            <p class="text-xs uppercase font-bold tracking-wider text-slate-400">Total Students</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalStudents) }}</p>
        </div>
        <div class="admin-panel rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
            <p class="text-xs uppercase font-bold tracking-wider text-slate-400">Pending Documents</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($pendingDocuments) }}</p>
        </div>
        <div class="admin-panel rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
            <p class="text-xs uppercase font-bold tracking-wider text-slate-400">Total Users</p>
            <p class="tenant-primary-text mt-2 text-2xl font-bold">{{ number_format($totalUsers) }}</p>
        </div>
        <div class="admin-panel rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
            <p class="text-xs uppercase font-bold tracking-wider text-slate-400">Missing Submissions</p>
            <p class="mt-2 text-2xl font-bold text-rose-600">{{ number_format($missingSubmissions) }}</p>
        </div>
    </div>
    @else
    <div class="tenant-soft-panel admin-panel rounded-2xl border p-6">
        <p class="tenant-soft-text text-sm font-medium italic">"Focus on your assigned tasks. Limited overview mode active."</p>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.3fr_0.7fr]">
        <div class="admin-panel overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Recent Students</h3>
                @can('viewAny', App\Models\Student::class)
                <a href="{{ route('admin.students.index') }}" class="tenant-link text-xs font-bold uppercase tracking-wider">View all</a>
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead class="bg-slate-50/50 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">Student ID</th>
                            <th class="px-5 py-3 text-left font-semibold">Name</th>
                            <th class="px-5 py-3 text-left font-semibold">Department</th>
                            <th class="px-5 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentStudents as $student)
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs">{{ $student->student_id }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $student->full_name }}</td>
                            <td class="px-5 py-3">{{ $student->department->name ?? 'Unassigned' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase {{ $statusPalette[$student->status_category] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $student->status_category }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-slate-400 italic">No students yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            @if($widgetVisibility['status_overview'] ?? true)
            <div class="admin-panel rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-800">Status Overview</h3>
                    <span class="text-xs text-slate-400">{{ number_format($newThisMonth) }} new this month</span>
                </div>
                <div class="mt-4 space-y-3">
                    @foreach(['regular', 'affirmative', 'probation'] as $statusKey)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <span class="text-sm font-medium text-slate-700">{{ ucfirst($statusKey) }}</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusPalette[$statusKey] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ number_format($statusCounts[$statusKey] ?? 0) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($widgetVisibility['document_queue'] ?? true)
            <div class="admin-panel rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-800">Document Queue</h3>
                <div class="mt-4 grid grid-cols-1 gap-3">
                    @foreach(['pending', 'approved', 'rejected'] as $documentStatus)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3">
                        <span class="text-sm font-medium text-slate-700">{{ ucfirst($documentStatus) }}</span>
                        <span class="text-sm font-bold text-slate-900">{{ number_format($documentStatusCounts[$documentStatus] ?? 0) }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="tenant-soft-panel tenant-soft-text mt-4 rounded-xl border px-4 py-3 text-sm">
                    {{ number_format($totalDepartments) }} departments are active in this tenant.
                </div>
            </div>
            @endif
        </div>
    </div>
    </div>
</x-layouts.admin>
