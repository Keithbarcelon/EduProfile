<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCCS | Official Academic Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-blue-900 rounded-full flex items-center justify-center text-white font-bold text-lg">M</div>
                <h1 class="text-xl font-bold text-slate-950 tracking-tight">MCCS Official Portal</h1>
            </div>
            <nav class="flex items-center gap-6">
                <a href="#" class="text-sm font-medium text-slate-600 hover:text-blue-900 transition">Home</a>
                <a href="#" class="text-sm font-medium text-slate-600 hover:text-blue-900 transition">About</a>
                <a href="#" class="bg-blue-900 hover:bg-black text-white text-sm font-semibold px-5 py-2.5 rounded-md transition shadow-md">Portal Login</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="max-w-5xl mx-auto px-6 py-20">
        <section class="text-center">
            <h1 class="text-5xl font-extrabold text-slate-950 leading-tight">Student Profiling & Monitoring Portal</h1>
            <p class="mt-6 text-xl text-slate-700 max-w-2xl mx-auto leading-relaxed">The official MCCS platform for managing student records, monitoring academic performance, and streamlining essential institutional workflows.</p>
            <div class="mt-10 flex gap-4 justify-center">
                <a href="#" class="bg-blue-900 hover:bg-black text-white font-semibold px-8 py-4 rounded-md shadow-lg transition">Access Portal</a>
                <a href="#" class="bg-white border border-slate-300 hover:bg-slate-100 text-slate-900 font-semibold px-8 py-4 rounded-md transition">About MCCS</a>
            </div>
        </section>

        <!-- Overview & Purpose -->
        <section class="mt-24 grid md:grid-cols-2 gap-12">
            <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                <h2 class="text-2xl font-bold text-slate-950">MCCS Institutional Profile</h2>
                <p class="mt-4 text-slate-600 leading-relaxed">Dedicated to fostering academic excellence, MCCS utilizes this centralized system to maintain student integrity, track long-term performance trends, and ensure seamless communication between our faculty and administration.</p>
            </div>
            <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                <h2 class="text-2xl font-bold text-slate-950">System Purpose</h2>
                <ul class="mt-4 space-y-3 text-slate-600">
                    <li class="flex items-center gap-2">• Student record lifecycle management</li>
                    <li class="flex items-center gap-2">• Real-time academic performance monitoring</li>
                    <li class="flex items-center gap-2">• Faculty and administrative coordination</li>
                </ul>
            </div>
        </section>

        <!-- Access Portal -->
        <section class="mt-16 bg-blue-50 p-10 rounded-xl border border-blue-100 text-center">
            <h2 class="text-2xl font-bold text-blue-950">Secure Access for Students & Staff</h2>
            <p class="mt-3 text-slate-700">Please log in with your institutional credentials to access your dashboard.</p>
            <a href="#" class="mt-8 inline-block bg-blue-900 hover:bg-black text-white font-semibold px-10 py-3 rounded-md transition">Login to Portal</a>
            <p class="mt-4 text-xs text-slate-500">Contact the MCCS IT office if you require assistance.</p>
        </section>
    </main>

    <!-- Footer -->
    <footer class="max-w-5xl mx-auto px-6 py-12 border-t border-slate-200 mt-12 text-center text-slate-500 text-sm">
        <p class="font-bold text-slate-950 mb-1">MCCS</p>
        <p>&copy; {{ date('Y') }} MCCS Administration. All rights reserved.</p>
        <p class="mt-2 text-xs">Powered by EduProfile</p>
    </footer>
</body>
</html>
