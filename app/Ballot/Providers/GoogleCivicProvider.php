<?php

namespace App\Ballot\Providers;

final class GoogleCivicProvider implements BallotProvider
{
    /**
     * @return array<string, mixed>
     */
    public function lookup(string $address): array
    {
        // Stub only — real Google Civic API integration comes later
        return [
            'election' => [
                'id' => null,
                'name' => 'Stub Election',
                'date' => null,
            ],
            'jurisdiction' => [
                'state' => null,
                'county' => null,
                'locality' => null,
            ],
            'contests' => [],
            'sources' => [
                [
                    'label' => 'Google Civic (stub)',
                    'url' => null,
                ],
            ],
            'meta' => [
                'status' => 'provider_stub',
                'input' => [
                    'address' => $address,
                ],
            ],
        ];
    }
}
