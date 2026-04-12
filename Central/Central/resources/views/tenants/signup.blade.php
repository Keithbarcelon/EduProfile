<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>School Registration | EduProfile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-100 to-cyan-100 text-slate-900 antialiased">
    @if(session('success'))
        @php
            $requestedDomain = session('tenant_requested_domain');
        @endphp

        <div id="tenant-signup-success-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div id="tenant-signup-success-backdrop" class="absolute inset-0 bg-slate-900/55"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-emerald-200 bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-900">Registration Submitted</h3>
                        <p class="mt-1 text-sm text-slate-700">{{ session('success') }}</p>
                        @if($requestedDomain)
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Requested Domain</p>
                            <p class="text-sm text-slate-800">{{ $requestedDomain }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-1">
                    <button id="tenant-signup-success-close" type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Stay Here
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-24 -left-20 h-80 w-80 rounded-full bg-cyan-300/35 blur-3xl"></div>
        <div class="absolute top-40 -right-24 h-96 w-96 rounded-full bg-orange-300/35 blur-3xl"></div>
        <div class="absolute -bottom-20 left-1/3 h-72 w-72 rounded-full bg-amber-200/40 blur-3xl"></div>
    </div>

    <div class="relative z-10 flex min-h-screen flex-col">
        <nav class="border-b border-slate-200/70 bg-white/65 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3 transition hover:opacity-90" aria-label="Go to EduProfile Central landing page">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-orange-500 shadow-lg shadow-cyan-500/20">
                        <span class="text-sm font-black text-white">E</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Central Platform</p>
                        <h1 class="text-sm font-bold tracking-wide text-slate-900">EduProfile Central</h1>
                    </div>
                </a>

                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 transition hover:text-cyan-700">Already registered? Login</a>
            </div>
        </nav>

        <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col px-4 pb-14 pt-10 sm:px-6 lg:px-8 lg:pt-16">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-10">
                <section class="lg:col-span-4">
                    <div class="rounded-3xl border border-slate-200/80 bg-white/85 p-6 shadow-xl shadow-slate-400/10 backdrop-blur sm:p-7">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-700">School Onboarding</p>
                        <h2 class="mt-3 text-4xl font-black leading-tight text-slate-900">Create Your EduProfile Tenant</h2>
                        <p class="mt-4 text-sm leading-relaxed text-slate-600">Set up your school workspace for student profiling, status monitoring, remarks, intervention notes, and compliance document tracking.</p>

                        <div class="mt-6 space-y-3">
                            <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50/90 dark:bg-gray-900/50 p-4">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">Basic Plan</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-gray-300">Up to 300 students, 5 staff users, core monitoring.</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50/90 dark:bg-gray-900/50 p-4">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">Standard Plan</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-gray-300">Up to 1,500 students, exports, and up to 20 staff users.</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50/90 dark:bg-gray-900/50 p-4">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">Premium Plan</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-gray-300">Unlimited students and users with advanced analytics.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="lg:col-span-8">
                    <div class="rounded-3xl border border-slate-200/80 bg-white/85 p-6 shadow-xl shadow-slate-400/10 backdrop-blur sm:p-8 lg:p-9">
                        <h2 class="text-3xl font-black text-slate-900">School Registration</h2>
                        <p class="mt-1 text-sm text-slate-600">Submit your school request. Tenant account and database are created only after central approval.</p>

                        @if(session('success'))
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                <p class="font-semibold">Please check your inputs.</p>
                                <ul class="mt-1 list-disc pl-5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('tenant-signup.store') }}" class="mt-6 space-y-5">
                            @csrf

                            <div>
                                <label for="tenant_name" class="mb-1 block text-sm font-semibold text-slate-700">School Name</label>
                                <input id="tenant_name" type="text" name="tenant_name" value="{{ old('tenant_name') }}" required autofocus class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                                @error('tenant_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="address" class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                                <input id="address" type="text" name="address" value="{{ old('address') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                                @error('address')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label for="plan_type" class="mb-1 block text-sm font-semibold text-slate-700">Plan</label>
                                    <select id="plan_type" name="plan_type" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                                        <option value="basic" @selected(old('plan_type', 'basic') === 'basic')>Basic</option>
                                        <option value="standard" @selected(old('plan_type') === 'standard')>Standard</option>
                                        <option value="premium" @selected(old('plan_type') === 'premium')>Premium</option>
                                    </select>
                                    <p class="mt-1 text-xs text-slate-500">You can upgrade your subscription anytime.</p>
                                    @error('plan_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="signup_admin_name" class="mb-1 block text-sm font-semibold text-slate-700">Admin Full Name</label>
                                    <input id="signup_admin_name" type="text" name="signup_admin_name" value="{{ old('signup_admin_name') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                                    @error('signup_admin_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label for="admin_email" class="mb-1 block text-sm font-semibold text-slate-700">Admin Email</label>
                                    <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                                    @error('admin_email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="admin_password" class="mb-1 block text-sm font-semibold text-slate-700">Admin Password</label>
                                    <input id="admin_password" type="password" name="admin_password" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                                    @error('admin_password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="max-w-md">
                                <label for="admin_password_confirmation" class="mb-1 block text-sm font-semibold text-slate-700">Confirm Password</label>
                                <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                            </div>

                            <div>
                                <label for="plan_expiration_email" class="mb-1 block text-sm font-semibold text-slate-700">Plan Expiration Email (Optional)</label>
                                <input id="plan_expiration_email" type="email" name="plan_expiration_email" value="{{ old('plan_expiration_email') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                                @error('plan_expiration_email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="max-w-md">
                                <label for="tenant_domain" class="mb-1 block text-sm font-semibold text-slate-700">Preferred Tenant Domain (Optional)</label>
                                <input id="tenant_domain" type="text" name="tenant_domain" value="{{ old('tenant_domain') }}" placeholder="myschool.localhost" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                                <p class="mt-1 text-xs text-slate-500">Example: myschool.localhost. This domain is activated after central admin approval.</p>
                                @error('tenant_domain')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 transition hover:text-cyan-700">Already have a school account?</a>
                                <div class="flex w-full gap-3 sm:w-auto">
                                    <button type="button" onclick="window.history.back()" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white/80 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:w-auto">Back</button>
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 hover:bg-slate-800 sm:w-auto">Submit Request</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    </div>

    @if(session('success'))
        <script>
            (function () {
                const modal = document.getElementById('tenant-signup-success-modal');
                const closeBtn = document.getElementById('tenant-signup-success-close');
                const backdrop = document.getElementById('tenant-signup-success-backdrop');

                const closeModal = () => {
                    if (!modal) return;
                    modal.remove();
                };

                closeBtn?.addEventListener('click', closeModal);
                backdrop?.addEventListener('click', closeModal);
            })();
        </script>
    @endif
</body>
</html>
