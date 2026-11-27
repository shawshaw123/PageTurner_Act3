<?php

namespace App\Integrations\GoogleSignIn;

use App\Http\Controllers\Auth\CompletesLogin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * IP-6: "Sign in with Google" through Laravel Socialite (OAuth 2.0).
 *
 *   GET /auth/google/redirect  -> Google's sign-in page
 *   GET /auth/google/callback  <- Google sends the buyer back with a code;
 *                                 Socialite exchanges it for the name and email.
 *
 * Accounts are linked by email: a Google address that matches an existing
 * account signs in to that account. A new address creates a buyer account.
 * Only email addresses Google has verified are accepted.
 */
class GoogleSignInController extends Controller
{
    use CompletesLogin;

    public static function isConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    public function redirect(): RedirectResponse
    {
        abort_unless(self::isConfigured(), 404);

        return Socialite::driver('google')->scopes(['openid', 'profile', 'email'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(self::isConfigured(), 404);

        if ($request->has('error')) {
            return redirect()->route('login')->with('error', 'Google sign-in was cancelled.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google sign-in failed', ['error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Google sign-in did not complete. Please try again.');
        }

        $email = Str::lower((string) $google->getEmail());
        $verified = (bool) ($google->user['email_verified'] ?? $google->user['verified_email'] ?? false);

        if ($email === '' || !$verified) {
            return redirect()->route('login')->with('error', 'Your Google account has no verified email address.');
        }

        $user = User::where('google_id', $google->getId())->first();

        if (!$user) {
            $user = User::where('email', $email)->first();

            if ($user && $user->google_id && $user->google_id !== $google->getId()) {
                return redirect()->route('login')->with('error', 'This email is already linked to a different Google account.');
            }

            if ($user) {
                // Link the existing account. Google has verified the address, so ours is verified too.
                $user->forceFill([
                    'google_id' => $google->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            } else {
                // New visitor: create a buyer account. They can set a password later with "Forgot password".
                $user = User::create([
                    'name' => $google->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'password' => Hash::make(Str::random(40)),
                    'role' => 'customer',
                ]);
                $user->forceFill(['google_id' => $google->getId(), 'email_verified_at' => now()])->save();
            }
        }

        \Illuminate\Support\Facades\Auth::login($user, true);

        return $this->completeLogin($request, $user, true);
    }
}
