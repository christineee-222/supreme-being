<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Moderation Routes
|--------------------------------------------------------------------------
|
| All moderation-related routes live here.
|
*/

// Moderator routes
Route::middleware(['auth', 'role:moderator,admin'])
    ->prefix('mod')
    ->name('mod.')
    ->group(function () {
        // To be implemented
    });

// Admin routes
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // To be implemented
    });

// User moderation actions
Route::middleware(['auth'])
    ->group(function () {
        // report submission, appeals, etc.
    });
