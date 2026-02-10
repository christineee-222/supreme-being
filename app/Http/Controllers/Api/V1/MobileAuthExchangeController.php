<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\AuthTokenController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

final class MobileAuthExchangeController
{
    public function __invoke(Request $request, AuthTokenController $tokenController)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $code = $validated['code'];

        // Single-use: pull() removes it so it cannot be reused
        $userId = Cache::pull("mobile_auth_code:{$code}");

        if (! $userId) {
            return response()->json(['message' => 'Invalid or expired code.'], 401);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 401);
        }

        /**
         * Key trick:
         * Your AuthTokenController already knows how to mint the JWT.
         * We "log in" the user on the web guard for this request ONLY,
         * then call the same controller method to issue the token.
         */
        Auth::guard('web')->login($user);

        // Call existing issuer logic; assumes it uses auth()->user() to mint token.
        return $tokenController->store($request);
    }
}
