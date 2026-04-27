<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app('currentSchool')->name }} | Tenant Portal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:600,700,800|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased" style="font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
    @php
        $school = app('currentSchool');
        $portalInfo = [
            [
                'title' => 'Tenant Access',
                'description' => 'This portal is only for users of this school.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a7.5 7.5 0 0115 0" />',
            ],
            [
                'title' => 'User Roles',
                'description' => 'Admins, faculty, and students can only see assigned pages.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3.75 18h16.5M7.5 14.25V9m4.5 5.25V5.25m4.5 9V11.25" />',
            ],
            [
                'title' => 'Student Records',
                'description' => 'Student profile, status, and documents are handled here.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7.5 3.75h6l3 3v13.5H7.5a2.25 2.25 0 01-2.25-2.25V6A2.25 2.25 0 017.5 3.75z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M13.5 3.75V7.5h3.75" />',
            ],
            [
                'title' => 'Secure Login',
                'description' => 'Sign in to open your school dashboard.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4.5 15.75l4.5-4.5 3 3 6-6" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M14.25 8.25h3.75V12" />',
            ],
        ];
    @endphp

    <div class="relative isolate overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(15,23,42,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(15,23,42,0.05)_1px,transparent_1px)] bg-[size:72px_72px]"></div>
            <div class="absolute inset-x-0 top-0 h-[32rem] bg-[radial-gradient(circle_at_top,rgba(15,23,42,0.18),transparent_58%)]"></div>
            <div class="absolute right-[-8rem] top-24 h-80 w-80 rounded-full bg-sky-200/40 blur-3xl"></div>
            <div class="absolute left-[-8rem] top-[28rem] h-96 w-96 rounded-full bg-emerald-100/50 blur-3xl"></div>
        </div>

        <div class="relative">
            <header class="border-b border-slate-300/80 bg-white/90 backdrop-blur">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="#home" class="flex min-w-0 items-center gap-4">
                        @php
                            $hasTenantLogo = filled($school->logo_path);
                        @endphp
                        @if($hasTenantLogo)
                        <img
                            src="{{ route('tenant.logo', ['v' => optional($school->updated_at)->timestamp]) }}"
                            alt="{{ $school->name }} logo"
                            class="h-12 w-12 shrink-0 rounded-sm border border-slate-300 bg-white object-contain p-1 shadow-lg shadow-slate-900/15"
                        >
                        @else
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-sm border border-slate-800 bg-slate-950 text-sm font-extrabold uppercase tracking-[0.2em] text-white shadow-lg shadow-slate-900/15">
                            {{ strtoupper(substr($school->name, 0, 1)) }}
                        </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-slate-500">{{ $school->name }}</p>
                            <h1 class="truncate text-base font-bold uppercase text-slate-950 sm:text-lg" style="font-family: 'Outfit', 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
                                {{ $school->name }}
                            </h1>
                        </div>
                    </a>

                    <nav class="hidden items-center gap-8 text-sm font-semibold uppercase tracking-[0.14em] text-slate-600 md:flex">
                        <a href="#home" class="transition hover:text-slate-950">Home</a>
                        <a href="#about" class="transition hover:text-slate-950">About</a>
                        <a href="{{ route('login') }}" class="rounded-sm border border-sky-700 bg-sky-700 px-4 py-2 text-white shadow-sm transition hover:border-sky-800 hover:bg-sky-800">
                            Login
                        </a>
                    </nav>

                    <a href="{{ route('login') }}" class="rounded-sm border border-sky-700 bg-sky-700 px-4 py-2 text-sm font-semibold uppercase tracking-[0.12em] text-white shadow-sm transition hover:bg-sky-800 md:hidden">
                        Login
                    </a>
                </div>
            </header>

            <main>
                <section id="home" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
                    <div class="grid gap-6 lg:grid-cols-[1.35fr_0.65fr]">
                        <div class="relative overflow-hidden border border-slate-300 bg-slate-950 text-white shadow-[0_24px_60px_rgba(15,23,42,0.18)]">
                            <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(148,163,184,0.14)_1px,transparent_1px),linear-gradient(to_bottom,rgba(148,163,184,0.14)_1px,transparent_1px)] bg-[size:36px_36px]"></div>
                            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_center,rgba(56,189,248,0.18),transparent_62%)]"></div>
                            <div class="relative grid min-h-[32rem] content-between gap-10 p-8 sm:p-10 lg:p-12">
                                <div class="max-w-3xl">
                                    <div class="inline-flex items-center gap-3 border border-slate-700 bg-white/5 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.32em] text-slate-300">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                        Tenant Portal
                                    </div>
                                    <h2 class="mt-8 max-w-4xl text-4xl font-black uppercase leading-tight sm:text-5xl xl:text-6xl" style="font-family: 'Outfit', 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
                                        {{ $school->name }} Tenant Portal
                                    </h2>
                                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                                        This page is for {{ $school->name }} users only. Sign in to continue.
                                    </p>
                                    <div class="mt-8 flex flex-wrap items-center gap-4">
                                        <a href="{{ route('login') }}" class="rounded-sm bg-emerald-500 px-6 py-3 text-sm font-bold uppercase tracking-[0.16em] text-slate-950 transition hover:bg-emerald-400">
                                            Access Portal
                                        </a>
                                        <a href="#portal-info" class="rounded-sm border border-slate-600 px-6 py-3 text-sm font-bold uppercase tracking-[0.16em] text-white transition hover:border-slate-400 hover:bg-white/5">
                                            About This Portal
                                        </a>
                                    </div>
                                </div>

                                <div class="grid gap-4 border-t border-slate-800 pt-6 sm:grid-cols-3">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">System Focus</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-200">School portal access</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Deployment</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-200">School-only environment</p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Environment</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-200">{{ $school->name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6">
                            <article class="border border-slate-300 bg-white p-6 shadow-sm sm:p-7">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500">Institution</p>
                                <div class="mt-5 space-y-5">
                                    <div class="border-b border-slate-200 pb-5">
                                        <p class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">School Name</p>
                                        <p class="mt-2 text-xl font-bold uppercase text-slate-950" style="font-family: 'Outfit', 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
                                            {{ $school->name }}
                                        </p>
                                    </div>
                                    <div class="border-b border-slate-200 pb-5">
                                        <p class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">System Role</p>
                                        <p class="mt-2 text-sm leading-7 text-slate-700">
                                            Dedicated login for this school.
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Operational Goal</p>
                                        <p class="mt-2 text-sm leading-7 text-slate-700">
                                            Keep users in the correct school portal.
                                        </p>
                                    </div>
                                </div>
                            </article>

                            <article class="border border-emerald-200 bg-emerald-50/80 p-6 shadow-sm sm:p-7">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-emerald-700">Academic Support</p>
                                <p class="mt-4 text-sm leading-7 text-slate-700">
                                    Use this portal for school records and updates.
                                </p>
                            </article>
                        </div>
                    </div>
                </section>

                <section id="portal-info" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                    <div class="grid gap-6 border-y border-slate-300 py-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-sky-800">Portal Information</p>
                            <h3 class="mt-3 text-3xl font-black uppercase leading-tight text-slate-950 sm:text-4xl" style="font-family: 'Outfit', 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
                                About this tenant portal
                            </h3>
                        </div>
                        <p class="max-w-3xl text-sm leading-8 text-slate-600 sm:text-base">
                            This page confirms you are in the correct school portal.
                        </p>
                    </div>

                    <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        @foreach($portalInfo as $item)
                            <article class="group border border-slate-300 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-5">
                                    <span class="inline-flex h-12 w-12 items-center justify-center border border-slate-300 bg-slate-50 text-sky-800">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            {!! $item['icon'] !!}
                                        </svg>
                                    </span>
                                    <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-slate-400">Portal</span>
                                </div>
                                <h4 class="mt-6 text-lg font-bold uppercase leading-snug text-slate-950" style="font-family: 'Outfit', 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
                                    {{ $item['title'] }}
                                </h4>
                                <p class="mt-3 text-sm leading-7 text-slate-600">
                                    {{ $item['description'] }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="about" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                    <div class="grid gap-6 lg:grid-cols-[0.7fr_1.3fr]">
                        <article class="border border-slate-300 bg-white p-7 shadow-sm sm:p-8">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-500">About {{ $school->name }}</p>
                            <h3 class="mt-4 text-3xl font-black uppercase leading-tight text-slate-950" style="font-family: 'Outfit', 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;">
                                About this tenant portal
                            </h3>
                        </article>

                        <article class="border border-slate-800 bg-slate-900 p-7 text-slate-200 shadow-[0_18px_48px_rgba(15,23,42,0.16)] sm:p-8">
                            <p class="text-base leading-8">
                                <span class="font-semibold text-white">{{ $school->name }}</span> uses this portal for school users. Sign in to access your assigned pages.
                            </p>
                        </article>
                    </div>
                </section>
            </main>

            <footer class="border-t border-slate-300 bg-white/90">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p class="font-bold uppercase tracking-[0.14em] text-slate-900">{{ $school->name }}</p>
                    <p>&copy; {{ now()->year }} {{ $school->name }}. {{ $school->name }} Tenant Portal.</p>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
