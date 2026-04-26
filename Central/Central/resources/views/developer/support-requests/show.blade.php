<x-layouts.admin :pageTitle="$request->subject" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.support-requests.index') }}" class="text-cyan-400 hover:text-cyan-300">Support Requests</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">{{ $request->subject }}</span>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

    <!-- Request Details -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Message</h2>
                <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">
                    {{ $request->message }}
                </div>
                <div class="mt-6 pt-4 border-t border-gray-200 text-sm text-gray-500">
                    <p>Submitted: {{ $request->created_at->format('M d, Y \a\t H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700 mb-4">Status</h3>
                
                <div class="mb-4">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold
                        @if($request->status === 'open') bg-amber-100 text-amber-800
                        @elseif($request->status === 'in_progress') bg-blue-100 text-blue-800
                        @elseif($request->status === 'resolved') bg-emerald-100 text-emerald-800
                        @else bg-gray-100 text-gray-800
                        @endif
                    ">
                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('developer.support-requests.update-status', $request) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">Update Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="open" {{ $request->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $request->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $request->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Tenant Info Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700 mb-4">Tenant Information</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">Name</p>
                        <p class="font-semibold text-gray-900">{{ $request->tenant->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Domain</p>
                        <p class="font-semibold text-gray-900">{{ $request->tenant->tenant_domain }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="font-semibold text-gray-900">{{ $request->tenant->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Database</p>
                        <p class="font-mono text-xs text-gray-900">{{ $request->tenant->tenant_database ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Delete Action -->
            <form method="POST" action="{{ route('developer.support-requests.destroy', $request) }}" onsubmit="return confirm('Are you sure you want to delete this support request?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                    Delete Request
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
