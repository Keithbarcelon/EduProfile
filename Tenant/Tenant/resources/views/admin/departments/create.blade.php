@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Tenant Admin';
@endphp
<x-layouts.admin :pageTitle="'Create Department'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.departments.index') }}" class="text-slate-500 hover:text-slate-700">Departments</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Create</span>
    </x-slot>

    <div class="mx-auto w-full max-w-3xl rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.departments.store') }}" class="space-y-5">
            @csrf
            @include('admin.departments.partials.form', ['department' => null, 'selectedFacultyIds' => []])
        </form>
    </div>
</x-layouts.admin>
