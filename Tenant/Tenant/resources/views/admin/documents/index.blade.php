@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
@endphp
<x-layouts.admin :pageTitle="'Document Monitoring'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Document Monitoring</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="admin-soft-ring rounded-3xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-6 text-white sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100">Verification System</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Tenant Document Monitoring</h2>
            <p class="mt-2 max-w-2xl text-sm text-emerald-100">Review uploads, filter by review state, and identify students who have not submitted any records yet.</p>
        </section>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach(['pending', 'approved', 'rejected'] as $status)
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ ucfirst($status) }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($documentStatusCounts[$status] ?? 0) }}</p>
            </div>
            @endforeach
        </div>

        <div class="admin-panel flex flex-col rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Uploaded Documents</h2>
                    <p class="text-sm text-slate-500">Only records from the current tenant are listed.</p>
                </div>
                <form method="GET" action="{{ route('admin.documents.index') }}" class="grid w-full gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-3 xl:grid-cols-4">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search student/document"
                        class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >

                    <select name="status" class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">All statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>

                    <select name="department_id" class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">All departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected((int) request('department_id') === (int) $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="reviewer_id" class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">All reviewers</option>
                        @foreach($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" @selected((int) request('reviewer_id') === (int) $reviewer->id)>
                                {{ $reviewer->name }}
                            </option>
                        @endforeach
                    </select>

                    <input
                        type="date"
                        name="uploaded_from"
                        value="{{ request('uploaded_from') }}"
                        class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        title="Uploaded from"
                    >

                    <input
                        type="date"
                        name="uploaded_to"
                        value="{{ request('uploaded_to') }}"
                        class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                        title="Uploaded to"
                    >

                    <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-3 xl:col-span-4">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-700">Apply Filters</button>
                        @if(!empty(collect(request()->query())->except('page')->filter()->all()))
                            <a href="{{ route('admin.documents.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Student</th>
                            <th class="px-6 py-4 text-left font-semibold">Department</th>
                            <th class="px-6 py-4 text-left font-semibold">Document</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($documents as $doc)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800">{{ $doc->student->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $doc->student->student_id }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $doc->student->department->name ?? 'Unassigned' }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.documents.view', $doc) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 hover:underline">{{ $doc->name }}</a>
                                <p class="text-xs text-slate-400">{{ $doc->created_at->format('M d, Y') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $doc->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($doc->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($doc->status === 'pending')
                                    @can('update', $doc)
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.documents.view', $doc) }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">View File</a>
                                        <form method="POST" action="{{ route('admin.documents.approve', $doc) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Approve</button>
                                        </form>
                                        <button type="button" @click="$dispatch('open-modal', 'reject-doc-{{ $doc->id }}')" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Reject</button>
                                    </div>

                                    <x-modal name="reject-doc-{{ $doc->id }}" :show="false" focusable>
                                        <form method="POST" action="{{ route('admin.documents.reject', $doc) }}" class="p-6">
                                            @csrf
                                            <h2 class="text-lg font-medium text-slate-900">Reject Document</h2>
                                            <div class="mt-4">
                                                <textarea name="remarks" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Reason for rejection"></textarea>
                                            </div>
                                            <div class="mt-6 flex justify-end gap-3">
                                                <button type="button" x-on:click="$dispatch('close')" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">Cancel</button>
                                                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Reject</button>
                                            </div>
                                        </form>
                                    </x-modal>
                                    @else
                                    <span class="text-xs text-slate-400">No review access</span>
                                    @endcan
                                @else
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.documents.view', $doc) }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">View File</a>
                                    <span class="text-xs text-slate-500">{{ $doc->reviewer->name ?? 'Reviewed' }}</span>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">No documents found for the selected filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($documents->hasPages())
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/30">
                {{ $documents->links() }}
            </div>
            @endif
        </div>

        <div class="rounded-2xl border border-rose-100 bg-white shadow-sm">
            <div class="border-b border-rose-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-800">Missing Submissions</h2>
                <p class="text-sm text-slate-500">Students with incomplete required documents for their current status.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($missingStudents as $student)
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $student->full_name }}</p>
                        <p class="text-sm text-slate-500">{{ $student->department->name ?? 'Unassigned' }} • {{ $student->student_id }}</p>
                        @if(!empty($missingDocumentMap[$student->id] ?? []))
                        <p class="mt-1 text-xs text-rose-700">Missing: {{ implode(', ', $missingDocumentMap[$student->id]) }}</p>
                        @endif
                    </div>
                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-rose-700">Missing</span>
                </div>
                @empty
                <div class="px-6 py-12 text-center text-slate-400">No missing submissions detected.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
