<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Throwable;

class AuthenticateWorkOS
{
    public function handle(Request $request, Closure $next)
    {
        // --------------------------------------------------
        // 1. Header Extraction
        // --------------------------------------------------
        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('AUTH_HEADER_MISSING');
        }

        $token = substr($authHeader, 7);

        // --------------------------------------------------
        // 2. Cryptographic Validation
        // --------------------------------------------------
        try {
            $decoded = $this->decodeJwt($token);
        } catch (Throwable $e) {
            return $this->unauthorized('INVALID_TOKEN');
        }

        // --------------------------------------------------
        // 3. Claims Verification
        // --------------------------------------------------
        if (
            config('auth.require_verified_email', true) &&
            empty($decoded->email_verified)
        ) {
            return $this->forbidden('EMAIL_NOT_VERIFIED');
        }

        // Optional: org / tenant enforcement
        // Example: route parameter {org}
        if ($request->route('org')) {
            $orgId = $request->route('org');

            if (
                empty($decoded->org_id) ||
                $decoded->org_id !== $orgId
            ) {
                return $this->forbidden('ORG_ACCESS_DENIED');
            }
        }

        // --------------------------------------------------
        // 4. User Resolution (JIT Provisioning)
        // --------------------------------------------------
        $user = User::firstOrCreate(
            ['workos_id' => $decoded->sub],
            [
                'email' => $decoded->email ?? null,
                'name'  => $decoded->name ?? null,
            ]
        );

        // --------------------------------------------------
        // 5. Inject User into Request Lifecycle
        // --------------------------------------------------
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    // ======================================================
    // Helpers
    // ======================================================

    protected function decodeJwt(string $token): object
    {
        $jwks = cache()->remember(
            'workos.jwks',
            now()->addHour(),
            fn () => json_decode(
                file_get_contents(config('services.workos.jwks_url')),
                true
            )
        );

        return JWT::decode(
            $token,
            JWK::parseKeySet($jwks),
            ['RS256']
        );
    }

    protected function unauthorized(string $code)
    {
        return response()->json([
            'error' => $code,
        ], Response::HTTP_UNAUTHORIZED);
    }

    protected function forbidden(string $code)
    {
        return response()->json([
            'error' => $code,
        ], Response::HTTP_FORBIDDEN);
    }
}
