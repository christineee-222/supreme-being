<?php

use App\Http\Controllers\Moderation\AdminAppealController;
use App\Http\Controllers\Moderation\AdminApplicationController;
use App\Http\Controllers\Moderation\AdminDecisionController;
use App\Http\Controllers\Moderation\AdminModeratorController;
use App\Http\Controllers\Moderation\AdminPerformanceReviewController;
use App\Http\Controllers\Moderation\AppealController;
use App\Http\Controllers\Moderation\ModReportController;
use App\Http\Controllers\Moderation\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Moderation Routes
|--------------------------------------------------------------------------
*/

// Moderator routes
Route::middleware(['auth', 'role:moderator,admin'])->prefix('mod')->name('mod.')->group(function () {
    Route::get('/reports', [ModReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [ModReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/assign', [ModReportController::class, 'assign'])->name('reports.assign');
    Route::post('/reports/{report}/resolve', [ModReportController::class, 'resolve'])->name('reports.resolve');
    Route::post('/reports/{report}/dismiss', [ModReportController::class, 'dismiss'])->name('reports.dismiss');
    Route::post('/reports/{report}/escalate', [ModReportController::class, 'escalate'])->name('reports.escalate');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/appeals', [AdminAppealController::class, 'index'])->name('appeals.index');
    Route::get('/appeals/{appeal}', [AdminAppealController::class, 'show'])->name('appeals.show');
    Route::post('/appeals/{appeal}/decide', [AdminAppealController::class, 'decide'])->name('appeals.decide');
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications/{application}/decide', [AdminApplicationController::class, 'decide'])->name('applications.decide');
    Route::get('/moderators', [AdminModeratorController::class, 'index'])->name('moderators.index');
    Route::get('/performance-reviews', [AdminPerformanceReviewController::class, 'index'])->name('performance.index');
    Route::post('/performance-reviews/{review}/decide', [AdminPerformanceReviewController::class, 'decide'])->name('performance.decide');
    Route::post('/decisions/{decision}/cosign', [AdminDecisionController::class, 'cosign'])->name('decisions.cosign');
});

// User-facing routes
Route::middleware(['auth'])->group(function () {
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::post('/appeals', [AppealController::class, 'store'])->name('appeals.store');
});
