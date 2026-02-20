<?php

namespace App\Enums;

enum ReportReason: string
{
    case HateSpeech = 'hate_speech';
    case Violence = 'violence';
    case Manipulation = 'manipulation';
    case Spam = 'spam';
    case Harassment = 'harassment';
    case Language = 'language';
    case Other = 'other';
}
