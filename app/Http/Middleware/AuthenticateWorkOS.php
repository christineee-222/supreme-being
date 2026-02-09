<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateWorkOS
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'error' => 'MISSING_TOKEN',
            ], 401);
        }

        $token = trim(substr($header, 7));

        if ($token === '') {
            return response()->json([
                'error' => 'MISSING_TOKEN',
            ], 401);
        }

        $publicKeyPath = storage_path('oauth/workos-public.key');

        if (! file_exists($publicKeyPath)) {
            return response()->json([
                'error' => 'PUBLIC_KEY_NOT_FOUND',
            ], 500);
        }

        try {
            $publicKey = file_get_contents($publicKeyPath);
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (ExpiredException) {
            return response()->json([
                'error' => 'EXPIRED_TOKEN',
            ], 401);
        } catch (Throwable $e) {
            $payload = ['error' => 'INVALID_TOKEN'];

            if (config('app.debug')) {
                $payload['debug'] = $e->getMessage();
            }

            return response()->json($payload, 401);
        }

        if (! isset($decoded->sub) || ! is_string($decoded->sub) || $decoded->sub === '') {
            return response()->json([
                'error' => 'SUB_MISSING',
            ], 401);
        }

        $user = User::firstOrCreate(
            ['workos_id' => $decoded->sub],
            ['email' => isset($decoded->email) && is_string($decoded->email) ? $decoded->email : null]
        );

        $request->setUserResolver(fn (): User => $user);

        return $next($request);
    }
}
