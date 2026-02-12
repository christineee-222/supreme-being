<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Http\Controllers\Mobile\MobileAuthStartController;
use App\Http\Controllers\Mobile\MobileAuthCompleteController;
use App\Http\Controllers\Auth\WorkOSAuthController;
use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRsvpController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\PortraitController;
use App\Http\Controllers\LegislationController;
use App\Http\Controllers\CommentController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

/*
|--------------------------------------------------------------------------
| WorkOS Authentication Routes (Web)
|--------------------------------------------------------------------------
|
| These are intentionally PUBLIC.
| They start the OAuth flow and receive the callback.
|
*/

Route::get('/login', [WorkOSAuthController::class, 'redirect'])->name('login');
Route::get('/auth/workos/callback', [WorkOSAuthController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| Mobile Auth Return Bridge (PUBLIC)
|--------------------------------------------------------------------------
|
| This handles the mobile login redirect safely without relying on
| shared browser cookies. It generates a one-time auth code that
| the iOS app exchanges for a JWT.
|
*/

Route::get('/mobile/start', MobileAuthStartController::class);
Route::get('/mobile/complete', MobileAuthCompleteController::class);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', fn () => Inertia::render('dashboard'))
        ->name('dashboard');

    Route::get('/events/{event}', [EventController::class, 'show'])
        ->name('events.show');

    /*
    |--------------------------------------------------------------------------
    | API Token Exchange (Session → JWT)
    |--------------------------------------------------------------------------
    |
    | Still used by web app, but mobile now uses mobile auth code.
    |
    */

    Route::post('/api/v1/token', [AuthTokenController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Primary Resources
    |--------------------------------------------------------------------------
    */

    Route::resource('polls', PollController::class)
        ->only(['store', 'update', 'destroy']);

    Route::resource('donations', DonationController::class)
        ->only(['store', 'update', 'destroy']);

    Route::resource('events', EventController::class)
        ->only(['show', 'store', 'update', 'destroy']);

    Route::resource('forums', ForumController::class)
        ->only(['store', 'update', 'destroy']);

    Route::resource('portraits', PortraitController::class)
        ->only(['store', 'update', 'destroy']);

    Route::resource('legislations', LegislationController::class)
        ->only(['store', 'update', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Event RSVPs (Nested Resource)
    |--------------------------------------------------------------------------
    */

    Route::post('/events/{event}/rsvps', [EventRsvpController::class, 'store']);
    Route::patch('/events/{event}/rsvps/{rsvp}', [EventRsvpController::class, 'update']);
    Route::delete('/events/{event}/rsvps/{rsvp}', [EventRsvpController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Misc Actions
    |--------------------------------------------------------------------------
    */

    Route::post('polls/{poll}/vote', [PollController::class, 'vote']);

    Route::post('/forums/{forum}/comments', [CommentController::class, 'store'])
        ->name('forums.comments.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';








