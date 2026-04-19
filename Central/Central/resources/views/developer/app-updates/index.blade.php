<x-layouts.admin :pageTitle="'App Updates'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.dashboard') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Developer</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">App Updates</span>
    </x-slot>

    <div class="space-y-5">
        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-5 py-4 shadow-sm dark:border-cyan-900/40 dark:bg-cyan-900/20">
            <h2 class="text-base font-semibold text-cyan-800 dark:text-cyan-200">Release Updates</h2>
            <p class="mt-1 text-sm text-cyan-700/90 dark:text-cyan-300">Maintain version logs, release notes, and optional document links.</p>
        </div>

        <div class="rounded-2xl bg-slate-900/70 shadow-xl border border-slate-700/70 backdrop-blur-sm overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-700/80 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-100">Updates</h3>
                <a href="{{ route('developer.app-updates.create') }}" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Create Update</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50 text-xs uppercase tracking-wider text-slate-300">
                        <tr>
                            <th class="px-6 py-3 text-left">Version</th>
                            <th class="px-6 py-3 text-left">Title</th>
                            <th class="px-6 py-3 text-left">Release Date</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/70">
                        @forelse($updates as $update)
                        <tr class="hover:bg-slate-800/50">
                            <td class="px-6 py-3 text-slate-100 font-semibold">{{ $update->version }}</td>
                            <td class="px-6 py-3">
                                <p class="font-semibold text-slate-100">{{ $update->title }}</p>
                                @if($update->description)
                                <p class="mt-1 max-w-xl text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($update->description, 120) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-300">{{ $update->release_date?->format('M d, Y') ?? 'N/A' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $update->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $update->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('developer.app-updates.edit', $update) }}" class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200">Edit</a>
                                    <form method="POST" action="{{ route('developer.app-updates.destroy', $update) }}" onsubmit="return confirm('Delete app update {{ addslashes($update->version) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">No app updates found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($updates->hasPages())
            <div class="border-t border-slate-700/80 px-6 py-4">
                {{ $updates->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
