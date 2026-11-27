<?php

namespace App\Integrations\RestApi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/** Issues and revokes Sanctum personal access tokens. */
class AuthController extends Controller
{
    /** POST /api/v1/auth/token — exchange email + password for a bearer token. */
    public function token(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['These credentials do not match our records.']]);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Verify your email address before using the API.'], 403);
        }

        // A password alone must not bypass two-factor authentication.
        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'message' => 'This account uses two-factor authentication. Create a token from the API Tokens page on the website instead.',
            ], 403);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'api-client');

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $this->userData($user),
        ], 201);
    }

    /** GET /api/v1/auth/me — the account the token belongs to. */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->userData($request->user())]);
    }

    /** POST /api/v1/auth/logout — revoke the token used for this request. */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Token revoked.']);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role_label,
            'store_name' => $user->isSeller() ? $user->store_display_name : null,
            'seller_status' => $user->seller_status,
        ];
    }
}
