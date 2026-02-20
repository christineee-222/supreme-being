<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Requests\DecideAppealRequest;
use App\Models\Appeal;
use App\Services\AppealService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminAppealController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Appeal::class);

        $appeals = Appeal::with('user')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Appeals/Index', [
            'appeals' => $appeals,
        ]);
    }

    public function show(Appeal $appeal): Response
    {
        $this->authorize('viewAny', Appeal::class);

        $appeal->load(['user', 'reviewedBy']);

        return Inertia::render('Admin/Appeals/Show', [
            'appeal' => $appeal,
        ]);
    }

    public function decide(DecideAppealRequest $request, Appeal $appeal, AppealService $appealService): RedirectResponse
    {
        $this->authorize('decide', $appeal);

        $appealService->decideAppeal(
            $appeal,
            $request->user(),
            $request->validated('decision'),
            $request->validated('admin_decision_note')
        );

        return back()->with('success', 'Appeal decided.');
    }
}
