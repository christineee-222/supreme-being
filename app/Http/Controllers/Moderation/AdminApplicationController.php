<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Requests\DecideModeratorApplicationRequest;
use App\Models\ModeratorApplication;
use App\Notifications\ModeratorApplicationDecisionNotification;
use App\Services\ModerationEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminApplicationController extends Controller
{
    public function index(): Response
    {
        $this->authorize('decide', ModeratorApplication::class);

        $applications = ModeratorApplication::with('user')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Applications/Index', [
            'applications' => $applications,
        ]);
    }

    public function decide(
        DecideModeratorApplicationRequest $request,
        ModeratorApplication $application,
        ModerationEventService $eventService
    ): RedirectResponse {
        $this->authorize('decide', $application);

        DB::transaction(function () use ($request, $application, $eventService) {
            $application->update([
                'status' => $request->validated('status'),
                'reviewed_by' => $request->user()->id,
                'admin_notes' => $request->validated('admin_notes'),
                'decided_at' => now(),
            ]);

            if ($request->validated('status') === 'approved' && $application->user) {
                $application->user->update([
                    'role' => 'moderator',
                    'is_moderator_probationary' => true,
                ]);
            }

            $eventService->log(
                'moderator_application_decided',
                $request->user(),
                $application->user,
                ['status' => $request->validated('status')],
                application: $application
            );
        });

        if ($application->user) {
            $application->user->notify(new ModeratorApplicationDecisionNotification($application));
        }

        return back()->with('success', 'Application decided.');
    }
}
