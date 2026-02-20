<?php

namespace App\Services;

use App\Models\Appeal;
use App\Models\ModerationEvent;
use App\Models\ModeratorApplication;
use App\Models\Report;
use App\Models\User;
use App\Models\Violation;

class ModerationEventService
{
    public function log(
        string $type,
        ?User $actor,
        ?User $subject,
        array $metadata = [],
        ?Report $report = null,
        ?Violation $violation = null,
        ?Appeal $appeal = null,
        ?ModeratorApplication $application = null
    ): void {
        ModerationEvent::create([
            'event_type' => $type,
            'actor_id' => $actor?->id,
            'subject_user_id' => $subject?->id,
            'report_id' => $report?->id,
            'violation_id' => $violation?->id,
            'appeal_id' => $appeal?->id,
            'moderator_application_id' => $application?->id,
            'metadata' => ! empty($metadata) ? $metadata : null,
        ]);
    }
}
