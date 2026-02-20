<?php

namespace App\Enums;

enum AppealStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Denied = 'denied';
}
