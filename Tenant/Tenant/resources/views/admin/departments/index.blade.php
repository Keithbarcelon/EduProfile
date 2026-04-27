<x-layouts.admin :pageTitle="'Department Management'" :role="$roleLabel">
    <x-slot:breadcrumbs>
        <a href="{{ route('admin.dashboard') }}" class="text-slate-500 hover:text-slate-700">Dashboard</a>
        <x-heroicon-o-chevron-right class="h-4 w-4 text-slate-400" />
        <span class="text-slate-600">Departments</span>
    </x-slot:breadcrumbs>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-indigo-100 p-2.5 text-indigo-600">
                    <x-heroicon-o-office-building class="h-6 w-6" />
                </div>
                <h2 class="text-2xl font-bold text-slate-900">Departments</h2>
            </div>
            <p class="mt-1 text-sm text-slate-500">Manage departments and assign faculty within the current tenant.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                <x-heroicon-o-plus class="h-4 w-4" />
                Add Department
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        @forelse($departments as $department)
            <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ $department->name }}</h3>
                        <p class="text-sm font-medium text-slate-500">{{ $department->code ?: 'No code assigned' }}</p>
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

                <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $department->description ?: 'No description provided.' }}</p>

                <div class="mt-6 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-5 text-xs font-medium text-slate-400">
                    <div class="flex items-center gap-1.5">
                        <x-heroicon-o-users class="h-4 w-4" />
                        <span>{{ $department->students_count }} students</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <x-heroicon-o-academic-cap class="h-4 w-4" />
                        <span>{{ $department->faculty_count }} faculty</span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex -space-x-2 overflow-hidden">
                        @forelse($department->users as $faculty)
                            <div class="inline-block h-8 w-8 rounded-full bg-slate-100 ring-2 ring-white" title="{{ $faculty->name }}">
                                <div class="flex h-full w-full items-center justify-center text-[10px] font-bold text-slate-500 uppercase">
                                    {{ substr($faculty->name, 0, 2) }}
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">No faculty assigned.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400 lg:col-span-2">No departments created yet.</div>
        @endforelse
    </div>
</x-layouts.admin>
