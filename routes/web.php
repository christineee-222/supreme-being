<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

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
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    /**ValidateSessionWithWorkOS::class,*/
])->group(function () {

    Route::get('dashboard', fn () => Inertia::render('dashboard'))
        ->name('dashboard');
    Route::get('/events/{event}', [EventController::class, 'show'])
    ->name('events.show');


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


