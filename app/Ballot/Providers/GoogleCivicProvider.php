<?php

namespace App\Ballot\Providers;

final class GoogleCivicProvider implements BallotProvider
{
    public string $apiKey;

    public string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('ballot.google_civic.api_key', '');
        $this->baseUrl = (string) config(
            'ballot.google_civic.base_url',
            'https://www.googleapis.com/civicinfo/v2'
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function lookup(string $address): array
    {
        // Guard: never call external API if key isn't configured
        if ($this->apiKey === '') {
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
                    'config' => [
                        'has_api_key' => false,
                        'base_url' => $this->baseUrl,
                    ],
                    'input' => [
                        'address' => $address,
                    ],
                ],
            ];
        }

        // Still stubbed — real API call comes later
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
                'config' => [
                    'has_api_key' => true,
                    'base_url' => $this->baseUrl,
                ],
                'input' => [
                    'address' => $address,
                ],
            ],
        ];
    }
}



