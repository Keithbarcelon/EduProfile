@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
@endphp
<x-layouts.admin :pageTitle="'Create Role'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.roles.index') }}" class="hover:text-indigo-600">Roles</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Create</span>
    </x-slot>

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <section class="admin-soft-ring rounded-3xl bg-gradient-to-r from-indigo-600 via-cyan-600 to-emerald-600 px-6 py-6 text-white sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">RBAC</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Create Role</h2>
            <p class="mt-2 max-w-2xl text-sm text-cyan-100">Build a custom role and pick the exact permissions it needs.</p>
        </section>

        <div class="admin-panel rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                @include('admin.roles.partials.form', [
                    'roleModel' => null,
                    'permissions' => $permissions,
                    'submitLabel' => 'Create Role',
                ])
            </form>
        </div>
    </div>
</x-layouts.admin>
