@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
@endphp
<x-layouts.admin :pageTitle="$student->full_name" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.students.index') }}" class="hover:text-indigo-600 transition-colors">Students</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">{{ $student->full_name }}</span>
    </x-slot>

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <section class="admin-soft-ring rounded-3xl bg-gradient-to-r from-sky-600 via-cyan-600 to-emerald-600 px-6 py-6 text-white sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">Student Registry</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Student Information</h2>
            <p class="mt-2 max-w-2xl text-sm text-cyan-100">Complete student profile summary linked to the account record and academic details.</p>
        </section>

        <div class="admin-panel rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 flex flex-wrap items-start gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-xl font-bold text-indigo-700 shrink-0">
                    {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                </div>

                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $student->full_name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $student->student_id }} • {{ $student->course }}</p>

                    @php
                        $badge = match($student->status) {
                            'active' => 'bg-emerald-100 text-emerald-700',
                            'inactive' => 'bg-slate-100 text-slate-700',
                            'graduated' => 'bg-sky-100 text-sky-700',
                            'dropped' => 'bg-rose-100 text-rose-700',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                        {{ ucfirst($student->status) }}
                    </span>
                </div>

                <div class="w-full sm:w-auto sm:ml-auto flex items-center gap-2 justify-start sm:justify-end">
                    <a href="{{ route('admin.students.edit', $student) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Profile
                    </a>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 gap-x-10 gap-y-6 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Linked Account</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->user?->name ?? 'Not linked' }}</p>
                    <p class="text-xs text-slate-500">{{ $student->user?->email ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Department</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->department?->name ?? 'Unassigned' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->email }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Phone</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->phone ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Course</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->course }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">School</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->school?->name ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Year and Section</p>
                    <p class="mt-1 text-sm text-slate-800">Year {{ $student->year_level }}{{ $student->section ? ' • '.$student->section : '' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Gender</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->gender ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Birthdate</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->birthdate?->format('F j, Y') ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Date Enrolled</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->enrolled_at?->format('F j, Y') ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Record Created</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->created_at->format('F j, Y') }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Guardian</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->guardian_name ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Guardian Contact</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->guardian_contact ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Emergency Contact</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->emergency_contact_name ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Emergency Number</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->emergency_contact_number ?: '—' }}</p>
                </div>

                @if($student->address)
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Address</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $student->address }}</p>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                <a href="{{ route('admin.students.index') }}"
                   class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 transition-colors">
                    Back to Students
                </a>

                <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
                      onsubmit="return confirm('Delete {{ addslashes($student->full_name) }}? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-xl bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100 transition-colors">
                        Delete Student
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
