<?php

namespace App\Enums;

enum ViolationConsequence: string
{
    case SevenDay = '7_day';
    case ThirtyDay = '30_day';
    case Indefinite = 'indefinite';
}
