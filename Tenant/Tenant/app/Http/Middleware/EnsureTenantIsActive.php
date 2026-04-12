<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = $request->attributes->get('tenant');

        if (! $user) {
            return $next($request);
        }

        if (! $tenant) {
            return $next($request);
        }

        if (! $tenant->is_enabled) {
            return $this->logoutAndBlock(
                $request,
                'This tenant account is currently disabled. Reason: '.$tenant->disabledReasonForUsers()
            );
        }

        if ($tenant->isSubscriptionExpired()) {
            $tenant->update([
                'is_enabled' => false,
                'disabled_at' => now(),
                'disable_reason' => 'Plan expired',
            ]);

            return $this->logoutAndBlock($request, 'This tenant subscription has expired. Reason: Plan expired.');
        }

        if ((int) ($user->school_id ?? 0) !== (int) ($tenant->id ?? 0)) {
            return $this->logoutAndBlock($request, 'Your session does not belong to this tenant. Please sign in again.');
        }

        return $next($request);
    }

    private function logoutAndBlock(Request $request, string $message): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
