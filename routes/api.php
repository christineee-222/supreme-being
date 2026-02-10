<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\V1\EventCancelController;
use App\Http\Controllers\Api\V1\EventIndexController;
use App\Http\Controllers\Api\V1\EventShowController;
use App\Http\Controllers\Api\V1\EventStoreController;
use App\Http\Controllers\Api\V1\EventUpdateController;
use App\Http\Controllers\Api\V1\EventRsvpStoreController;
use App\Http\Controllers\Api\V1\EventRsvpDestroyController;
use App\Http\Controllers\Api\V1\MeController;
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
| Exchange a logged-in Laravel web session for a signed API JWT.
| Must use "web" middleware so session is available.
|
*/

Route::middleware('web')->post('/v1/token', [AuthTokenController::class, 'store']);
Route::middleware('web')->post('/v1/token/refresh', [AuthTokenController::class, 'refresh']);

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| Protected by WorkOS JWT via auth.workos middleware
|
*/

Route::middleware('auth.workos')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Identity
    |--------------------------------------------------------------------------
    */

    Route::get('/v1/me', MeController::class);

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    Route::get('/v1/events', EventIndexController::class);
    Route::get('/v1/events/{event}', EventShowController::class);
    Route::post('/v1/events', EventStoreController::class);
    Route::patch('/v1/events/{event}', EventUpdateController::class);
    Route::post('/v1/events/{event}/cancel', EventCancelController::class);

    /*
    |--------------------------------------------------------------------------
    | RSVP (mobile engagement loop)
    |--------------------------------------------------------------------------
    */

    Route::post('/v1/events/{event}/rsvp', EventRsvpStoreController::class);
    Route::delete('/v1/events/{event}/rsvp', EventRsvpDestroyController::class);
});

