<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AuthTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'error' => 'UNAUTHENTICATED_SESSION',
            ], 401);
        }

        $privateKeyPath = storage_path('oauth/workos-private.key');

        if (! file_exists($privateKeyPath)) {
            return response()->json([
                'error' => 'PRIVATE_KEY_NOT_FOUND',
            ], 500);
        }

        $ttlSeconds = (int) (config('services.workos.jwt_ttl_seconds') ?? 3600);

        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->workos_id,
            'aud' => config('services.workos.client_id'),
            'iat' => time(),
            'exp' => time() + $ttlSeconds,
            'email' => $user->email,
        ];

        try {
            $privateKey = file_get_contents($privateKeyPath);
            $jwt = JWT::encode($payload, $privateKey, 'RS256');
        } catch (Throwable $e) {
            $body = ['error' => 'TOKEN_MINT_FAILED'];

            if (config('app.debug')) {
                $body['debug'] = $e->getMessage();
            }

            return response()->json($body, 500);
        }

        return response()->json([
            'token' => $jwt,
        ]);
    }
}
