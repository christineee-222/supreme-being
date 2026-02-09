<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\V1\EventIndexController;
use App\Http\Controllers\Api\V1\EventShowController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api
| API routes are stateless by default unless we explicitly add "web"
|
*/

/*
|--------------------------------------------------------------------------
| Auth / Token
|--------------------------------------------------------------------------
|
| Exchange a logged-in Laravel *web session* for a signed API JWT.
| This MUST use the "web" middleware so the session is available.
|
*/

Route::middleware('web')->post('/v1/token', [AuthTokenController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| Protected by WorkOS-signed JWTs via auth.workos middleware
|
*/

Route::middleware('auth.workos')->group(function () {

    Route::get('/v1/events', EventIndexController::class);

    Route::get('/v1/events/{event}', EventShowController::class);

    Route::get('/v1/me', function (Request $request) {
        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'email' => $request->user()->email,
                'workos_id' => $request->user()->workos_id,
            ],
        ]);
    });

});
