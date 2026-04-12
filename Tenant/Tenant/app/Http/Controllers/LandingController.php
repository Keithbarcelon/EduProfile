<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Show the tenant landing page.
     */
    public function index(): View|RedirectResponse
    {
        if (auth()->check()) {
            return $this->redirectAuthenticatedUser((string) auth()->user()->role);
        }

        $tenant = app('currentSchool');

        abort_unless($tenant !== null, 404, 'Tenant not found');

        return view('tenant.landing', [
            'tenant' => $tenant,
        ]);
    }

    private function redirectAuthenticatedUser(string $role): RedirectResponse
    {
        return match ($role) {
            'tenant_admin', 'admin' => redirect()->to('/admin/dashboard'),
            'admission' => redirect()->to('/admission/dashboard'),
            'department' => redirect()->to('/department/dashboard'),
            'faculty' => redirect()->to('/faculty/dashboard'),
            'student' => redirect()->to('/student/dashboard'),
            default => redirect()->to('/login'),
        };
    }
}
