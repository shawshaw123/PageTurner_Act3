<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * The last steps of signing in, shared by password login and Google sign-in:
 * the two-factor challenge when it is enabled, then the dashboard for the role.
 */
trait CompletesLogin
{
    /** Call after the user's identity is confirmed (password or Google). */
    protected function completeLogin(Request $request, User $user, bool $remember = false): RedirectResponse
    {
        if ($user->hasTwoFactorEnabled()) {
            Auth::logout();

            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $remember);

            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put("2fa_code_{$user->id}", $code, now()->addMinutes(10));

            Mail::raw("Your LEBROn 2FA verification code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
                $message->to($user->email)->subject('LEBROn - Two-Factor Authentication Code');
            });

            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isSeller()) {
            return redirect()->intended(route('seller.dashboard'));
        }

        return redirect()->intended(route('user.dashboard'));
    }
}
