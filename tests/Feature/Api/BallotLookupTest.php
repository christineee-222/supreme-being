<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

final class BallotLookupTest extends TestCase
{
    public function test_ballot_lookup_returns_normalized_schema(): void
    {
        $response = $this->postJson('/api/v1/ballot/lookup', [
            'address' => '123 Main St, Portland, OR 97205',
        ]);

        $response->assertOk();

        $response->assertJsonStructure([
            'election' => ['id', 'name', 'date'],
            'jurisdiction' => ['state', 'county', 'locality'],
            'contests',
            'sources',
            'meta' => ['status', 'input' => ['address']],
        ]);

        $this->assertSame('stub', $response->json('meta.status'));
    }

    public function test_ballot_lookup_requires_address(): void
    {
        $response = $this->postJson('/api/v1/ballot/lookup', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
    }
}
