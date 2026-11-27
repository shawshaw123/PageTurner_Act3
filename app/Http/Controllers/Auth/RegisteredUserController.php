<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'account_type' => ['required', 'in:buyer,seller'],
            'store_name' => ['required_if:account_type,seller', 'nullable', 'string', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:1000'],
        ]);

        $isSeller = $request->account_type === 'seller';

        // Role and seller status are set here, never taken from the request.
        // New sellers wait for an administrator to approve them.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $isSeller ? 'seller' : 'customer',
            'store_name' => $isSeller ? $request->store_name : null,
            'store_description' => $isSeller ? $request->store_description : null,
            'seller_status' => $isSeller ? 'pending' : null,
        ]);

        // Fire Registered event which triggers email verification
        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
