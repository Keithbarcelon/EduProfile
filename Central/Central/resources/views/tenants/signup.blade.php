<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration | EduProfile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-slate-100 text-slate-800 font-sans">
    <div class="relative min-h-screen py-16 px-6">
        <!-- Abstract Background -->
        <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-200 rounded-full blur-[100px] opacity-60"></div>
            <div class="absolute top-[20%] -right-[10%] w-[30%] h-[30%] bg-indigo-200 rounded-full blur-[100px] opacity-60"></div>
        </div>

        <div class="relative z-10 max-w-3xl mx-auto">
            <!-- Header -->
            <header class="flex justify-between items-center mb-16">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 bg-blue-700 rounded-lg flex items-center justify-center text-white font-black">E</div>
                    <span class="font-bold text-slate-900 tracking-tight">EduProfile <span class="text-slate-500 font-normal">| Central Platform</span></span>
                </div>
                <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Already registered? Login</a>
            </header>

            <!-- Hero -->
            <div class="text-center mb-12">
                <h1 class="text-5xl font-black text-slate-950 tracking-tighter">Create Your EduProfile Tenant</h1>
                <p class="text-slate-600 mt-4 text-lg">Onboard your school into our central academic ecosystem.</p>
            </div>

            <!-- Registration Card -->
            <div class="bg-white rounded-3xl shadow-2xl shadow-slate-300/50 border border-slate-200 p-8 md:p-12">
                <form method="POST" action="{{ route('tenant-signup.store') }}" class="space-y-10" x-data="{ plan: '{{ old('plan_type', 'basic') }}' }">
                    @csrf
                    
                    <!-- School & Admin -->
                    <section class="grid md:grid-cols-2 gap-x-8 gap-y-10">
                        <div class="space-y-6 md:col-span-2">
                            <h2 class="text-lg font-bold text-slate-950 flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-700 text-white text-xs">1</span>
                                Institutional Details
                            </h2>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">School Name</label>
                            <input type="text" name="tenant_name" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition text-slate-900">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Address</label>
                            <input type="text" name="address" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition text-slate-900">
                        </div>
                        
                        <div class="space-y-6 md:col-span-2 border-t border-slate-200 pt-8">
                            <h2 class="text-lg font-bold text-slate-950 flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-700 text-white text-xs">2</span>
                                Admin Identity
                            </h2>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
                            <input type="text" name="signup_admin_name" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition text-slate-900">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                            <input type="email" name="admin_email" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition text-slate-900">
                        </div>
                        <div x-data="{ show: false }">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="admin_password" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition pr-12 text-slate-900">
                                <button type="button" @click="show = !show" class="absolute right-4 top-3.5 text-slate-500 hover:text-blue-700">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                        <div x-data="{ show: false }">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="admin_password_confirmation" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition pr-12 text-slate-900">
                                <button type="button" @click="show = !show" class="absolute right-4 top-3.5 text-slate-500 hover:text-blue-700">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Plan Selection (Final Step) -->
                    <section class="border-t border-slate-200 pt-10">
                        <h2 class="text-lg font-bold text-slate-950 mb-8 flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-700 text-white text-xs">3</span>
                            Select Your Plan
                        </h2>
                        <div class="grid md:grid-cols-3 gap-6">
                            @foreach([
                                ['basic', 'Basic', 'Core monitoring for small setups', '300 students'],
                                ['standard', 'Standard', 'Advanced tools for growth', '1,500 students'],
                                ['premium', 'Premium', 'Full enterprise scale', 'Unlimited']
                            ] as $p)
                                <div @click="plan = '{{ $p[0] }}'" 
                                     class="cursor-pointer p-6 rounded-2xl border-2 transition-all duration-300 relative group"
                                     :class="plan === '{{ $p[0] }}' ? 'border-blue-700 bg-blue-50 shadow-lg shadow-blue-500/10' : 'border-slate-200 hover:border-blue-300 bg-white'">
                                    
                                    <input type="radio" name="plan_type" value="{{ $p[0] }}" class="hidden" :checked="plan === '{{ $p[0] }}'">
                                    
                                    <div class="flex justify-between items-start mb-4">
                                        <h4 class="font-bold text-slate-950 text-lg">{{ $p[1] }}</h4>
                                        <div class="h-5 w-5 rounded-full border-2 flex items-center justify-center transition-colors"
                                             :class="plan === '{{ $p[0] }}' ? 'border-blue-700 bg-blue-700' : 'border-slate-300'">
                                            <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </div>
                                    <p class="text-sm text-slate-600 mb-4 leading-snug">{{ $p[2] }}</p>
                                    <span class="inline-block px-3 py-1 rounded-full bg-slate-200 text-xs font-bold text-slate-700">{{ $p[3] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <!-- Footer -->
                    <footer class="border-t pt-8">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5 active:scale-[0.98]">
                            Create Tenant Account
                        </button>
                        <p class="text-center text-slate-400 text-sm mt-6">
                            Your request will be reviewed by the central administrator.<br>
                            <a href="{{ url('/') }}" class="text-blue-600 font-semibold hover:underline mt-2 inline-block">Return to Home</a>
                        </p>
                    </footer>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
