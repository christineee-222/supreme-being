# Civic Engagement Platform (Working Title)

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.5%2B-blue)
![Docker](https://img.shields.io/badge/Docker-Sail-informational)
![License](https://img.shields.io/badge/License-Proprietary-lightgrey)

A modern civic technology platform designed to improve democratic participation through accessible information, secure identity, and community coordination tools.

Built with **Laravel 12**, the system supports both:
- **Web:** A session-based application powered by Inertia.js.
- **Mobile:** A stateless, high-volume API for native iOS and Android clients.

Authentication is managed via **WorkOS**, providing passwordless login, social identity providers, and enterprise SSO while maintaining a high-performance local relational data model.

---

## 🆔 Identifier Strategy (UUIDv7 Binary Architecture)

This application implements a high-performance **Binary UUIDv7** strategy to ensure database efficiency without sacrificing developer experience.

- **Storage:** Primary keys are stored as `BINARY(16)` for optimal indexing and performance.
- **Boundaries:** UUID strings are used exclusively at system boundaries:
  - API responses (JSON Resources)
  - URL Route parameters
  - JWT / Auth claims
  - Frontend state & Tests

### Benefits
- **Performance:** Faster indexed queries and joins compared to string-based UUIDs.
- **Integrity:** Native Eloquent relationship support by keeping internal IDs in binary format.
- **Security:** Clean public identifiers that don't leak database sequential logic.
- **Stability:** A "Fail-Safe" trait ensures binary data never reaches the frontend, preventing malformed UTF-8 crashes.

---

## 🚀 Local Development (Docker / Laravel Sail)

Laravel Sail provides a consistent containerized environment. You do **not** need to manually install PHP, MySQL, or Node on your host machine.

### First-Time Setup

```bash
# 1. Clone the repository
git clone <repo-url>
cd social-app

# 2. Install dependencies
composer install
cp .env.example .env

# 3. Start the environment
./vendor/bin/sail up -d

# 4. Prepare the database & assets
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev

**Access the app at:** [http://localhost](http://localhost)

---

### 🔄 Daily Workflow

| Action | Command |
| :--- | :--- |
| **Start Containers** | `./vendor/bin/sail up -d` |
| **Stop Containers** | `./vendor/bin/sail down` |
| **Run Tests** | `./vendor/bin/sail artisan test` |

> [!CAUTION]
> Avoid running `./vendor/bin/sail down -v` unless you intend to wipe your local database volumes.

### ✨ Core Features

#### Hybrid Application Architecture
* **Session-Based Web:** Secure, traditional web auth for browsers and admin tools.
* **Stateless JWT API:** Scalable, token-based auth for mobile clients.

#### WorkOS Identity Integration
* **Passwordless Magic Links:** Seamless, email-based authentication.
* **Social Sign-in:** Support for Apple and Google identities.
* **Enterprise SSO:** OIDC and SAML ready for organizational scaling.

#### Civic Engagement Data Model
* **Users:** Local relational anchors for global WorkOS identities.
* **Events & RSVPs:** Tools for community coordination and mobilization.
* **Forums & Polls:** Structured platforms for civic feedback and discussion.
* **Donations:** Secure, integrated community-led funding modules.

## 🏗️ Architecture Principles

* **Clear Separation:** Intentional boundaries between Identity (WorkOS), Data (Local MySQL), and API layers to ensure modularity.
* **Mobile-First Auth:** Stateless API design utilizing JWTs to support high-concurrency mobile usage without session overhead.
* **Relational Anchoring:** Just-In-Time (JIT) user provisioning that seamlessly links external WorkOS identities to local relational data structures.
* **RS256 JWTs:** Secure cryptographic verification using asymmetric private/public key pairs, ensuring the API can verify tokens without needing to store the signing secret.

## 🔐 Identity & Authentication

### Web Authentication (Session)
Used for browser users and moderation tools. This layer utilizes standard Laravel cookie-based sessions with full CSRF protection to ensure a secure, stateful experience for web-based interactions.

### API Authentication (JWT)
Designed for mobile apps and external integrations where statelessness is required for scalability.

* **Algorithm:** RS256 (Asymmetric Private/Public key signing)
* **Flow:** Authenticate via WorkOS → Exchange session for JWT → Stateless requests
* **Claims:** Includes `sub` (WorkOS ID), `email`, and `exp` (Expiration)

## 🧰 Tech Stack

- **Backend:** Laravel 12, PHP 8.5+, MySQL 8
- **Frontend:** Inertia.js, React, TypeScript, Vite
- **Authentication:** WorkOS AuthKit + Custom RS256 JWT Implementation
- **Infrastructure:** Docker (Laravel Sail), Target deployment: Laravel Cloud

## 🚧 Project Status

- [x] **Foundation:** Binary UUIDv7 architecture & trait system
- [x] **Auth:** Hybrid Session + JWT model with WorkOS integration
- [x] **Environment:** Fully containerized development via Laravel Sail
- [~] **Development:** API expansion and Mobile client integration
- [ ] **Pending:** Polls, Civic Data resources, and UI Refinement

## 🤝 Contributing

We welcome contributions that help push the platform forward. Please follow these guidelines:

1. **Start the Conversation:** Open an issue to discuss proposed changes before starting work.
2. **Quality Assurance:** Ensure the full test suite passes before submitting a PR:
   ```bash
   ./vendor/bin/sail artisan test

Building a transparent future through thoughtful technology.

