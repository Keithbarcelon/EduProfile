<x-layouts.admin :pageTitle="'Admin Dashboard'" :role="'Admin'">
    <x-slot name="breadcrumb">
        <span>Home</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Dashboard</span>
    </x-slot>

    {{-- ===== ANALYTICS CARDS ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

        {{-- Total Students --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900 shrink-0">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Students</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($totalStudents) }}</p>
            </div>
        </div>

        {{-- Active Students --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900 shrink-0">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Active Students</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($activeStudents) }}</p>
            </div>
        </div>

        {{-- New This Month --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900 shrink-0">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">New This Month</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($newThisMonth) }}</p>
            </div>
        </div>

        {{-- Courses Offered --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 flex items-center gap-4 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900 shrink-0">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Courses</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $courseBreakdown->count() }}</p>
            </div>
        </div>
    </div>

    {{-- ===== MAIN CONTENT GRID ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ===== STUDENT TABLE (2/3) ===== --}}
        <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col">

            {{-- Table header --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white">Student List</h2>
                <a href="{{ route('admin.students.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Student
                </a>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.students.index') }}"
                  class="flex flex-wrap gap-3 px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search name, ID, email…"
                       class="flex-1 min-w-[180px] text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2">
                <select name="course"
                        class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2">
                    <option value="">All Courses</option>
                    @foreach($courseBreakdown as $cb)
                        <option value="{{ $cb->course }}" @selected(request('course') === $cb->course)>{{ $cb->course }}</option>
                    @endforeach
                </select>
                <select name="status"
                        class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2">
                    <option value="">All Status</option>
                    <option value="active"    @selected(request('status') === 'active')>Active</option>
                    <option value="inactive"  @selected(request('status') === 'inactive')>Inactive</option>
                    <option value="graduated" @selected(request('status') === 'graduated')>Graduated</option>
                    <option value="dropped"   @selected(request('status') === 'dropped')>Dropped</option>
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search','course','status']))
                <a href="{{ route('admin.students.index') }}"
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg transition-colors">
                    Clear
                </a>
                @endif
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Student</th>
                            <th class="px-6 py-3 text-left">ID</th>
                            <th class="px-6 py-3 text-left">Course</th>
                            <th class="px-6 py-3 text-left">Year</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($students as $student)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-semibold text-xs shrink-0">
                                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white">{{ $student->full_name }}</p>
                                        <p class="text-xs text-gray-400">{{ $student->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 font-mono text-gray-600 dark:text-gray-300">{{ $student->student_id }}</td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-200">{{ $student->course }}</td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-200">{{ $student->year_level }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $badge = match($student->status) {
                                        'active'    => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                        'inactive'  => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                        'graduated' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                        'dropped'   => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
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
                                       class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
                                       title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student) }}"
                                       class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-colors"
                                       title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
                                          onsubmit="return confirm('Remove {{ addslashes($student->full_name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                                title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
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
                                    <p class="text-gray-500">No students found.</p>
                                    <a href="{{ route('admin.students.create') }}"
                                       class="text-sm text-indigo-600 hover:underline">Add the first student</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $students->links() }}
            </div>
            @endif
        </div>

        {{-- ===== SIDEBAR ANALYTICS (1/3) ===== --}}
        <div class="flex flex-col gap-6">

            {{-- Course Breakdown --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Students per Course</h3>
                @forelse($courseBreakdown as $cb)
                @php $pct = $totalStudents > 0 ? round(($cb->total / $totalStudents) * 100) : 0; @endphp
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-300 mb-1">
                        <span class="font-medium">{{ $cb->course }}</span>
                        <span>{{ $cb->total }} ({{ $pct }}%)</span>
                    </div>
                    <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-2 bg-indigo-500 rounded-full transition-all duration-500"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">No data yet.</p>
                @endforelse
            </div>

            {{-- Status Breakdown --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Enrollment Status</h3>
                @php
                    $statuses = [
                        ['label' => 'Active',    'key' => 'active',    'color' => 'bg-green-500'],
                        ['label' => 'Inactive',  'key' => 'inactive',  'color' => 'bg-gray-400'],
                        ['label' => 'Graduated', 'key' => 'graduated', 'color' => 'bg-blue-500'],
                        ['label' => 'Dropped',   'key' => 'dropped',   'color' => 'bg-red-500'],
                    ];
                @endphp
                @foreach($statuses as $s)
                @php $count = $statusCounts[$s['key']] ?? 0; $pct = $totalStudents > 0 ? round(($count / $totalStudents) * 100) : 0; @endphp
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-2.5 h-2.5 rounded-full {{ $s['color'] }} shrink-0"></span>
                    <span class="flex-1 text-xs text-gray-600 dark:text-gray-300">{{ $s['label'] }}</span>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $count }}</span>
                    <span class="text-xs text-gray-400">{{ $pct }}%</span>
                </div>
                @endforeach
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Quick Actions</h3>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('admin.students.create') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Add New Student
                    </a>
                    <a href="{{ route('admin.students.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/40 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        View All Students
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
