<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | EduProfile</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --ep-bg-a: #e0f2fe;
            --ep-bg-b: #f8fafc;
            --ep-card-border: #dbeafe;
            --ep-accent-a: #0ea5e9;
            --ep-accent-b: #0369a1;
            --ep-text: #0f172a;
            --ep-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            color: var(--ep-text);
            background:
                radial-gradient(42rem 42rem at 10% 8%, rgba(14, 165, 233, 0.20), transparent 60%),
                radial-gradient(35rem 35rem at 88% 90%, rgba(3, 105, 161, 0.14), transparent 58%),
                linear-gradient(160deg, var(--ep-bg-a) 0%, var(--ep-bg-b) 62%);
            min-height: 100vh;
        }

        .ep-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.25rem;
        }

        .ep-card {
            width: 100%;
            max-width: 34rem;
            border: 1px solid var(--ep-card-border);
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 24px 55px rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(4px);
        }

        .ep-logo {
            width: 3.2rem;
            height: 3.2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.9rem;
            color: #fff;
            font-weight: 800;
            font-size: 1.2rem;
            background: linear-gradient(135deg, var(--ep-accent-a), var(--ep-accent-b));
            box-shadow: 0 12px 28px rgba(3, 105, 161, 0.28);
        }

        .ep-tenant-box {
            border: 1px solid #bae6fd;
            background: #f0f9ff;
            border-radius: 0.9rem;
            padding: 0.8rem 0.9rem;
        }

        .ep-tenant-label {
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: var(--ep-accent-b);
            text-transform: uppercase;
            margin-bottom: 0.2rem;
        }

        .ep-muted {
            color: var(--ep-muted);
        }

        .ep-btn {
            background: linear-gradient(135deg, var(--ep-accent-a), var(--ep-accent-b));
            border: none;
            color: #fff;
            font-weight: 700;
            border-radius: 0.75rem;
            padding: 0.72rem 1rem;
        }

        .ep-btn:hover,
        .ep-btn:focus {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(3, 105, 161, 0.26);
        }
    </style>
</head>
<body>
    <main class="ep-shell">
        <div class="ep-card p-4 p-sm-5">
            <div class="text-center mb-4">
                <a href="{{ route('landing') }}" class="ep-logo text-decoration-none">E</a>
                <h1 class="h2 mt-3 mb-1 fw-bold">EduProfile Login</h1>
                <p class="ep-muted mb-0">Sign in to access your academic workspace.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger py-2 mb-3" role="alert">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-auth-session-status class="alert alert-success py-2 mb-3" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="form-control form-control-lg">
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="form-control form-control-lg">
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4 gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small fw-semibold text-decoration-none">Forgot Password?</a>
                    @endif
                </div>

                <button type="submit" class="btn ep-btn w-100">Login to Tenant Portal</button>
            </form>

            <p class="small ep-muted text-center mt-4 mb-0">Secure access for admins, faculty, and students.</p>
        </div>
    </main>
</body>
</html>
