@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'Tenant Admin';
@endphp
<x-layouts.admin :pageTitle="'School Settings'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <span>Dashboard</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600">School Settings</span>
    </x-slot>

    <div class="mx-auto w-full max-w-4xl space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 to-slate-700 px-6 py-6 text-white shadow-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">Tenant Profile</p>
            <h2 class="mt-2 text-2xl font-bold">{{ $school->name }}</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">Update school identity and basic tenant information. Changes apply only to this tenant.</p>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">School Name</label>
                        <input type="text" name="name" value="{{ old('name', $school->name) }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">School Type</label>
                        <input type="text" name="school_type" value="{{ old('school_type', $school->school_type) }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Address</label>
                    <textarea name="address" rows="3" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('address', $school->address) }}</textarea>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" value="{{ $school->email }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 shadow-sm" readonly disabled>
                        <p class="mt-1 text-xs text-slate-500">Email is managed at tenant provisioning level and cannot be changed here.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Contact Number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number', $school->contact_number) }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-700">Tenant Branding</p>
                    <p class="mt-1 text-xs text-indigo-600">These colors are applied to your tenant UI theme.</p>
                    <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Primary Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="branding_primary_picker" value="{{ old('branding_primary_color', $branding['primary_color']) }}" class="h-10 w-12 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                <input type="text" id="branding_primary_color" name="branding_primary_color" value="{{ old('branding_primary_color', $branding['primary_color']) }}" placeholder="#4f46e5" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Accent Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="branding_accent_picker" value="{{ old('branding_accent_color', $branding['accent_color']) }}" class="h-10 w-12 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                <input type="text" id="branding_accent_color" name="branding_accent_color" value="{{ old('branding_accent_color', $branding['accent_color']) }}" placeholder="#0891b2" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tenant Domain</label>
                        <input type="text" value="{{ $school->tenant_domain }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 shadow-sm" disabled>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">School Logo</label>
                        <input type="file" name="logo" accept="image/*" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                @if($school->logo_path)
                <div>
                    <p class="mb-2 text-sm font-medium text-slate-700">Current Logo</p>
                    <img src="{{ route('tenant.logo', ['v' => optional($school->updated_at)->timestamp]) }}" alt="{{ $school->name }} logo" class="h-20 rounded-2xl border border-slate-200 bg-slate-50 p-2">
                </div>
                @endif

                @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Please review the highlighted fields and try again.
                </div>
                @endif

                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save Settings</button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-cyan-100 bg-cyan-50/60 p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700">Support and Updates</p>
                    <h3 class="mt-1 text-lg font-semibold text-slate-900">Check for Latest Version</h3>
                    <p class="mt-1 text-sm text-slate-600">Run a manual update check from settings and open full support dashboard.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <form method="POST" action="{{ route('support-updates.check') }}">
                        @csrf
                        <button type="submit" class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-700">
                            Check Updates
                        </button>
                    </form>
                    <a href="{{ route('support-updates.index') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        Open Support Center
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            function bindColorPair(inputId, pickerId, fallback) {
                const input = document.getElementById(inputId);
                const picker = document.getElementById(pickerId);

                if (!input || !picker) {
                    return;
                }

                const normalize = (value) => {
                    const raw = String(value || '').trim();
                    return /^#(?:[0-9a-fA-F]{3}){1,2}$/.test(raw) ? raw : fallback;
                };

                picker.value = normalize(input.value);

                picker.addEventListener('input', function () {
                    input.value = picker.value.toLowerCase();
                });

                input.addEventListener('blur', function () {
                    picker.value = normalize(input.value);
                    input.value = picker.value.toLowerCase();
                });
            }

            bindColorPair('branding_primary_color', 'branding_primary_picker', '#4f46e5');
            bindColorPair('branding_accent_color', 'branding_accent_picker', '#0891b2');
        })();
    </script>
</x-layouts.admin>
