<?php

namespace App\Services;

use App\Enums\ModeratorDecisionType;
use App\Enums\ReportResolution;
use App\Enums\ReportStatus;
use App\Events\ReportCreated;
use App\Models\ModeratorDecision;
use App\Models\ModeratorPerformanceReview;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ReportResolvedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class ReportService
{
    public function __construct(
        public ModerationEventService $eventService,
        public ViolationService $violationService
    ) {}

    public function createReport(User $reporter, User $reportedUser, Model $reportable, string $reason, ?string $note): Report
    {
        if ($reporter->id === $reportedUser->id) {
            throw new RuntimeException('You cannot report yourself.');
        }

        $key = 'report-rate:'.$reporter->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw new RuntimeException('Too many reports. Please try again later.');
        }
        RateLimiter::hit($key, 3600);

        $isAgainstModerator = $reportedUser->role === 'moderator';

        return DB::transaction(function () use ($reporter, $reportedUser, $reportable, $reason, $note, $isAgainstModerator) {
            $report = Report::create([
                'reporter_id' => $reporter->id,
                'reported_user_id' => $reportedUser->id,
                'reportable_type' => $reportable->getMorphClass(),
                'reportable_id' => $reportable->id,
                'reason' => $reason,
                'reporter_note' => $note,
                'status' => ReportStatus::Pending,
                'is_against_moderator' => $isAgainstModerator,
            ]);

            if ($isAgainstModerator) {
                ModeratorPerformanceReview::create([
                    'moderator_id' => $reportedUser->id,
                    'report_id' => $report->id,
                ]);
            }

            $this->eventService->log(
                'report_created',
                $reporter,
                $reportedUser,
                report: $report
            );

            DB::afterCommit(function () use ($report) {
                event(new ReportCreated($report));
            });

            return $report;
        });
    }

    public function assignReport(Report $report, User $moderator): Report
    {
        if ($report->reported_user_id === $moderator->id) {
            throw new RuntimeException('You cannot assign a report filed against yourself.');
        }

        if ($report->status !== ReportStatus::Pending) {
            throw new RuntimeException('Report is not in pending status.');
        }

        return DB::transaction(function () use ($report, $moderator) {
            $report->update([
                'status' => ReportStatus::Assigned,
                'assigned_to' => $moderator->id,
                'assigned_at' => now(),
            ]);

            $this->eventService->log(
                'report_assigned',
                $moderator,
                $report->reportedUser,
                report: $report
            );

            return $report;
        });
    }

    public function resolveReport(Report $report, User $moderator, string $resolution, string $note, string $ruleReference = '', string $moderatorNote = ''): Report
    {
        if ($report->reporter_id === $moderator->id) {
            throw new RuntimeException('You cannot resolve a report you filed.');
        }

        if ($report->reported_user_id === $moderator->id) {
            throw new RuntimeException('You cannot resolve a report filed against yourself.');
        }

        $decisionType = match ($resolution) {
            ReportResolution::ViolationConfirmed->value => ModeratorDecisionType::Confirmed,
            ReportResolution::Dismissed->value => ModeratorDecisionType::Dismissed,
            ReportResolution::EscalatedToAdmin->value => ModeratorDecisionType::Escalated,
            default => throw new RuntimeException("Invalid resolution: {$resolution}"),
        };

        return DB::transaction(function () use ($report, $moderator, $resolution, $note, $ruleReference, $moderatorNote, $decisionType) {
            $decision = ModeratorDecision::create([
                'moderator_id' => $moderator->id,
                'report_id' => $report->id,
                'decision' => $decisionType,
                'requires_cosign' => $moderator->is_moderator_probationary,
            ]);

            if ($resolution === ReportResolution::ViolationConfirmed->value) {
                $reportedUser = User::whereKey($report->reported_user_id)->first();

                if ($reportedUser) {
                    $this->violationService->confirmViolation(
                        $reportedUser,
                        $report,
                        $moderator,
                        $ruleReference,
                        $moderatorNote,
                        $decision
                    );
                }
            }

            $report->update([
                'status' => ReportStatus::Resolved,
                'resolved_by' => $moderator->id,
                'resolution' => $resolution,
                'resolution_note' => $note,
                'resolved_at' => now(),
            ]);

            $this->eventService->log(
                'report_resolved',
                $moderator,
                $report->reportedUser,
                report: $report
            );

            if ($report->reporter) {
                $report->reporter->notify(new ReportResolvedNotification($report));
            }

            return $report;
        });
    }

    public function dismissReport(Report $report, User $moderator, string $note): Report
    {
        return DB::transaction(function () use ($report, $moderator, $note) {
            ModeratorDecision::create([
                'moderator_id' => $moderator->id,
                'report_id' => $report->id,
                'decision' => ModeratorDecisionType::Dismissed,
            ]);

            $report->update([
                'status' => ReportStatus::Dismissed,
                'resolved_by' => $moderator->id,
                'resolution' => ReportResolution::Dismissed,
                'resolution_note' => $note,
                'resolved_at' => now(),
            ]);

            $this->eventService->log(
                'report_dismissed',
                $moderator,
                $report->reportedUser,
                report: $report
            );

            if ($report->reporter) {
                $report->reporter->notify(new ReportResolvedNotification($report));
            }

            return $report;
        });
    }

    public function escalateReport(Report $report, User $moderator): Report
    {
        return DB::transaction(function () use ($report, $moderator) {
            ModeratorDecision::create([
                'moderator_id' => $moderator->id,
                'report_id' => $report->id,
                'decision' => ModeratorDecisionType::Escalated,
            ]);

            $report->update([
                'status' => ReportStatus::Escalated,
                'resolution' => ReportResolution::EscalatedToAdmin,
            ]);

            $this->eventService->log(
                'report_escalated',
                $moderator,
                $report->reportedUser,
                report: $report
            );

            return $report;
        });
    }

    public function returnStaleReports(): void
    {
        $staleReports = Report::where('status', ReportStatus::Assigned)
            ->where('assigned_at', '<=', now()->subHours(24))
            ->get();

        foreach ($staleReports as $report) {
            DB::transaction(function () use ($report) {
                $report->update([
                    'status' => ReportStatus::Pending,
                    'assigned_to' => null,
                    'assigned_at' => null,
                ]);

                $this->eventService->log(
                    'report_returned_to_queue',
                    null,
                    $report->reportedUser,
                    report: $report
                );
            });
        }
    }
}
