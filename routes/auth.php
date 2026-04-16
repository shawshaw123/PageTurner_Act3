<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

// ─── Guest Routes ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Password Reset
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
});

// ─── 2FA Challenge Routes (no auth middleware, session-based) ────
Route::get('two-factor-challenge', [TwoFactorController::class, 'challenge'])
            ->name('two-factor.challenge');

Route::post('two-factor-challenge', [TwoFactorController::class, 'verify'])
            ->name('two-factor.verify');

Route::post('two-factor-challenge/send', [TwoFactorController::class, 'sendCode'])
            ->name('two-factor.send');

// ─── Authenticated Routes ────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');

    // Email Verification
    Route::get('email/verify', [EmailVerificationController::class, 'notice'])
                ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    // 2FA Settings (requires verified email)
    Route::middleware('verified')->group(function () {
        Route::get('two-factor', [TwoFactorController::class, 'index'])
                    ->name('two-factor.index');

        Route::post('two-factor/enable', [TwoFactorController::class, 'enable'])
                    ->name('two-factor.enable');

        Route::delete('two-factor/disable', [TwoFactorController::class, 'disable'])
                    ->name('two-factor.disable');

        Route::post('two-factor/regenerate', [TwoFactorController::class, 'regenerateCodes'])
                    ->name('two-factor.regenerate');
    });
});
