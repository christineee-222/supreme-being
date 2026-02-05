<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthTokenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are stateless and protected by WorkOS JWTs.
| All routes here are prefixed with /api automatically.
|
*/

/*
|--------------------------------------------------------------------------
| Auth / Token
|--------------------------------------------------------------------------
|
| Exchanges a valid WorkOS session for a JWT
|
*/

Route::post('/v1/token', [AuthTokenController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth.workos')->group(function () {

    Route::get('/v1/me', function (Request $request) {
        return response()->json([
            'data' => [
                'id'        => $request->user()->id,
                'email'     => $request->user()->email,
                'workos_id' => $request->user()->workos_id,
            ],
        ]);
    });

});


