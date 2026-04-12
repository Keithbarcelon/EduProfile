<x-layouts.admin :pageTitle="'Dashboard'" :role="'Admin'">
    <x-slot name="breadcrumb">
        <span class="text-gray-600 dark:text-gray-300">Overview</span>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-500">Total Students</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalStudents) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-500">Active Students</p>
            <p class="mt-2 text-2xl font-bold text-green-600">{{ number_format($activeStudents) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-500">New This Month</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">{{ number_format($newThisMonth) }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Recent Students</h3>
            <a href="{{ route('admin.students.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Student ID</th>
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Course</th>
                        <th class="px-5 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentStudents as $student)
                    <tr>
                        <td class="px-5 py-3 text-gray-700 dark:text-gray-200">{{ $student->student_id }}</td>
                        <td class="px-5 py-3 text-gray-700 dark:text-gray-200">{{ $student->full_name }}</td>
                        <td class="px-5 py-3 text-gray-700 dark:text-gray-200">{{ $student->course ?? 'N/A' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $student->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($student->status ?? 'unknown') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-gray-500">No students yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
