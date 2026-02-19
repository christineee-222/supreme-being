<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MeController extends Controller
{
    public function __invoke(Request $request): JsonResource
    {
        $user = $request->user();

        return JsonResource::make([
            'id' => $user->id,
            'email' => $user->email,
            'workos_id' => $user->workos_id,
        ]);
    }
}
