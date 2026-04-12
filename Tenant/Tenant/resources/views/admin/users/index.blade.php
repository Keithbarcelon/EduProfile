@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Tenant Admin';
@endphp
<x-layouts.admin :pageTitle="'User Management'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">Users</span>
    </x-slot>

    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="rounded-3xl bg-gradient-to-r from-violet-600 to-indigo-700 px-6 py-6 text-white shadow-xl shadow-indigo-900/20">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-violet-100">Tenant Admin</p>
            <h2 class="mt-2 text-2xl font-bold">User Management</h2>
            <p class="mt-2 max-w-2xl text-sm text-violet-100">Manage tenant users with school-scoped search, role filtering, and protected actions.</p>
        </section>

        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-1 flex-wrap items-center gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email" class="min-w-[220px] flex-1 rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <select name="role" class="rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All roles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ \App\Enums\UserRole::labels()[$role] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Apply</button>
                    @if(request()->filled('search') || request()->filled('role'))
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Clear</a>
                    @endif
                </form>
                <a href="{{ route('admin.users.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Create User</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Name</th>
                            <th class="px-6 py-4 text-left font-semibold">Email</th>
                            <th class="px-6 py-4 text-left font-semibold">Role</th>
                            <th class="px-6 py-4 text-left font-semibold">Department</th>
                               <th class="px-6 py-4 text-left font-semibold">Student Profile</th>
                            <th class="px-6 py-4 text-left font-semibold">Created Date</th>
                            <th class="px-6 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $user)
                        <tr class="hover:bg-slate-50" x-data="{ confirmDelete: false }">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ \App\Enums\UserRole::labels()[$user->role] ?? ucfirst($user->role) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $user->department->name ?? 'Unassigned' }}</td>
                               <td class="px-6 py-4 text-slate-600">
                                   @if($user->role === \App\Enums\UserRole::STUDENT->value)
                                       @if($user->student)
                                           <a href="{{ route('admin.students.show', $user->student) }}" class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-200">
                                               Linked
                                           </a>
                                       @else
                                           <form method="POST" action="{{ route('admin.students.link-user', $user) }}">
                                               @csrf
                                               <button type="submit" class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                                   Link Profile
                                               </button>
                                           </form>
                                       @endif
                                   @else
                                       <span class="text-xs text-slate-400">N/A</span>
                                   @endif
                               </td>
                            <td class="px-6 py-4 text-slate-600">{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">Edit</a>
                                    <button type="button" @click="confirmDelete = true" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Delete</button>
                                </div>

                                <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4" @click.self="confirmDelete = false">
                                    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                                        <h3 class="text-lg font-semibold text-slate-900">Delete User</h3>
                                        <p class="mt-2 text-sm text-slate-600">Delete <span class="font-semibold">{{ $user->name }}</span>? This action cannot be undone.</p>
                                        <div class="mt-6 flex justify-end gap-3">
                                            <button type="button" @click="confirmDelete = false" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Cancel</button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Confirm Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                               <td colspan="7" class="px-6 py-12 text-center text-slate-400">No users found for the current tenant.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/30">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
