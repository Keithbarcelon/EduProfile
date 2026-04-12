<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'EduProfile') }} — Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:500,600,700,800|plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-shell antialiased bg-slate-100 text-slate-900">

<div class="relative flex min-h-screen overflow-hidden">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-20 -right-10 h-72 w-72 rounded-full bg-cyan-200/40 blur-3xl"></div>
        <div class="absolute top-40 -left-24 h-80 w-80 rounded-full bg-indigo-200/35 blur-3xl"></div>
        <div class="absolute bottom-10 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-emerald-200/25 blur-3xl"></div>
    </div>

    {{-- ===================== SIDEBAR ===================== --}}
        <div id="sidebarBackdrop" class="fixed inset-0 z-30 hidden bg-slate-900/40 lg:hidden"></div>

        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-gradient-to-b from-indigo-900 via-indigo-900 to-indigo-950 text-white shadow-2xl transition-transform duration-300 ease-out lg:static lg:z-auto lg:translate-x-0 lg:shadow-none">

        {{-- Logo --}}
        <div class="flex items-center gap-3 border-b border-indigo-800/80 px-6 py-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500 shadow shadow-indigo-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l6.16-3.422A12.083 12.083 0 0121 12c0 3.866-4.03 7-9 7s-9-3.134-9-7a12.08 12.08 0 012.84-7.422L12 14z"/>
                </svg>
            </div>
            <span class="admin-display text-lg font-bold tracking-wide">EduProfile</span>
        </div>

        {{-- Role Badge --}}
        <div class="border-b border-indigo-800/80 px-6 py-3">
            <span class="inline-flex items-center rounded-full bg-indigo-700/80 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider text-indigo-100 ring-1 ring-indigo-400/40">
                {{ $role ?? 'Admin' }}
            </span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            @php
                $user = auth()->user();
                $userRole = strtolower($user?->role ?? 'student');
                if ($userRole === 'admin') {
                    $userRole = 'tenant_admin';
                }

                $staffRoles = ['tenant_admin', 'admission', 'department', 'faculty'];

                if (in_array($userRole, $staffRoles, true)) {
                    $items = [
                        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home'],
                        ['label' => 'Students', 'route' => 'admin.students.index', 'icon' => 'users', 'permission' => 'manage_students'],
                        ['label' => 'Status Monitoring', 'route' => 'admin.status-updates.index', 'icon' => 'clipboard-list', 'permission' => 'manage_status_updates'],
                        ['label' => 'Document Reviews', 'route' => 'admin.documents.index', 'icon' => 'book-open', 'permission' => 'review_documents'],
                        ['label' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'chart-bar', 'permission' => 'view_reports'],
                        ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'user-group', 'permission' => 'manage_users'],
                        ['label' => 'Roles', 'route' => 'admin.roles.index', 'icon' => 'shield-check', 'permission' => 'manage_roles'],
                        ['label' => 'Role Assignments', 'route' => 'admin.role-assignments.index', 'icon' => 'user-group', 'permission' => 'manage_roles'],
                        ['label' => 'Departments', 'route' => 'admin.departments.index', 'icon' => 'office-building', 'permission' => 'manage_departments'],
                        ['label' => 'Settings', 'route' => 'admin.settings.index', 'icon' => 'cog', 'permission' => 'manage_settings'],
                    ];

                    $items = array_values(array_filter($items, function (array $item) use ($user): bool {
                        if (! isset($item['permission'])) {
                            return true;
                        }

                        return $user?->hasPermission($item['permission']) ?? false;
                    }));
                } else {
                    $items = [
                        ['label' => 'Dashboard', 'route' => 'student.dashboard', 'icon' => 'home'],
                        ['label' => 'My Documents', 'route' => 'student.documents.index', 'icon' => 'book-open'],
                        ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'user'],
                    ];
                }
            @endphp

            @foreach($items as $item)
                @php
                    $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'].'*');
                @endphp
                @if(Route::has($item['route']))
                <a href="{{ route($item['route']) }}"
                   class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all
                          {{ $isActive
                              ? 'bg-indigo-600 text-white shadow-md shadow-indigo-950/40'
                              : 'text-indigo-100/90 hover:bg-indigo-800/80 hover:text-white' }}">
                    <x-admin-nav-icon :icon="$item['icon']" class="w-5 h-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
                @else
                <span class="group cursor-not-allowed rounded-lg px-3 py-2.5 text-sm font-medium text-indigo-300/60 opacity-60">
                    <x-admin-nav-icon :icon="$item['icon']" class="w-5 h-5 shrink-0" />
                    {{ $item['label'] }}
                </span>
                @endif
            @endforeach
        </nav>

        {{-- Sidebar Footer / User --}}
        <div class="border-t border-indigo-800/80 px-4 py-4">
            @php
                $appVersion = (string) config('app.version', 'v1.0.0');
                $releaseUrl = trim((string) config('app.release.github_url', ''));
                $supportUrl = trim((string) config('app.release.support_url', ''));
                $updatesLabel = (string) config('app.release.updates_label', 'Support & Updates');
            @endphp

            <div class="mb-3 rounded-lg border border-indigo-800/70 bg-indigo-900/40 px-3 py-2">
                <p class="text-[10px] uppercase tracking-wider text-indigo-300">Application Version</p>
                <p class="mt-0.5 text-sm font-semibold text-white">{{ $appVersion }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                    @if($releaseUrl !== '')
                        <a href="{{ $releaseUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-md border border-indigo-700/70 px-2 py-1 text-indigo-200 hover:bg-indigo-800/70 hover:text-white">
                            Release GitHub
                        </a>
                    @endif
                    @if($supportUrl !== '')
                        <a href="{{ $supportUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-md border border-indigo-700/70 px-2 py-1 text-indigo-200 hover:bg-indigo-800/70 hover:text-white">
                            {{ $updatesLabel }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-500 text-white text-sm font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-indigo-300 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('logout', [], false) }}">
                    @csrf
                    <button type="submit"
                            title="Log out"
                            class="p-1.5 rounded-md text-indigo-300 hover:text-white hover:bg-indigo-700 transition-colors">
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
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

        {{-- ===================== TOP NAVBAR ===================== --}}
        <header class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200/80 bg-white/85 px-4 shadow-sm backdrop-blur sm:px-6">

            {{-- Left: Hamburger + Page Title --}}
            <div class="flex items-center gap-4">
                <button id="sidebarToggle"
                    class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700"
                        aria-label="Toggle sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div>
                    <h1 class="admin-display leading-tight text-lg font-semibold text-slate-900">
                        {{ $pageTitle ?? 'Dashboard' }}
                    </h1>
                    @isset($breadcrumb)
                    <nav class="mt-0.5 flex items-center gap-1 text-xs text-slate-500">
                        {{ $breadcrumb }}
                    </nav>
                    @endisset
                </div>
            </div>

            {{-- Right: Actions + User --}}
            <div class="flex items-center gap-3">

                {{-- Notifications --}}
                <button class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                </button>

                {{-- Divider --}}
                <div class="h-6 w-px bg-slate-200"></div>

                {{-- User dropdown --}}
                <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm text-slate-700 transition-colors hover:bg-slate-100">
                        <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="font-medium">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition
                                                 class="absolute right-0 z-50 mt-2 w-48 rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                        <a href="{{ route('profile.edit') }}"
                                                     class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </a>
                        <div class="my-1 border-t border-slate-100"></div>
                        <form method="POST" action="{{ route('logout', [], false) }}">
                            @csrf
                            <button type="submit"
                                    class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
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
        <main class="relative z-10 flex-1 overflow-y-auto p-4 sm:p-6">
            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
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

{{-- Sidebar Toggle Script --}}
<script>
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');

    function closeSidebar() {
        sidebar?.classList.add('-translate-x-full');
        backdrop?.classList.add('hidden');
    }

    function openSidebar() {
        sidebar?.classList.remove('-translate-x-full');
        backdrop?.classList.remove('hidden');
    }

    toggle?.addEventListener('click', () => {
        const isOpen = !sidebar?.classList.contains('-translate-x-full');
        if (window.matchMedia('(min-width: 1024px)').matches) {
            return;
        }

        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    backdrop?.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 1024px)').matches) {
            backdrop?.classList.add('hidden');
            sidebar?.classList.remove('-translate-x-full');
        } else {
            sidebar?.classList.add('-translate-x-full');
        }
    });
</script>

</body>
</html>
