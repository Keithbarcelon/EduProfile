<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register School | EduProfile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-24 -left-16 h-80 w-80 rounded-full bg-cyan-200/60 blur-3xl"></div>
        <div class="absolute top-16 right-0 h-96 w-96 rounded-full bg-amber-200/50 blur-3xl"></div>
    </div>

    <div class="relative z-10">
        <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-sky-600 text-sm font-black text-white shadow-lg shadow-cyan-500/25">E</span>
                    <span class="text-sm font-semibold tracking-wide text-slate-700">EduProfile Tenant</span>
                </a>
                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 transition hover:text-cyan-700">Already registered? Login</a>
            </div>
        </header>

        <main class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-12 lg:gap-10 lg:px-8 lg:py-14">
            <section class="lg:col-span-4 lg:sticky lg:top-8 lg:self-start">
                <div class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-xl shadow-slate-200/60 sm:p-7">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">School Onboarding</p>
                    <h1 class="mt-3 text-3xl font-black leading-tight text-slate-900">Create Your EduProfile Tenant</h1>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Set up your school workspace for student profiling, status monitoring, remarks, intervention notes, and compliance document tracking.</p>

                    <div class="mt-6 space-y-3 text-sm">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="font-semibold text-slate-800">Basic Plan</p>
                            <p class="text-slate-600">Up to 300 students, 5 staff users, core monitoring.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="font-semibold text-slate-800">Standard Plan</p>
                            <p class="text-slate-600">Up to 1,500 students, exports, and up to 20 staff users.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="font-semibold text-slate-800">Premium Plan</p>
                            <p class="text-slate-600">Unlimited students and users with advanced analytics.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="lg:col-span-8">
                <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-xl shadow-slate-200/60 sm:p-8 lg:p-9">
                    <h2 class="text-xl font-bold text-slate-900">School Registration</h2>
                    <p class="mt-1 text-sm text-slate-600">Register your school and create its admin account.</p>

                    @if(session('success'))
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="mt-7 grid gap-5 sm:grid-cols-2">
                        @csrf

                        <div class="sm:col-span-2">
                            <label for="tenant_name" class="mb-1 block text-sm font-semibold text-slate-700">School Name</label>
                            <input id="tenant_name" type="text" name="tenant_name" value="{{ old('tenant_name') }}" required autofocus class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                            @error('tenant_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                            <input id="address" type="text" name="address" value="{{ old('address') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                            @error('address')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

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

                        <div>
                            <label for="admin_password_confirmation" class="mb-1 block text-sm font-semibold text-slate-700">Confirm Password</label>
                            <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="plan_expiration_email" class="mb-1 block text-sm font-semibold text-slate-700">Plan Expiration Email (Optional)</label>
                            <input id="plan_expiration_email" type="email" name="plan_expiration_email" value="{{ old('plan_expiration_email') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                            @error('plan_expiration_email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="tenant_domain" class="mb-1 block text-sm font-semibold text-slate-700">Preferred Tenant Domain (Optional)</label>
                            <input id="tenant_domain" type="text" name="tenant_domain" value="{{ old('tenant_domain') }}" placeholder="myschool.localhost" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                            @error('tenant_domain')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2 mt-3 flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 transition hover:text-cyan-700">Already have a school account?</a>
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 hover:bg-slate-800 sm:w-auto">Register School</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
