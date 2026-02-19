# UUID Refactor Plan (Laravel 12 + Sail): binary(16) → CHAR(36) UUID strings

> READ THIS ENTIRE DOCUMENT BEFORE TOUCHING ANYTHING.
> RE-READ THE RELEVANT SECTION BEFORE EVERY SINGLE DECISION.
> IF YOU ARE UNSURE ABOUT ANYTHING, STOP AND ASK.

---

## The Situation

This project is in production but the database is essentially empty:
- One real admin user (recreated automatically on next WorkOS login)
- One test/fake event (can be discarded)
- No meaningful user data to preserve

Therefore:
- ✅ We ARE allowed to modify existing migration files
- ✅ We WILL rebuild the database from scratch
- ✅ We WILL run `migrate:fresh` at the end — after explicit confirmation only
- ✅ This is NOT a live data migration. The goal is to make the codebase correct when rebuilt.

---

## Why We Are Doing This

Binary(16) UUIDs add significant developer friction at no meaningful benefit at this
project's current scale:
- IDs are unreadable in raw DB queries and logs
- Laravel helpers (`foreignUuid`, `uuidMorphs`, `HasUuids`) do not work cleanly with binary(16)
- Requires binary↔string conversion logic throughout the codebase
- Raw queries require `BIN_TO_UUID()` / `UUID_TO_BIN()` wrappers
- Route model binding and API serialization become fragile
- Factories, seeders, and tests all need custom handling

CHAR(36) UUID strings give all the same security benefits (non-enumerable,
non-sequential IDs) with none of the friction. At this project's scale,
developer velocity and reliability outweigh micro-optimisations.

---

## Preflight — No Code Changes Yet

First, confirm Sail is running and confirm the Laravel version:

```bash
./vendor/bin/sail artisan --version
./vendor/bin/sail php -v
```

Report both version numbers before proceeding. This plan is written for Laravel 12.
If the version is different, stop and flag it.

---

## UUID Generation

In Laravel 12, `HasUuids` generates true UUIDv7 by default. This is a change from
Laravel 10/11, which used time-ordered UUIDv4. The `HasVersion7Uuids` trait that
existed in Laravel 11 has been merged into `HasUuids` and removed. 

-Use only Laravel's built-in HasUuids trait. 
-Do not use UUIDv4. Do not use HasVersion4Uuids.
-Do not override newUniqueId().
-Do not manually generate UUIDs (Str::uuid(), Str::orderedUuid(), etc.).
-Do not install or use symfony/uid or any third-party UUID generator.

HasUuids alone is the authoritative UUID generation mechanism for this codebase.

Use `HasUuids` on every model with a UUID primary key. No additional packages,
no overrides, no custom generation logic:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Model {
    use HasUuids;
}
```

Do not install `symfony/uid` for UUID generation. Do not override `newUniqueId()`.
Do not use `Str::orderedUuid()` manually. `HasUuids` handles everything correctly.

---

## Sanctum UUID Compatibility — Critical

The most common point of failure when switching to UUID strings is Sanctum's
`personal_access_tokens` table. It uses a polymorphic relationship (`tokenable`).
If `users.id` is now a UUID string (CHAR(36)) but Sanctum's `tokenable_id` column
is still a BIGINT or binary(16), authentication will crash the moment a token is issued.

### The Fix

Locate the Sanctum migration in `database/migrations/`. It will be named something like
`xxxx_xx_xx_xxxxxx_create_personal_access_tokens_table.php`.

Replace the morph definition:

```php
// REMOVE THIS (or whatever binary/integer version exists):
$table->morphs('tokenable');

// REPLACE WITH THIS:
$table->uuidMorphs('tokenable');
```

`uuidMorphs('tokenable')` creates exactly two columns:
- `tokenable_type` — string/varchar, the model class name
- `tokenable_id` — char(36), compatible with CHAR(36) UUID strings

Do NOT modify any vendor files. Only modify the migration in `database/migrations/`.

### User Model Requirements for Sanctum

`app/Models/User.php` must have all of the following:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
}
```

---

## Core Rules — Non-Negotiable

Check yourself against every rule below before writing any migration, model,
relationship, query, factory, or controller.

**Rule 1 — Never use `binary()` for any ID column.**
Not for primary keys. Not for foreign keys. Not for polymorphic IDs.
If you see `binary()` in any file, that is what you are replacing.

**Rule 2 — Primary keys must always be:**
```php
$table->uuid('id')->primary();
```

**Rule 3 — Foreign keys must use `foreignUuid()` with explicit constraints:**
```php
// Non-nullable, cascade on delete:
$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

// Nullable, set null on delete:
$table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
```

**Rule 4 — Polymorphic relationships must use `uuidMorphs()`. This includes Sanctum's `tokenable`.**
```php
$table->uuidMorphs('reportable');
// Creates: reportable_type (string) + reportable_id (char(36))
// Never define these two columns manually.
// Never use morphs() if the parent model uses UUID primary keys.
```

**Rule 5 — All models with UUID primary keys must have:**
```php
protected $keyType = 'string';
public $incrementing = false;
```

**Rule 6 — UUID generation must use Laravel's built-in `HasUuids` trait. Nothing else.**
- No manual UUID generation in `boot()` methods
- No `newUniqueId()` overrides
- No `Str::uuid()` called manually
- No `Str::orderedUuid()` called manually
- No `symfony/uid` or any third-party UUID package for generation
- `HasUuids` handles everything automatically and produces true UUIDv7 in Laravel 12

**Rule 7 — Remove all binary conversion logic without exception.**
Delete or stop using:
- Any custom binary UUID cast classes or traits (e.g. `UsesBinaryUuidV7`, `BinaryUuidFk`, etc.)
- Any `boot()` methods that exist solely to generate or convert binary IDs
- Any `BIN_TO_UUID()` / `UUID_TO_BIN()` usage in queries
- Any `hex2bin()` / `bin2hex()` usage related to IDs
- Any `resolveRouteBinding()` overrides that existed only to handle binary IDs
- Any `->uuid` accessor patterns that existed only for binary conversion
- Any `getAttribute` / `setAttribute` methods using `bin2hex` or `hex2bin`
- Any custom Casts related to binary UUIDs

**Rule 8 — After this refactor, IDs are plain strings everywhere.**
Use `$model->id` universally. There is no secondary `uuid` field.

**Rule 9 — Do not guess. If anything is ambiguous, stop and ask.**

**Rule 10 — Do not run any destructive commands without explicit confirmation.**
Before `migrate:fresh`, summarise all changes and wait for approval.

---

## Known Tables and Special Cases

### `users` (exists, must be updated)
```
id                  binary(16) PK         → change to uuid('id')->primary()
name                string                → leave as-is
email               string UNIQUE         → leave as-is
email_verified_at   timestamp nullable    → leave as-is
workos_id           string UNIQUE         → leave as-is
remember_token                            → leave as-is
avatar              text nullable         → leave as-is
role                string default('user')→ leave as-is
is_admin            boolean default(false)→ leave as-is
created_at / updated_at                   → leave as-is
```

### `sessions` (exists, must be updated)
In Laravel 12 this is typically defined at the bottom of
`0001_01_01_000000_create_users_table.php`, not in a separate file — check first.
Replace the binary user_id column:

```php
// Remove this:
$table->binary('user_id', 16)->nullable()->index();

// Replace with this:
$table->string('user_id', 36)->nullable()->index();
```

Do NOT add a formal foreign key constraint on `sessions.user_id` —
this matches Laravel's default sessions table behaviour.

### `personal_access_tokens` (Sanctum — must be updated)
Find the Sanctum migration in `database/migrations/` and ensure it uses:

```php
$table->uuidMorphs('tokenable');
```

Not `morphs('tokenable')` and not any binary implementation.
See the Sanctum section above for full details.

### `jobs` (exists, likely safe)
Check whether it contains any binary UUID columns.
If it only uses auto-incrementing integers for its own ID, leave it
completely untouched. Report what you find before making any changes.

### Other tables
Audit all other migrations before touching them.
Report findings from Step 1 before making any changes.

---

## Step-by-Step Execution Plan

Work through these steps in order. Do not skip ahead.
Confirm findings with the project owner at each checkpoint marked ⏸ STOP.

---

### Step 1 — Audit every migration file (NO edits yet)

Read every file in `database/migrations/`. Identify every occurrence of:
- `binary(` — any binary column definition
- Binary primary keys
- Binary foreign keys
- Manually defined polymorphic columns that should be `uuidMorphs()`
- `morphs()` used where the parent model will have a UUID primary key
- Any `BIN_TO_UUID` / `UUID_TO_BIN` / `hex2bin` / `bin2hex` usage

Run:
```bash
./vendor/bin/sail bash -c "grep -RIn \"binary(\" database/migrations || true"
./vendor/bin/sail bash -c "grep -RIn \"BIN_TO_UUID\|UUID_TO_BIN\|hex2bin\|bin2hex\" database/migrations || true"
./vendor/bin/sail bash -c "grep -RIn \"morphs(\" database/migrations || true"
```

⏸ STOP — Report the full list of files and line numbers before making any changes.

---

### Step 2 — Update the `users` table migration

In the existing `create_users_table` migration:
- Change `$table->binary('id', 16)->primary()` to `$table->uuid('id')->primary()`
- Leave every other column exactly as-is

---

### Step 3 — Update the `sessions` migration

Find the sessions schema (may be at the bottom of the users migration or a separate file).
Replace the binary user_id column as described in the Known Tables section above.
No foreign key constraint.

---

### Step 4 — Update the Sanctum `personal_access_tokens` migration

Find the Sanctum migration in `database/migrations/`.
- Replace `$table->morphs('tokenable');` with `$table->uuidMorphs('tokenable');`
- Change nothing else unless it also contains binary UUID logic that needs removing

---

### Step 5 — Update all other migrations found in Step 1

For each remaining binary UUID usage found:
- Binary PK → `uuid('id')->primary()`
- Binary FK → `foreignUuid('column')->constrained()...`
- Manual polymorphic binary columns → `uuidMorphs('name')`
- `morphs()` where parent uses UUID → `uuidMorphs()`

Do not rename any columns unless absolutely unavoidable.
If you are unsure whether a column rename is needed, stop and ask.

---

### Step 6 — Update the User model

In `app/Models/User.php`:
- Ensure `use Laravel\Sanctum\HasApiTokens;` is present
- Add `use Illuminate\Database\Eloquent\Concerns\HasUuids;`
- Ensure both traits are used: `use HasApiTokens, HasUuids;`
- Confirm `protected $keyType = 'string';` is present
- Confirm `public $incrementing = false;` is present
- Remove any binary UUID traits, casts, accessors, mutators, or boot logic
- Remove any `getAttribute` / `setAttribute` methods using `bin2hex` or `hex2bin`
- Remove any custom Casts related to binary UUIDs
- Remove any `resolveRouteBinding()` override that existed only for binary ID handling

---

### Step 7 — Update all other models

Check every file in `app/Models/`. For each model that has a UUID primary key:
- Add `HasUuids` trait
- Confirm `$keyType` and `$incrementing`
- Remove all binary conversion logic and manual UUID generation

---

### Step 8 — Audit and update everything that touches IDs

Search for any remaining binary ID handling across the entire codebase:

```bash
./vendor/bin/sail bash -c "grep -RIn \"binary(\|BIN_TO_UUID\|UUID_TO_BIN\|hex2bin\|bin2hex\" . \
  --include='*.php' \
  --exclude-dir=vendor \
  --exclude-dir=node_modules \
  --exclude-dir=storage \
  --exclude-dir=bootstrap || true"

./vendor/bin/sail bash -c "grep -RIn \"UsesBinaryUuid\|BinaryUuid\|fromBinary\|toBinary\|binaryId\|resolveRouteBinding\|newUniqueId\|orderedUuid\" \
  app database routes resources tests config \
  --include='*.php' || true"
```

Pay particular attention to:
- **Controllers** — any ID handling in store/update/show methods
- **API Resources / Inertia shared data** — ID serialization
- **Policies** — ID comparisons
- **Form Requests** — UUID validation rules (use Laravel's built-in `'uuid'` validation rule)
- **Services and Jobs** — any ID passing or lookup logic
- **Factories** — remove any manual binary UUID generation; `HasUuids` handles it automatically
- **Seeders** — same as factories
- **Tests** — remove hardcoded binary IDs or binary comparison logic
- **Config files** — any package config referencing model binding or ID format

Report everything found. Update each one. If anything is ambiguous, stop and ask.

---

### Step 9 — Check for package-related ID assumptions

You are not looking to modify vendor files. You are checking whether any installed
package has its own published migrations in `database/migrations/` that still use
binary UUIDs or plain `morphs()`:

```bash
./vendor/bin/sail bash -c "grep -RIn \"binary(\|morphs(\" database/migrations --include='*.php' || true"
```

If any published package migration uses binary IDs or `morphs()` where UUID is needed,
stop and ask before deciding how to handle it.

⏸ STOP — Report results of Steps 8 and 9 before proceeding.

---

### Step 10 — Final confirmation before database rebuild

Before running anything destructive, confirm all of the following:
- Every changed file is summarised with what changed in it
- Steps 8 and 9 searches show no remaining binary/legacy ID logic
- All UUID models have `HasUuids`, correct `$keyType` and `$incrementing`
- All migrations use `uuid()`, `foreignUuid()`, and `uuidMorphs()` as appropriate
- Sanctum's `personal_access_tokens` migration uses `uuidMorphs('tokenable')`
- User model has both `HasApiTokens` and `HasUuids`

⏸ STOP — Wait for explicit approval before continuing.

Then run:
```bash
./vendor/bin/sail artisan migrate:fresh
```

If any migration fails, stop immediately and report the exact error
before attempting to fix anything.

---

### Step 11 — Final verification

Run the test suite and route check:
```bash
./vendor/bin/sail artisan test
./vendor/bin/sail artisan route:list
```

Verify the Sanctum schema:
```bash
./vendor/bin/sail mysql -e "DESCRIBE personal_access_tokens;"
```
Expected: `tokenable_id` should show as `char(36)` or `varchar(36)`.

Verify token issuance works end-to-end in Tinker:
```bash
./vendor/bin/sail artisan tinker
```
Then run:
```php
$user = App\Models\User::factory()->create();
echo $user->id;        // Should be a UUID string e.g. 018f4a3c-...
$token = $user->createToken('test-token')->plainTextToken;
echo $token;           // Should return a token string successfully
```

If token issuance fails, stop immediately and report the exact error.

Confirm all of the following before reporting complete:
- All migrations ran cleanly
- Tests pass (or clearly report which fail and why)
- Routes list without errors
- IDs serialise as plain UUID strings, not binary blobs
- Route model binding resolves correctly for UUID string routes
- Sanctum `tokenable_id` is `char(36)` or `varchar(36)`
- Token creation in Tinker succeeds

---

## Out of Scope — Do Not Do Any of the Following

This task is strictly the UUID storage format refactor, rebuild, and verification.

- Building the moderation system (a separate document exists for this)
- Adding moderation columns to the users table
- Creating new features, tables, services, controllers, or routes
- Any frontend or Inertia component changes unrelated to ID handling
- WorkOS configuration changes unless directly broken by this refactor
- Stripe or payment-related changes
- Any database design decisions beyond fixing binary(16) → CHAR(36)

---

## Stop Conditions

Stop immediately and ask if you encounter any of the following:

- A package migration that uses binary IDs or `morphs()` in a way not covered by this document
- Conflicting ID formats across tables not explained by this document
- Token issuance failure after `migrate:fresh`
- Any migration error during `migrate:fresh`
- Any situation where a required change would affect more than what is described here
- Any uncertainty about whether a change is in scope

When in doubt: stop, describe exactly what you found, and ask.