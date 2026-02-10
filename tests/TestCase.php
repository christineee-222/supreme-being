<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function withoutWorkosAuth(): static
    {
        // This string must match what your routes use: Route::middleware('auth.workos')
        return $this->withoutMiddleware(\App\Http\Middleware\AuthWorkOS::class);
    }
}
