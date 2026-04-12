<x-layouts.admin :pageTitle="$student->full_name" :role="'Admin'">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.students.index') }}" class="hover:text-indigo-600 transition-colors">Students</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">{{ $student->full_name }}</span>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

            {{-- Profile header --}}
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-6 py-8 flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-white text-2xl font-bold shrink-0">
                    {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $student->full_name }}</h2>
                    <p class="text-indigo-200 text-sm mt-0.5">{{ $student->student_id }} &bull; {{ $student->course }}</p>
                    @php
                        $badge = match($student->status) {
                            'active'    => 'bg-green-400/20 text-green-200 ring-1 ring-green-400/30',
                            'inactive'  => 'bg-gray-400/20 text-gray-200 ring-1 ring-gray-400/30',
                            'graduated' => 'bg-blue-400/20 text-blue-200 ring-1 ring-blue-400/30',
                            'dropped'   => 'bg-red-400/20 text-red-200 ring-1 ring-red-400/30',
                            default     => 'bg-gray-400/20 text-gray-200',
                        };
                    @endphp
                    <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                        {{ ucfirst($student->status) }}
                    </span>
                </div>
                <div class="ml-auto">
                    <a href="{{ route('admin.students.edit', $student) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                </div>
            </div>

            {{-- Details --}}
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Email</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->email }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Phone</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->phone ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Course</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->course }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">School</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->school?->name ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Year / Section</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">
                        Year {{ $student->year_level }}{{ $student->section ? ' — ' . $student->section : '' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Gender</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->gender ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Birthdate</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">
                        {{ $student->birthdate?->format('F j, Y') ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Date Enrolled</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">
                        {{ $student->enrolled_at?->format('F j, Y') ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Record Created</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">
                        {{ $student->created_at->format('F j, Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Guardian</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->guardian_name ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Guardian Contact</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->guardian_contact ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Emergency Contact</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->emergency_contact_name ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Emergency Number</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->emergency_contact_number ?: '—' }}</p>
                </div>

                @if($student->address)
                <div class="sm:col-span-2">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Address</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $student->address }}</p>
                </div>
                @endif
            </div>

            {{-- Footer actions --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <a href="{{ route('admin.students.index') }}"
                   class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    &larr; Back to Students
                </a>
                <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
                      onsubmit="return confirm('Delete {{ addslashes($student->full_name) }}? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-sm text-red-500 hover:text-red-700">
                        Delete Student
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
