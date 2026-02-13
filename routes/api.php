<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Mobile\MobileAuthExchangeController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\EventIndexController;
use App\Http\Controllers\Api\V1\EventShowController;
use App\Http\Controllers\Api\V1\EventStoreController;
use App\Http\Controllers\Api\V1\EventUpdateController;
use App\Http\Controllers\Api\V1\EventCancelController;
use App\Http\Controllers\Api\V1\EventRsvpStoreController;
use App\Http\Controllers\Api\V1\EventRsvpDestroyController;

/*
|--------------------------------------------------------------------------
| Health / Ping
|--------------------------------------------------------------------------
*/

Route::get('/ping', function () {
    return response()->json([
        'ok' => true,
        'message' => 'hello from Laravel',
        'ts' => now()->toISOString(),
    ]);
});

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Mobile Auth: one-time code -> JWT
    |--------------------------------------------------------------------------
    */

    Route::post('/mobile/exchange', MobileAuthExchangeController::class);

    /*
    |--------------------------------------------------------------------------
    | Web session -> JWT
    |--------------------------------------------------------------------------
    | Used by web frontend; mobile uses exchange endpoint above.
    */

    Route::middleware('web')->group(function () {
        Route::post('/token', [AuthTokenController::class, 'store']);
        Route::post('/token/refresh', [AuthTokenController::class, 'refresh']);
    });

    /*
    |--------------------------------------------------------------------------
    | Authenticated API (WorkOS JWT)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth.workos')->group(function () {

        // Current user
        Route::get('/me', MeController::class);

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        */

        Route::get('/events', EventIndexController::class);
        Route::get('/events/{event}', EventShowController::class);
        Route::post('/events', EventStoreController::class);
        Route::patch('/events/{event}', EventUpdateController::class);

        // Domain-specific action (better than DELETE)
        Route::post('/events/{event}/cancel', EventCancelController::class);

        /*
        |--------------------------------------------------------------------------
        | RSVP
        |--------------------------------------------------------------------------
        | PUT = idempotent set/update RSVP
        | DELETE = remove RSVP
        */

        Route::put('/events/{event}/rsvp', EventRsvpStoreController::class);
        Route::delete('/events/{event}/rsvp', EventRsvpDestroyController::class);
    });
});





