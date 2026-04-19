@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
@endphp
<x-layouts.admin :pageTitle="'Students'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Students</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
    <section class="tenant-hero admin-soft-ring rounded-3xl px-6 py-6 text-white sm:px-8">
        <p class="tenant-hero-kicker text-xs font-semibold uppercase tracking-[0.2em]">Student Registry</p>
        <h2 class="admin-display mt-2 text-2xl font-bold">Manage Enrollees</h2>
        <p class="tenant-hero-body mt-2 max-w-2xl text-sm">Search, filter, and maintain student records with quick actions and live enrollment summaries.</p>
    </section>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="min-w-0 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Students</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($overview['total']) }}</p>
        </div>
        <div class="min-w-0 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Regular</p>
            <p class="mt-2 text-2xl font-bold text-sky-600">{{ number_format($overview['regular']) }}</p>
        </div>
        <div class="min-w-0 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Affirmative</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($overview['affirmative']) }}</p>
        </div>
        <div class="min-w-0 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Probation</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($overview['probation']) }}</p>
        </div>
    </div>

    {{-- ===== MAIN CONTENT GRID ===== --}}
    <div class="grid grid-cols-1 gap-6">

        <div class="admin-panel rounded-2xl">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Student User Accounts Pending Profile Link</h2>
                    <p class="mt-1 text-xs text-slate-500">Create student users in User Management, then link them here and finalize profile details in edit.</p>
                </div>
                @if(auth()->user()?->hasPermission('manage_users'))
                    <a href="{{ route('admin.users.create') }}" class="tenant-primary-btn inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-colors">
                        Create Student User
                    </a>
                @endif
            </div>

            @if($unlinkedStudentUsers->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-3 text-left">User Name</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Department</th>
                            <th class="px-6 py-3 text-left">Created</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($unlinkedStudentUsers as $studentUser)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-medium text-slate-800">{{ $studentUser->name }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $studentUser->email }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $studentUser->department?->name ?? 'Unassigned' }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $studentUser->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-right">
                                @can('create', App\Models\Student::class)
                                <form method="POST" action="{{ route('admin.students.link-user', $studentUser) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                        Link to Student Page
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-8 text-sm text-slate-500">
                No unlinked student user accounts found.
            </div>
            @endif
        </div>

        {{-- ===== STUDENT TABLE ===== --}}
        <div class="admin-panel flex flex-col rounded-2xl">

            {{-- Table header --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-800">Student List</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Direct add removed</span>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.students.index') }}"
                class="flex flex-wrap gap-3 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-cyan-50/40 px-6 py-3">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search name, ID, email…"
                     class="tenant-focus-ring min-w-[180px] flex-1 rounded-lg border-slate-300 px-3 py-2 text-sm">
                
                <select name="department_id"
                    class="tenant-focus-ring rounded-lg border-slate-300 px-3 py-2 text-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>

                <select name="status_category"
                    class="tenant-focus-ring rounded-lg border-slate-300 px-3 py-2 text-sm">
                    <option value="">All Categories</option>
                    <option value="regular"     @selected(request('status_category') === 'regular')>Regular</option>
                    <option value="affirmative" @selected(request('status_category') === 'affirmative')>Affirmative</option>
                    <option value="probation"   @selected(request('status_category') === 'probation')>Probation</option>
                </select>

                <button type="submit"
                    class="tenant-primary-btn px-4 py-2 text-sm rounded-lg transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search','department_id','status_category']))
                <a href="{{ route('admin.students.index') }}"
                   class="rounded-lg bg-slate-200 px-4 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-300">
                    Clear
                </a>
                @endif
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Student</th>
                            <th class="px-6 py-3 text-left">ID</th>
                            <th class="px-6 py-3 text-left">Dept / Course</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                        <tr class="transition-colors hover:bg-slate-50">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="tenant-primary-soft-bg tenant-primary-text flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold">
                                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $student->full_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $student->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 font-mono text-slate-600 text-xs">{{ $student->student_id }}</td>
                            <td class="px-6 py-3">
                                <p class="text-slate-700 font-medium">{{ $student->department->name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $student->course }}</p>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $catBadge = match($student->status_category) {
                                        'regular'     => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'affirmative' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'probation'   => 'bg-amber-50 text-amber-700 border-amber-100',
                                        default       => 'bg-slate-50 text-slate-700 border-slate-100',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider {{ $catBadge }}">
                                    {{ $student->status_category }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $badge = match($student->status) {
                                        'active'    => 'bg-green-100 text-green-800',
                                        'inactive'  => 'bg-gray-100 text-gray-700',
                                        'graduated' => 'bg-blue-100 text-blue-800',
                                        'dropped'   => 'bg-red-100 text-red-800',
                                        default     => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.students.show', $student) }}"
                                                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 tenant-link"
                                       title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @can('update', $student)
                                    <a href="{{ route('admin.students.edit', $student) }}"
                                                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-amber-50 hover:text-amber-600"
                                       title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('delete', $student)
                                    <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
                                          onsubmit="return confirm('Remove {{ addslashes($student->full_name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                                title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-10 h-10 text-gray-300" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <p class="text-slate-500">No students found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $students->links() }}
            </div>
            @endif
        </div>
    </div>
    </div>
</x-layouts.admin>
