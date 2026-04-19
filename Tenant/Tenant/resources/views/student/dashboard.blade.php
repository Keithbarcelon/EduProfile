<x-layouts.admin :pageTitle="'Student Dashboard'" :role="'Student'">
    <x-slot name="breadcrumb">
        <span>Student Portal</span>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-slate-600">Dashboard</span>
    </x-slot>

    @php
        $statusBadgeClass = $statusMeta['badge'];
        $statusPanelClass = $statusMeta['panel'];

        $documentBadgeClasses = [
            'pending' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'approved' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-700 ring-rose-200',
        ];
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-6">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Please review the highlighted fields and try again.
            </div>
        @endif

        <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white px-6 py-8 shadow-sm sm:px-8">
            <div class="tenant-primary-soft-bg absolute inset-y-0 right-0 hidden w-1/3 bg-gradient-to-l to-transparent lg:block"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <p class="tenant-primary-text text-xs font-semibold uppercase tracking-[0.24em]">Student Overview</p>
                    <h2 class="admin-display mt-3 text-3xl font-bold text-slate-900">{{ $student->full_name }}</h2>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                        <span>{{ $student->course }}</span>
                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                        <span>Year {{ $student->year_level }}</span>
                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                        <span>{{ $student->department->name ?? 'No department assigned' }}</span>
                    </div>
                    <div class="mt-5">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] ring-1 {{ $statusBadgeClass }}">
                            {{ $statusMeta['label'] }}
                        </span>
                    </div>
                    <div class="mt-5 rounded-2xl border px-4 py-4 text-sm {{ $statusPanelClass }}">
                        {{ $statusMeta['message'] }}
                    </div>
                </div>

                <div class="grid w-full gap-4 sm:grid-cols-3 lg:w-[22rem] lg:grid-cols-1">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Documents</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $documentCounts['total'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">Uploaded submissions</p>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Pending Review</p>
                        <p class="mt-2 text-3xl font-bold text-amber-900">{{ $documentCounts['pending'] }}</p>
                        <p class="mt-1 text-sm text-amber-800">Waiting for faculty action</p>
                    </div>
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700">Pending Requirements</p>
                        <p class="mt-2 text-3xl font-bold text-rose-900">{{ $pendingRequiredDocuments->count() }}</p>
                        <p class="mt-1 text-sm text-rose-800">Documents still missing</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Profile</h3>
                    <p class="mt-1 text-sm text-slate-500">Your current academic and account information.</p>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Name</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $student->full_name }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Email</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $student->email ?: auth()->user()->email }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Course</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $student->course }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Year Level</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">Year {{ $student->year_level }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-4 sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Department</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $student->department->name ?? 'No department assigned' }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Notifications</h3>
                <p class="mt-1 text-sm text-slate-500">Important reminders based on your current status.</p>

                <div class="mt-6 space-y-3">
                    @forelse ($notifications as $notification)
                        <div class="tenant-soft-panel tenant-soft-text rounded-2xl border px-4 py-3 text-sm">
                            {{ $notification }}
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                            No pending reminders at this time.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Required Documents</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-700">
                        @foreach ($requiredDocumentNames as $requiredDocumentName)
                            <li class="flex items-center justify-between gap-3">
                                <span>{{ $requiredDocumentName }}</span>
                                @if ($pendingRequiredDocuments->contains($requiredDocumentName))
                                    <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700">Missing</span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Submitted</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Status Details</h3>
                        <p class="mt-1 text-sm text-slate-500">Current academic status, history, and faculty remarks.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] ring-1 {{ $statusBadgeClass }}">
                        {{ $statusMeta['label'] }}
                    </span>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div>
                        <h4 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Status History</h4>
                        <div class="mt-4 space-y-4">
                            @forelse ($statusUpdates as $statusUpdate)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ ucfirst($statusUpdate->old_status ?: 'Initial') }} to {{ ucfirst($statusUpdate->new_status) }}
                                        </p>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase ring-1 {{ $documentBadgeClasses[$statusUpdate->approval_status] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                                            {{ $statusUpdate->approval_status }}
                                        </span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500">
                                        {{ $statusUpdate->created_at->format('M d, Y h:i A') }}
                                    </p>
                                    @if ($statusUpdate->remarks)
                                        <p class="mt-3 text-sm text-slate-700">{{ $statusUpdate->remarks }}</p>
                                    @endif

                                    @if(!empty($statusUpdate->approval_audit))
                                        <div class="mt-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Approval Timeline</p>
                                            <div class="mt-2 space-y-1">
                                                @foreach((array) $statusUpdate->approval_audit as $audit)
                                                    <p class="text-[11px] text-slate-600">
                                                        <span class="font-semibold">{{ str((string) ($audit['action'] ?? 'event'))->replace('_', ' ')->title() }}</span>
                                                        · {{ \Illuminate\Support\Carbon::parse((string) ($audit['at'] ?? now()))->format('M d, Y h:i A') }}
                                                    </p>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                    No status history available yet.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Remarks and Intervention Notes</h4>
                        <div class="mt-4 space-y-4">
                            @forelse ($remarks as $remark)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-slate-900">{{ $remark->user->name ?? 'Faculty' }}</p>
                                        <p class="text-xs text-slate-500">{{ $remark->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <p class="mt-3 text-sm leading-6 text-slate-700">{{ $remark->content }}</p>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                    No faculty remarks or intervention notes yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Upload Documents</h3>
                <p class="mt-1 text-sm text-slate-500">Submit one or multiple files for review (for example, front and back images).</p>

                <form method="POST" action="{{ route('student.documents.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Document Name</label>
                        @if($requiredDocumentNames->isNotEmpty())
                            <select
                                id="name"
                                name="name"
                                required
                                class="tenant-focus-ring w-full rounded-2xl border-slate-300 text-sm shadow-sm"
                            >
                                <option value="">Select required document</option>
                                @foreach ($requiredDocumentNames as $requiredDocumentName)
                                    <option value="{{ $requiredDocumentName }}" @selected(old('name') === $requiredDocumentName)>
                                        {{ $requiredDocumentName }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                placeholder="e.g. Birth Certificate"
                                required
                                class="tenant-focus-ring w-full rounded-2xl border-slate-300 text-sm shadow-sm"
                            >
                        @endif
                        @error('name')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="file" class="mb-2 block text-sm font-medium text-slate-700">File(s)</label>
                        <input
                            id="file"
                            type="file"
                            name="file[]"
                            multiple
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            required
                            class="tenant-file-input block w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700"
                        >
                        <p class="mt-2 text-xs text-slate-500">Allowed files: PDF, DOC, DOCX, JPG, JPEG, PNG. Maximum size: 10MB each.</p>
                        @error('file')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                        @error('file.*')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="tenant-primary-btn inline-flex w-full items-center justify-center rounded-2xl px-4 py-3 text-sm font-semibold transition"
                    >
                        Upload Document
                    </button>
                </form>
            </section>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Document Status Tracker</h3>
                    <p class="mt-1 text-sm text-slate-500">Track your uploaded files, approval status, and review remarks.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.18em]">
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-700">Pending: {{ $documentCounts['pending'] }}</span>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">Approved: {{ $documentCounts['approved'] }}</span>
                    <span class="rounded-full bg-rose-100 px-3 py-1 text-rose-700">Rejected: {{ $documentCounts['rejected'] }}</span>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <th class="px-4 py-3">Document Name</th>
                            <th class="px-4 py-3">Upload Date</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($documents as $document)
                            <tr>
                                <td class="px-4 py-4 font-medium text-slate-900">{{ $documentDisplayNames[$document->id] ?? $document->name }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $document->created_at->format('M d, Y h:i A') }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase ring-1 {{ $documentBadgeClasses[$document->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                                        {{ $document->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-slate-600">
                                    {{ $document->review_remarks ?: ($document->status === 'rejected' ? 'Rejected without remarks.' : 'No remarks yet.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">
                                    No documents uploaded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.admin>
