# UUIDv7 Binary Refactor – Pre‑Approval Corrections & Safety Notes

These are REQUIRED corrections and RECOMMENDED safety tweaks to apply **before executing the UUIDv7 binary refactor plan**.

This document supplements the main refactor instructions. Claude should read this FIRST before proceeding.

---

## 🚨 REQUIRED CORRECTIONS

### 1. Fix Binary UUID Trait Boot Method

The trait must use Laravel’s bootable trait convention:

**Correct method name:**
`bootUsesBinaryUuidV7()`

NOT:
`initializeUsesBinaryUuidV7()`

Example:

```php
protected static function bootUsesBinaryUuidV7()
{
    static::creating(function ($model) {
        if (!$model->getKey()) {
            $model->{$model->getKeyName()} =
                \Symfony\Component\Uid\Uuid::v7()->toBinary();
        }
    });
}
```

Without this:
- IDs will not generate
- Inserts may fail with null PKs

This is critical.

---

### 2. Cast ALL Binary UUID Foreign Keys

Every FK column stored as BINARY(16) must have a cast:

Examples:

```php
protected $casts = [
    'id' => BinaryUuid::class,
    'user_id' => BinaryUuid::class,
    'event_id' => BinaryUuid::class,
    'essence_numen_id' => BinaryUuid::class,
    'forum_id' => BinaryUuid::class,
];
```

Missing FK casts cause:
- Broken relationships
- Failed comparisons
- Hidden bugs

Apply casts consistently across ALL models.

---

### 3. Route Model Binding for Slugs

Models using slugs must override:

```php
public function getRouteKeyName()
{
    return 'slug';
}
```

Apply ONLY to public slug models:

- User (if public profiles)
- Poll
- Forum
- Event
- Legislation
- Portrait

Internal relations must still use UUID PKs.

---

### 4. EssenceNumen Table Naming

The model uses:

```php
protected $table = 'essence_numen';
```

All foreign keys must reference:

```php
->on('essence_numen')
```

Never:
- `essence_numens`
- default plural guesses

This must be consistent across all migrations.

---

## 👍 RECOMMENDED SAFETY IMPROVEMENTS

### A. Archive Old Migrations Instead of Deleting

Instead of deleting redundant migrations, move them to:

```
database/migrations_archive/
```

Benefits:
- Preserves history
- Easier rollback understanding
- Prevents accidental schema loss

---

### B. Optional Binary UUID Logging Helper

Binary UUIDs can appear unreadable in logs.

Consider helper:

```php
Str::uuidFromBinary($bytes)
```

This is optional but improves debugging clarity.

---

### C. Slug Trait Collision Handling

Ensure `HasUniqueSlug`:

- Checks DB for existing slug
- Appends numeric suffix (-2, -3, etc)
- Only runs on `creating`
- Produces deterministic results

This prevents unstable URLs.

---

## 🧠 FINAL NOTE

This refactor is safe because:

- Clean slate database
- Early project stage
- Version control protection
- Explicit migration reset planned

# Additional Requirements

## Critical Casting Verification
Before completing the refactor, verify these models have complete FK casting:

**EventRsvp must cast:**
- `id` => BinaryUuid::class
- `user_id` => BinaryUuid::class  
- `event_id` => BinaryUuid::class

**Comment must cast:**
- `id` => BinaryUuid::class
- `user_id` => BinaryUuid::class
- `forum_id` => BinaryUuid::class

**Poll/Event/Forum/Legislation/Portrait must cast:**
- `id` => BinaryUuid::class
- `user_id` => BinaryUuid::class
- `essence_numen_id` => BinaryUuid::class

## BinaryUuid Cast Implementation
The `BinaryUuid` cast must handle:
- Raw binary bytes from database
- Resource values (from queries)
- Clean string output in logs/JSON (no garbled binary)

Ensure `get()` method checks if value is already a resource and handles appropriately.

Proceed only after applying the required corrections above.


