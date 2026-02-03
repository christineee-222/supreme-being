<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use App\Http\Controllers\PollController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\PortraitController;
use App\Http\Controllers\LegislationController;


Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware([
    'auth',
    ValidateSessionWithWorkOS::class,
])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::resource('polls', PollController::class)
        ->only(['store', 'update', 'destroy']);
    
    Route::resource('donations', DonationController::class)
        ->only(['store', 'update', 'destroy']);
    
    Route::resource('events', EventController::class)
        ->only(['store', 'update', 'destroy']);
    
    Route::resource('forums', ForumController::class)
        ->only(['store', 'update', 'destroy']);
    
    Route::resource('portraits', PortraitController::class)
        ->only(['store', 'update', 'destroy']);
    
    Route::resource('legislations', LegislationController::class)
        ->only(['store', 'update', 'destroy']);
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
