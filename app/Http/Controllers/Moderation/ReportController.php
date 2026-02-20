<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public const REPORTABLE_TYPES = [
        'post' => \App\Models\Post::class,
        'comment' => \App\Models\Comment::class,
    ];

    public function store(StoreReportRequest $request, ReportService $reportService): RedirectResponse
    {
        $reportableType = $request->validated('reportable_type');
        abort_unless(isset(self::REPORTABLE_TYPES[$reportableType]), 404);

        $modelClass = self::REPORTABLE_TYPES[$reportableType];
        $reportable = $modelClass::findOrFail($request->validated('reportable_id'));

        $reportedUser = User::findOrFail($request->validated('reported_user_id'));

        $reportService->createReport(
            $request->user(),
            $reportedUser,
            $reportable,
            $request->validated('reason'),
            $request->validated('reporter_note')
        );

        return back()->with('success', 'Report submitted.');
    }
}
