<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;

class AuthTokenController extends Controller
{
    public function store(Request $request)
    {
        // 1. Must be logged in via web session
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // 2. Build JWT payload
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->workos_id,
            'aud' => config('services.workos.client_id'),
            'iat' => time(),
            'exp' => time() + (60 * 60), // 1 hour
        ];

        // 3. Sign with YOUR private key
        $privateKey = file_get_contents(storage_path('oauth/workos-private.key'));

        $jwt = JWT::encode($payload, $privateKey, 'RS256');

        return response()->json([
            'token' => $jwt,
        ]);
    }
}




