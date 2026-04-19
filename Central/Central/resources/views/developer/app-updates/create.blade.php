<x-layouts.admin :pageTitle="'Create App Update'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.app-updates.index') }}" class="hover:text-gray-600 dark:hover:text-gray-200">App Updates</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Create</span>
    </x-slot>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Create App Update</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Log a release version and notes for central tracking.</p>

        <form method="POST" action="{{ route('developer.app-updates.store') }}" class="mt-6">
            @csrf
            @include('developer.app-updates.partials.form', ['submitLabel' => 'Create Update'])
        </form>
    </div>
</x-layouts.admin>
