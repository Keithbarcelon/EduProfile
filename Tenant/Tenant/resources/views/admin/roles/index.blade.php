@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
@endphp
<x-layouts.admin :pageTitle="'Roles and Permissions'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <span>Admin</span>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Roles</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="admin-soft-ring rounded-3xl bg-gradient-to-r from-indigo-600 via-cyan-600 to-emerald-600 px-6 py-6 text-white sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">RBAC</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Role Management</h2>
            <p class="mt-2 max-w-2xl text-sm text-cyan-100">Create dynamic roles, assign permission bundles, and manage user access at tenant level.</p>
        </section>

        <div class="admin-panel rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Roles</h3>
                    <p class="mt-1 text-xs text-slate-500">Permission-based roles available for this tenant.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.role-assignments.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                        User Role Assignments
                    </a>
                    <a href="{{ route('admin.roles.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Create Role
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Role</th>
                            <th class="px-6 py-3 text-left">Permissions</th>
                            <th class="px-6 py-3 text-left">Users</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($roles as $role)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 align-top">
                                    <p class="font-semibold text-slate-800">{{ $role->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $role->slug }}</p>
                                    @if($role->description)
                                    <p class="mt-1 text-xs text-slate-500">{{ $role->description }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-3 align-top">
                                    <p class="text-sm font-semibold text-slate-700">{{ $role->permissions_count }}</p>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($role->permissions->take(3) as $permission)
                                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">{{ $permission->slug }}</span>
                                        @endforeach
                                        @if($role->permissions_count > 3)
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">+{{ $role->permissions_count - 3 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-3 align-top text-slate-700">{{ $role->users_count }}</td>
                                <td class="px-6 py-3 align-top">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $role->is_system ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $role->is_system ? 'System' : 'Custom' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right align-top">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200">
                                            Edit
                                        </a>
                                        @if(! $role->is_system)
                                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete role {{ addslashes($role->name) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-500">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($roles->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
