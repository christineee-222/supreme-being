# Juggernaut.love

A Laravel 12 application focused on preserving collective knowledge, encouraging transparent civic engagement, harnessing collective willpower and enabling structured discussion without allowing rage-deletion or historical revisionism.

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
- **React** (TypeScript)
- **Inertia.js**: v2
- **Wayfinder**: v0 (typed route helpers)
- **Auth**: WorkOS (AuthKit)
- **Database**: MySQL / PostgreSQL
- **Mobile**: iOS / Android (planned)
- **PHPUnit**: v11
- **Laravel Pint**: v1
- **Laravel Boost (MCP)**: Enabled for IDE tooling and documentation search

Frontend is a client‑side rendered Inertia SPA using existing Laravel server‑side patterns.


---


## 🔐 Authentication Modes

### Web (Session-Based)
- Used by browser users
- Powered by WorkOS AuthKit
- Laravel sessions + cookies
- Inertia-rendered UI

### API (Stateless JWT)
- Used by mobile apps and external clients
- JWTs signed with **RS256**
- Sent via `Authorization: Bearer <token>`
- Validated by custom middleware

## 🛣️ Key Routes

### Web
- `/login` → Start WorkOS login
- `/auth/workos/callback` → OAuth callback
- `/dashboard` → Authenticated UI

### API
- `POST /api/v1/token` → Exchange session for JWT
- `GET /api/v1/me` → Authenticated user (JWT)
- `/api/v1/*` → Protected resources

## 🛡️ Security Principles

- Fail fast on invalid auth
- Never trust headers without cryptographic proof
- Explicit error codes for client UX
- No silent auth fallbacks
- No “JWT-only” users without a local anchor

## 📱 Mobile Strategy

Mobile clients authenticate via WorkOS and use **JWTs exclusively**.  
No cookies. No sessions. Fully stateless.

This enables:
- App Store–compliant auth
- High scalability
- Clear separation between web and API concerns


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
- Committee hearings
- Public comment deadlines
- Floor votes
- Provides hub for:
  1. Protests
  2. Economic Blackouts
  3. Marches
  4. Strategic use of effort
  5. Harnessing collective willpower


---


## 📜 Legislation

- Viewable by everyone
- Users may propose legislation
- Requires admin/mod approval to publish
- Updates are suggested by users and approved by admin/mod
- Transparency & comprehension, local and national
  1. Plain language summaries
  2. "What this actually does" explanations
  3. Clear who benefits / who pays / what changes
  4. Who proposed it
  5. What changed from last version
  6. What stage is it in, can/cannot be influenced, timeline
  7. Get alerts for votes
- Sorted by feature
- Equal visibility
- Clear sourcing
- Separation of facts vs interpretation 
- No jargon

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
- Contact Rep info
- Track votes
- Follow issues

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


## 🚀 Status

This README reflects the **intended final architecture**.

Current progress:
- ✅ WorkOS login flow
- ✅ Stateless API auth
- ✅ JWT issuance & validation
- 🚧 API resource expansion
- 🚧 Mobile app implementation

Active development.  
Architecture and authentication foundation are in place.
This project intentionally favors correctness and durability over speed.


---


## Planned Feature: Decentralized Emergency Messaging

A future release is planned to include a **decentralized, peer-to-peer chat system** designed for emergency and resilience scenarios.

This system is intended to:
- Provide **end-to-end encrypted messaging**
- Operate as **open-source** and auditable
- Use **Bluetooth Low Energy (BLE)** to form a local **mesh network**
- Function **without internet or cellular service**
- Enable communication during outages, disasters, or network disruptions
- Remain **censorship-resistant by design**, with no central server dependency

The goal is to support **local, community-based communication** when traditional infrastructure is unavailable, while prioritizing privacy, transparency, and user safety.

> **Status:** Concept / Research phase  
> **Note:** This feature is not yet implemented and may evolve significantly as technical, security, and regulatory considerations are evaluated.


---


## Accessibility & Internationalization

This project is designed with a **global audience** in mind and aims to be
accessible to users across languages, regions, and abilities.

Planned and ongoing efforts include:
- Support for **internationalization (i18n)** and community-driven translations
- Accessibility best practices for **screen readers**, keyboard navigation,
  and high-contrast interfaces
- Inclusive design decisions aligned with **WCAG guidelines**

Implementation details and contribution guidelines are documented separately.


---


## ❤️ Philosophy

> Juggernaut aims to reduce the expert gap five minutes at a time. If understanding requires legal training, prior context and time to decode jargon then only elites can participate. Will focuses on the ethical consumption/sharing of information, pooling/sharing resources for mutual aid and establishing information archives in perpetuity. Progressive disclosure reduces intimidation, creates agency, and provides information required for personal understanding. 

