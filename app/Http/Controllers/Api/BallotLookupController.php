<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\BallotLookupRequest;
use App\Ballot\Providers\GoogleCivicProvider;
use Illuminate\Http\JsonResponse;

final class BallotLookupController
{
    public function __construct(
        public GoogleCivicProvider $provider,
    ) {
    }

    public function __invoke(BallotLookupRequest $request): JsonResponse
    {
        $address = $request->validated('address');

        return response()->json(
            $this->provider->lookup($address),
        );
    }
}

