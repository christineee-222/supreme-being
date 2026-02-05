<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class AuthTokenController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $payload = [
            'sub'       => $user->id,
            'workos_id' => $user->workos_id,
            'email'     => $user->email,
            'iat'       => now()->timestamp,
            'exp'       => now()->addHour()->timestamp,
            'iss'       => config('app.url'),
        ];

        $jwt = JWT::encode(
            $payload,
            config('app.key'),
            'HS256'
        );

        return response()->json([
            'token' => $jwt,
            'type'  => 'Bearer',
            'expires_in' => 3600,
        ]);
    }
}
