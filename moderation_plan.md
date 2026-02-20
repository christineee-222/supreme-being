# Moderation System — Claude Code Build Instructions

> Read this entire document before writing any code or running any commands.
> It contains everything you need to know about the existing codebase, the stack,
> and exactly what to build. Do not make assumptions — if something is unclear, ask.

---

## 1. Stack & Conventions

| Item | Detail |
|---|---|
| Framework | Laravel 12 |
| PHP (local) | 8.5.2 |
| PHP (production) | Laravel Cloud — must remain compatible with PHP ≥ 8.2 |
| Code target | PHP 8.2-compatible syntax only. You may use PHP 8.2–8.4 features that do not require 8.5. (Production is PHP 8.4.x.) |
| Frontend | Inertia.js v2 + React 19, served by Vite 7 |
| Mobile | Expo (React Native) |
| Auth | WorkOS via `laravel/workos` — no passwords stored locally |
| Database (local) | MySQL 8.4.8 |
| Database (production) | MySQL 8.0 on Laravel Cloud |
| Primary keys | `char(36)` UUID — use `$table->uuid('id')->primary()` |
| UUID version | UUIDv7 via Laravel 12's built-in `HasUuids` trait (default in L12) |
| Containerisation | Docker |

### Primary Keys & Foreign Keys

All tables use `char(36)` UUIDs. Laravel 12's `HasUuids` trait generates UUIDv7 by default — no extra package required.

```php
// PRIMARY KEY — on the owning table
$table->uuid('id')->primary();

// FOREIGN KEY — nullable
$table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();

// FOREIGN KEY — non-nullable
$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

// FOREIGN KEY referencing a specific table
$table->foreignUuid('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
```

### UUID Generation in Models

Every new model must use the `HasUuids` trait. No custom `newUniqueId()` override needed — Laravel 12 generates UUIDv7 automatically. The DB layer does not enforce UUIDv7; it is the model trait that ensures new rows receive v7 values.

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
}
```

### DB Transactions & Row Locking (CRITICAL)

Every service method that writes to multiple tables must be wrapped in `DB::transaction()`. Any method that increments a counter (`violation_count`, `appeal_count`, cosign count) must also lock the user row to prevent race conditions.

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($user) {
    $user = User::whereKey($user->id)->lockForUpdate()->first();
    // ... multi-table writes here
});
```

This is mandatory — without it, concurrent actions can double-increment counters or apply the wrong consequence in production.

### PHP Backed Enums

For every database ENUM column, create a corresponding PHP 8.1+ Backed Enum in `app/Enums/`. This provides strict typing in the application layer while keeping the DB constraint.

```php
// app/Enums/ReportReason.php
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
```

Create Backed Enums for: `ReportReason`, `ReportStatus`, `ReportResolution`, `ViolationConsequence`, `AppealStatus`, `ModeratorApplicationStatus`, `ModeratorDecisionType`, `PerformanceReviewOutcome`.

Cast enum columns in models using Laravel's enum casting:

```php
protected $casts = [
    'reason' => ReportReason::class,
    'status' => ReportStatus::class,
];
```

### Existing Middleware (Already Built — Do Not Recreate)

The following middleware already exists in `app/Http/Middleware/` and is registered in `bootstrap/app.php`:

```php
$middleware->alias([
    'auth.workos' => AuthenticateWorkOS::class,
    'admin'       => AdminMiddleware::class,
    'role'        => RoleMiddleware::class,
]);
```

- `admin` — aborts with 403 if `is_admin` is false or user is unauthenticated
- `role` — checks `users.role` string against allowed roles; automatically passes users where `is_admin = true`; usage: `role:moderator,admin`
- `auth` — standard Laravel session auth; use this for all moderation routes
- `auth.workos` — specific to the WorkOS authentication bridge; **do not use on moderation routes**

### Existing Moderation Routes File (Already Created)

Moderation routes live in `routes/moderation.php` — **not** `routes/web.php`. This file is already registered in `bootstrap/app.php`:

```php
->withRouting(
    web: [
        __DIR__.'/../routes/web.php',
        __DIR__.'/../routes/moderation.php',
    ],
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

All moderation routes use the standard web middleware stack (session auth, CSRF, Inertia compatible).

### Restriction State — Canonical Truth

| State | Condition |
|---|---|
| Unrestricted | `restriction_ends_at` is null AND `is_indefinitely_restricted` is false |
| Timed restriction | `restriction_ends_at` is not null AND `is_indefinitely_restricted` is false |
| Indefinite restriction | `is_indefinitely_restricted` is true AND `restriction_ends_at` is null |

`liftExpiredRestrictions()` must only touch timed restrictions — never users where `is_indefinitely_restricted = true`.

### AI-Ready Hooks (No AI Now)

The schema includes nullable JSON columns and an event hook as seams for future AI integration. No AI logic, no listeners, and no external services are to be implemented now.

- `moderation_metadata json nullable` and `is_system_bot boolean default(false)` are added to the users table via the **Task 1 `add_moderation_fields_to_users_table` migration only**
- `ai_analysis json nullable` and `metadata json nullable` are added to the reports table
- Dispatch a `ReportCreated` event after commit using `DB::afterCommit()` inside the transaction
- Create the `ReportCreated` event class in `app/Events/` — no listeners yet
- `ReportCreated` is not used for audit logging; logging is synchronous inside the transaction via `ModerationEventService`
- Cast `moderation_metadata` as `array` in the User model
- Cast `ai_analysis` and `metadata` as `array` in the Report model
- The `is_system_bot` flag allows a future "Virtual Moderator" bot user to participate in the moderation queue as a normal user without special UI logic
- AI actions can be recorded in `moderation_events` with `actor_id = null` and `metadata['is_ai'] = true`

### WorkOS Auth

- The existing `users` table has a `workos_id` column (not `workos_user_id`)
- Users are created on first WorkOS login
- `account_created_at` is set on first WorkOS login if null, using the WorkOS user's `created_at` timestamp; fallback to `users.created_at` if unavailable
- Do not add password fields or standard Laravel auth scaffolding

### Charset

`config/database.php` already sets `utf8mb4` / `utf8mb4_unicode_ci` globally. Do not add per-migration `ALTER TABLE ... CONVERT TO CHARACTER SET` statements. Do not change global charset or collation to `utf8mb4_bin`.

---

## 2. Existing Tables — Do Not Recreate or Modify These Files

### `users` (already exists)

```
id                  char(36) PK
name                string
email               string UNIQUE
email_verified_at   timestamp nullable
workos_id           string UNIQUE
remember_token      string nullable
avatar              text nullable
role                string default('user')
is_admin            boolean default(false)
created_at          timestamp
updated_at          timestamp
```

### `sessions` (already exists)

```
id            string PK
user_id       char(36) nullable indexed
ip_address    string(45) nullable
user_agent    text nullable
payload       longText
last_activity integer indexed
```

### `jobs` (already exists — Laravel default jobs table)

---

## 3. What You Need to Build

Work through these in order. Complete and test each migration before moving to the next. Create the migration, then the model, then the service class if applicable, then controllers and routes.

---

### TASK 1 — Add moderation fields to existing `users` table

**Migration name:** `add_moderation_fields_to_users_table`
**Important:** This is an `add_*` migration — do not touch the original users table migration file.

```
is_moderator_probationary   boolean default(false)
violation_count             tinyInteger unsigned default(0)
appeal_count                tinyInteger unsigned default(0)
is_indefinitely_restricted  boolean default(false)
restriction_ends_at         timestamp nullable
next_appeal_eligible_at     timestamp nullable
account_created_at          timestamp nullable
is_system_bot               boolean default(false)       -- AI-ready: identifies Virtual Moderator bot users
moderation_metadata         json nullable                -- AI-ready: future behavioral scoring storage
```

Place all new columns after `is_admin` using `->after('is_admin')`. The `down()` method must drop all of these columns cleanly.

**Note on `account_created_at`:** Set on first WorkOS login (if null) using the WorkOS user's `created_at` timestamp. Used for the 90-day moderator application eligibility check. Do not confuse with `users.created_at`.

**Note on `next_appeal_eligible_at`:**
- This is the canonical field for appeal eligibility timing.
- `null` explicitly means **permanent ineligibility** (never eligible again), not "unset".

---

### TASK 2 — `reports` table

**Migration name:** `create_reports_table`
**Model:** `App\Models\Report`

```
id                   char(36) PK
reporter_id          char(36) FK → users.id, SET NULL on delete, nullable
reported_user_id     char(36) FK → users.id, SET NULL on delete, nullable
reportable_type      string(100)
reportable_id        char(36)
reason               ENUM('hate_speech','violence','manipulation','spam','harassment','language','other')
reporter_note        text nullable
status               ENUM('pending','assigned','under_review','resolved','dismissed','escalated') default('pending')
assigned_to          char(36) FK → users.id, SET NULL on delete, nullable
assigned_at          timestamp nullable
resolved_by          char(36) FK → users.id, SET NULL on delete, nullable
resolution           ENUM('violation_confirmed','dismissed','escalated_to_admin') nullable
resolution_note      text nullable
resolved_at          timestamp nullable
is_against_moderator boolean default(false)
ai_analysis          json nullable        -- AI-ready: future toxicity/classification scores
metadata             json nullable        -- AI-ready: context (e.g. IP, repeat-report count)
created_at / updated_at
```

**Indexes:**
- Index on `reporter_id`
- Index on `assigned_to`
- Index on `status`
- Composite index on `(reported_user_id, created_at)`
- Composite index on `(reportable_type, reportable_id)`

**Notes:**
- Do NOT use `morphs()` — define polymorphic columns manually as shown above
- `is_against_moderator` is auto-set in `ReportService::createReport()` if `$reportedUser->role === 'moderator'`
- Cast `ai_analysis` and `metadata` as `array` in the Report model
- Dispatch `ReportCreated` event via `DB::afterCommit()` after save

---

### TASK 3 — `moderator_decisions` table

**Migration name:** `create_moderator_decisions_table`
**Model:** `App\Models\ModeratorDecision`

> Must run before `create_violations_table` (TASK 4) — violations holds a FK to this table.

```
id              char(36) PK
moderator_id    char(36) FK → users.id, SET NULL on delete, nullable
report_id       char(36) FK → reports.id, CASCADE on delete
decision        ENUM('confirmed','dismissed','escalated')
requires_cosign boolean default(false)
cosigned_by     char(36) FK → users.id, SET NULL on delete, nullable
cosigned_at     timestamp nullable
created_at      timestamp   -- no updated_at; append-only
```

**Indexes:** `(moderator_id, created_at)`, `report_id`

**Notes:**
- Append-only with one permitted exception: `cosigned_by` and `cosigned_at` are completion fields — they start null and are filled in when an admin cosigns. These are the only two fields that may be written after creation. The decision content (`decision`, `requires_cosign`, `moderator_id`, `report_id`) is permanently immutable.
- `ReportService::resolveReport()` creates the decision first, then passes it to `ViolationService::confirmViolation()`
- A moderator cannot be assigned or resolve a report filed against themselves
- Cosign count for probation lift must be queried from `moderator_decisions` where `cosigned_at IS NOT NULL` — do not use JSON metadata queries for this

---

### TASK 4 — `violations` table

**Migration name:** `create_violations_table`
**Model:** `App\Models\Violation`

```
id                    char(36) PK
user_id               char(36) FK → users.id, SET NULL on delete, nullable
report_id             char(36) FK → reports.id, SET NULL on delete, nullable
moderator_decision_id char(36) FK → moderator_decisions.id, CASCADE on delete, UNIQUE
confirmed_by          char(36) FK → users.id, SET NULL on delete, nullable
rule_reference        string(20)
violation_number      tinyInteger unsigned
consequence_applied   ENUM('7_day','30_day','indefinite')
restriction_ends_at   timestamp nullable
applied_to_user       boolean default(false)  -- false if cosign pending; true once applied
moderator_note        text nullable
created_at / updated_at
```

**Indexes:** `(user_id, created_at)`, `moderator_decision_id` (unique)

**FK note:** Use `foreignUuid('moderator_decision_id')->constrained('moderator_decisions')->cascadeOnDelete()` — do not use `uuid()` without the constraint.

**Cosign hold:** Create Violation immediately with `applied_to_user = false`. Do NOT mutate user restriction fields until admin cosigns.

---

### TASK 5 — `appeals` table

**Migration name:** `create_appeals_table`
**Model:** `App\Models\Appeal`

```
id                  char(36) PK
user_id             char(36) FK → users.id, SET NULL on delete, nullable
appeal_number       tinyInteger unsigned
user_statement      text
status              ENUM('pending','under_review','approved','denied') default('pending')
reviewed_by         char(36) FK → users.id, SET NULL on delete, nullable
admin_decision_note text nullable
submitted_at        timestamp
decided_at          timestamp nullable
eligible_from       timestamp
created_at / updated_at
```

**Indexes:** `(user_id, created_at)`, `status`

### Appeal eligibility — `next_appeal_eligible_at` is the single source of truth

| Event | `next_appeal_eligible_at` set to |
|---|---|
| First indefinite restriction applied | `now() + 1 year` |
| Appeal #1 approved, then new violation occurs | `now() + 5 years` |
| Appeal #2 decided (any outcome) OR second post-appeal violation | `null` — permanent, no further appeals |

**Notes:**
- Permanent ineligibility occurs **only** when `next_appeal_eligible_at === null`.
- `appeal_count` is historical/numbering only; do not use it in eligibility checks.
- `next_appeal_eligible_at` is the canonical timestamp used for all eligibility decisions.

---

### TASK 6 — `moderator_applications` table

**Migration name:** `create_moderator_applications_table`
**Model:** `App\Models\ModeratorApplication`

```
id                      char(36) PK
user_id                 char(36) FK → users.id, SET NULL on delete, nullable, UNIQUE
motivation              text
scenario_response_1     text
scenario_response_2     text
conflicts_of_interest   text nullable
status                  ENUM('pending','approved','denied','deferred') default('pending')
reviewed_by             char(36) FK → users.id, SET NULL on delete, nullable
admin_notes             text nullable
decided_at              timestamp nullable
created_at / updated_at
```

**Eligibility (enforce in Form Request or service):** `violation_count = 0`, no active restriction, `account_created_at` ≥ 90 days ago, no existing `pending` or `deferred` application.

---

### TASK 7 — `moderator_performance_reviews` table

**Migration name:** `create_moderator_performance_reviews_table`
**Model:** `App\Models\ModeratorPerformanceReview`

```
id              char(36) PK
moderator_id    char(36) FK → users.id, SET NULL on delete, nullable
report_id       char(36) FK → reports.id, CASCADE on delete
status          ENUM('pending','reviewed') default('pending')
admin_outcome   ENUM('no_action','warning_issued','role_revoked') nullable
admin_notes     text nullable
reviewed_by     char(36) FK → users.id, SET NULL on delete, nullable
reviewed_at     timestamp nullable
created_at / updated_at
```

**Auto-creation:** In `ReportService::createReport()`, if `is_against_moderator = true`, auto-create a linked `ModeratorPerformanceReview`.

---

### TASK 8 — `moderation_events` table (Append-only audit log)

**Migration name:** `create_moderation_events_table`
**Model:** `App\Models\ModerationEvent`

This table is the immutable audit log for the trust and safety system. It is strictly append-only:
- **No `updated_at` column**
- No update or delete routes or methods anywhere in the application

```
id                       char(36) PK
event_type               string(50)      -- e.g. 'report_created', 'violation_confirmed', 'restriction_applied'
actor_id                 char(36) FK → users.id, SET NULL on delete, nullable  -- null = system/automation/AI
subject_user_id          char(36) FK → users.id, SET NULL on delete, nullable  -- user being impacted
report_id                char(36) FK → reports.id, SET NULL on delete, nullable
violation_id             char(36) FK → violations.id, SET NULL on delete, nullable
appeal_id                char(36) FK → appeals.id, SET NULL on delete, nullable
moderator_application_id char(36) FK → moderator_applications.id, SET NULL on delete, nullable
metadata                 json nullable   -- before/after snapshots, notes, AI flags (e.g. is_ai => true)
created_at               timestamp
```

**Indexes:**
- Index on `event_type`
- Composite index on `(subject_user_id, created_at)`
- Composite index on `(actor_id, created_at)`
- Composite index on `(report_id, created_at)`
- Index on `violation_id`
- Index on `appeal_id`
- Index on `moderator_application_id`

**`ModerationEventService`:**

Create `app/Services/ModerationEventService.php` with:

```php
public function log(
    string $type,
    ?User $actor,
    ?User $subject,
    array $metadata = [],
    ?Report $report = null,
    ?Violation $violation = null,
    ?Appeal $appeal = null,
    ?ModeratorApplication $application = null
): void
```

Store `report_id = $report?->id`, `violation_id = $violation?->id`, etc. inside the method.

**Critical rules:**
- Do NOT use Laravel Observers or generic Event Listeners for this audit log
- Every `ModerationEventService::log()` call must be made **inside the same `DB::transaction()`** as the state mutation it records — audit log and state must never be able to diverge on rollback
- For system-initiated actions (scheduler jobs), pass `actor_id = null`
- For future AI actions, pass `actor_id = null` and `metadata['is_ai'] = true`
- Audit logging must not rely on `ReportCreated` or any Event Listener. `ModerationEventService::log()` must be called explicitly inside the same `DB::transaction()` as the state change. `ReportCreated` exists only as a future AI seam and must remain listener-free for now.

**Minimum event types to log on day one:**

| Service method | Event type string |
|---|---|
| `ReportService::createReport()` | `report_created` |
| `ReportService::assignReport()` | `report_assigned` |
| `ReportService::resolveReport()` | `report_resolved` |
| `ReportService::dismissReport()` | `report_dismissed` |
| `ReportService::escalateReport()` | `report_escalated` |
| `ReportService::returnStaleReports()` | `report_returned_to_queue` |
| `ViolationService::confirmViolation()` | `violation_confirmed` |
| `ViolationService::cosignDecision()` | `decision_cosigned` + `restriction_applied` |
| `ViolationService::liftExpiredRestrictions()` | `restriction_lifted` |
| `AppealService::submitAppeal()` | `appeal_submitted` |
| `AppealService::decideAppeal()` | `appeal_decided` |
| `AppealService::handlePostAppealViolation()` | `restriction_applied` |
| Moderator application decided | `moderator_application_decided` |
| Probation lifted | `moderator_probation_lifted` |

---

## 4. Events to Create

### `ReportCreated`

**Location:** `app/Events/ReportCreated.php`

Create the event class now. No listeners yet — seam for future AI integration.

Dispatch in `ReportService::createReport()` using `DB::afterCommit()`:

```php
DB::afterCommit(function () use ($report) {
    event(new ReportCreated($report));
});
```

---

## 5. Service Classes to Create

Create in `app/Services/`. Controllers must be thin — call service, return response.

**All service methods writing to multiple tables must use `DB::transaction()`. Counter-incrementing methods must also use `lockForUpdate()` on the user row. All audit log calls must be inside the same transaction.**

---

### `ModerationEventService`

See TASK 8 for the full method signature. This service is injected into all other moderation services.

---

### `ReportService`

`createReport(User $reporter, User $reportedUser, Model $reportable, string $reason, ?string $note): Report`
- Validates reporter is not reporting themselves
- Rate limit: max 10/hour using `Illuminate\Support\Facades\RateLimiter`
- Sets `is_against_moderator = true` if `$reportedUser->role === 'moderator'`
- If `is_against_moderator`, auto-creates `ModeratorPerformanceReview`
- Logs `report_created` via `ModerationEventService`
- Dispatches `ReportCreated` via `DB::afterCommit()`
- Wraps in `DB::transaction()`

`assignReport(Report $report, User $moderator): Report`
- Validates moderator is not the reported user
- Sets `status = assigned`, records `assigned_to` and `assigned_at`
- Logs `report_assigned`
- Throws if already assigned

`resolveReport(Report $report, User $moderator, string $resolution, string $note): Report`
- Validates moderator is not the reporter or reported user
- Creates `ModeratorDecision` first
- If `violation_confirmed`, calls `ViolationService::confirmViolation()` passing the decision instance
- Logs `report_resolved`
- Sends `ReportResolvedNotification`
- Wraps in `DB::transaction()`

`dismissReport(Report $report, User $moderator, string $note): Report`
- Creates `ModeratorDecision`
- Logs `report_dismissed`
- Sends `ReportResolvedNotification`
- Wraps in `DB::transaction()`

`escalateReport(Report $report, User $moderator): Report`
- Creates `ModeratorDecision`
- Logs `report_escalated`

`returnStaleReports(): void`
- Hourly scheduler job
- Resets `assigned` reports older than 24h to `pending`
- Logs `report_returned_to_queue` for each

---

### `ViolationService`

`confirmViolation(User $user, Report $report, User $confirmedBy, string $ruleReference, string $moderatorNote, ModeratorDecision $decision): Violation`
- Wraps in `DB::transaction()` with `lockForUpdate()` on user
- Increments `violation_count`
- Consequence: `1 → 7_day`, `2 → 30_day`, `3+ → indefinite`
- Creates `Violation` with `applied_to_user = false` and `moderator_decision_id = $decision->id`
- If moderator `is_moderator_probationary`: sets `requires_cosign = true` on `$decision`, does NOT mutate user
- If NOT probationary and `appeal_count > 0`: calls `AppealService::handlePostAppealViolation()`
- If NOT probationary and `appeal_count = 0`: applies restriction, sets `applied_to_user = true`
- Logs `violation_confirmed`
- Sends `ViolationAppliedNotification` (or pending notification if cosign required)

`cosignDecision(ModeratorDecision $decision, User $admin): void`
- Wraps in `DB::transaction()` with `lockForUpdate()` on the violating user (the subject of the violation, not the moderator)
- Records cosign, fetches the linked Violation by `moderator_decision_id = $decision->id` (not by user or any other heuristic), applies consequence
- After recording the cosign, determine whether moderator probation should lift:
  - Count decisions where `moderator_id = $decision->moderator_id` and `cosigned_at IS NOT NULL`
  - If count ≥ 10: lock the moderator user row with `lockForUpdate()`, set `is_moderator_probationary = false`, log `moderator_probation_lifted`, send `ProbationLiftedNotification`
  - This check MUST run inside the same `DB::transaction()` as the cosign write
- Sets `applied_to_user = true`
- Logs `decision_cosigned` and `restriction_applied`
- Sends `ViolationAppliedNotification`

`liftExpiredRestrictions(): void`
- Hourly scheduler job
- Only touches: `restriction_ends_at <= now()` AND `is_indefinitely_restricted = false`
- Clears `restriction_ends_at`
- Logs `restriction_lifted` with `actor_id = null`
- Sends `RestrictionLiftedNotification`

---

### `AppealService`

`next_appeal_eligible_at` is the single source of truth for appeal eligibility. `appeal_count` is used only for appeal numbering and for post-appeal consequence tiering (e.g., first post-appeal violation vs second), but must never be used to decide whether an appeal can be submitted.

`checkEligibility(User $user): array`

Returns:

```php
['eligible' => bool, 'eligible_from' => Carbon|null]
```

Eligibility rules:

1. If `$user->next_appeal_eligible_at === null`:
   Permanently ineligible for further appeals.
   ```php
   return ['eligible' => false, 'eligible_from' => null];
   ```

2. Else if `$user->next_appeal_eligible_at <= now()`:
   Eligible to submit an appeal.
   ```php
   return [
       'eligible'      => true,
       'eligible_from' => $user->next_appeal_eligible_at,
   ];
   ```

3. Otherwise:
   Not yet eligible. Return the earliest eligible date.
   ```php
   return [
       'eligible'      => false,
       'eligible_from' => $user->next_appeal_eligible_at,
   ];
   ```

**Notes:**
- Use strict comparison (`=== null`) when checking timestamps.
- Permanent ineligibility occurs **only** when `next_appeal_eligible_at === null`.
- `appeal_count` is historical/numbering only; do not use it in eligibility checks.
- Implementation must follow the eligibility rules defined in TASK 5 exactly.

`submitAppeal(User $user, string $statement): Appeal`
- Calls `checkEligibility()`, throws `AppealNotEligibleException` with earliest date if not eligible
- Creates appeal with `appeal_number = $user->appeal_count + 1`
- Logs `appeal_submitted`

`decideAppeal(Appeal $appeal, User $admin, string $decision, string $note): Appeal`
- Wraps in `DB::transaction()` with `lockForUpdate()` on the appellant user row
- Increments `appeal_count` **(must happen for BOTH approved and denied decisions)**
- If approved: lift restriction if applicable, log `appeal_decided`
- If denied: recalculate `next_appeal_eligible_at` (e.g. `now()->addYear()`), log `appeal_decided`
- **Crucial override:** If the newly incremented `appeal_count >= 2`, strictly set `next_appeal_eligible_at = null` (permanent ineligibility) regardless of decision outcome
- Sends `AppealDecisionNotification`

`handlePostAppealViolation(User $user): void`
- Called by `ViolationService::confirmViolation()` when `appeal_count > 0`
- `appeal_count = 1`: indefinite restriction, `next_appeal_eligible_at = now()->addYears(5)`
- `appeal_count = 2`: permanent restriction, `next_appeal_eligible_at = null`
- Logs `restriction_applied`

---

## 6. Scheduled Jobs

Register in `routes/console.php`:

```php
Schedule::call(fn() => app(ViolationService::class)->liftExpiredRestrictions())->hourly();
Schedule::call(fn() => app(ReportService::class)->returnStaleReports())->hourly();
```

---

## 7. Notifications to Create

Create in `app/Notifications/`. Implement `mail` channel at minimum.

| Class | Sent when | Recipient |
|---|---|---|
| `ViolationAppliedNotification` | Violation applied | Reported user |
| `RestrictionLiftedNotification` | Timed restriction expires | Affected user |
| `AppealDecisionNotification` | Admin decides appeal | Appellant |
| `ReportResolvedNotification` | Report closed | Reporter |
| `ModeratorApplicationDecisionNotification` | Admin decides application | Applicant |
| `ProbationLiftedNotification` | 10 cosigned decisions reached | Moderator |

---

## 8. Policies & Gates

**ReportPolicy:** `create` (any auth, rate limited in service), `assign` (mod/admin, not self-conflicted), `resolve` (mod/admin, not reporter or reported), `escalate` (mod), `viewAny` (mod/admin)

**ViolationPolicy:** `create` (admin or mod — cosign handled in service), `viewAny` (mod/admin)

**AppealPolicy:** `create` (indefinitely restricted + eligible), `decide` (admin only), `viewAny` (admin only)

**ModeratorApplicationPolicy:** `create` (no restriction, 90-day account, no pending application), `decide` (admin only)

**ModeratorPerformanceReviewPolicy:** `viewAny`, `decide` (admin only)

**ModeratorDecisionPolicy:** `cosign` (admin only) — no update/delete, records are immutable

**ModerationEventPolicy:** `viewAny` (admin only) — no create/update/delete via policy; writes are service-only

---

## 9. Routes

All moderation routes go in `routes/moderation.php` — not `routes/web.php`.

```php
// Moderator routes
Route::middleware(['auth', 'role:moderator,admin'])->prefix('mod')->name('mod.')->group(function () {
    Route::get('/reports', [ModReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [ModReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/assign', [ModReportController::class, 'assign'])->name('reports.assign');
    Route::post('/reports/{report}/resolve', [ModReportController::class, 'resolve'])->name('reports.resolve');
    Route::post('/reports/{report}/dismiss', [ModReportController::class, 'dismiss'])->name('reports.dismiss');
    Route::post('/reports/{report}/escalate', [ModReportController::class, 'escalate'])->name('reports.escalate');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/appeals', [AdminAppealController::class, 'index'])->name('appeals.index');
    Route::get('/appeals/{appeal}', [AdminAppealController::class, 'show'])->name('appeals.show');
    Route::post('/appeals/{appeal}/decide', [AdminAppealController::class, 'decide'])->name('appeals.decide');
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications/{application}/decide', [AdminApplicationController::class, 'decide'])->name('applications.decide');
    Route::get('/moderators', [AdminModeratorController::class, 'index'])->name('moderators.index');
    Route::get('/performance-reviews', [AdminPerformanceReviewController::class, 'index'])->name('performance.index');
    Route::post('/performance-reviews/{review}/decide', [AdminPerformanceReviewController::class, 'decide'])->name('performance.decide');
    Route::post('/decisions/{decision}/cosign', [AdminDecisionController::class, 'cosign'])->name('decisions.cosign');
});

// User-facing routes
Route::middleware(['auth'])->group(function () {
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::post('/appeals', [AppealController::class, 'store'])->name('appeals.store');
});
```

**Note:** Controllers do not exist yet. Scaffold routes with controller references commented out until controllers are built per implementation order.

---

## 10. What Claude Code Should NOT Do

- **Do not** hard-delete user accounts — "delete profile" must be implemented as deactivation or soft-delete; moderation history, violations, and audit records must be retained. This is required for ban enforcement integrity and future cross-referencing of repeat offenders.
- **Do not** drop or recreate the `users`, `sessions`, or `jobs` tables
- **Do not** use `binary(16)` for any ID column — all IDs are `char(36)`
- **Do not** use `morphs()` helper — define polymorphic columns manually
- **Do not** add frontend moderation UI to the Expo mobile app — web only
- **Do not** build a login system or password reset — WorkOS handles auth
- **Do not** run `migrate:fresh` — always use `migrate`
- **Do not** assume `role` needs to be added to users — it already exists
- **Do not** override `newUniqueId()` — `HasUuids` handles UUIDv7 automatically
- **Do not** recreate `admin`, `role`, or `auth.workos` middleware — they already exist
- **Do not** use `auth.workos` on moderation routes — use `auth`
- **Do not** add per-migration `ALTER TABLE ... CONVERT TO CHARACTER SET` statements
- **Do not** change charset/collation to `utf8mb4_bin`
- **Do not** write service methods that mutate multiple tables without `DB::transaction()`
- **Do not** apply restriction fields to the user when `requires_cosign = true`
- **Do not** add AI logic, Ollama integration, or AI listeners — JSON columns and `ReportCreated` event are the only AI-related additions
- **Do not** use PHP 8.3, 8.4, or 8.5-specific features — code must be PHP ≥ 8.2 compatible
- **Do not** place moderation routes in `routes/web.php` — use `routes/moderation.php`
- **Do not** use Observers or Event Listeners for the audit log — call `ModerationEventService::log()` explicitly inside transactions
- **Do not** add update or delete logic for `moderation_events` — it is fully immutable. `moderator_decisions` is append-only with one exception: `cosigned_by` and `cosigned_at` may be written once as completion fields when an admin cosigns. No other fields on `moderator_decisions` may be updated.

---

## 11. When You Are Done

1. `php artisan migrate` — confirm all tables created without errors
2. `php artisan route:list` — confirm all moderation routes registered
3. `php artisan schedule:list` — confirm scheduler tasks registered
4. `php artisan test` — confirm existing tests still pass
5. Report any deviations from this spec and why

---

## 12. Smoke Tests

- Probationary moderator resolves report → Violation created with `applied_to_user = false`, user restriction NOT applied
- Admin cosigns → restriction applied, `applied_to_user = true`, notification sent, `moderation_events` row written
- Moderator hits 10 cosigned decisions → `is_moderator_probationary = false`, `ProbationLiftedNotification` sent
- Appeal submitted before eligible date → throws `AppealNotEligibleException` with earliest eligible date
- `liftExpiredRestrictions()` → only clears timed restrictions; never touches `is_indefinitely_restricted = true` users
- Stale assigned reports reset after 24h, `report_returned_to_queue` event logged
- Reporter cannot report themselves
- Moderator cannot assign or resolve a report filed against themselves
- `ReportCreated` event dispatched after commit (verify via event fake)
- Every state-changing service method produces a corresponding `moderation_events` row inside the same transaction


