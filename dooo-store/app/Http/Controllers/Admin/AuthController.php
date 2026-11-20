<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        // Per-account lockout (on top of the per-IP route throttle)
        $key = 'admin-login:'.Str::lower($credentials['email']).'|'.$request->ip();
        $max = config('security.login_max_attempts', 5);
        if (RateLimiter::tooManyAttempts($key, $max)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            Audit::log('login.locked', ['email' => $credentials['email']]);
            throw ValidationException::withMessages([
                'email' => "Too many failed sign-in attempts. Please try again in {$minutes} ".Str::plural('minute', $minutes).'.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, config('security.login_lockout_minutes', 15) * 60);
            Audit::log('login.failed', ['email' => $credentials['email'], 'attempts' => RateLimiter::attempts($key)]);
            $left = $max - RateLimiter::attempts($key);
            throw ValidationException::withMessages([
                'email' => 'These details do not match our records.'.($left > 0 && $left <= 2 ? " {$left} ".Str::plural('attempt', $left).' left before this account is locked for '.config('security.login_lockout_minutes', 15).' minutes.' : ''),
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();
        Audit::log('login.success');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Audit::log('logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('ok', 'You have been signed out.');
    }
}
