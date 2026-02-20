<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case UnderReview = 'under_review';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';
    case Escalated = 'escalated';
}
