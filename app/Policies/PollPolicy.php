<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PollPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Poll $poll): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Poll $poll): bool
    {
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        return $poll->user_id === $user->id;
    }

    public function delete(User $user, Poll $poll): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $poll->user_id === $user->id;
    }

    public function comment(User $user, Poll $poll): bool
    {
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        return $poll->status === 'published';
    }

    public function vote(User $user, Poll $poll): bool
    {
        if ($user->isAdmin() || $user->isModerator()) {
            return true;
        }

        return $poll->status === 'published';
    }

    public function restore(User $user, Poll $poll): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Poll $poll): bool
    {
        return $user->isAdmin();
    }
}



