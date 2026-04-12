<x-layouts.admin :pageTitle="'Plans'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.dashboard') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Developer</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Plans</span>
    </x-slot>

    <div class="space-y-5">
        <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-5 py-4 shadow-sm dark:border-cyan-900/40 dark:bg-cyan-900/20">
            <h2 class="text-base font-semibold text-cyan-800 dark:text-cyan-200">Modular Plan Management</h2>
            <p class="mt-1 text-sm text-cyan-700/90 dark:text-cyan-300">Create dynamic and sale-ready plans with configurable features and tenant assignments.</p>
        </div>

        <div class="rounded-2xl bg-slate-900/70 shadow-xl border border-slate-700/70 backdrop-blur-sm overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-700/80 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-100">Plan Catalog</h3>
                <a href="{{ route('developer.plans.create') }}" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">Create Plan</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50 text-xs uppercase tracking-wider text-slate-300">
                        <tr>
                            <th class="px-6 py-3 text-left">Plan</th>
                            <th class="px-6 py-3 text-left">Billing</th>
                            <th class="px-6 py-3 text-left">Features</th>
                            <th class="px-6 py-3 text-left">Tenants</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/70">
                        @forelse($plans as $plan)
                        <tr class="hover:bg-slate-800/50">
                            <td class="px-6 py-3">
                                <p class="font-semibold text-slate-100">{{ $plan->name }}</p>
                                <p class="text-xs text-slate-400">{{ $plan->slug }}</p>
                                @if($plan->description)
                                <p class="mt-1 text-xs text-slate-400">{{ $plan->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-slate-200">{{ ucfirst($plan->billing_cycle) }}</p>
                                <p class="text-xs text-slate-400">Price: {{ number_format((float) $plan->price, 2) }}</p>
                                @if($plan->is_sale)
                                    <p class="text-xs text-amber-300">Sale: {{ number_format((float) $plan->sale_price, 2) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-200">{{ $plan->features_count }}</td>
                            <td class="px-6 py-3 text-slate-200">{{ $plan->tenant_plans_count }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('developer.plans.edit', $plan) }}" class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200">Edit</a>
                                    <form method="POST" action="{{ route('developer.plans.assign', $plan) }}" class="inline-flex items-center gap-2">
                                        @csrf
                                        <select name="school_id" class="rounded-lg border-slate-300 bg-white px-2 py-1 text-xs text-slate-800">
                                            @foreach($schools as $school)
                                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-cyan-700">Assign</button>
                                    </form>
                                    <form method="POST" action="{{ route('developer.plans.destroy', $plan) }}" onsubmit="return confirm('Delete plan {{ addslashes($plan->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-200">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">No plans found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($plans->hasPages())
            <div class="border-t border-slate-700/80 px-6 py-4">
                {{ $plans->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
