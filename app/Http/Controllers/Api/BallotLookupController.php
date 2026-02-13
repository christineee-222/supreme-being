<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\BallotLookupRequest;
use Illuminate\Http\JsonResponse;

final class BallotLookupController
{
    public function __invoke(BallotLookupRequest $request): JsonResponse
    {
        $address = $request->validated('address');

        return response()->json([
            'election' => [
                'id' => null,
                'name' => null,
                'date' => null,
            ],
            'jurisdiction' => [
                'state' => null,
                'county' => null,
                'locality' => null,
            ],
            'contests' => [],
            'sources' => [],
            'meta' => [
                'status' => 'stub',
                'input' => [
                    'address' => $address,
                ],
            ],
        ]);
    }
}
