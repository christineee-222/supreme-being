<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\ModeratorDecision;
use App\Services\ViolationService;
use Illuminate\Http\RedirectResponse;

class AdminDecisionController extends Controller
{
    public function cosign(ModeratorDecision $decision, ViolationService $violationService): RedirectResponse
    {
        $this->authorize('cosign', $decision);

        $violationService->cosignDecision($decision, request()->user());

        return back()->with('success', 'Decision cosigned.');
    }
}
