<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | EduProfile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-900 antialiased font-sans flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white p-10 rounded-3xl shadow-2xl shadow-slate-200/50 border border-slate-200">
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-950 tracking-tight">Forgot password?</h1>
            <p class="text-slate-600 mt-2 text-sm leading-relaxed">No problem. Just enter your email address and we'll send you a link to reset your password.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-semibold border border-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border-slate-300 bg-slate-50 px-4 py-3 focus:bg-white focus:border-blue-700 focus:ring-4 focus:ring-blue-100 transition outline-none text-slate-900">
                @error('email') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-800/20 transition transform hover:-translate-y-0.5 active:scale-[0.98]">
                Send Reset Link
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-700 hover:underline">Back to login</a>
        </div>
    </div>
</body>
</html>
