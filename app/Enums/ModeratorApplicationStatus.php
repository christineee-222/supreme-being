<?php

namespace App\Enums;

enum ModeratorApplicationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';
    case Deferred = 'deferred';
}
