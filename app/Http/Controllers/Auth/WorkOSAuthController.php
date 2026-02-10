<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\WorkOS\Facades\WorkOS;
use App\Http\Controllers\Controller;
use App\Models\User;

class WorkOSAuthController extends Controller
{
    /**
     * Redirect the user to WorkOS.
     */
    public function redirect()
    {
        return redirect()->away(
            WorkOS::userManagement()->getAuthorizationUrl([
                'provider' => 'authkit',
            ])
        );
    }

    /**
     * Handle the WorkOS callback.
     */
    public function callback(Request $request)
    {
        $profile = WorkOS::userManagement()->authenticateWithCode(
            $request->get('code')
        );

        $user = User::firstOrCreate(
            ['workos_id' => $profile->id],
            [
                'email' => $profile->email,
                'name'  => $profile->firstName.' '.$profile->lastName,
            ]
        );

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}

