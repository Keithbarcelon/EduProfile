<x-layouts.admin :pageTitle="'Edit Plan'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.tenants.plan-management', ['tab' => 'modular']) }}" class="hover:text-gray-600 dark:hover:text-gray-200">Plan Management</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Edit {{ $plan->name }}</span>
    </x-slot>

    <div class="rounded-2xl border border-slate-700/70 bg-slate-900/70 p-6 shadow-xl backdrop-blur-sm">
        <h2 class="text-lg font-semibold text-slate-100">Edit Plan</h2>
        <p class="mt-1 text-sm text-slate-300">Update plan pricing, sale windows, and feature limits.</p>

        <form method="POST" action="{{ route('developer.plans.update', $plan) }}" class="mt-6">
            @csrf
            @method('PATCH')
            @include('developer.plans.partials.form', ['plan' => $plan, 'submitLabel' => 'Save Changes'])
        </form>
    </div>
</x-layouts.admin>
