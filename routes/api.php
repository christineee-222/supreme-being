<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\BallotLookupController;
use App\Http\Controllers\Api\V1\EventCancelController;
use App\Http\Controllers\Api\V1\EventIndexController;
use App\Http\Controllers\Api\V1\EventRsvpDestroyController;
use App\Http\Controllers\Api\V1\EventRsvpStoreController;
use App\Http\Controllers\Api\V1\EventShowController;
use App\Http\Controllers\Api\V1\EventStoreController;
use App\Http\Controllers\Api\V1\EventUpdateController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\SanctumTokenController;
use App\Http\Controllers\Donations\CreateDonationCheckoutController;
use App\Http\Controllers\Mobile\MobileAuthExchangeController;
use Illuminate\Support\Facades\Route;

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
| Donations (Stripe Checkout)
|--------------------------------------------------------------------------
*/

Route::post('/donate/checkout', CreateDonationCheckoutController::class)
    ->name('donate.checkout');

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Ballot Lookup (public civic data)
    |--------------------------------------------------------------------------
    */

    Route::post('/ballot/lookup', BallotLookupController::class);

    /*
    |--------------------------------------------------------------------------
    | Mobile Auth: WorkOS code -> Sanctum PAT
    |--------------------------------------------------------------------------
    */

    Route::post('/mobile/token', [SanctumTokenController::class, 'store'])
        ->middleware('throttle:10,1');

    /*
    |--------------------------------------------------------------------------
    | Mobile Auth: one-time code -> JWT (DEPRECATED — use /mobile/token)
    |--------------------------------------------------------------------------
    */

    Route::post('/mobile/exchange', MobileAuthExchangeController::class);

    /*
    |--------------------------------------------------------------------------
    | Web session -> JWT (DEPRECATED — Sanctum handles session auth now)
    |--------------------------------------------------------------------------
    | Kept for backwards compatibility until confirmed unused.
    */

    Route::middleware('web')->group(function () {
        Route::post('/token', [AuthTokenController::class, 'store']);
        Route::post('/token/refresh', [AuthTokenController::class, 'refresh']);
    });

    /*
    |--------------------------------------------------------------------------
    | Authenticated API (Sanctum: session cookie OR Bearer token)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // Current user
        Route::get('/me', MeController::class);

        // Logout (revoke current PAT)
        Route::post('/logout', [SanctumTokenController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        */

        Route::get('/events', EventIndexController::class);
        Route::get('/events/{event:id}', EventShowController::class);
        Route::post('/events', EventStoreController::class);
        Route::patch('/events/{event:id}', EventUpdateController::class);

        // Domain-specific action (better than DELETE)
        Route::post('/events/{event:id}/cancel', EventCancelController::class);

        /*
        |--------------------------------------------------------------------------
        | RSVP
        |--------------------------------------------------------------------------
        | PUT = idempotent set/update RSVP
        | DELETE = remove RSVP
        */

        Route::put('/events/{event:id}/rsvp', EventRsvpStoreController::class);
        Route::delete('/events/{event:id}/rsvp', EventRsvpDestroyController::class);
    });
});
