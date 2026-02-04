<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

trait IsImmutableAfterCreation
{
    protected function isImmutable(): bool
    {
        return true;
    }
}
