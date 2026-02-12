<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\RedirectResponse;

final class MobileAuthStartController
{
    public function __invoke(): RedirectResponse
    {
        // TODO: return redirect to WorkOS authorization URL
        // using the WorkOS Laravel package / your existing WorkOS setup.

        return redirect()->away('/'); // placeholder so you can deploy safely
    }
}
