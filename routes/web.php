<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use App\Http\Controllers\PollController;


Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware([
    'auth',
    ValidateSessionWithWorkOS::class,
])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::put('/polls/{poll}', [PollController::class, 'update']);
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
