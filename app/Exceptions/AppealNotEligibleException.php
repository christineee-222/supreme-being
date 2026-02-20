<?php

namespace App\Exceptions;

use Exception;

class AppealNotEligibleException extends Exception
{
    public function __construct(public ?\DateTimeInterface $eligibleFrom = null)
    {
        $message = $eligibleFrom
            ? "Appeal not eligible until {$eligibleFrom->toDateTimeString()}."
            : 'Permanently ineligible for appeals.';

        parent::__construct($message);
    }
}
