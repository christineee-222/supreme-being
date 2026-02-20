<?php

use App\Services\ReportService;
use App\Services\ViolationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(ViolationService::class)->liftExpiredRestrictions())->hourly();
Schedule::call(fn () => app(ReportService::class)->returnStaleReports())->hourly();
