<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use WorkOS\UserManagement;
use WorkOS\WorkOS;
use Inertia\Inertia;

final class WorkOSAuthController extends Controller
{
    /**
     * Configure WorkOS SDK from config (idempotent).
     *
     * @return array{0:string,1:string} [$clientId, $redirectUrl]
     */
    private function configureWorkOS(): array
    {
        $apiKey = (string) config('services.workos.api_key');
        $clientId = (string) config('services.workos.client_id');
        $redirectUrl = (string) config('services.workos.redirect_url');

        if ($apiKey === '') {
            throw new \RuntimeException('Missing WorkOS API key (services.workos.api_key / WORKOS_API_KEY).');
        }
        if ($clientId === '') {
            throw new \RuntimeException('Missing WorkOS client id (services.workos.client_id / WORKOS_CLIENT_ID).');
        }
        if ($redirectUrl === '') {
            throw new \RuntimeException('Missing WorkOS redirect url (services.workos.redirect_url / WORKOS_REDIRECT_URL).');
        }

        WorkOS::setApiKey($apiKey);
        WorkOS::setClientId($clientId);

        return [$clientId, $redirectUrl];
    }

    private function b64urlDecode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $pad = strlen($data) % 4;

        if ($pad) {
            $data .= str_repeat('=', 4 - $pad);
        }

        return base64_decode($data, true) ?: '';
    }

    private function buildUserManagementAuthorizeUrl(
        string $clientId,
        string $redirectUrl,
        string $state = ''
    ): string {
        $base = 'https://api.workos.com/user_management/authorize';

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'provider' => 'authkit',
            'scope' => 'openid email profile',
        ];

        if ($state !== '') {
            $params['state'] = $state;
        }

        return $base . '?' . http_build_query($params);
    }

    /**
     * Redirect to WorkOS login (web + mobile entrypoint)
     */
    public function redirect(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        Log::info('login route hit', [
            'auth_check' => Auth::check(),
            'has_session_cookie' => $request->hasCookie(config('session.cookie')),
            'session_cookie_name' => config('session.cookie'),
            'session_id' => session()->getId(),
            'host' => $request->getHost(),
            'intended' => session('url.intended'),
            'is_inertia' => $request->header('X-Inertia') ? true : false,
        ]);

        // Prevent redirect loop if already authenticated
        if (Auth::check()) {
            // If this was triggered by an Inertia request, force a full navigation
            if ($request->header('X-Inertia')) {
                return Inertia::location(route('dashboard'));
            }

            return redirect()->route('dashboard');
        }

        // If user navigated directly to /login, default intended to home
        if (! session()->has('url.intended')) {
            session(['url.intended' => route('home')]);
        }

        [$clientId, $redirectUrl] = $this->configureWorkOS();

        $state = (string) $request->query('state', '');

        $authUrl = $this->buildUserManagementAuthorizeUrl(
            clientId: $clientId,
            redirectUrl: $redirectUrl,
            state: $state,
        );

        Log::info('WorkOS redirect invoked (user_management/authorize)', [
            'redirect_url' => $redirectUrl,
            'state_len' => strlen($state),
            'intended' => session('url.intended'),
            'is_inertia' => $request->header('X-Inertia') ? true : false,
        ]);

        // ✅ KEY FIX: external redirects must be full-page navigations for Inertia
        if ($request->header('X-Inertia')) {
            return Inertia::location($authUrl);
        }

        return redirect()->away($authUrl);
    }

    /**
     * WorkOS OAuth callback handler
     */
    public function callback(Request $request): RedirectResponse
    {
        [$clientId] = $this->configureWorkOS();

        Log::info('===== WORKOS CALLBACK START =====', [
            'state_param' => $request->query('state'),
            'code_param' => $request->query('code'),
            'full_url' => $request->fullUrl(),
            'intended' => session('url.intended'),
        ]);

        $code = (string) $request->query('code', '');

        if ($code === '') {
            Log::warning('WorkOS callback missing code', [
                'full_url' => $request->fullUrl(),
            ]);

            // Missing code = failed/aborted OAuth callback; send user back to login.
            return redirect()
                ->route('login')
                ->with('error', 'Authentication failed. Please try again.');
        }

        Log::info('WorkOS authenticateWithCode input', [
            'client_id' => $clientId,
            'redirect_url' => config('services.workos.redirect_url'),
            'code_len' => strlen($code),
        ]);

        $um = new UserManagement();
        $resp = $um->authenticateWithCode(
            clientId: $clientId,
            code: $code,
            ipAddress: $request->ip(),
            userAgent: (string) $request->userAgent(),
        );

        $data = is_array($resp->raw ?? null) ? $resp->raw : [];
        $userData = $data['user'] ?? $data['profile'] ?? $data;

        $workosId = $userData['id'] ?? null;
        $email = $userData['email'] ?? null;

        $firstName = $userData['first_name'] ?? $userData['firstName'] ?? '';
        $lastName = $userData['last_name'] ?? $userData['lastName'] ?? '';
        $name = trim($firstName . ' ' . $lastName);

        if (! is_string($workosId) || $workosId === '' || ! is_string($email) || $email === '') {
            Log::error('WorkOS authenticateWithCode returned unexpected shape', [
                'keys' => array_keys($data),
                'user_keys' => is_array($userData) ? array_keys($userData) : gettype($userData),
            ]);

            abort(500, 'WorkOS auth response missing user id/email.');
        }

        $derivedName = $name !== '' ? $name : $email;

        /**
         * ✅ Better matching strategy:
         * 1) Try workos_id
         * 2) Fallback to email
         * 3) If found by email, link by setting workos_id
         * 4) Update name/email safely (avoid collisions)
         */
        $user = User::query()
            ->where('workos_id', $workosId)
            ->first();

        if (! $user) {
            $user = User::query()
                ->where('email', $email)
                ->first();

            if ($user) {
                // Link existing account to WorkOS automatically
                $user->workos_id = $workosId;
            }
        }

        if (! $user) {
            $user = new User();
            $user->workos_id = $workosId;
            $user->email = $email;
            $user->name = $derivedName;
            $user->save();
        } else {
            // Keep workos_id correct
            if ($user->workos_id !== $workosId) {
                $user->workos_id = $workosId;
            }

            // Update name if present / changed (non-destructive)
            if ($derivedName !== '' && $user->name !== $derivedName) {
                $user->name = $derivedName;
            }

            /**
             * Email-change edge case:
             * If WorkOS email differs from our stored email, update it ONLY if it won't collide.
             */
            if ($user->email !== $email) {
                $emailTaken = User::query()
                    ->where('email', $email)
                    ->where('id', '!=', $user->id)
                    ->exists();

                if ($emailTaken) {
                    Log::warning('WorkOS email differs but new email already belongs to another user; not updating email', [
                        'user_id' => $user->id,
                        'workos_id' => $workosId,
                        'current_email' => $user->email,
                        'workos_email' => $email,
                    ]);
                } else {
                    $user->email = $email;
                }
            }

            // Persist only if dirty
            if ($user->isDirty()) {
                $user->save();
            }
        }

        // Capture mobile session flag BEFORE regeneration
        $mobileReturnTo = session()->pull('mobile.return_to');

        Auth::login($user);
        $request->session()->regenerate();

        $isMobile = false;

        if ($mobileReturnTo !== null) {
            $isMobile = true;
            session(['mobile.return_to' => $mobileReturnTo]);
        } else {
            $stateRaw = (string) $request->query('state', '');

            if ($stateRaw !== '') {
                $decoded = $this->b64urlDecode($stateRaw);

                if ($decoded === '') {
                    $decoded = base64_decode($stateRaw, true) ?: '';
                }

                if ($decoded !== '') {
                    $stateData = json_decode($decoded, true);

                    if (is_array($stateData) && ! empty($stateData['is_mobile'])) {
                        $isMobile = true;

                        session([
                            'mobile.return_to' => $stateData['return_to'] ?? 'assemblyrequired://auth',
                            'mobile.state' => $stateData['mobile_state'] ?? '',
                        ]);
                    }
                }
            }
        }

        Log::info('WorkOS callback completed', [
            'user_id' => $user->id,
            'mobile_detected' => $isMobile,
            'mobile_return_to' => session('mobile.return_to'),
            'intended' => session('url.intended'),
        ]);

        return $isMobile
            ? redirect()->route('mobile.complete')
            : redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}








