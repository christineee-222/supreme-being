<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppealRequest;
use App\Models\Appeal;
use App\Services\AppealService;
use Illuminate\Http\RedirectResponse;

class AppealController extends Controller
{
    public function store(StoreAppealRequest $request, AppealService $appealService): RedirectResponse
    {
        $this->authorize('create', Appeal::class);

        $appealService->submitAppeal(
            $request->user(),
            $request->validated('user_statement')
        );

        return back()->with('success', 'Appeal submitted.');
    }
}
