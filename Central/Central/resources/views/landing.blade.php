<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduProfile | Central Academic Monitoring</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 bg-blue-700 rounded-lg flex items-center justify-center text-white font-bold">E</div>
                <span class="text-xl font-bold text-slate-950 tracking-tight">EduProfile</span>
            </div>
            <div class="hidden md:flex gap-8 text-sm font-semibold text-slate-700">
                <a href="#" class="hover:text-blue-800 transition">Home</a>
                <a href="#features" class="hover:text-blue-800 transition">Features</a>
                <a href="#" class="hover:text-blue-800 transition">About</a>
                <a href="#" class="hover:text-blue-800 transition">Contact</a>
            </div>
            <a href="{{ route('login') }}" class="bg-slate-900 hover:bg-slate-950 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow-lg">Central Admin Login</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-6 py-24 lg:py-36 grid lg:grid-cols-2 gap-16 lg:gap-24 items-center">
        <div>
            <h1 class="text-5xl lg:text-7xl font-black leading-[1.1] text-slate-950 tracking-tighter">Centralized Control for Academic Systems</h1>
            <p class="mt-8 text-lg lg:text-xl text-slate-700 leading-relaxed">Run every tenant from one control plane, onboard institutions quickly, and monitor your full education ecosystem from a single modern dashboard.</p>
            <div class="mt-12 flex flex-col sm:flex-row gap-4">
                <a href="{{ route('login') }}" class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-10 py-4 rounded-xl shadow-lg shadow-blue-900/20 transition text-center">Access Portal</a>
                <a href="{{ route('tenant-signup.create') }}" class="border border-slate-400 hover:border-slate-500 text-slate-900 font-semibold px-10 py-4 rounded-xl transition text-center">Create New Tenant</a>
            </div>
            
            <!-- Statistics -->
            <div class="mt-16 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach([['24/7', 'Uptime Monitoring'], ['1 Click', 'Provisioning'], ['Multi-Tenant', 'Architecture']] as $stat)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-2xl font-black text-slate-950">{{ $stat[0] }}</p>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-600 mt-1">{{ $stat[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="bg-slate-200 h-96 lg:h-[450px] rounded-3xl flex items-center justify-center text-slate-500 font-medium border border-slate-200 shadow-inner">
            [Analytics Dashboard Visualization]
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="bg-slate-200 py-32">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-4xl font-black text-center mb-20 tracking-tight text-slate-950">Platform Capabilities</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
                <div class="p-10 border border-slate-300 rounded-3xl shadow-md hover:shadow-xl transition duration-300 bg-white">
                    <div class="h-14 w-14 bg-blue-100 rounded-2xl mb-8 flex items-center justify-center text-blue-800">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-950">Tenant Lifecycle Management</h3>
                    <p class="mt-3 text-sm text-slate-700 leading-relaxed">Create, configure, and monitor each school tenant with clear ownership and status tracking.</p>
                </div>
                <div class="p-10 border border-slate-300 rounded-3xl shadow-md hover:shadow-xl transition duration-300 bg-white">
                    <div class="h-14 w-14 bg-indigo-100 rounded-2xl mb-8 flex items-center justify-center text-indigo-800">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-950">Subscription Oversight</h3>
                    <p class="mt-3 text-sm text-slate-700 leading-relaxed">Track plan types, billing windows, and renewals before they become operational risks.</p>
                </div>
                <div class="p-10 border border-slate-300 rounded-3xl shadow-md hover:shadow-xl transition duration-300 bg-white">
                    <div class="h-14 w-14 bg-slate-300 rounded-2xl mb-8 flex items-center justify-center text-slate-800">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-950">Operational Insights</h3>
                    <p class="mt-3 text-sm text-slate-700 leading-relaxed">Use usage and performance data to optimize your tenant portfolio with confidence.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="max-w-7xl mx-auto px-6 pb-32 pt-32">
        <div class="bg-blue-800 rounded-3xl p-12 lg:p-20 flex flex-col md:flex-row items-center justify-between gap-8 text-white shadow-2xl">
            <div>
                <h3 class="text-3xl font-black">Need a New School Tenant?</h3>
                <p class="text-blue-100 mt-3 text-lg">Spin one up in minutes and start managing.</p>
            </div>
            <a href="{{ route('tenant-signup.create') }}" class="bg-white hover:bg-slate-100 text-blue-900 font-bold px-10 py-4 rounded-xl shadow-lg transition whitespace-nowrap">Launch Tenant Signup</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-slate-100 py-20">
        <div class="max-w-7xl mx-auto px-6 text-center text-sm text-slate-600">
            <p class="font-bold text-slate-950 mb-3 text-lg">EduProfile</p>
            <p>&copy; {{ date('Y') }} EduProfile. All rights reserved.</p>
            <div class="mt-8 flex justify-center gap-8">
                <a href="#" class="hover:text-blue-800 transition">Privacy</a>
                <a href="#" class="hover:text-blue-800 transition">Contact</a>
                <a href="#" class="hover:text-blue-800 transition">Help</a>
            </div>
        </div>
    </footer>
</body>
</html>
