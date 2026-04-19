@php
    $roleLabel = \App\Enums\UserRole::labels()[auth()->user()->role] ?? 'User';
@endphp

<x-layouts.admin :pageTitle="'Settings'" :role="$roleLabel">
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Dashboard</a>
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-slate-600">Settings</span>
    </x-slot>

    <div class="mx-auto w-full max-w-5xl space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white px-6 py-6 shadow-sm">
            <p class="tenant-primary-text text-xs font-semibold uppercase tracking-[0.24em]">Account Settings</p>
            <h2 class="admin-display mt-2 text-2xl font-bold text-slate-900">{{ $user->name }}</h2>
            <p class="mt-2 text-sm text-slate-600">Manage your profile details and password for this tenant account.</p>
        </section>

        @if(session('status') === 'profile-updated')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                Profile updated successfully.
            </div>
        @endif

        @if(session('status') === 'password-updated')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                Password updated successfully.
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Profile Information</h3>
                <p class="mt-1 text-sm text-slate-500">Update your display name and email address.</p>

                <form method="POST" action="{{ route('settings.update') }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="tenant-focus-ring w-full rounded-xl border-slate-300 shadow-sm">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="tenant-focus-ring w-full rounded-xl border-slate-300 shadow-sm">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="tenant-primary-btn rounded-xl px-4 py-2 text-sm font-semibold">Save Profile</button>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Update Password</h3>
                <p class="mt-1 text-sm text-slate-500">Use a strong password and update it regularly.</p>

                <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="mb-1 block text-sm font-medium text-slate-700">Current Password</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="tenant-focus-ring w-full rounded-xl border-slate-300 shadow-sm">
                        @if($errors->updatePassword->has('current_password'))
                            <p class="mt-1 text-xs text-rose-600">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-slate-700">New Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" class="tenant-focus-ring w-full rounded-xl border-slate-300 shadow-sm">
                        @if($errors->updatePassword->has('password'))
                            <p class="mt-1 text-xs text-rose-600">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="tenant-focus-ring w-full rounded-xl border-slate-300 shadow-sm">
                        @if($errors->updatePassword->has('password_confirmation'))
                            <p class="mt-1 text-xs text-rose-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                        @endif
                    </div>

                    <button type="submit" class="tenant-primary-btn rounded-xl px-4 py-2 text-sm font-semibold">Save Password</button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.admin>
