<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Policies\Concerns\AllowsRoles;
use App\Policies\Concerns\OwnsModel;

class CommentPolicy
{
    use AllowsRoles, OwnsModel;

    public function update(User $user, Comment $comment): bool
    {
        if ($this->isAdminOrModerator($user)) {
            return true;
        }

        return $this->owns($user, $comment);
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($this->isAdminOrModerator($user)) {
            return true;
        }

        return $this->owns($user, $comment);
    }
}



