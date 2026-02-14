<?php

namespace App\Ballot\Providers;

use Illuminate\Support\Facades\Cache;

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

        $normalizedAddress = strtolower(trim(preg_replace('/\s+/', ' ', $address)));
        $cacheKey = 'ballot_lookup_'.md5($normalizedAddress);

        $response = Cache::remember(
            $cacheKey,
            now()->addMinutes(config('ballot.cache_minutes', 30)),
            function () use ($address) {
                try {
                    return \Illuminate\Support\Facades\Http::baseUrl($this->baseUrl)
                        ->timeout(10)
                        ->acceptJson()
                        ->get('/voterinfo', [
                            'address' => $address,
                            'key' => $this->apiKey,
                        ]);
                } catch (\Throwable $e) {
                    return null;
                }
            }
        );

        if ($response === null) {
            return [
                'election' => [
                    'id' => null,
                    'name' => 'Google Civic unreachable',
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
                        'label' => 'Google Civic exception',
                        'url' => null,
                    ],
                ],
                'meta' => [
                    'status' => 'provider_exception',
                    'input' => [
                        'address' => $address,
                    ],
                ],
            ];
        }

        // HTTP failure guard
        if (! $response->ok()) {
            return [
                'election' => [
                    'id' => null,
                    'name' => 'Google Civic unavailable',
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
                        'label' => 'Google Civic error',
                        'url' => null,
                    ],
                ],
                'meta' => [
                    'status' => 'provider_http_error',
                    'http_status' => $response->status(),
                    'input' => [
                        'address' => $address,
                    ],
                ],
            ];
        }

        $data = $response->json();

        if (config('ballot.debug')) {
            logger()->debug('Google Civic raw response', [
                'address' => $address,
                'response' => $data,
            ]);
        }


        if (! is_array($data)) {
            $data = [];
        }

        $contests = [];

        if (! empty($data['contests']) && is_array($data['contests'])) {
            foreach ($data['contests'] as $contest) {
                $isMeasure = isset($contest['referendumTitle']) || isset($contest['referendumSubtitle']);

                $candidates = [];

                if (! $isMeasure && ! empty($contest['candidates']) && is_array($contest['candidates'])) {
                    foreach ($contest['candidates'] as $candidate) {
                        $candidates[] = [
                            'name' => $candidate['name'] ?? null,
                        ];
                    }
                }

                $contests[] = [
                    'type' => $isMeasure ? 'measure' : 'candidate',
                    'office' => $isMeasure ? null : ($contest['office'] ?? null),
                    'measure' => $isMeasure ? [
                        'title' => $contest['referendumTitle'] ?? null,
                        'subtitle' => $contest['referendumSubtitle'] ?? null,
                    ] : null,
                    'candidates' => $candidates,
                    'raw_type' => $contest['type'] ?? null,
                    'source' => 'google_civic',
                ];
            }
        }

        $state = null;

        if (preg_match('/,\s*([A-Z]{2})\s*\d{5}(-\d{4})?\s*$/', strtoupper($address), $matches) === 1) {
            $state = $matches[1];
        }

        return [
            'election' => [
                'id' => $data['election']['id'] ?? null,
                'name' => $data['election']['name'] ?? 'Google Civic (unparsed)',
                'date' => $data['election']['electionDay'] ?? null,
            ],
            'jurisdiction' => [
                'state' => $state,
                'county' => null,
                'locality' => null,
            ],
            'contests' => $contests,
            'sources' => [
                [
                    'label' => 'Google Civic voterinfo',
                    'url' => null,
                ],
            ],
            'meta' => [
                'status' => 'provider_unparsed',
                'http' => [
                    'ok' => $response->ok(),
                    'status' => $response->status(),
                ],
                'input' => [
                    'address' => $address,
                ],
            ],
        ];
    }
}
