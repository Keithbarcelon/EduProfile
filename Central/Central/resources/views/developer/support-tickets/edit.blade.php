<x-layouts.admin :pageTitle="'Edit Support Ticket'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.support-tickets.index') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Support Tickets</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Edit</span>
    </x-slot>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Edit Support Ticket</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update status and ticket details.</p>

        <form method="POST" action="{{ route('developer.support-tickets.update', $ticket) }}" class="mt-6">
            @csrf
            @method('PATCH')
            @include('developer.support-tickets.partials.form', ['ticket' => $ticket, 'submitLabel' => 'Save Changes'])
        </form>
    </div>
</x-layouts.admin>
