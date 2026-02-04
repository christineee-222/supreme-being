<?php

namespace App\Providers;

use App\Models\Poll;
use App\Models\Donation;
use App\Models\Forum;
use App\Models\Portrait;
use App\Models\Legislation;
use App\Policies\PollPolicy;
use App\Policies\DonationPolicy;
use App\Policies\ForumPolicy;
use App\Policies\PortraitPolicy;
use App\Policies\LegislationPolicy;
use App\Models\Event;
use App\Policies\EventPolicy;
use App\Models\EventRsvp;
use App\Policies\EventRsvpPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Poll::class => PollPolicy::class,
        Donation::class    => DonationPolicy::class,
        Event::class       => EventPolicy::class,
        Forum::class       => ForumPolicy::class,
        Portrait::class    => PortraitPolicy::class,
        Legislation::class => LegislationPolicy::class,
        EventRsvp::class => EventRsvpPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}


