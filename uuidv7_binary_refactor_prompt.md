You are operating in a Laravel 12 / PHP 8.5 project using MySQL 8.0 (local via Sail/Docker; production on Laravel Cloud).
We are doing a CLEAN SLATE refactor with no important production data. We will EDIT ORIGINAL MIGRATIONS and then run migrate:fresh.

GOAL:
Convert the entire app from integer primary keys + foreign keys to UUIDv7 stored as BINARY(16) for ALL primary keys and ALL foreign keys across the schema.
Additionally, add SEO-friendly slugs for selected public-facing models (forums, legislation, events, polls, portraits, users if public profiles). Include the HasUniqueSlug trait for models requiring SEO URLs. Ensure the trait handles uniqueness by appending a counter (e.g., -2, -3) and only triggers on the creating event to ensure URL stability. Ensure the slug column in migrations has a unique() index.

PROJECT-SPECIFIC CONTEXT:
- DB: MySQL 8.0. We want BINARY(16) storage, not CHAR(36).
- Sessions are stored in DB (sessions table created in users migration). sessions.user_id MUST become BINARY(16) to match users.id.
- WorkOS AuthKit is used. We map external identity via users.workos_id (string unique). DO NOT change workos_id type. Keep it as string unique.
- There is a custom table named 'essence_numen' (singular). The EssenceNumen model sets protected $table='essence_numen'. Any FK constraints referencing it must use constrained('essence_numen') or explicit on('essence_numen').
- Stripe IDs remain strings (checkout session id, payment intent id, webhook event id). Do not cast/convert those.
- Models present in app/Models that must be updated to binary UUID PK + casts as applicable:
  - Comment.php
  - Donation.php
  - EssenceNumen.php
  - Event.php
  - EventRsvp.php
  - Forum.php
  - Legislation.php
  - Poll.php
  - Portrait.php
  - User.php

SCHEMA TABLES INVOLVED (from migrations list):
- users + sessions (0001_01_01_000000_create_users_table.php)
- donations (2026_01_28_231756_create_donations_table.php + later stripe fields)
- events (2026_01_28_231811_create_events_table.php + later fields/cancelled_at)
- essence_numen (2026_02_02_153520_create_essence_numen_table.php.php and 2026_02_02_210050_fix_essence_numen_id_autoincrement.php)
- polls (2026_01_28_231647_create_polls_table.php + add_fields + finish schema + add_essence_numen_id)
- event_rsvps (2026_02_04_211224_create_event_rsvps_table.php)
- plus any tables used by Comment/Forum/Legislation/Portrait models (search their migrations and convert too)

IMPLEMENTATION REQUIREMENTS:

A) BINARY UUID storage
- All PKs must be BINARY(16): use $table->binary('id',16)->primary()
- All FKs to those PKs must be BINARY(16): use $table->binary('<col>',16)->index()
- Add foreign key constraints explicitly where appropriate (MySQL). Do not use foreignUuid() because it assumes CHAR UUIDs.

B) UUIDv7 generation + Eloquent casting (string <-> binary)
- Create app/Casts/BinaryUuid.php:
  - get(): convert binary(16) -> RFC4122 string using Symfony\Component\Uid\Uuid::fromBinary(...)->toRfc4122()
  - set(): accept RFC4122 string and convert -> binary using Uuid::fromString(...)->toBinary()
  - also accept already-16-byte strings and store as-is.
- Create app/Models/Concerns/UsesBinaryUuidV7.php trait:
  - On creating, set key to Uuid::v7()->toBinary()
  - Set public $incrementing=false; protected $keyType='string';
- Update ALL models with UUID PKs to use UsesBinaryUuidV7 and casts:
  - casts: 'id' => BinaryUuid::class
  - and for each FK binary uuid column (user_id, event_id, poll_id, essence_numen_id, forum_id, legislation_id, portrait_id, etc): BinaryUuid::class
  - Ensure code that sets/reads IDs uses UUID strings in PHP, not raw binary.

C) Migration edits (clean slate; edit originals)
- Update the users migration: users.id -> binary(16) pk; sessions.user_id -> binary(16) nullable + index.
- Update essence_numen migration: essence_numen.id -> binary(16) pk. Remove or refactor any autoincrement fix migration (it should no longer apply under UUID).
- Update polls/events/donations/event_rsvps/comment/forum/legislation/portrait tables: all PKs + FKs as binary(16).
- Fix known bug: 2026_02_02_194028_add_fields_to_polls_table.php down() currently drops user_id though up() does not add it. Correct down() to drop only columns actually added there.
- Ensure any FK constraints referencing essence_numen use correct table name 'essence_numen'.

D) Slugs for SEO-friendly public URLs
- Add slug columns + unique index to the following tables:
  - forums.slug (string, unique)
  - legislations.slug (string, unique)
  - events.slug (string, unique)
  - polls.slug (string, unique)
  - portraits.slug (string, unique) IF portraits are public pages
  - users.slug (string, unique) ONLY if we have public profile pages
- Implement slug generation:
  - Add a small helper method or use Str::slug().
  - On creating (or saving), if slug is empty, generate from name/title and ensure uniqueness by appending -2, -3, etc.
  - Keep slug generation deterministic and testable.
- Route model binding:
  - For models with slugs, implement getRouteKeyName() returning 'slug' ONLY if those routes should be slug-based.
  - Keep internal relations and foreign keys on UUID PK, not slug.
- Ensure TypeScript/inertia routes use slug for public pages where applicable.

E) Frontend TypeScript
- Update TS types/interfaces so id and *_id fields are strings (UUID), not numbers.
- Check any code that assumes numeric sorting by id.

F) Smoke test checklist after migrate:fresh
- WorkOS web login + callback should create/find user by workos_id and store internal UUID PK.
- Mobile auth flow endpoints must work:
  - /mobile/start -> WorkOS redirect -> /auth/workos/callback -> /mobile/complete -> POST /api/v1/mobile/exchange
- Event RSVP and Donations still work with UUID user_id/event_id/donation_id in DB.
- Public pages resolve via slugs (poll/event/forum/legislation/portrait/user profile if enabled).

DELIVERABLES:
1) Provide the updated versions of ALL affected migration files (edited originals) including slug columns and uuid binary changes.
2) Provide new files:
   - app/Casts/BinaryUuid.php
   - app/Models/Concerns/UsesBinaryUuidV7.php
3) Provide updated models for:
   - Comment, Donation, EssenceNumen, Event, EventRsvp, Forum, Legislation, Poll, Portrait, User
   Each must include UsesBinaryUuidV7 + proper $casts for id and all FK columns.
   For slugged models, include slug generation + (optional) getRouteKeyName() if needed.
4) Provide exact Sail commands:
   - ./vendor/bin/sail artisan optimize:clear
   - ./vendor/bin/sail artisan migrate:fresh --seed
   - ./vendor/bin/sail artisan test

Be extremely careful: update every FK column type consistently, especially sessions.user_id and event_rsvps.*.
Also search for any unsignedBigInteger('..._id') or foreignId(...) across migrations and convert them too.
