<x-layouts.admin :pageTitle="'Review Tenant Request'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.tenant-requests.index') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Tenant Requests</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Review #{{ $tenantRequest->id }}</span>
    </x-slot>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $tenantRequest->tenant_name }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Requested on {{ $tenantRequest->created_at->format('M d, Y h:i A') }}</p>
                </div>
                @if($tenantRequest->status === 'pending')
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending Review</span>
                @elseif($tenantRequest->status === 'approved')
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Approved</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Rejected</span>
                @endif
            </div>

            <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Address</dt>
                    <dd class="mt-1 text-sm text-slate-800 dark:text-slate-200">{{ $tenantRequest->address }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Plan</dt>
                    <dd class="mt-1 text-sm text-slate-800 uppercase dark:text-slate-200">{{ $tenantRequest->plan_type }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Admin Name</dt>
                    <dd class="mt-1 text-sm text-slate-800 dark:text-slate-200">{{ $tenantRequest->signup_admin_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Admin Email</dt>
                    <dd class="mt-1 text-sm text-slate-800 dark:text-slate-200">{{ $tenantRequest->admin_email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Plan Reminder Email</dt>
                    <dd class="mt-1 text-sm text-slate-800 dark:text-slate-200">{{ $tenantRequest->plan_expiration_email ?? 'Not set' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Requested Domain</dt>
                    <dd class="mt-1 text-sm text-slate-800 dark:text-slate-200">{{ $tenantRequest->requested_tenant_domain ?? 'Auto-generate on approval' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Plan Window</dt>
                    <dd class="mt-1 text-sm text-slate-800 dark:text-slate-200">{{ optional($tenantRequest->plan_started_at)->format('M d, Y') ?? 'N/A' }} - {{ optional($tenantRequest->plan_due_at)->format('M d, Y') ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submitted IP</dt>
                    <dd class="mt-1 text-sm text-slate-800 dark:text-slate-200">{{ $tenantRequest->submitted_ip ?? 'N/A' }}</dd>
                </div>
            </dl>

            @if($tenantRequest->status !== 'pending')
                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Review Summary</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Reviewed by {{ $tenantRequest->reviewer?->name ?? 'System' }}
                        on {{ optional($tenantRequest->reviewed_at)->format('M d, Y h:i A') ?? 'N/A' }}.
                    </p>
                    @if($tenantRequest->status === 'rejected')
                        <p class="mt-2 text-sm text-rose-700 dark:text-rose-300">Reason: {{ $tenantRequest->rejection_reason ?: 'No reason provided.' }}</p>
                    @endif
                    @if($tenantRequest->status === 'approved' && $tenantRequest->approvedSchool)
                        <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">
                            Provisioned tenant: {{ $tenantRequest->approvedSchool->name }} ({{ $tenantRequest->approvedSchool->tenant_domain }})
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <a href="{{ route('developer.tenant-requests.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                Back to Queue
            </a>

            @if($tenantRequest->status === 'pending')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/40 dark:bg-emerald-900/20">
                    <h3 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Approve Request</h3>
                    <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-400">Approval will create the school record, provision database, and seed tenant admin account.</p>

                    <form method="POST" action="{{ route('developer.tenant-requests.approve', $tenantRequest) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="tenant_domain" class="mb-1 block text-xs font-semibold text-emerald-800 dark:text-emerald-300">Tenant Domain (Optional override)</label>
                            <input id="tenant_domain" type="text" name="tenant_domain" value="{{ old('tenant_domain', $tenantRequest->requested_tenant_domain) }}" placeholder="myschool.localhost" class="w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-800 dark:bg-slate-900 dark:text-slate-100">
                            @error('tenant_domain')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                            Approve and Provision Tenant
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/40 dark:bg-rose-900/20">
                    <h3 class="text-sm font-bold text-rose-800 dark:text-rose-300">Reject Request</h3>

                    <form method="POST" action="{{ route('developer.tenant-requests.reject', $tenantRequest) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="rejection_reason" class="mb-1 block text-xs font-semibold text-rose-800 dark:text-rose-300">Reason</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="4" required class="w-full rounded-lg border border-rose-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-rose-500 focus:ring-rose-500 dark:border-rose-800 dark:bg-slate-900 dark:text-slate-100">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">
                            Reject Request
                        </button>
                    </form>
                </div>
            @endif

            @if($tenantRequest->status === 'approved' && $tenantRequest->approvedSchool)
                <a href="{{ route('developer.tenants.show', $tenantRequest->approvedSchool) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700">
                    View Provisioned Tenant
                </a>
            @endif
        </div>
    </div>
</x-layouts.admin>
