<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     * Checks if the user has 2FA enabled and the session is not yet verified.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasTwoFactorEnabled() && !session()->has('2fa:verified')) {
            // If not on 2FA challenge pages, redirect there
            if (!$request->is('two-factor*')) {
                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }
}
