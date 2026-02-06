<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class AuthenticateWorkOS
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        // 1. Header Extraction
        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'error' => 'AUTH_HEADER_MISSING',
            ], 401);
        }

        $token = substr($header, 7);

        // 2. Cryptographic Validation
        try {
            $publicKeyPath = storage_path('oauth/workos-public.key');

            if (! file_exists($publicKeyPath)) {
                return response()->json([
                    'error' => 'PUBLIC_KEY_NOT_FOUND',
                ], 500);
            }

            $publicKey = file_get_contents($publicKeyPath);

            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'INVALID_TOKEN',
                'debug' => $e->getMessage(),
            ], 401);
        }

        // 3. Minimal Claim Validation
        if (isset($decoded->exp) && $decoded->exp < time()) {
            return response()->json([
                'error' => 'TOKEN_EXPIRED',
            ], 401);
        }

        if (! isset($decoded->sub)) {
            return response()->json([
                'error' => 'SUB_MISSING',
            ], 401);
        }

        // 4. User Resolution
        $user = User::firstOrCreate(
            ['workos_id' => $decoded->sub],
            [
                'email' => $decoded->email ?? null,
            ]
        );

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}



