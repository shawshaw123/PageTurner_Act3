<?php

namespace App\Integrations\RestApi\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

/** Lets a signed-in user create and revoke their own REST API tokens. */
class ApiTokenController extends Controller
{
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens()->latest()->get();

        return view('api.tokens', compact('tokens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);

        $token = $request->user()->createToken($data['name']);

        // The plain-text token is only available now; it is stored hashed.
        return redirect()->route('api-tokens.index')->with('new_token', $token->plainTextToken);
    }

    public function destroy(Request $request, int $tokenId)
    {
        $request->user()->tokens()->whereKey($tokenId)->delete();

        return redirect()->route('api-tokens.index')->with('success', 'Token revoked. Apps using it can no longer access the API.');
    }
}
