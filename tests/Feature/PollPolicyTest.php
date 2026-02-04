<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class PollPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_cannot_update_published_poll(): void
    {
        $user = User::factory()->create();

        $poll = Poll::factory()
            ->published()
            ->create([
                'user_id' => $user->id,
    ]);


        $this->assertFalse(
            Gate::forUser($user)->allows('update', $poll)
        );
    }
}

