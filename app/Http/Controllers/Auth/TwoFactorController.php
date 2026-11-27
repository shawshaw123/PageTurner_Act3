<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\TwoFactorEnabledNotification;
use App\Notifications\TwoFactorDisabledNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA settings page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        return view('auth.two-factor-settings', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'recoveryCodes' => $user->getRecoveryCodes(),
        ]);
    }

    /**
     * Enable 2FA for the user.
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Generate a simple secret key
        $secret = Str::random(32);

        // Generate 8 recovery codes
        $recoveryCodes = collect(range(1, 8))->map(fn() => Str::random(10))->toArray();

        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);

        // Send notification
        $user->notify(new TwoFactorEnabledNotification());

        return redirect()->route('two-factor.index')
            ->with('success', '2FA has been enabled! Please save your recovery codes.')
            ->with('recoveryCodes', $recoveryCodes);
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        // Send notification
        $user->notify(new TwoFactorDisabledNotification());

        return redirect()->route('two-factor.index')
            ->with('success', '2FA has been disabled.');
    }

    /**
     * Show the 2FA challenge page during login.
     */
    public function challenge(): View
    {
        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the 2FA code during login.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required_without:recovery_code|nullable|string|size:6',
            'recovery_code' => 'required_without:code|nullable|string',
        ]);

        $userId = session('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $user = \App\Models\User::findOrFail($userId);

        // Try OTP code first
        if ($request->code) {
            $cachedCode = Cache::get("2fa_code_{$userId}");

            if (!$cachedCode || $cachedCode !== $request->code) {
                return back()->withErrors(['code' => 'Invalid or expired verification code.']);
            }

            // Clear the used code
            Cache::forget("2fa_code_{$userId}");
        }
        // Try recovery code
        elseif ($request->recovery_code) {
            $recoveryCodes = $user->getRecoveryCodes();
            $decryptedCodes = [];

            try {
                $decryptedCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?? [];
            } catch (\Exception $e) {
                $decryptedCodes = $recoveryCodes;
            }

            if (!in_array($request->recovery_code, $decryptedCodes)) {
                return back()->withErrors(['recovery_code' => 'Invalid recovery code.']);
            }

            // Remove the used recovery code
            $remainingCodes = array_values(array_diff($decryptedCodes, [$request->recovery_code]));
            $user->update([
                'two_factor_recovery_codes' => encrypt(json_encode($remainingCodes)),
            ]);
        }

        // Complete the login
        Auth::login($user, session('2fa:remember', false));

        // Clean up session
        session()->forget(['2fa:user:id', '2fa:remember']);
        $request->session()->regenerate();

        return redirect()->intended('/')->with('success', 'Logged in successfully!');
    }

    /**
     * Send a new OTP code via email.
     */
    public function sendCode(Request $request): RedirectResponse
    {
        $userId = session('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $user = \App\Models\User::findOrFail($userId);

        // Generate a 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in cache for 10 minutes
        Cache::put("2fa_code_{$userId}", $code, now()->addMinutes(10));

        // Send code via email
        Mail::raw("Your PageTurner 2FA verification code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('PageTurner - Two-Factor Authentication Code');
        });

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $recoveryCodes = collect(range(1, 8))->map(fn() => Str::random(10))->toArray();

        $request->user()->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return redirect()->route('two-factor.index')
            ->with('success', 'Recovery codes regenerated! Please save your new codes.')
            ->with('recoveryCodes', $recoveryCodes);
    }
}
