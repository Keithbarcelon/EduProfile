<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'EduProfile') }} — Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

<div class="flex h-screen overflow-hidden">

    {{-- ===================== SIDEBAR ===================== --}}
        <aside id="sidebar"
            class="flex flex-col w-64 min-h-screen bg-gradient-to-b from-slate-900 to-cyan-950 text-white transition-all duration-300 ease-in-out shrink-0">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-cyan-900/60">
            <div class="flex items-center justify-center w-9 h-9 bg-cyan-500 rounded-lg shadow shadow-cyan-900/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l6.16-3.422A12.083 12.083 0 0121 12c0 3.866-4.03 7-9 7s-9-3.134-9-7a12.08 12.08 0 012.84-7.422L12 14z"/>
                </svg>
            </div>
            <span class="text-lg font-bold tracking-wide">EduProfile</span>
        </div>

        {{-- Role Badge --}}
        <div class="px-6 py-3 border-b border-cyan-900/60">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-900/70 text-cyan-100 uppercase tracking-wider border border-cyan-700/60">
                {{ $role ?? 'Admin' }}
            </span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            @php
                $role = strtolower($role ?? 'admin');
                $navItems = [
                    'developer' => [
                        ['label' => 'Tenants', 'route' => 'developer.tenants.index', 'icon' => 'home'],
                        ['label' => 'Plan Management', 'route' => 'developer.tenants.plan-management', 'icon' => 'chart-bar'],
                        ['label' => 'Tenant Monitoring', 'route' => 'developer.tenants.monitoring', 'icon' => 'clipboard-list'],
                        ['label' => 'Version Management', 'route' => 'developer.version-management.index', 'icon' => 'cog'],
                    ],
                    'admin' => [
                        ['label' => 'Dashboard',  'route' => 'admin.dashboard',  'icon' => 'home'],
                        ['label' => 'Students',   'route' => 'admin.students.index', 'icon' => 'users'],
                        ['label' => 'Faculty',    'route' => 'admin.faculty.index',  'icon' => 'academic-cap'],
                        ['label' => 'Courses',    'route' => 'admin.courses.index',  'icon' => 'book-open'],
                        ['label' => 'Reports',    'route' => 'admin.reports',        'icon' => 'chart-bar'],
                        ['label' => 'Settings',   'route' => 'admin.settings',       'icon' => 'cog'],
                    ],
                    'faculty' => [
                        ['label' => 'Dashboard',  'route' => 'faculty.dashboard', 'icon' => 'home'],
                        ['label' => 'My Classes', 'route' => 'faculty.classes',   'icon' => 'book-open'],
                        ['label' => 'Students',   'route' => 'faculty.students',  'icon' => 'users'],
                        ['label' => 'Grades',     'route' => 'faculty.grades',    'icon' => 'clipboard-list'],
                    ],
                    'student' => [
                        ['label' => 'Dashboard',  'route' => 'student.dashboard', 'icon' => 'home'],
                        ['label' => 'My Profile', 'route' => 'student.profile',   'icon' => 'user'],
                        ['label' => 'Enrollment', 'route' => 'student.enrollment','icon' => 'clipboard-list'],
                        ['label' => 'Grades',     'route' => 'student.grades',    'icon' => 'chart-bar'],
                    ],
                ];
                $items = $navItems[$role] ?? $navItems['admin'];
            @endphp

            @foreach($items as $item)
                @php
                    $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'].'*');
                @endphp
                @if(Route::has($item['route']))
                <a href="{{ route($item['route']) }}"
                   class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ $isActive
                              ? 'bg-cyan-600/80 text-white shadow-inner shadow-cyan-950/40'
                              : 'text-cyan-100/85 hover:bg-cyan-900/50 hover:text-white' }}">
                    <x-admin-nav-icon :icon="$item['icon']" class="w-5 h-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
                @else
                <span class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-cyan-200/50 cursor-not-allowed opacity-60">
                    <x-admin-nav-icon :icon="$item['icon']" class="w-5 h-5 shrink-0" />
                    {{ $item['label'] }}
                </span>
                @endif
            @endforeach
        </nav>

        {{-- Sidebar Footer / User --}}
        <div class="px-4 py-4 border-t border-cyan-900/60">
            @php
                $appVersion = (string) config('app.version', 'v1.0.0');
                $releaseUrl = trim((string) config('app.release.github_url', ''));
                $supportUrl = trim((string) config('app.release.support_url', ''));
                $updatesLabel = (string) config('app.release.updates_label', 'Support & Updates');
            @endphp

            <div class="mb-3 text-[11px] text-cyan-200/80">
                <p class="text-[10px] uppercase tracking-wide text-cyan-300/75">Version {{ $appVersion }}</p>
                <div class="mt-1 flex flex-wrap items-center gap-3">
                    @if($releaseUrl !== '')
                        <a href="{{ $releaseUrl }}" target="_blank" rel="noopener noreferrer" class="text-cyan-100/85 hover:text-white">
                            Release GitHub
                        </a>
                    @endif
                    @if($supportUrl !== '')
                        <a href="{{ $supportUrl }}" target="_blank" rel="noopener noreferrer" class="text-cyan-100/85 hover:text-white">
                            {{ $updatesLabel }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-cyan-500 text-white text-sm font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-cyan-200/75 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            title="Log out"
                            class="p-1.5 rounded-md text-cyan-200/80 hover:text-white hover:bg-cyan-900/60 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===================== MAIN AREA ===================== --}}
    <div class="flex flex-col flex-1 overflow-hidden">

        {{-- ===================== TOP NAVBAR ===================== --}}
        <header class="flex items-center justify-between h-16 px-6 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm shrink-0">

            {{-- Left: Hamburger + Page Title --}}
            <div class="flex items-center gap-4">
                <button id="sidebarToggle"
                        class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        aria-label="Toggle sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div>
                    <h1 class="text-lg font-semibold text-gray-800 dark:text-white leading-tight">
                        {{ $pageTitle ?? 'Dashboard' }}
                    </h1>
                    @isset($breadcrumb)
                    <nav class="flex items-center gap-1 text-xs text-gray-400 mt-0.5">
                        {{ $breadcrumb }}
                    </nav>
                    @endisset
                </div>
            </div>

            {{-- Right: Actions + User --}}
            <div class="flex items-center gap-3">

                {{-- Notifications --}}
                <button class="relative p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                </button>

                {{-- Divider --}}
                <div class="h-6 w-px bg-gray-200 dark:bg-gray-700"></div>

                {{-- User dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <div class="w-7 h-7 rounded-full bg-cyan-600 text-white flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="font-medium">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 py-1 z-50">
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </a>
                        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                                </svg>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- ===================== CONTENT ===================== --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm dark:bg-emerald-900/25 dark:border-emerald-800 dark:text-emerald-200">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm dark:bg-rose-900/25 dark:border-rose-800 dark:text-rose-200">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@if(session('success'))
<div x-data="{ open: true }" x-show="open" x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/55" @click="open = false"></div>
    <div class="relative w-full max-w-md rounded-2xl border border-emerald-200 bg-white p-6 shadow-2xl dark:border-emerald-900/60 dark:bg-slate-900">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Success</h3>
                <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ session('success') }}</p>
            </div>
        </div>
        <div class="mt-5 flex justify-end">
            <button type="button" @click="open = false" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                OK
            </button>
        </div>
    </div>
</div>
@endif

{{-- Sidebar Toggle Script --}}
<script>
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    toggle?.addEventListener('click', () => {
        sidebar.classList.toggle('w-64');
        sidebar.classList.toggle('w-0');
        sidebar.classList.toggle('overflow-hidden');
    });
</script>

</body>
</html>
