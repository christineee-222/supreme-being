<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use WorkOS\UserManagement;
use WorkOS\WorkOS;

final class SanctumTokenController extends Controller
{
    /**
     * Exchange a WorkOS authorization code for a Sanctum personal access token.
     *
     * POST /api/v1/mobile/token
     *
     * Body: { "code": "..." }
     * Returns: { "token": "...", "user": { "id": ..., "email": ..., "name": ... } }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'min:1'],
        ]);

        $code = $request->input('code');

        $clientId = (string) config('services.workos.client_id');
        $apiKey = (string) config('services.workos.api_key');

        if ($clientId === '' || $apiKey === '') {
            Log::error('SanctumToken: WorkOS credentials missing');

            return response()->json([
                'message' => 'Server configuration error.',
            ], 500);
        }

        WorkOS::setApiKey($apiKey);
        WorkOS::setClientId($clientId);

        try {
            $um = new UserManagement;
            $resp = $um->authenticateWithCode(
                clientId: $clientId,
                code: $code,
                ipAddress: $request->ip(),
                userAgent: (string) $request->userAgent(),
            );
        } catch (\Throwable $e) {
            Log::warning('SanctumToken: WorkOS code verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Invalid or expired authorization code.',
            ], 401);
        }

        $data = is_array($resp->raw ?? null) ? $resp->raw : [];
        $userData = $data['user'] ?? $data['profile'] ?? $data;

        $workosId = $userData['id'] ?? null;
        $email = $userData['email'] ?? null;

        $firstName = $userData['first_name'] ?? $userData['firstName'] ?? '';
        $lastName = $userData['last_name'] ?? $userData['lastName'] ?? '';
        $name = trim($firstName.' '.$lastName);

        if (! is_string($workosId) || $workosId === '' || ! is_string($email) || $email === '') {
            Log::error('SanctumToken: WorkOS response missing user id/email', [
                'keys' => array_keys($data),
            ]);

            return response()->json([
                'message' => 'Identity verification failed.',
            ], 422);
        }

        $derivedName = $name !== '' ? $name : $email;

        // Match by workos_id first, then fallback to email
        $user = User::query()->where('workos_id', $workosId)->first();

        if (! $user) {
            $user = User::query()->where('email', $email)->first();

            if ($user) {
                $user->workos_id = $workosId;
            }
        }

        if (! $user) {
            $user = new User;
            $user->workos_id = $workosId;
            $user->email = $email;
            $user->name = $derivedName;
            $user->save();
        } else {
            if ($user->workos_id !== $workosId) {
                $user->workos_id = $workosId;
            }

            if ($derivedName !== '' && $user->name !== $derivedName) {
                $user->name = $derivedName;
            }

            // Handle email changes safely
            if ($user->email !== $email) {
                $emailTaken = User::query()
                    ->where('email', $email)
                    ->where('id', '!=', $user->id)
                    ->exists();

                if (! $emailTaken) {
                    $user->email = $email;
                } else {
                    Log::warning('SanctumToken: email collision, not updating', [
                        'user_id' => $user->id,
                        'workos_email' => $email,
                    ]);
                }
            }

            if ($user->isDirty()) {
                $user->save();
            }
        }

        $token = $user->createToken('civic-mobile')->plainTextToken;

        Log::info('SanctumToken: PAT issued', [
            'user_id' => $user->id,
        ]);

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ],
        ]);
    }

    /**
     * Revoke the current access token.
     *
     * POST /api/v1/logout
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(null, 204);
    }
}
