<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;

final class MobileAuthExchangeController extends Controller
{
    /**
     * Exchange a one-time auth code for a JWT.
     *
     * POST /api/v1/mobile/exchange
     * Body: { "code": "..." }
     * Returns: { "token": "eyJ..." }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $code = $request->input('code');

        if (! $code || strlen($code) !== 48) {
            Log::warning('Mobile exchange: invalid code format', [
                'code_length' => $code ? strlen($code) : 0,
            ]);

            return response()->json([
                'message' => 'Invalid or missing code.',
            ], 400);
        }

        // Retrieve user ID from cache
        $userId = Cache::pull("mobile_auth_code:{$code}");

        if (! $userId) {
            Log::warning('Mobile exchange: code not found or expired', [
                'code' => substr($code, 0, 8) . '...',
            ]);

            return response()->json([
                'message' => 'Code not found or expired.',
            ], 404);
        }

        // Load the user
        $user = \App\Models\User::find($userId);

        if (! $user) {
            Log::error('Mobile exchange: user not found', [
                'user_id' => $userId,
            ]);

            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Generate JWT
        $privateKeyPath = storage_path('oauth/workos-private.key');

        if (! file_exists($privateKeyPath)) {
            Log::error('Mobile exchange: private key file not found', [
                'path' => $privateKeyPath,
            ]);

            return response()->json([
                'message' => 'Server configuration error.',
            ], 500);
        }

        $privateKey = file_get_contents($privateKeyPath);
        $ttlSeconds = (int) (config('services.workos.jwt_ttl_seconds') ?? 3600);

        $payload = [
            'sub' => $user->workos_id,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + $ttlSeconds,
        ];

        $jwt = JWT::encode($payload, $privateKey, 'RS256');

        Log::info('Mobile exchange: JWT issued', [
            'user_id' => $user->id,
            'workos_id' => $user->workos_id,
        ]);

        return response()->json([
            'token' => $jwt,
        ]);
    }
}