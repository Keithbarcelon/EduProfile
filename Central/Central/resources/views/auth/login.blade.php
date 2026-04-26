<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EduProfile Central</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-slate-100 text-slate-900 antialiased font-sans">
    <div class="flex min-h-screen">
        <!-- Left Side: Visual Area -->
        <div class="hidden lg:flex lg:w-1/2 bg-blue-700 relative overflow-hidden items-center justify-center p-12">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="relative z-10 text-white max-w-lg">
                <h2 class="text-4xl font-black leading-tight tracking-tight">Centralized Control for Academic Systems</h2>
                <p class="mt-6 text-lg text-blue-100 leading-relaxed">EduProfile provides a seamless, secure, and robust platform for managing student profiles and academic monitoring across all institutions.</p>
            </div>
        </div>

        <!-- Right Side: Login Area -->
        <div class="flex flex-1 flex-col justify-center px-6 py-12 lg:px-20 xl:px-32 bg-slate-50">
            <div class="mx-auto w-full max-w-sm lg:max-w-md">
                <!-- Branding -->
                <div class="mb-12">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-10 w-10 bg-blue-700 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-700/20">E</div>
                        <span class="text-xl font-bold text-slate-950 tracking-tight">EduProfile</span>
                    </div>
                    <h1 class="text-3xl font-black text-slate-950 tracking-tighter">Central Admin Portal</h1>
                    <p class="text-slate-600 mt-2">Sign in to manage your institutions.</p>
                </div>

                <!-- Login Card -->
                <div class="bg-white border border-slate-200 shadow-2xl shadow-slate-200/50 p-8 rounded-3xl" x-data="{ show: false }">
                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 focus:bg-white focus:border-blue-700 focus:ring-4 focus:ring-blue-100 transition outline-none text-slate-900">
                        </div>

                        <div x-data="{ show: false }">
                            <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                            <div class="relative">
                                <input id="password" :type="show ? 'text' : 'password'" name="password" required class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 focus:bg-white focus:border-blue-700 focus:ring-4 focus:ring-blue-100 transition outline-none pr-12 text-slate-900">
                                <button type="button" @click="show = !show" class="absolute right-4 top-3.5 text-slate-500 hover:text-blue-700 transition">
                                    <span x-text="show ? 'Hide' : 'Show'" class="text-xs font-bold uppercase tracking-wider"></span>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 text-slate-600 font-medium">
                                <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                                Remember me
                            </label>
                            <a href="{{ route('password.request') }}" class="text-blue-700 font-semibold hover:underline">Forgot password?</a>
                        </div>

                        <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-800/20 transition transform hover:-translate-y-0.5 active:scale-[0.98]">
                            Sign In
                        </button>
                    </form>
                </div>
                
                <p class="text-center text-xs text-slate-500 mt-10">Authorized personnel only &copy; {{ date('Y') }} EduProfile</p>
                <div class="mt-6 flex justify-center gap-6 text-sm">
                    <a href="{{ url('/') }}" class="text-slate-600 font-semibold hover:text-blue-700 transition">Return to Home</a>
                    <a href="{{ route('tenant-signup.create') }}" class="text-blue-700 font-semibold hover:underline transition">Register New School</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
