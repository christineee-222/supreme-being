<?php

namespace App\Services;

use App\Enums\AppealStatus;
use App\Exceptions\AppealNotEligibleException;
use App\Models\Appeal;
use App\Models\User;
use App\Notifications\AppealDecisionNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AppealService
{
    public function __construct(public ModerationEventService $eventService) {}

    /**
     * @return array{eligible: bool, eligible_from: \Carbon\Carbon|null}
     */
    public function checkEligibility(User $user): array
    {
        if ($user->next_appeal_eligible_at === null) {
            return ['eligible' => false, 'eligible_from' => null];
        }

        if ($user->next_appeal_eligible_at <= now()) {
            return [
                'eligible' => true,
                'eligible_from' => $user->next_appeal_eligible_at,
            ];
        }

        return [
            'eligible' => false,
            'eligible_from' => $user->next_appeal_eligible_at,
        ];
    }

    public function submitAppeal(User $user, string $statement): Appeal
    {
        $eligibility = $this->checkEligibility($user);

        if (! $eligibility['eligible']) {
            throw new AppealNotEligibleException($eligibility['eligible_from']);
        }

        return DB::transaction(function () use ($user, $statement, $eligibility) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $appeal = Appeal::create([
                'user_id' => $user->id,
                'appeal_number' => $user->appeal_count + 1,
                'user_statement' => $statement,
                'submitted_at' => now(),
                'eligible_from' => $eligibility['eligible_from'],
            ]);

            $this->eventService->log(
                'appeal_submitted',
                $user,
                $user,
                appeal: $appeal
            );

            return $appeal;
        });
    }

    public function decideAppeal(Appeal $appeal, User $admin, string $decision, string $note): Appeal
    {
        if (! in_array($decision, ['approved', 'denied'], true)) {
            throw new RuntimeException('Invalid appeal decision.');
        }

        if ($appeal->user_id === null) {
            throw new RuntimeException('Appeal has no user_id; cannot decide.');
        }

        return DB::transaction(function () use ($appeal, $admin, $decision, $note) {
            $user = User::whereKey($appeal->user_id)->lockForUpdate()->firstOrFail();
            $user->increment('appeal_count');
            $user->refresh();

            $status = $decision === 'approved' ? AppealStatus::Approved : AppealStatus::Denied;

            $appeal->update([
                'status' => $status,
                'reviewed_by' => $admin->id,
                'admin_decision_note' => $note,
                'decided_at' => now(),
            ]);

            if ($decision === 'approved') {
                $user->update([
                    'is_indefinitely_restricted' => false,
                    'restriction_ends_at' => null,
                ]);
            }

            if ($user->appeal_count >= 2) {
                $user->update(['next_appeal_eligible_at' => null]);
            } elseif ($decision === 'denied') {
                $user->update(['next_appeal_eligible_at' => now()->addYear()]);
            }

            $this->eventService->log(
                'appeal_decided',
                $admin,
                $user,
                ['decision' => $decision],
                appeal: $appeal
            );

            $user->notify(new AppealDecisionNotification($appeal));

            return $appeal;
        });
    }

    public function handlePostAppealViolation(User $user): void
    {
        if (DB::transactionLevel() === 0) {
            throw new RuntimeException('handlePostAppealViolation must be called inside a DB transaction.');
        }

        if ($user->appeal_count === 1) {
            $user->update([
                'is_indefinitely_restricted' => true,
                'restriction_ends_at' => null,
                'next_appeal_eligible_at' => now()->addYears(5),
            ]);
        } elseif ($user->appeal_count >= 2) {
            $user->update([
                'is_indefinitely_restricted' => true,
                'restriction_ends_at' => null,
                'next_appeal_eligible_at' => null,
            ]);
        }

        $this->eventService->log(
            'restriction_applied',
            null,
            $user
        );
    }
}
