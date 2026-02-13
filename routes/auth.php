<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\WorkOSAuthController;

/*
|--------------------------------------------------------------------------
| WorkOS Authentication Routes
|--------------------------------------------------------------------------
|
| IMPORTANT:
| - Redirect/start routes can be guest-only.
| - Callback MUST NOT be guest (or you can get 403 if a session already exists).
| - We use our controller so the mobile bridge "state" is preserved end-to-end.
|
*/

Route::get('/login', [WorkOSAuthController::class, 'redirect'])
    ->middleware('guest')
    ->name('login');

Route::get('/auth/workos/redirect', [WorkOSAuthController::class, 'redirect'])
    ->middleware('guest')
    ->name('workos.redirect');

// Callback MUST NOT be guest
Route::get('/auth/workos/callback', [WorkOSAuthController::class, 'callback'])
    ->name('workos.callback');

Route::post('/logout', [WorkOSAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');




