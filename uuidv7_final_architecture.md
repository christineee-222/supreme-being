# UUIDv7 Binary Architecture Plan (Laravel)

## Core Goal

We are standardizing on **UUIDv7 stored as `BINARY(16)`** in
MySQL/SQLite to achieve:

-   No relationship mismatches
-   No hidden casting hacks
-   No controller workarounds
-   No future refactors around IDs
-   Clean professional architecture long‑term

This is a **foundational architectural decision**.

------------------------------------------------------------------------

## Core Decision (Critical)

-   Model primary key `id` must remain **raw binary internally**.
-   Remove the `BinaryUuid` cast from all model primary keys.
-   UUID strings should only exist at **system boundaries**:

### Allowed UUID String Boundaries

-   API responses (Resources)
-   URLs / route parameters
-   JWT / auth claims
-   Tests

This resolves the Eloquent issue where parent‑side `HasOne` / `HasMany`
relationships compare UUID string PKs against binary FK columns.

------------------------------------------------------------------------

## 1. Update `UsesBinaryUuidV7` Trait (Single Source of Truth)

This trait becomes the canonical UUID utility.

### Add

**UUID accessor**

``` php
$model->uuid
```

Converts raw binary PK → RFC4122 UUID string using:

``` php
Uuid::fromBinary($this->getRawOriginal($this->getKeyName()))->toRfc4122()
```

### Keep

-   `binaryId()` helper for WHERE clauses / FK comparisons.
-   `resolveRouteBinding()` converting UUID string → binary for PK
    lookups.
-   Respect `getRouteKeyName()` if slugs are used.

------------------------------------------------------------------------

## 2. Remove PK Casts Globally

Across all models:

-   Remove `BinaryUuid::class` cast from `id`.
-   Do NOT replace with another cast.
-   Primary keys must remain raw binary inside Eloquent.

### Keep FK Casts

`BinaryUuidFk` stays on foreign keys:

-   `set()` → UUID string → binary
-   `get()` → raw binary unchanged

This keeps relationships correct.

------------------------------------------------------------------------

## 3. API Resources = UUID Formatting Layer

Resources are the **only official presentation layer**.

### Requirements

Always output:

``` php
'id' => $this->uuid
```

Convert any exposed binary FK fields:

-   `user_id`
-   `event_id`
-   `essence_numen_id`
-   etc.

Use Symfony UUID conversion:

``` php
Uuid::fromBinary($value)->toRfc4122()
```

### Rule

No raw binary IDs should ever reach JSON responses.

Ensure all API endpoints return Resources (never raw models).

------------------------------------------------------------------------

## 4. Fix Event RSVP API Relationship Issue

We are NOT eager‑loading viewer‑specific RSVP.

### Do NOT:

``` php
$event->load('rsvpForViewer');
```

Reason:

Parent‑side eager loading may mismatch binary FK values.

### Instead

Query manually using:

``` php
$event->binaryId()
$request->user()->binaryId()
```

Then either:

**Preferred:**

``` php
$event->setRelation('rsvpForViewer', $rsvp);
```

This preserves `whenLoaded()` logic.

**OR**

Return `rsvp` / `rsvp_status` explicitly from controller.

### Goal

-   DB stays binary
-   API stays UUID string
-   Zero relationship mismatches

------------------------------------------------------------------------

## 5. Auth / JWT Handling Audit

Audit anything that:

-   Mints JWT tokens
-   Stores IDs in cache/session
-   Reads claims in middleware

### Requirements

-   JWT claims must use `$user->uuid` (string).
-   Middleware must convert UUID string → binary for DB lookup.

Follow approach used in `MobileAuthExchangeController`.

------------------------------------------------------------------------

## 6. Frontend / Inertia & Serialization Fail‑Safe (Crash‑Proof Layer)

### Standard Rule

-   Do NOT pass raw models directly to Inertia or JSON responses.
-   Always transform using Resources or explicitly built arrays.

### Fail‑Safe Requirement (Trait Level)

Keep a minimal `attributesToArray()` override in `UsesBinaryUuidV7` as
an **insurance layer**.

Purpose:

-   Prevent malformed UTF‑8 crashes
-   Prevent Inertia blank screens
-   Protect debugging scenarios (`dd()`, accidental raw model
    serialization)

### Fail‑Safe Rules

**Primary Key**

-   If `id` in the serialized array is 16‑byte binary: → Convert to
    RFC4122 UUID string.
-   This does NOT mutate the model attribute --- serialization only.

**Foreign Keys**

-   Convert any `BinaryUuidFk` attributes from binary → UUID strings.

**Convenience Field**

Ensure:

``` php
$attributes['uuid'] = <UUID string>
```

### Important Clarification

Resources remain the authoritative formatting layer.

This override exists only as a crash‑prevention safety net.

------------------------------------------------------------------------

## 7. Test Infrastructure (Critical)

Tests must remain readable and future‑proof.

### Add Helpers (TestCase or Trait)

-   `$model->uuid` becomes primary test identifier.
-   DB assertion helper:

Example behavior:

``` php
assertDatabaseHasUuid('events', ['user_id' => $user->uuid]);
```

Helper converts UUID string → binary internally.

### Audit Tests

-   Replace `$model->id` in URLs with `$model->uuid`.
-   Update JSON assertions accordingly.

------------------------------------------------------------------------

## 8. Database Sanity Check

Confirm:

-   All UUID PK/FK columns are `BINARY(16)`
-   No lingering `CHAR(36)` UUID columns
-   UUIDv7 indexes intact

No migration required unless inconsistencies found.

------------------------------------------------------------------------

## Final Architecture Outcome

### Internally

-   `$model->id` = raw binary
-   Relationships work natively
-   Fast indexed UUIDv7 keys

### Externally

-   `$model->uuid` = canonical identifier
-   APIs, routes, JWT, tests use UUID strings

### Professional Separation

-   Models store data
-   Resources format data
-   Tests target public representation

No hidden casts.\
No controller hacks.\
No broken relationships.

------------------------------------------------------------------------

## Final Instruction

Please implement carefully, then:

-   Run Pint
-   Run full test suite
-   Report:
    -   Remaining failures
    -   Architectural concerns
    -   Edge cases

Before making further refactors.
