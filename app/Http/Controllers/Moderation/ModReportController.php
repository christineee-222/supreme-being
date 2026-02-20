<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Requests\DismissReportRequest;
use App\Http\Requests\ResolveReportRequest;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ModReportController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Report::class);

        $reports = Report::with(['reporter', 'reportedUser', 'assignedTo'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Mod/Reports/Index', [
            'reports' => $reports,
        ]);
    }

    public function show(Report $report): Response
    {
        $this->authorize('viewAny', Report::class);

        $report->load(['reporter', 'reportedUser', 'assignedTo', 'resolvedBy']);

        return Inertia::render('Mod/Reports/Show', [
            'report' => $report,
        ]);
    }

    public function assign(Report $report, ReportService $reportService): RedirectResponse
    {
        $this->authorize('assign', $report);

        $reportService->assignReport($report, request()->user());

        return back()->with('success', 'Report assigned.');
    }

    public function resolve(ResolveReportRequest $request, Report $report, ReportService $reportService): RedirectResponse
    {
        $this->authorize('resolve', $report);

        $reportService->resolveReport(
            $report,
            $request->user(),
            $request->validated('resolution'),
            $request->validated('resolution_note'),
            $request->validated('rule_reference', ''),
            $request->validated('moderator_note', '')
        );

        return back()->with('success', 'Report resolved.');
    }

    public function dismiss(DismissReportRequest $request, Report $report, ReportService $reportService): RedirectResponse
    {
        $this->authorize('resolve', $report);

        $reportService->dismissReport(
            $report,
            $request->user(),
            $request->validated('resolution_note')
        );

        return back()->with('success', 'Report dismissed.');
    }

    public function escalate(Report $report, ReportService $reportService): RedirectResponse
    {
        $this->authorize('escalate', $report);

        $reportService->escalateReport($report, request()->user());

        return back()->with('success', 'Report escalated.');
    }
}
