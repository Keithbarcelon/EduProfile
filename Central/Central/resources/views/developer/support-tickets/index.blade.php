<x-layouts.admin :pageTitle="'Support Tickets'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.dashboard') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Developer</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Support Tickets</span>
    </x-slot>

    <div class="space-y-5">
        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-5 py-4 shadow-sm dark:border-cyan-900/40 dark:bg-cyan-900/20">
            <h2 class="text-base font-semibold text-cyan-800 dark:text-cyan-200">Support and Updates</h2>
            <p class="mt-1 text-sm text-cyan-700/90 dark:text-cyan-300">Basic CRUD structure for tenant support concerns and update tracking.</p>
        </div>

        <div class="rounded-2xl bg-slate-900/70 shadow-xl border border-slate-700/70 backdrop-blur-sm overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-700/80 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-100">Tickets</h3>
                <a href="{{ route('developer.support-tickets.create') }}" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Create Ticket</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50 text-xs uppercase tracking-wider text-slate-300">
                        <tr>
                            <th class="px-6 py-3 text-left">Subject</th>
                            <th class="px-6 py-3 text-left">Tenant</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Updated</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/70">
                        @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-800/50">
                            <td class="px-6 py-3">
                                <p class="font-semibold text-slate-100">{{ $ticket->subject }}</p>
                                <p class="mt-1 max-w-xl text-xs text-slate-400">{{ \Illuminate\Support\Str::limit($ticket->message, 120) }}</p>
                            </td>
                            <td class="px-6 py-3 text-slate-200">{{ $ticket->tenant?->name ?? 'General' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">{{ str_replace('_', ' ', $ticket->status) }}</span>
                            </td>
                            <td class="px-6 py-3 text-slate-300">{{ $ticket->updated_at->format('M d, Y h:i A') }}</td>
                            <td class="px-6 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('developer.support-tickets.edit', $ticket) }}" class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200">Edit</a>
                                    <form method="POST" action="{{ route('developer.support-tickets.destroy', $ticket) }}" onsubmit="return confirm('Delete ticket {{ addslashes($ticket->subject) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">No support tickets found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
            <div class="border-t border-slate-700/80 px-6 py-4">
                {{ $tickets->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
