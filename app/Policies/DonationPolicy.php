<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;
use App\Policies\Concerns\AllowsRoles;
use App\Policies\Concerns\IsImmutableAfterCreation;

final class DonationPolicy
{
    use AllowsRoles;
    use IsImmutableAfterCreation;

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Donation $donation): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Donation $donation): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Donation $donation): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Donation $donation): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Donation $donation): bool
    {
        return $this->isAdmin($user);
    }
}

