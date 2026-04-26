<x-layouts.admin :pageTitle="'Edit Department'" :role="$roleLabel">
    <x-slot:breadcrumbs>
        <a href="{{ route('admin.dashboard') }}" class="text-slate-500 hover:text-slate-700">Dashboard</a>
        <x-heroicon-o-chevron-right class="h-4 w-4 text-slate-400" />
        <a href="{{ route('admin.departments.index') }}" class="text-slate-500 hover:text-slate-700">Departments</a>
        <x-heroicon-o-chevron-right class="h-4 w-4 text-slate-400" />
        <span class="text-slate-600">Edit Department</span>
    </x-slot:breadcrumbs>

    <div class="mx-auto max-w-4xl">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900">Edit Department: {{ $department->name }}</h2>
            <p class="mt-1 text-sm text-slate-500">Update department details and faculty assignments.</p>
        </div>

        <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="space-y-5">
            @csrf
            @method('PATCH')
            @include('admin.departments.partials.form', ['department' => $department, 'selectedFacultyIds' => $selectedFacultyIds])
        </form>
    </div>
</x-layouts.admin>
