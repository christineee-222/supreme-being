<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Symfony\Component\Uid\Uuid;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function withoutWorkosAuth(): static
    {
        // This string must match what your routes use: Route::middleware('auth.workos')
        return $this->withoutMiddleware(\App\Http\Middleware\AuthWorkOS::class);
    }

    /**
     * Assert a row exists in the database, converting UUID string values to binary.
     * Accepts UUID strings for any column — converts 36-char RFC4122 values to BINARY(16).
     */
    protected function assertDatabaseHasUuid(string $table, array $data): static
    {
        return $this->assertDatabaseHas($table, $this->convertUuidValues($data));
    }

    /**
     * Assert a row does NOT exist, converting UUID string values to binary.
     */
    protected function assertDatabaseMissingUuid(string $table, array $data): static
    {
        return $this->assertDatabaseMissing($table, $this->convertUuidValues($data));
    }

    /**
     * Convert RFC4122 UUID string values to BINARY(16) for database assertions.
     *
     * @return array<string, mixed>
     */
    private function convertUuidValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
                $data[$key] = Uuid::fromString($value)->toBinary();
            }
        }

        return $data;
    }
}
