<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\Donations\CreateDonationCheckoutController;
use App\Http\Controllers\Donations\StripeWebhookController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRsvpController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\LegislationController;
use App\Http\Controllers\Mobile\MobileAuthCompleteController;
use App\Http\Controllers\Mobile\MobileAuthStartController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PortraitController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::get('/topics/elections-101', fn () => Inertia::render('elections-101'))
    ->name('topics.elections-101');

Route::get('/topics/ballot-measures', fn () => Inertia::render('ballot-measures-101'))
    ->name('topics.ballot-measures');

Route::get('/ballot', fn () => Inertia::render('BallotLookup'))
    ->name('ballot');

Route::post('/donate/checkout', CreateDonationCheckoutController::class)
    ->name('donate.checkout');

Route::post('/stripe/webhook', StripeWebhookController::class)
    ->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| Mobile Auth Bridge (Public)
|--------------------------------------------------------------------------
|
| These endpoints are used by the iOS app to start the WorkOS flow and then
| complete it via deep-link back into the app.
|
*/

Route::get('/mobile/start', MobileAuthStartController::class)
    ->name('mobile.start');

Route::get('/mobile/complete', MobileAuthCompleteController::class)
    ->name('mobile.complete');

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
    | API Token Exchange (Web → JWT)
    */
    Route::post('/api/v1/token', [AuthTokenController::class, 'store']);

    /*
    | Primary Resources
    */
    Route::resource('polls', PollController::class)->only(['store', 'update', 'destroy']);
    Route::resource('donations', DonationController::class)->only(['store', 'update', 'destroy']);
    Route::resource('events', EventController::class)->only(['show', 'store', 'update', 'destroy']);
    Route::resource('forums', ForumController::class)->only(['store', 'update', 'destroy']);
    Route::resource('portraits', PortraitController::class)->only(['store', 'update', 'destroy']);
    Route::resource('legislations', LegislationController::class)->only(['store', 'update', 'destroy']);

    /*
    | Event RSVPs
    */
    Route::post('/events/{event}/rsvps', [EventRsvpController::class, 'store']);
    Route::patch('/events/{event}/rsvps/{rsvp}', [EventRsvpController::class, 'update']);
    Route::delete('/events/{event}/rsvps/{rsvp}', [EventRsvpController::class, 'destroy']);

    /*
    | Misc Actions
    */
    Route::post('polls/{poll}/vote', [PollController::class, 'vote']);

    Route::post('/forums/{forum}/comments', [CommentController::class, 'store'])
        ->name('forums.comments.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
