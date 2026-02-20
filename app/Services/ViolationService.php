<?php

namespace App\Services;

use App\Enums\ViolationConsequence;
use App\Models\ModeratorDecision;
use App\Models\Report;
use App\Models\User;
use App\Models\Violation;
use App\Notifications\ProbationLiftedNotification;
use App\Notifications\RestrictionLiftedNotification;
use App\Notifications\ViolationAppliedNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ViolationService
{
    public function __construct(
        public ModerationEventService $eventService,
        public AppealService $appealService
    ) {}

    public function confirmViolation(User $user, Report $report, User $confirmedBy, string $ruleReference, string $moderatorNote, ModeratorDecision $decision): Violation
    {
        return DB::transaction(function () use ($user, $report, $confirmedBy, $ruleReference, $moderatorNote, $decision) {
            $user = User::whereKey($user->id)->lockForUpdate()->first();
            $user->increment('violation_count');
            $user->refresh();

            $violationNumber = $user->violation_count;
            $consequence = $this->determineConsequence($violationNumber);
            $restrictionEndsAt = $this->calculateRestrictionEnd($consequence);

            $violation = Violation::create([
                'user_id' => $user->id,
                'report_id' => $report->id,
                'moderator_decision_id' => $decision->id,
                'confirmed_by' => $confirmedBy->id,
                'rule_reference' => $ruleReference,
                'violation_number' => $violationNumber,
                'consequence_applied' => $consequence,
                'restriction_ends_at' => $restrictionEndsAt,
                'applied_to_user' => false,
                'moderator_note' => $moderatorNote,
            ]);

            if ($decision->requires_cosign) {
                // Probationary moderator — do not mutate user restriction fields until admin cosigns
            } elseif ($user->appeal_count > 0) {
                $this->appealService->handlePostAppealViolation($user);

                $violation->update([
                    'consequence_applied' => ViolationConsequence::Indefinite,
                    'restriction_ends_at' => null,
                    'applied_to_user' => true,
                ]);
            } else {
                $this->applyRestriction($user, $consequence, $restrictionEndsAt);

                if ($consequence === ViolationConsequence::Indefinite) {
                    $user->update(['next_appeal_eligible_at' => now()->addYear()]);
                }

                $violation->update(['applied_to_user' => true]);
            }

            $this->eventService->log(
                'violation_confirmed',
                $confirmedBy,
                $user,
                report: $report,
                violation: $violation
            );

            $user->notify(new ViolationAppliedNotification($violation, $decision->requires_cosign));

            return $violation;
        });
    }

    public function cosignDecision(ModeratorDecision $decision, User $admin): void
    {
        if (! $decision->requires_cosign || $decision->cosigned_at !== null) {
            throw new RuntimeException(
                'This decision does not require cosign or has already been cosigned.'
            );
        }

        DB::transaction(function () use ($decision, $admin) {
            $decision = ModeratorDecision::whereKey($decision->id)->lockForUpdate()->firstOrFail();

            if (! $decision->requires_cosign || $decision->cosigned_at !== null) {
                throw new RuntimeException(
                    'This decision does not require cosign or has already been cosigned.'
                );
            }

            $violation = Violation::where('moderator_decision_id', $decision->id)->firstOrFail();

            if ($violation->user_id === null) {
                throw new RuntimeException('Violation has no user_id; cannot apply restriction.');
            }

            $user = User::whereKey($violation->user_id)->lockForUpdate()->firstOrFail();

            $decision->update([
                'cosigned_by' => $admin->id,
                'cosigned_at' => now(),
            ]);

            if ($user->appeal_count > 0) {
                $this->appealService->handlePostAppealViolation($user);

                $violation->update([
                    'consequence_applied' => ViolationConsequence::Indefinite,
                    'restriction_ends_at' => null,
                    'applied_to_user' => true,
                ]);
            } else {
                $this->applyRestriction($user, $violation->consequence_applied, $violation->restriction_ends_at);

                if ($violation->consequence_applied === ViolationConsequence::Indefinite) {
                    $user->update(['next_appeal_eligible_at' => now()->addYear()]);
                }

                $violation->update(['applied_to_user' => true]);
            }

            $this->eventService->log(
                'decision_cosigned',
                $admin,
                $user,
                violation: $violation
            );

            $this->eventService->log(
                'restriction_applied',
                $admin,
                $user,
                violation: $violation
            );

            $user->notify(new ViolationAppliedNotification($violation, false));

            $cosignedCount = ModeratorDecision::where('moderator_id', $decision->moderator_id)
                ->whereNotNull('cosigned_at')
                ->count();

            if ($cosignedCount >= 10) {
                $moderator = User::whereKey($decision->moderator_id)->lockForUpdate()->first();

                if ($moderator && $moderator->is_moderator_probationary) {
                    $moderator->update(['is_moderator_probationary' => false]);

                    $this->eventService->log(
                        'moderator_probation_lifted',
                        $admin,
                        $moderator
                    );

                    $moderator->notify(new ProbationLiftedNotification);
                }
            }
        });
    }

    public function liftExpiredRestrictions(): void
    {
        $users = User::where('restriction_ends_at', '<=', now())
            ->where('is_indefinitely_restricted', false)
            ->get();

        foreach ($users as $user) {
            DB::transaction(function () use ($user) {
                $user->update(['restriction_ends_at' => null]);

                $this->eventService->log(
                    'restriction_lifted',
                    null,
                    $user
                );

                $user->notify(new RestrictionLiftedNotification);
            });
        }
    }

    private function determineConsequence(int $violationNumber): ViolationConsequence
    {
        return match (true) {
            $violationNumber === 1 => ViolationConsequence::SevenDay,
            $violationNumber === 2 => ViolationConsequence::ThirtyDay,
            default => ViolationConsequence::Indefinite,
        };
    }

    private function calculateRestrictionEnd(ViolationConsequence $consequence): ?\DateTimeInterface
    {
        return match ($consequence) {
            ViolationConsequence::SevenDay => now()->addDays(7),
            ViolationConsequence::ThirtyDay => now()->addDays(30),
            ViolationConsequence::Indefinite => null,
        };
    }

    private function applyRestriction(User $user, ViolationConsequence $consequence, ?\DateTimeInterface $restrictionEndsAt): void
    {
        if ($consequence === ViolationConsequence::Indefinite) {
            $user->update([
                'is_indefinitely_restricted' => true,
                'restriction_ends_at' => null,
            ]);
        } else {
            $user->update([
                'restriction_ends_at' => $restrictionEndsAt,
            ]);
        }
    }
}
