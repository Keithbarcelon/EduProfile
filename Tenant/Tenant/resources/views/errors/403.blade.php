@php
    $message = trim((string) ($exception->getMessage() ?? ''));
    $normalized = strtolower($message);

    $isTenantDisabled = str_contains($normalized, 'tenant is disabled');

    $reason = 'Access to this workspace is currently restricted.';

    if (preg_match('/reason:\s*(.+)$/i', $message, $matches) === 1) {
        $reason = trim($matches[1]);
    } elseif ($message !== '') {
        $reason = $message;
    }

    $title = $isTenantDisabled ? 'Tenant Access Paused' : 'Access Restricted';
    $subtitle = $isTenantDisabled
        ? 'This tenant is temporarily disabled. Please contact your tenant administrator to continue.'
        : 'Your account does not have permission to view this page.';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 | {{ $title }}</title>
    <style>
        :root {
            color-scheme: dark;
            --bg-a: #0f172a;
            --bg-b: #111827;
            --panel: rgba(15, 23, 42, 0.75);
            --line: rgba(148, 163, 184, 0.25);
            --text: #e2e8f0;
            --muted: #94a3b8;
            --accent: #22d3ee;
            --accent-2: #34d399;
            --warn-bg: rgba(251, 191, 36, 0.16);
            --warn-text: #fde68a;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
            color: var(--text);
            background: radial-gradient(circle at 10% 15%, #1d4ed8 0%, transparent 35%),
                        radial-gradient(circle at 90% 20%, #0d9488 0%, transparent 30%),
                        linear-gradient(135deg, var(--bg-a), var(--bg-b));
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .card {
            width: min(760px, 100%);
            border: 1px solid var(--line);
            background: var(--panel);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.45);
        }

        .kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border: 1px solid rgba(34, 211, 238, 0.35);
            color: #a5f3fc;
            background: rgba(34, 211, 238, 0.12);
        }

        h1 {
            margin: 16px 0 8px;
            font-size: clamp(1.7rem, 3vw, 2.3rem);
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .reason {
            margin-top: 18px;
            border: 1px solid rgba(251, 191, 36, 0.35);
            background: var(--warn-bg);
            color: var(--warn-text);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 14px;
        }

        .code {
            margin-top: 20px;
            font-size: 13px;
            color: var(--muted);
        }

        .actions {
            margin-top: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 14px;
            border: 1px solid transparent;
            transition: 180ms ease;
        }

        .btn-primary {
            color: #06283d;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
        }

        .btn-primary:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        .btn-secondary {
            color: var(--text);
            border-color: var(--line);
            background: rgba(30, 41, 59, 0.55);
        }

        .btn-secondary:hover {
            border-color: rgba(148, 163, 184, 0.45);
            background: rgba(51, 65, 85, 0.6);
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="kicker">Error 403</span>
        <h1>{{ $title }}</h1>
        <p>{{ $subtitle }}</p>

        <div class="reason">
            <strong>Reason:</strong> {{ $reason }}
        </div>

        <div class="code">If you believe this is incorrect, contact your tenant administrator.</div>

        <div class="actions">
            <a class="btn btn-primary" href="{{ route('login', [], false) }}">Go to Login</a>
            <a class="btn btn-secondary" href="{{ url('/') }}">Try Homepage</a>
        </div>
    </main>
</body>
</html>
