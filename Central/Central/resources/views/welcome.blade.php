<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduProfile Central</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-100 to-cyan-100 text-slate-900 antialiased flex flex-col">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-24 -left-20 h-80 w-80 rounded-full bg-cyan-300/35 blur-3xl"></div>
        <div class="absolute top-40 -right-24 h-96 w-96 rounded-full bg-orange-300/35 blur-3xl"></div>
        <div class="absolute -bottom-20 left-1/3 h-72 w-72 rounded-full bg-amber-200/40 blur-3xl"></div>
    </div>

    <div class="relative z-10 flex min-h-screen flex-col">
        <nav class="border-b border-slate-200/70 bg-white/65 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-orange-500 shadow-lg shadow-cyan-500/20">
                        <span class="text-lg font-black text-white">E</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Central Platform</p>
                        <h1 class="text-lg font-bold text-slate-900">EduProfile Central</h1>
                    </div>
                </div>

                <div class="flex items-center gap-5 text-sm font-semibold text-slate-700">
                    @auth
                        <a href="{{ route('dashboard') }}" class="transition hover:text-cyan-700">Dashboard</a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="transition hover:text-orange-700">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="transition hover:text-cyan-700">Login</a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl flex-1 px-4 pb-16 pt-10 sm:px-6 lg:px-8 lg:pt-16">
            <section class="grid items-center gap-10 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white/85 px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-cyan-700 shadow-sm">
                        Central Management Hub
                    </div>

                    <h2 class="mt-6 max-w-3xl text-4xl font-black leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                        Manage School Networks Without the Chaos
                    </h2>

                    <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-700 sm:text-lg">
                        Run every tenant from one control plane, onboard institutions quickly, and monitor your full education ecosystem from a single modern dashboard.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @auth
                            <a href="{{ route('developer.dashboard') }}" class="rounded-xl bg-slate-900 px-7 py-3 text-sm font-bold text-white shadow-xl shadow-slate-900/20 transition hover:-translate-y-0.5 hover:bg-slate-800">
                                Open Developer Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl bg-slate-900 px-7 py-3 text-sm font-bold text-white shadow-xl shadow-slate-900/20 transition hover:-translate-y-0.5 hover:bg-slate-800">
                                Sign In
                            </a>
                        @endauth

                        <a href="{{ route('tenant-signup.create') }}" class="rounded-xl border border-cyan-200 bg-white/85 px-7 py-3 text-sm font-bold text-cyan-800 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                            Create New Tenant
                        </a>
                    </div>

                    <div class="mt-9 grid grid-cols-1 gap-3 text-sm font-semibold text-slate-700 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200/80 bg-white/80 px-4 py-3">
                            <p class="text-2xl font-black text-slate-900">24/7</p>
                            <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">Tenant Uptime Monitoring</p>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-white/80 px-4 py-3">
                            <p class="text-2xl font-black text-slate-900">1 Click</p>
                            <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">Provisioning Flow</p>
                        </div>
                        <div class="rounded-xl border border-slate-200/80 bg-white/80 px-4 py-3">
                            <p class="text-2xl font-black text-slate-900">Multi-Tenant</p>
                            <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">Architecture Ready</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-slate-200/80 bg-white/85 p-5 shadow-xl shadow-slate-400/10 backdrop-blur">
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-slate-500">Platform Capabilities</p>

                        <div class="mt-4 space-y-4">
                            <article class="rounded-2xl border border-cyan-100 bg-cyan-50/80 p-4">
                                <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-slate-900">Tenant Lifecycle Management</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-700">Create, configure, and monitor each school tenant with clear ownership and status tracking.</p>
                            </article>

                            <article class="rounded-2xl border border-amber-100 bg-amber-50/85 p-4">
                                <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-slate-900">Subscription Oversight</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-700">Track plan types, billing windows, and renewals before they become operational risks.</p>
                            </article>

                            <article class="rounded-2xl border border-slate-200 bg-slate-50/90 p-4">
                                <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-700 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-slate-900">Operational Insights</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-700">Use usage and performance data to optimize your tenant portfolio with confidence.</p>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-10 rounded-3xl border border-slate-200/80 bg-white/75 p-6 sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="lg:max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500 sm:tracking-[0.24em]">Need a New School Tenant?</p>
                        <h3 class="mt-2 text-2xl font-black leading-tight text-slate-900 sm:text-3xl">Spin one up in minutes.</h3>
                    </div>
                    <a href="{{ route('tenant-signup.create') }}" class="inline-flex w-full items-center justify-center self-start rounded-xl bg-gradient-to-r from-cyan-500 to-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-400/30 transition hover:-translate-y-0.5 sm:w-auto lg:self-auto">
                        Launch Tenant Signup
                    </a>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200/70 bg-white/60 py-6 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>&copy; {{ date('Y') }} EduProfile. All rights reserved.</p>
                <div class="flex flex-wrap gap-5 font-semibold">
                    @auth
                        <a href="{{ route('developer.tenants.index') }}" class="transition hover:text-cyan-700">Manage Tenants</a>
                    @endauth
                    <a href="{{ route('tenant-signup.create') }}" class="transition hover:text-cyan-700">Create Tenant</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
