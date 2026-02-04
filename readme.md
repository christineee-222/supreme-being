# Juggernaut.love

A Laravel 12 application focused on preserving collective knowledge, encouraging transparent civic engagement, and enabling structured discussion without allowing rage-deletion or historical revisionism.

This project prioritizes **immutability, accountability, and role-aware authorization** while still supporting healthy participation, moderation, and long‑term archival.

---

## ✨ Core Principles

- **Preservation over ephemerality** – published content is intentionally difficult (or impossible) to delete or rewrite.
- **Transparency by default** – public artifacts like polls, donations, and legislation remain viewable forever.
- **Role-aware governance** – admins and moderators enable safety and verification, not unilateral control.
- **Test-first authorization** – every rule is expressed in Policies and backed by tests.
- **Framework-native** – everything is done “the Laravel way.”

---

## 🧱 Tech Stack

- **PHP**: 8.5.2 (stable as of Jan 2026)
- **Laravel**: 12
- **Inertia.js**: v2
- **Wayfinder**: v0 (typed route helpers)
- **PHPUnit**: v11
- **Laravel Pint**: v1
- **Laravel Boost (MCP)**: Enabled for IDE tooling and documentation search

Frontend is a client‑side rendered Inertia SPA using existing Laravel server‑side patterns.

---

## 📦 Domain Models

- **Forum** – Long‑lived discussion threads
- **Comment** – Replies on forums and events
- **Poll** – Immutable public votes with end dates
- **Event** – Time‑bound civic or community events
- **Donation** – Public, read‑only financial contributions
- **Portrait** – Public representations of people (not user profiles)
- **Legislation** – Proposed or enacted policy artifacts

All primary models are publishable and role‑governed.

---

## 🔐 Authorization Philosophy

Authorization is enforced exclusively through **Laravel Policies**, never inline logic.

### Roles

- **Admin** – Full override authority, verification, legal takedowns
- **Moderator** – Content approval, moderation, verification assistance
- **User** – Standard authenticated participant
- **Guest** – Read‑only access where permitted

Role helpers exist on the `User` model:

- `isAdmin()`
- `isModerator()`

---

## 🧩 Shared Policy Traits

Policies are intentionally DRY’d using reusable traits:

- **AllowsRoles** – Centralized admin/mod override logic
- **OwnsModel** – Ownership checks via `user_id`
- **InteractsWithPublishableModels** –
  - Admin/mod → always allowed
  - Regular user → allowed only if `status === 'published'`

These traits are composed into model‑specific Policies rather than duplicated.

---

## 🗣️ Forums & Comments

### Forums

- Anyone (including guests) can **view** published forums
- Users can **create** forums
- Forums **cannot be edited or deleted** once published
- Forums may be **archived** after prolonged inactivity
- Optional **anonymous creation** is supported for verified whistleblowing
  - Identity is hidden publicly
  - Admin/mod can verify intent and legitimacy

### Comments

- Admin/mod → can comment anywhere
- Users → can comment only on published forums/events
- Comment deletion is role‑aware and ownership‑based

All comment authorization is enforced via `ForumPolicy::comment()` and `CommentPolicy`.

---

## 📊 Polls

- Viewable by everyone, including guests
- Show current tally and end date publicly
- Immutable after publication
- No edits or deletion by any role

---

## 💸 Donations

- Public forever
- Read‑only after creation
- Donations can be made:
  - User → User
  - User → Portrait owner
  - Portrait owner → User

Transparency is a core design requirement.

---

## 📅 Events

- Viewable by everyone
- Only users may create events
- Only creator may update
- Immutable after start date/time
- Comments allowed even after event conclusion
- Sorted by feature

---

## 📜 Legislation

- Viewable by everyone
- Users may propose legislation
- Requires admin/mod approval to publish
- Updates are suggested by users and approved by admin/mod
- Easy-to-read translations, local and national
- Sorted by feature

Legislation maintains a public, auditable history.

---

## 🖼️ Portraits

- Viewable by everyone once published
- Users may suggest portraits
- Admin/mod approval required
- Subject of a portrait may **claim ownership**:
  - “Is this you? Join and have more say over how you’re represented.”
  - Identity verified by admin/mod
- Updates are suggested and approved
- Deletion only for legal necessity
- Snapshot of compiled information on entity
- Sorted by feature

Portraits are **not** user profiles.

---

## 🧪 Testing

- PHPUnit only (no Pest)
- Feature tests preferred over unit tests
- Authorization rules are always tested
- Minimal test execution is encouraged during development

Examples:

```bash
php artisan test --compact
php artisan test --compact tests/Feature/ForumCommentAuthorizationTest.php
```

---

## 🛠️ Development Conventions

- Use `php artisan make:*` commands
- Use Form Request classes for validation
- Never call `env()` outside config files
- Use Eloquent relationships over raw queries
- Run Pint before finalizing changes:

```bash
vendor/bin/pint --dirty
```

---

## 🤖 Laravel Boost

Laravel Boost is enabled and provides:

- Version‑aware documentation search
- Artisan command introspection
- Tinker and database query helpers
- Browser log inspection

When working on Laravel or ecosystem features, documentation is searched via Boost before implementing changes.

---

## 📌 Project Status

- Core authorization architecture complete
- Comment system implemented and tested
- Policy refactor underway across remaining models
- Model intent and governance rules being formalized

This project intentionally favors correctness and durability over speed.

---

## ❤️ Philosophy

> This app is designed so that important conversations cannot simply disappear. Juggernaut focuses on the ethical consumption/sharing of information, pooling/sharing resources for mutual aid and establishing information archives in perpetuity. Will include chat function which has end-to-end encryption, is open-source and peer-to-peer which operates without internet or cell service using Bluetooth Low Energy (BLE) to create a mesh network for emergency communications, censor-proof. Intended to be accessible for visually impaired. 

