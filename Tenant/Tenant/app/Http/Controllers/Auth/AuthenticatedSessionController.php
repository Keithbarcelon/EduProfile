<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $hubDomain = $this->loginHubDomain();

        if ($this->shouldAllowCrossTenantLogin() && $hubDomain !== null && ! $this->hostsMatch(strtolower($request->getHost()), $hubDomain)) {
            return redirect()->away($this->loginHubUrl($request, $hubDomain));
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $this->ensureCurrentTenantCanLogin($request);

        try {
            $request->authenticate();
            $request->session()->regenerate();
            $request->session()->forget('url.intended');

            return $this->redirectByRole($request->user()?->role);
        } catch (ValidationException $exception) {
            if (! $this->shouldAllowCrossTenantLogin()) {
                throw $exception;
            }

            $transferRedirect = $this->attemptCrossTenantLogin($request);

            if ($transferRedirect !== null) {
                return $transferRedirect;
            }

            throw $exception;
        }
    }

    /**
     * Complete a one-time login transfer after cross-tenant credential validation.
     */
    public function transferLogin(Request $request): RedirectResponse
    {
        if (! $this->shouldAllowCrossTenantLogin()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Cross-tenant login transfer is disabled for this environment.',
            ]);
        }

        $token = (string) $request->query('token', '');

        if ($token === '') {
            return redirect()->route('login')->withErrors(['email' => 'Invalid login transfer token.']);
        }

        $payload = Cache::pull($this->transferCacheKey($token));

        if (! is_array($payload)) {
            return redirect()->route('login')->withErrors(['email' => 'Login transfer expired. Please try again.']);
        }

        $currentHost = strtolower($request->getHost());
        $targetDomain = strtolower((string) ($payload['tenant_domain'] ?? ''));

        if (! $this->hostsMatch($currentHost, $targetDomain)) {
            return redirect()->route('login')->withErrors(['email' => 'Login transfer host mismatch.']);
        }

        $user = User::query()
            ->where('id', (int) ($payload['user_id'] ?? 0))
            ->where('email', (string) ($payload['email'] ?? ''))
            ->first();

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Account not found for login transfer.']);
        }

        Auth::login($user, (bool) ($payload['remember'] ?? false));
        $request->session()->regenerate();

        return $this->redirectByRole($user->role);
    }

    private function attemptCrossTenantLogin(LoginRequest $request): ?RedirectResponse
    {
        $email = Str::lower((string) $request->input('email'));
        $password = (string) $request->input('password');

        if ($email === '' || $password === '') {
            return null;
        }

        $schools = DB::connection('central')
            ->table('schools')
            ->whereNotNull('tenant_domain')
            ->whereNotNull('tenant_database')
            ->select('tenant_domain', 'tenant_database', 'is_enabled', 'plan_due_at', 'disable_reason')
            ->get();

        foreach ($schools as $school) {
            $candidate = $this->findTenantUserByEmail((string) $school->tenant_database, $email);

            if (! $candidate || ! Hash::check($password, (string) $candidate->password)) {
                continue;
            }

            $planDueAt = isset($school->plan_due_at) && $school->plan_due_at !== null
                ? Carbon::parse((string) $school->plan_due_at)
                : null;

            $subscriptionExpired = $planDueAt?->isPast() ?? false;
            $tenantEnabled = (bool) ($school->is_enabled ?? false);

            if ($subscriptionExpired) {
                throw ValidationException::withMessages([
                    'email' => 'This tenant subscription has expired. Please contact support to renew your account.',
                ]);
            }

            if (! $tenantEnabled) {
                $reason = trim((string) ($school->disable_reason ?? ''));

                throw ValidationException::withMessages([
                    'email' => 'This tenant account is currently disabled. Reason: '.($reason !== '' ? $reason : 'Tenant access was disabled by the administrator.').
                        ' Please contact support to reactivate your account.',
                ]);
            }

            $currentHost = strtolower($request->getHost());
            $targetDomain = strtolower((string) $school->tenant_domain);
            $destinationHost = $this->resolveDestinationHost(strtolower($request->getHost()), $targetDomain);

            if ($this->hostsMatch($currentHost, $targetDomain)) {
                // Account belongs to current tenant host; authenticate locally.
                Auth::loginUsingId((int) $candidate->id, $request->boolean('remember'));
                $request->session()->regenerate();

                return $this->redirectByRole((string) $candidate->role);
            }

            // Issue one-time login transfer token for the destination tenant host.
            $token = Str::random(64);
            Cache::put($this->transferCacheKey($token), [
                'user_id' => (int) $candidate->id,
                'email' => $email,
                'tenant_domain' => $destinationHost,
                'remember' => $request->boolean('remember'),
            ], now()->addMinutes(2));

            $port = (int) env('TENANT_LOCAL_PORT', 8001);
            $scheme = app()->environment('local') ? 'http' : 'https';
            $destination = app()->environment('local')
                ? sprintf('%s://%s:%d/auth/transfer-login?token=%s', $scheme, $destinationHost, $port, $token)
                : sprintf('%s://%s/auth/transfer-login?token=%s', $scheme, $destinationHost, $token);

            return redirect()->away($destination);
        }

        return null;
    }

    private function findTenantUserByEmail(string $tenantDatabase, string $email): ?object
    {
        $connection = config('database.connections.mysql');
        $connection['database'] = $tenantDatabase;

        config(['database.connections.tenant_probe' => $connection]);
        DB::purge('tenant_probe');

        try {
            return DB::connection('tenant_probe')
                ->table('users')
                ->select('id', 'email', 'password', 'role')
                ->where('email', $email)
                ->first();
        } catch (QueryException $exception) {
            // Skip tenants that are not fully provisioned yet (e.g. missing users table).
            return null;
        }
    }

    private function hostsMatch(string $hostA, string $hostB): bool
    {
        if ($hostA === $hostB) {
            return true;
        }

        // Treat .local and .localhost as aliases in local development.
        if (str_ends_with($hostA, '.localhost') && preg_replace('/\.localhost$/', '.local', $hostA) === $hostB) {
            return true;
        }

        if (str_ends_with($hostA, '.local') && preg_replace('/\.local$/', '.localhost', $hostA) === $hostB) {
            return true;
        }

        return false;
    }

    private function transferCacheKey(string $token): string
    {
        return 'tenant_login_transfer:'.$token;
    }

    private function loginHubDomain(): ?string
    {
        $hub = strtolower(trim((string) env('LOGIN_HUB_DOMAIN', '')));

        return $hub !== '' ? $hub : null;
    }

    private function loginHubUrl(Request $request, string $hubDomain): string
    {
        $scheme = app()->environment('local') ? 'http' : $request->getScheme();
        $port = (int) env('TENANT_LOCAL_PORT', 8001);

        if (app()->environment('local')) {
            return sprintf('%s://%s:%d/login', $scheme, $hubDomain, $port);
        }

        return sprintf('%s://%s/login', $scheme, $hubDomain);
    }

    private function resolveDestinationHost(string $requestHost, string $targetDomain): string
    {
        if (! app()->environment('local')) {
            return $targetDomain;
        }

        if (str_ends_with($requestHost, '.localhost') && str_ends_with($targetDomain, '.local')) {
            return preg_replace('/\.local$/', '.localhost', $targetDomain) ?: $targetDomain;
        }

        if (str_ends_with($requestHost, '.local') && str_ends_with($targetDomain, '.localhost')) {
            return preg_replace('/\.localhost$/', '.local', $targetDomain) ?: $targetDomain;
        }

        return $targetDomain;
    }

    private function shouldAllowCrossTenantLogin(): bool
    {
        return filter_var(env('ALLOW_CROSS_TENANT_LOGIN', false), FILTER_VALIDATE_BOOL);
    }

    private function ensureCurrentTenantCanLogin(Request $request): void
    {
        $host = strtolower($request->getHost());

        $candidateDomains = array_unique(array_filter([
            $host,
            preg_replace('/^www\./', '', $host) ?: null,
            str_ends_with($host, '.localhost') ? preg_replace('/\.localhost$/', '.local', $host) : null,
            str_ends_with($host, '.local') ? preg_replace('/\.local$/', '.localhost', $host) : null,
        ]));

        $school = DB::connection('central')
            ->table('schools')
            ->whereIn('tenant_domain', $candidateDomains)
            ->whereNotNull('tenant_database')
            ->select('is_enabled', 'plan_due_at', 'disable_reason')
            ->first();

        if (! $school) {
            return;
        }

        if ($school->plan_due_at && Carbon::parse((string) $school->plan_due_at)->isPast()) {
            throw ValidationException::withMessages([
                'email' => 'This tenant subscription has expired. Reason: Plan expired. Please contact support to renew your account.',
            ]);
        }

        if (! (bool) ($school->is_enabled ?? false)) {
            $reason = trim((string) ($school->disable_reason ?? ''));

            throw ValidationException::withMessages([
                'email' => 'This tenant account is currently disabled. Reason: '.($reason !== '' ? $reason : 'Tenant access was disabled by the administrator.').
                    ' Please contact support to reactivate your account.',
            ]);
        }
    }

    private function redirectByRole(?string $role): RedirectResponse
    {
        return match ($role) {
            'admin', 'tenant_admin' => redirect()->route('admin.dashboard'),
            'admission' => redirect()->route('admission.dashboard'),
            'department' => redirect()->route('department.dashboard'),
            'faculty' => redirect()->route('faculty.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => \Route::has('landing') ? redirect()->route('landing') : redirect('/'),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
