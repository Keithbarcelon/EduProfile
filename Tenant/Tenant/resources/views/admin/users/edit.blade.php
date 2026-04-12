@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Tenant Admin';
@endphp
<x-layouts.admin :pageTitle="'Edit User'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.users.index') }}" class="text-slate-500 hover:text-slate-700">Users</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Edit</span>
    </x-slot>

    <div class="mx-auto w-full max-w-3xl rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.users.update', $userModel) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('admin.users.partials.form', ['userModel' => $userModel])
        </form>
    </div>
</x-layouts.admin>
