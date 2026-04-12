@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Tenant Admin';
@endphp
<x-layouts.admin :pageTitle="'Department Management'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Departments</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Departments</h2>
                <p class="text-sm text-slate-500">Manage departments and assign faculty within the current tenant.</p>
            </div>
            <a href="{{ route('admin.departments.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add Department</a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @forelse($departments as $department)
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ $department->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $department->code ?: 'No code assigned' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.departments.edit', $department) }}" class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">Edit</a>
                        <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" onsubmit="return confirm('Delete {{ addslashes($department->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button>
                        </form>
                    </div>
                </div>
                <p class="mt-4 text-sm text-slate-600">{{ $department->description ?: 'No description provided.' }}</p>
                <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-500">
                    <span>{{ $department->students_count }} students</span>
                    <span>{{ $department->faculty_count }} faculty</span>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse($department->users as $faculty)
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $faculty->name }}</span>
                    @empty
                    <span class="text-sm text-slate-400">No faculty assigned.</span>
                    @endforelse
                </div>
            </div>
            @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400 lg:col-span-2">No departments created yet.</div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
