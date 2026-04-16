<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
     * Handle an incoming authentication request with rate limiting.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Rate limiting: max 5 attempts per minute
        $throttleKey = Str::transliterate(Str::lower($request->email) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
            ]);
        }

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user = Auth::user();

        // Check if 2FA is enabled
        if ($user->hasTwoFactorEnabled()) {
            // Logout the user temporarily
            Auth::logout();

            // Store user ID in session for 2FA challenge
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $request->boolean('remember'));

            // Generate and send OTP
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put("2fa_code_{$user->id}", $code, now()->addMinutes(10));

            Mail::raw("Your PageTurner 2FA verification code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('PageTurner - Two-Factor Authentication Code');
            });

            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        // Redirect based on role
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('user.dashboard'));
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
