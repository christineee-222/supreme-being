<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class ForumCommentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_comment_on_any_forum(): void
    {
        $admin = User::factory()->admin()->create();
        $forum = Forum::factory()->unpublished()->create();

        $response = $this->actingAs($admin)->post(
            route('forums.comments.store', $forum),
            ['body' => 'Admin comment']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'body' => 'Admin comment',
            'forum_id' => $forum->id,
            'user_id' => $admin->id,
        ]);
    }

    #[Test]
    public function regular_user_cannot_comment_on_unpublished_forum(): void
    {
        $user = User::factory()->create();
        $forum = Forum::factory()->unpublished()->create();

        $response = $this->actingAs($user)->post(
            route('forums.comments.store', $forum),
            ['body' => 'Blocked comment']
        );

        $response->assertForbidden();
        $this->assertDatabaseMissing('comments', [
            'body' => 'Blocked comment',
        ]);
    }

    #[Test]
    public function regular_user_can_comment_on_published_forum(): void
    {
        $user = User::factory()->create();
        $forum = Forum::factory()->published()->create();

        $response = $this->actingAs($user)->post(
            route('forums.comments.store', $forum),
            ['body' => 'Allowed comment']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'body' => 'Allowed comment',
            'forum_id' => $forum->id,
            'user_id' => $user->id,
        ]);
    }
}

