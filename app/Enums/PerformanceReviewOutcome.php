<?php

namespace App\Enums;

enum PerformanceReviewOutcome: string
{
    case NoAction = 'no_action';
    case WarningIssued = 'warning_issued';
    case RoleRevoked = 'role_revoked';
}
