<x-layouts.admin :pageTitle="'Edit Tenant'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.tenants.index') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Tenants</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Edit</span>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Edit {{ $tenant->name }}</h2>
        <form method="POST" action="{{ route('developer.tenants.update', $tenant) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('developer.tenants._form', ['tenant' => $tenant])

            <div class="flex items-center gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">Save Changes</button>
                <a href="{{ route('developer.tenants.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
