<?php

namespace App\Enums;

enum ModeratorDecisionType: string
{
    case Confirmed = 'confirmed';
    case Dismissed = 'dismissed';
    case Escalated = 'escalated';
}
