<?php

namespace Tests\Feature;

use App\Enums\ModeratorDecisionType;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\ViolationConsequence;
use App\Events\ReportCreated;
use App\Exceptions\AppealNotEligibleException;
use App\Models\Forum;
use App\Models\ModeratorDecision;
use App\Models\Report;
use App\Models\User;
use App\Models\Violation;
use App\Services\AppealService;
use App\Services\ReportService;
use App\Services\ViolationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModerationSystemTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function reporter_cannot_report_themselves(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $comment = Forum::factory()->create();

        $reportService = app(ReportService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You cannot report yourself.');

        $reportService->createReport(
            $user,
            $user,
            $comment,
            ReportReason::Spam->value,
            null
        );
    }

    #[Test]
    public function report_created_event_dispatched_after_commit(): void
    {
        Event::fake([ReportCreated::class]);
        Notification::fake();

        $reporter = User::factory()->create();
        $reported = User::factory()->create();
        $comment = Forum::factory()->create();

        $reportService = app(ReportService::class);
        $reportService->createReport(
            $reporter,
            $reported,
            $comment,
            ReportReason::HateSpeech->value,
            'Test note'
        );

        Event::assertDispatched(ReportCreated::class);
    }

    #[Test]
    public function report_creation_logs_moderation_event(): void
    {
        Event::fake([ReportCreated::class]);
        Notification::fake();

        $reporter = User::factory()->create();
        $reported = User::factory()->create();
        $comment = Forum::factory()->create();

        $reportService = app(ReportService::class);
        $reportService->createReport(
            $reporter,
            $reported,
            $comment,
            ReportReason::Spam->value,
            null
        );

        $this->assertDatabaseHas('moderation_events', [
            'event_type' => 'report_created',
            'actor_id' => $reporter->id,
            'subject_user_id' => $reported->id,
        ]);
    }

    #[Test]
    public function moderator_cannot_assign_report_against_themselves(): void
    {
        Notification::fake();
        $moderator = User::factory()->moderator()->create();
        $reporter = User::factory()->create();

        $report = Report::factory()->create([
            'reporter_id' => $reporter->id,
            'reported_user_id' => $moderator->id,
        ]);

        $reportService = app(ReportService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You cannot assign a report filed against yourself.');

        $reportService->assignReport($report, $moderator);
    }

    #[Test]
    public function moderator_cannot_resolve_report_against_themselves(): void
    {
        Notification::fake();
        $moderator = User::factory()->moderator()->create();

        $report = Report::factory()->create([
            'reported_user_id' => $moderator->id,
            'status' => ReportStatus::Assigned,
            'assigned_to' => $moderator->id,
        ]);

        $reportService = app(ReportService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You cannot resolve a report filed against yourself.');

        $reportService->resolveReport(
            $report,
            $moderator,
            'violation_confirmed',
            'note'
        );
    }

    #[Test]
    public function probationary_moderator_creates_violation_without_applying(): void
    {
        Notification::fake();
        Event::fake([ReportCreated::class]);

        $probMod = User::factory()->probationaryModerator()->create();
        $offender = User::factory()->create();
        $comment = Forum::factory()->create();

        $reportService = app(ReportService::class);
        $report = $reportService->createReport(
            User::factory()->create(),
            $offender,
            $comment,
            ReportReason::Violence->value,
            null
        );

        $reportService->assignReport($report, $probMod);

        $decision = ModeratorDecision::create([
            'moderator_id' => $probMod->id,
            'report_id' => $report->id,
            'decision' => ModeratorDecisionType::Confirmed,
            'requires_cosign' => true,
        ]);

        $violationService = app(ViolationService::class);
        $violation = $violationService->confirmViolation(
            $offender,
            $report,
            $probMod,
            'R-1',
            'Test note',
            $decision
        );

        $this->assertFalse($violation->applied_to_user);
        $offender->refresh();
        $this->assertNull($offender->restriction_ends_at);
        $this->assertFalse($offender->is_indefinitely_restricted);
    }

    #[Test]
    public function admin_cosign_applies_restriction(): void
    {
        Notification::fake();

        $probMod = User::factory()->probationaryModerator()->create();
        $offender = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $report = Report::factory()->create([
            'reported_user_id' => $offender->id,
        ]);

        $decision = ModeratorDecision::create([
            'moderator_id' => $probMod->id,
            'report_id' => $report->id,
            'decision' => ModeratorDecisionType::Confirmed,
            'requires_cosign' => true,
        ]);

        $violation = Violation::factory()->create([
            'user_id' => $offender->id,
            'report_id' => $report->id,
            'moderator_decision_id' => $decision->id,
            'confirmed_by' => $probMod->id,
            'violation_number' => 1,
            'consequence_applied' => ViolationConsequence::SevenDay->value,
            'restriction_ends_at' => now()->addDays(7),
            'applied_to_user' => false,
        ]);

        $violationService = app(ViolationService::class);
        $violationService->cosignDecision($decision, $admin);

        $violation->refresh();
        $offender->refresh();

        $this->assertTrue($violation->applied_to_user);
        $this->assertNotNull($offender->restriction_ends_at);

        $this->assertDatabaseHas('moderation_events', [
            'event_type' => 'decision_cosigned',
            'actor_id' => $admin->id,
            'subject_user_id' => $offender->id,
        ]);

        $this->assertDatabaseHas('moderation_events', [
            'event_type' => 'restriction_applied',
            'actor_id' => $admin->id,
            'subject_user_id' => $offender->id,
        ]);
    }

    #[Test]
    public function probation_lifts_after_10_cosigned_decisions(): void
    {
        Notification::fake();

        $probMod = User::factory()->probationaryModerator()->create();
        $admin = User::factory()->admin()->create();

        for ($i = 0; $i < 10; $i++) {
            $offender = User::factory()->create();
            $report = Report::factory()->create(['reported_user_id' => $offender->id]);

            $decision = ModeratorDecision::create([
                'moderator_id' => $probMod->id,
                'report_id' => $report->id,
                'decision' => ModeratorDecisionType::Confirmed,
                'requires_cosign' => true,
            ]);

            Violation::factory()->create([
                'user_id' => $offender->id,
                'report_id' => $report->id,
                'moderator_decision_id' => $decision->id,
                'confirmed_by' => $probMod->id,
                'violation_number' => 1,
                'consequence_applied' => ViolationConsequence::SevenDay->value,
                'restriction_ends_at' => now()->addDays(7),
                'applied_to_user' => false,
            ]);

            $violationService = app(ViolationService::class);
            $violationService->cosignDecision($decision, $admin);
        }

        $probMod->refresh();
        $this->assertFalse($probMod->is_moderator_probationary);

        $this->assertDatabaseHas('moderation_events', [
            'event_type' => 'moderator_probation_lifted',
            'subject_user_id' => $probMod->id,
        ]);
    }

    #[Test]
    public function appeal_before_eligible_date_throws_exception(): void
    {
        Notification::fake();

        $user = User::factory()->indefinitelyRestricted()->create([
            'next_appeal_eligible_at' => now()->addYear(),
        ]);

        $appealService = app(AppealService::class);

        $this->expectException(AppealNotEligibleException::class);

        $appealService->submitAppeal($user, 'I should be unbanned.');
    }

    #[Test]
    public function lift_expired_restrictions_only_clears_timed(): void
    {
        Notification::fake();

        $timedUser = User::factory()->create([
            'restriction_ends_at' => now()->subHour(),
        ]);

        $indefiniteUser = User::factory()->indefinitelyRestricted()->create();

        $violationService = app(ViolationService::class);
        $violationService->liftExpiredRestrictions();

        $timedUser->refresh();
        $indefiniteUser->refresh();

        $this->assertNull($timedUser->restriction_ends_at);
        $this->assertTrue($indefiniteUser->is_indefinitely_restricted);

        $this->assertDatabaseHas('moderation_events', [
            'event_type' => 'restriction_lifted',
            'subject_user_id' => $timedUser->id,
            'actor_id' => null,
        ]);

        $this->assertDatabaseMissing('moderation_events', [
            'event_type' => 'restriction_lifted',
            'subject_user_id' => $indefiniteUser->id,
        ]);
    }

    #[Test]
    public function stale_reports_returned_to_queue(): void
    {
        Notification::fake();

        $moderator = User::factory()->moderator()->create();

        $staleReport = Report::factory()->create([
            'status' => ReportStatus::Assigned,
            'assigned_to' => $moderator->id,
            'assigned_at' => now()->subHours(25),
        ]);

        $freshReport = Report::factory()->create([
            'status' => ReportStatus::Assigned,
            'assigned_to' => $moderator->id,
            'assigned_at' => now()->subHours(12),
        ]);

        $reportService = app(ReportService::class);
        $reportService->returnStaleReports();

        $staleReport->refresh();
        $freshReport->refresh();

        $this->assertEquals(ReportStatus::Pending, $staleReport->status);
        $this->assertNull($staleReport->assigned_to);

        $this->assertEquals(ReportStatus::Assigned, $freshReport->status);
        $this->assertEquals($moderator->id, $freshReport->assigned_to);

        $this->assertDatabaseHas('moderation_events', [
            'event_type' => 'report_returned_to_queue',
            'report_id' => $staleReport->id,
        ]);
    }

    #[Test]
    public function report_against_moderator_creates_performance_review(): void
    {
        Event::fake([ReportCreated::class]);
        Notification::fake();

        $reporter = User::factory()->create();
        $moderator = User::factory()->moderator()->create();
        $comment = Forum::factory()->create();

        $reportService = app(ReportService::class);
        $report = $reportService->createReport(
            $reporter,
            $moderator,
            $comment,
            ReportReason::Harassment->value,
            null
        );

        $this->assertTrue($report->is_against_moderator);

        $this->assertDatabaseHas('moderator_performance_reviews', [
            'moderator_id' => $moderator->id,
            'report_id' => $report->id,
        ]);
    }
}
