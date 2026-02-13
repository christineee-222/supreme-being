<?php

namespace App\Ballot\Providers;

interface BallotProvider
{
    /**
     * @return array<string, mixed>
     */
    public function lookup(string $address): array;
}
