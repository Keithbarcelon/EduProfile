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

        if (! $user || ! $user->school) {
            return $next($request);
        }

        $tenant = $user->school;

        if (! $tenant->is_enabled) {
            return $this->logoutAndBlock(
                $request,
                'Your tenant account is currently deactivated. Reason: '.$tenant->disabledReasonForUsers()
            );
        }

        if ($tenant->isSubscriptionExpired()) {
            // Safety net: ensure central status reflects expiration even if scheduler has not run yet.
            $tenant->update([
                'is_enabled' => false,
                'disabled_at' => now(),
                'disable_reason' => 'Plan expired',
            ]);

            return $this->logoutAndBlock($request, 'Your tenant subscription has expired. Reason: Plan expired.');
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
