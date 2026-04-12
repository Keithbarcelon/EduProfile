@php
    $tenant = $tenant ?? null;
    $isEdit = $tenant !== null;
    $isPendingForApproval = $tenant?->approval_status === 'pending';
    $domainValue = old('tenant_domain', $tenant?->isApproved() ? $tenant?->tenant_domain : $tenant?->requested_tenant_domain);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">School Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $tenant?->name) }}" required
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="signup_admin_name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Admin Full Name</label>
        <input id="signup_admin_name" name="signup_admin_name" type="text" value="{{ old('signup_admin_name', $tenant?->signup_admin_name) }}" required
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('signup_admin_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="admin_email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tenant Admin Login Email</label>
        <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email', $tenant?->email) }}" @if(!$isEdit) required @endif
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        <p class="text-xs text-gray-500 mt-1">This is the login email for the tenant admin inside the tenant app.</p>
        @error('admin_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="admin_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Admin Password</label>
        <input id="admin_password" name="admin_password" type="password" @if(!$isEdit) required @endif
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        <p class="text-xs text-gray-500 mt-1">@if($isEdit)Leave blank to keep current tenant admin password.@elseMinimum 8 characters.@endif</p>
        @error('admin_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    @if(!$isEdit)
    <div>
        <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Confirm Password</label>
        <input id="admin_password_confirmation" name="admin_password_confirmation" type="password" required
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
    </div>
    @endif

    <div>
        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Address</label>
        <input id="address" name="address" type="text" value="{{ old('address', $tenant?->address) }}" required
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="school_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tenant Type</label>
        <input id="school_type" name="school_type" type="text" value="{{ old('school_type', $tenant?->school_type ?? 'School') }}"
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('school_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="plan_type" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Plan</label>
        <select id="plan_type" name="plan_type" required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
            <option value="basic" @selected(old('plan_type', $tenant?->plan_type ?? 'basic') === 'basic')>Basic</option>
            <option value="standard" @selected(old('plan_type', $tenant?->plan_type) === 'standard')>Standard</option>
            <option value="premium" @selected(old('plan_type', $tenant?->plan_type) === 'premium')>Premium</option>
        </select>
        @error('plan_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
         <label for="plan_expiration_email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Plan Expiration Email (Optional)</label>
         <input id="plan_expiration_email" name="plan_expiration_email" type="email" value="{{ old('plan_expiration_email', $tenant?->plan_expiration_email) }}"
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('plan_expiration_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="plan_started_at" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Plan Start Date</label>
        <input id="plan_started_at" name="plan_started_at" type="date" value="{{ old('plan_started_at', optional($tenant?->plan_started_at)->format('Y-m-d')) }}"
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('plan_started_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="plan_due_at" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Plan Due Date</label>
        <input id="plan_due_at" name="plan_due_at" type="date" value="{{ old('plan_due_at', optional($tenant?->plan_due_at)->format('Y-m-d')) }}"
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('plan_due_at') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
         <label for="tenant_domain" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Preferred Tenant Domain (Optional)</label>
         <input id="tenant_domain" name="tenant_domain" type="text"
             value="{{ $domainValue }}"
             placeholder="myschool.localhost"
             class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
         <p class="text-xs text-gray-500 mt-1">Example: mytenant.localhost. Domain is activated only after approval.</p>
         @error('tenant_domain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

    @if($isEdit)
        <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tenant Database</label>
        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300">
            {{ $tenant?->tenant_database ?? 'Auto-generated on create' }}
        </div>
        <p class="text-xs text-gray-500 mt-1">Database name is auto-generated and managed by the system.</p>
    </div>
    @endif

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tenant Contact Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $tenant?->email) }}"
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="contact_number" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tenant Contact Number</label>
        <input id="contact_number" name="contact_number" type="text" value="{{ old('contact_number', $tenant?->contact_number) }}"
               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('contact_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    @if($isEdit && ! $isPendingForApproval)
        <div class="flex items-center gap-3 mt-2">
            <input id="is_enabled" name="is_enabled" type="checkbox" value="1"
                   @checked(old('is_enabled', $tenant?->is_enabled ?? false))
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="is_enabled" class="text-sm font-medium text-gray-700 dark:text-gray-200">Tenant is enabled</label>
        </div>
    @elseif($isEdit && $isPendingForApproval)
        <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
            This tenant is pending approval. Enable access after approval from the tenant list.
        </div>
    @else
        <div class="mt-2 rounded-lg border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs text-cyan-700">
            New tenants are created as pending and stay disabled until approved.
        </div>
    @endif
</div>
