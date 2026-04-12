@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
@endphp
<x-layouts.admin :pageTitle="'Reports'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Reports</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="admin-soft-ring rounded-3xl bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-6 text-white sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Analytics Engine</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Tenant Reports</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-400">Generate student status, department, and document compliance reports scoped to the current school.</p>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm xl:col-span-1">
                <h3 class="text-lg font-bold text-slate-800">Students Per Status</h3>
                <div class="mt-4 space-y-3">
                    @foreach($studentStats['by_category'] as $category => $count)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <span class="text-sm text-slate-600">{{ ucfirst($category) }}</span>
                        <span class="text-sm font-bold text-slate-900">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('admin.reports.export') }}" class="mt-5">
                    @csrf
                    <input type="hidden" name="report_type" value="status">
                    <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">Export Status Report</button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm xl:col-span-1">
                <h3 class="text-lg font-bold text-slate-800">Department Reports</h3>
                <div class="mt-4 space-y-3">
                    @forelse($departmentReports as $department)
                    <div class="rounded-xl border border-slate-100 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-800">{{ $department->name }}</span>
                            <span class="text-xs text-slate-400">{{ $department->code ?: 'No code' }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $department->students_count }} students • {{ $department->faculty_count }} faculty</p>
                    </div>
                    @empty
                    <p class="text-sm text-slate-400">No departments available yet.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('admin.reports.export') }}" class="mt-5">
                    @csrf
                    <input type="hidden" name="report_type" value="department">
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Export Department Report</button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm xl:col-span-1">
                <h3 class="text-lg font-bold text-slate-800">Document Compliance</h3>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <span class="text-sm text-slate-600">Students with documents</span>
                        <span class="text-sm font-bold text-slate-900">{{ $documentCompliance['students_with_documents'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <span class="text-sm text-slate-600">Missing submissions</span>
                        <span class="text-sm font-bold text-rose-700">{{ $documentCompliance['missing_submissions'] }}</span>
                    </div>
                    @foreach($documentCompliance['by_status'] as $status => $count)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <span class="text-sm text-slate-600">{{ ucfirst($status) }}</span>
                        <span class="text-sm font-bold text-slate-900">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('admin.reports.export') }}" class="mt-5">
                    @csrf
                    <input type="hidden" name="report_type" value="documents">
                    <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Export Compliance Report</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
