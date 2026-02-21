<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoreAppSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Ensure we have some users to attach RSVPs to.
        //    Prefer existing users (e.g., WorkOS-created) and only create if needed.
        $users = User::query()->take(12)->get();

        if ($users->count() < 6) {
            $needed = 10 - $users->count();

            if (method_exists(User::class, 'factory')) {
                User::factory()->count($needed)->create();
            } else {
                // Fallback: minimal users (adjust if your users table has stricter requirements)
                for ($i = 1; $i <= $needed; $i++) {
                    User::query()->create([
                        'name' => "Demo User {$i}",
                        'email' => "demo{$i}_" . Str::lower(Str::random(6)) . '@example.test',
                        'role' => 'user',
                    ]);
                }
            }

            $users = User::query()->take(12)->get();
        }

        // Use one "host" for events if available (admin preferred, else first user).
        $host = User::query()->where('role', 'admin')->first() ?? $users->first();

        $now = CarbonImmutable::now();

        // 2) Create events that exercise real UI/logic states.
        // Note:
        // - We do NOT set slug; HasUniqueSlug should handle it.
        // - We do NOT set essence_numen_id; Event::booted() will create it if missing.
        // - Event::booted() sets status to 'scheduled' if status is empty.
        // - Migration default is 'draft' but the model hook overrides only when status is NOT provided.
        $eventsData = [
            [
                'title' => 'City Council Open Forum',
                'description' => 'Public comment + discussion of upcoming agenda items.',
                'starts_at' => $now->addDays(3)->setTime(18, 0),
                'ends_at' => $now->addDays(3)->setTime(20, 0),
                'status' => 'scheduled',
            ],
            [
                'title' => 'Neighborhood Cleanup',
                'description' => 'Meet at the north entrance. Gloves + bags provided.',
                'starts_at' => $now->addDays(7)->setTime(9, 30),
                'ends_at' => $now->addDays(7)->setTime(11, 30),
                'status' => 'scheduled',
            ],
            [
                'title' => 'Transit Planning Workshop',
                'description' => 'Workshop on route changes and accessibility upgrades.',
                'starts_at' => $now->addDays(10)->setTime(17, 30),
                'ends_at' => $now->addDays(10)->setTime(19, 0),
                'status' => 'scheduled',
            ],
            [
                'title' => 'Community Town Hall (Past)',
                'description' => 'Recap of last quarter initiatives and open Q&A.',
                'starts_at' => $now->subDays(5)->setTime(19, 0),
                'ends_at' => $now->subDays(5)->setTime(20, 30),
                'status' => 'scheduled', // keep scheduled so "past" is derived from starts_at
            ],
            [
                'title' => 'Volunteer Orientation (Draft)',
                'description' => 'Intro session for new volunteers. Details TBD.',
                'starts_at' => null,
                'ends_at' => null,
                'status' => 'draft',
            ],
            [
                'title' => 'Organizer Meetup (Cancelled)',
                'description' => 'Rescheduling soon — apologies!',
                'starts_at' => $now->addDays(2)->setTime(12, 0),
                'ends_at' => $now->addDays(2)->setTime(13, 0),
                'status' => 'cancelled',
                'cancelled_at' => $now->subHours(2),
            ],
        ];

        $events = collect();

        foreach ($eventsData as $data) {
            // Prevent duplicates if you run db:seed multiple times without migrate:fresh
            $event = Event::query()->firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'user_id' => $host?->id,
                ])
            );

            // If we created it earlier but want to ensure cancelled_at is set correctly:
            if (($data['status'] ?? null) === 'cancelled' && ($data['cancelled_at'] ?? null)) {
                if ($event->cancelled_at === null) {
                    $event->forceFill(['cancelled_at' => $data['cancelled_at']])->save();
                }
            }

            $events->push($event);
        }

        // 3) Seed RSVPs so detail pages look alive.
        // Statuses are strings; your default is 'going' but we'll vary.
        $rsvpStatuses = ['going', 'interested', 'not_going'];

        foreach ($events as $event) {
            // Skip RSVP seeding for drafts (no date/details) if you prefer
            if (($event->status ?? null) === 'draft') {
                continue;
            }

            $attendees = $users->shuffle()->take(random_int(3, min(8, $users->count())));

            foreach ($attendees as $i => $user) {
                $status = $rsvpStatuses[$i % count($rsvpStatuses)];

                EventRsvp::query()->firstOrCreate(
                    [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'status' => $status,
                    ]
                );
            }
        }
    }
}
