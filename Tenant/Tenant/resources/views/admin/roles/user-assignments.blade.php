@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Staff';
@endphp
<x-layouts.admin :pageTitle="'Role Assignments'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('admin.roles.index') }}" class="hover:text-indigo-600">Roles</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">User Assignments</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="admin-soft-ring rounded-3xl bg-gradient-to-r from-indigo-600 via-cyan-600 to-emerald-600 px-6 py-6 text-white sm:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">RBAC</p>
            <h2 class="admin-display mt-2 text-2xl font-bold">Assign Roles to Users</h2>
            <p class="mt-2 max-w-2xl text-sm text-cyan-100">Map one or more roles to each tenant user account.</p>
        </section>

        <div class="admin-panel rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-800">User Role Matrix</h3>
                <a href="{{ route('admin.roles.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                    Back to Roles
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-3 text-left">User</th>
                            <th class="px-6 py-3 text-left">Current Roles</th>
                            <th class="px-6 py-3 text-left">Direct Permissions</th>
                            <th class="px-6 py-3 text-left">Update Access</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $userModel)
                            <tr class="align-top hover:bg-slate-50">
                                <td class="px-6 py-3">
                                    <p class="font-semibold text-slate-800">{{ $userModel->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $userModel->email }}</p>
                                    <p class="mt-1 text-xs text-slate-400">Legacy role: {{ $userModel->role }}</p>
                                </td>

                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($userModel->roles as $assignedRole)
                                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">{{ $assignedRole->name }}</span>
                                        @empty
                                            <span class="text-xs text-slate-500">No assigned RBAC roles.</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="px-6 py-3">
                                    @if($supportsDirectPermissions)
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($userModel->permissions as $assignedPermission)
                                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">{{ $assignedPermission->name }}</span>
                                            @empty
                                                <span class="text-xs text-slate-500">No direct permissions.</span>
                                            @endforelse
                                        </div>
                                    @else
                                        <span class="text-xs text-amber-600">Direct permissions are unavailable until tenant migrations are up to date.</span>
                                    @endif
                                </td>

                                <td class="px-6 py-3">
                                    <form method="POST" action="{{ route('admin.role-assignments.update', $userModel) }}" class="space-y-3">
                                        @csrf
                                        @method('PATCH')

                                        <div>
                                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Roles</p>
                                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                                @foreach($roles as $role)
                                                    @php
                                                        $isChecked = $userModel->roles->contains('id', $role->id);
                                                    @endphp
                                                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700">
                                                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked($isChecked) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                        <span>{{ $role->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        @if($supportsDirectPermissions)
                                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                                <div class="sm:col-span-2">
                                                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Direct Permissions</p>
                                                </div>
                                                @foreach($permissions as $permission)
                                                    @php
                                                        $isPermissionChecked = $userModel->permissions->contains('id', $permission->id);
                                                    @endphp
                                                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700">
                                                        <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked($isPermissionChecked) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                        <span>{{ $permission->name }}</span>
                                                        @if($permission->module)
                                                            <span class="ml-auto rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">{{ $permission->module }}</span>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div>
                                            <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                                Save Access
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-500">No users found for this tenant.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
