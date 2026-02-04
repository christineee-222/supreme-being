<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class DonationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_cannot_update_donation(): void
    {
        $user = User::factory()->create();

        $donation = Donation::factory()->create();

        $this->assertFalse(
            Gate::forUser($user)->allows('update', $donation)
        );
    }
}
