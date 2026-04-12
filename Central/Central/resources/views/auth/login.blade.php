<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | EduProfile Central</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-100 to-cyan-100 text-slate-900 antialiased">
    @if(session('success'))
        <div id="registration-success-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div id="registration-success-backdrop" class="absolute inset-0 bg-slate-900/55"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-emerald-200 bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-900">Registration Successful</h3>
                        <p class="mt-1 text-sm text-slate-700">{{ session('success') }}</p>
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button id="registration-success-close" type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        OK
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
        <header class="border-b border-slate-200/70 bg-white/65 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3 transition hover:opacity-90" aria-label="Go to EduProfile Central landing page">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-orange-500 text-sm font-black text-white shadow-lg shadow-cyan-500/20">E</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Central Platform</p>
                        <h1 class="text-sm font-bold tracking-wide text-slate-900">EduProfile Central</h1>
                    </div>
                </a>
                @if (Route::has('tenant-signup.create'))
                    <a href="{{ route('tenant-signup.create') }}" class="text-sm font-semibold text-slate-600 transition hover:text-cyan-700">Register School</a>
                @endif
            </div>
        </header>

        <main class="mx-auto grid w-full max-w-7xl flex-1 gap-8 px-4 py-8 sm:px-6 lg:grid-cols-12 lg:gap-10 lg:px-8 lg:py-14">
            <section class="lg:col-span-4 lg:sticky lg:top-8 lg:self-start">
                <div class="rounded-3xl border border-slate-200/80 bg-white/85 p-6 shadow-xl shadow-slate-400/10 backdrop-blur sm:p-7">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">Welcome Back</p>
                    <h2 class="mt-3 text-3xl font-black leading-tight text-slate-900">Sign In To EduProfile Central</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Access tenant lifecycle controls, provisioning tools, and subscription oversight from your central dashboard.</p>

                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50/90 p-4 text-sm text-slate-700">
                        <p class="font-semibold text-slate-900">Central Access</p>
                        <p class="mt-1">Use your developer account credentials to continue.</p>
                    </div>
                </div>
            </section>

            <section class="lg:col-span-8">
                <div class="rounded-3xl border border-slate-200/80 bg-white/85 p-6 shadow-xl shadow-slate-400/10 backdrop-blur sm:p-8 lg:p-9">
                    <h2 class="text-xl font-bold text-slate-900">Login</h2>
                    <p class="mt-1 text-sm text-slate-600">Sign in to continue to your dashboard.</p>

                    @if ($errors->any())
                        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <p class="font-semibold">Login failed.</p>
                            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    <x-auth-session-status class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                            @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">Password</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                            @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-cyan-600 shadow-sm focus:ring-cyan-500" name="remember">
                                <span>Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-slate-600 transition hover:text-cyan-700">Forgot password?</a>
                            @endif
                        </div>

                        <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            @if (Route::has('tenant-signup.create'))
                                <a href="{{ route('tenant-signup.create') }}" class="text-sm font-semibold text-slate-600 transition hover:text-cyan-700">Need a school account?</a>
                            @else
                                <span></span>
                            @endif
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 hover:bg-slate-800 sm:w-auto">Log In</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    @if(session('success'))
        <script>
            (function () {
                const modal = document.getElementById('registration-success-modal');
                const closeBtn = document.getElementById('registration-success-close');
                const backdrop = document.getElementById('registration-success-backdrop');

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
