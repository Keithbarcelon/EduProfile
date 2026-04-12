<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $role = $request->user()?->role;

        if ($role === 'developer') {
            if (\Route::has('developer.dashboard')) {
                return redirect()->intended(route('developer.dashboard', absolute: false));
            }
        }

        if ($role === 'admin') {
            if (\Route::has('admin.dashboard')) {
                return redirect()->intended(route('admin.dashboard', absolute: false));
            }

            if (\Route::has('dashboard')) {
                return redirect()->intended(route('dashboard', absolute: false));
            }
        }

        if ($role === 'faculty') {
            if (\Route::has('faculty.dashboard')) {
                return redirect()->intended(route('faculty.dashboard', absolute: false));
            }

            if (\Route::has('dashboard')) {
                return redirect()->intended(route('dashboard', absolute: false));
            }
        }

        if ($role === 'student') {
            if (\Route::has('student.dashboard')) {
                return redirect()->intended(route('student.dashboard', absolute: false));
            }

            if (\Route::has('dashboard')) {
                return redirect()->intended(route('dashboard', absolute: false));
            }
        }

        if (\Route::has('dashboard')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return redirect()->intended('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
