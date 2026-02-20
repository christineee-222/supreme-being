<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Requests\DecidePerformanceReviewRequest;
use App\Models\ModeratorPerformanceReview;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminPerformanceReviewController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', ModeratorPerformanceReview::class);

        $reviews = ModeratorPerformanceReview::with(['moderator', 'report'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/PerformanceReviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    public function decide(DecidePerformanceReviewRequest $request, ModeratorPerformanceReview $review): RedirectResponse
    {
        $this->authorize('decide', $review);

        $review->update([
            'status' => 'reviewed',
            'admin_outcome' => $request->validated('admin_outcome'),
            'admin_notes' => $request->validated('admin_notes'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Performance review decided.');
    }
}
