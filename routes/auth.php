<?php

use Illuminate\Support\Facades\Route;
use Laravel\WorkOS\Http\Requests\AuthKitAuthenticationRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLoginRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLogoutRequest;

/*
|--------------------------------------------------------------------------
| WorkOS Authentication Routes
|--------------------------------------------------------------------------
*/

/**
 * Step 1: Initiate login with WorkOS
 * GET /login
 */
Route::get('/login', function (AuthKitLoginRequest $request) {
    return $request->redirect();
})->middleware('guest')->name('login');

/**
 * Step 2: WorkOS OAuth callback
 * This MUST match WORKOS_REDIRECT_URI exactly
 * GET /auth/workos/callback
 */
Route::get('/auth/workos/callback', function (AuthKitAuthenticationRequest $request) {
    return tap(
        redirect()->intended(route('dashboard')),
        fn () => $request->authenticate()
    );
})->middleware('guest');

/**
 * Step 3: Logout
 */
Route::post('/logout', function (AuthKitLogoutRequest $request) {
    return $request->logout();
})->middleware('auth')->name('logout');

