<?php

namespace App\Enums;

enum ReportResolution: string
{
    case ViolationConfirmed = 'violation_confirmed';
    case Dismissed = 'dismissed';
    case EscalatedToAdmin = 'escalated_to_admin';
}
