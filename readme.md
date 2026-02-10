# Civic Engagement App (Working Title)
![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.5%2B-blue)
![Docker](https://img.shields.io/badge/Docker-Sail-informational)
![License](https://img.shields.io/badge/License-Proprietary-lightgrey)


A modern civic engagement platform built with **Laravel 12**, designed to support both:

* a **session-based web application** (Inertia + Laravel)
* a **stateless, high-volume API** for native mobile clients (iOS / Android)

Authentication and identity are powered by **WorkOS**, enabling secure passwordless login, social identity providers, and enterprise SSO while maintaining a strong local relational data model.

---

## 🚀 Local Development (Docker / Laravel Sail)

This project uses **Laravel Sail (Docker)** to provide a consistent development environment.

This means contributors do **not** need to manually install:

* PHP
* MySQL
* Redis
* Node dependencies
* System-level extensions

Everything runs in containers.

### First-time setup

```bash
git clone <repo-url>
cd social-app

composer install
cp .env.example .env

./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate

./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Then open:

```
http://localhost
```

### Daily workflow

Start containers:

```bash
./vendor/bin/sail up -d
```

Stop containers:

```bash
./vendor/bin/sail down
```

⚠️ Avoid:

```bash
./vendor/bin/sail down -v
```

That deletes the local database volume.

---

## ✨ Key Features

### Hybrid architecture

* Web app: session-based Laravel authentication
* API: stateless JWT authentication for mobile and external clients

### WorkOS-powered identity

Supports:

* Passwordless Magic Links
* Sign in with Apple
* Sign in with Google
* Enterprise SSO (OIDC / SAML-ready)

### Mobile-first API design

* Stateless authentication
* No cookies or CSRF
* Horizontally scalable
* Designed for native mobile clients

### Strong relational model

Core domain entities include:

* Users
* Events & RSVPs
* Forums & comments
* Polls
* Donations
* Civic data structures

### Security-focused architecture

* Explicit authentication boundaries
* Signed RS256 JWTs
* Minimal token claims
* Fail-fast error handling

---

## 🏗️ Architecture Overview

The system deliberately separates:

1. Identity provider
2. Local relational data model
3. API authorization layer

This improves scalability, security, and maintainability.

---

## 🔐 Identity Layer (WorkOS)

WorkOS acts as the **source of truth for authentication**.

Supported methods:

* Magic Link authentication
* Apple OAuth
* Google OAuth
* Enterprise SSO

WorkOS handles:

* Credential security
* Identity federation
* Authentication UX
* Account recovery flows

---

## 🧱 Local User Anchor Model

Even though identity lives externally, the application maintains a local `users` table.

This provides:

* Referential integrity for relational data
* Query performance
* Clear domain modeling
* Authorization flexibility

Each local user includes:

* Internal primary key (`id`)
* `workos_id` (indexed)
* Email
* Role / authorization metadata

Users are provisioned automatically using **Just-In-Time creation** after authentication.

---

## 🔐 Authentication Models

### Web Authentication (Session-Based)

Used for:

* Browser users
* Admin/moderation UI
* Inertia-rendered pages

Flow:

1. User initiates login
2. Redirected to WorkOS AuthKit
3. Authentication completes
4. Callback handled by Laravel
5. Session established

Characteristics:

* Cookie-based
* CSRF protected
* Standard Laravel middleware

---

### API Authentication (Stateless JWT)

Used by:

* Mobile apps
* External clients
* High-throughput API usage

Flow:

1. User authenticates via WorkOS
2. Authenticated session exchanges for JWT
3. Laravel signs token (RS256)
4. Client sends JWT per request

Characteristics:

* Stateless
* Horizontally scalable
* No cookies or CSRF

---

## 🔑 JWT Design

* Algorithm: **RS256**
* Private key signing
* Public key verification
* Short TTL (configurable)
* Minimal claims:

```
sub   → WorkOS user ID
email → user email
exp   → expiration
```

Custom middleware:

* Validates signature
* Checks expiration
* Resolves or creates local user
* Injects `$request->user()`

---

## 🛣️ Routes Overview

### Web Routes

| Route                   | Purpose                     |
| ----------------------- | --------------------------- |
| `/login`                | Begin WorkOS authentication |
| `/auth/workos/callback` | OAuth callback              |
| `/dashboard`            | Authenticated UI            |

### API Routes

| Route                | Auth    | Purpose       |
| -------------------- | ------- | ------------- |
| `POST /api/v1/token` | Session | Issue JWT     |
| `GET /api/v1/me`     | JWT     | Current user  |
| `/api/v1/*`          | JWT     | App resources |

---

## 📱 Mobile App Strategy

Mobile clients:

* Use WorkOS-hosted authentication
* Never rely on cookies
* Never interact with Laravel sessions
* Authenticate exclusively via JWTs

This enables:

* App Store–compliant auth
* High scalability
* External API extensibility
* Clear separation of concerns

---

## 🛡️ Security Principles

* Explicit authentication boundaries
* Cryptographic verification over trust
* No silent auth fallbacks
* Clear error semantics
* Local user anchoring for all identities

---

## 🧰 Tech Stack

### Backend

* Laravel 12
* PHP 8.5+
* MySQL (Docker locally / Laravel Cloud in production)

### Authentication

* WorkOS AuthKit
* OAuth providers
* RS256 JWT implementation

### Frontend

* Inertia.js
* React / TypeScript
* Vite

### Infrastructure

* Docker (Laravel Sail) for local development
* Laravel Cloud for deployment

---

## 🚧 Project Status

Architecture largely defined; active development ongoing.

### Completed

* WorkOS authentication integration
* Hybrid session + JWT auth model
* Containerized local development environment
* Core civic data structures

### In Progress

* API expansion
* Mobile client integration
* Feature refinement

---

## 🤝 Contributing

Docker/Sail is configured to minimize onboarding friction.

Typical contributor workflow:

```
composer install
cp .env.example .env
sail up -d
sail artisan migrate
sail npm install
sail npm run dev
```

If something isn’t clear:

* open an issue
* suggest improvements
* or submit a PR

This project aims to support transparent civic engagement and collaborative development.

---

## 📄 License

This project is licensed under the **Apache License 2.0**.

You should include a separate `LICENSE` file in the repository containing the full Apache 2.0 license text.

Summary:

* ✔ Commercial use allowed
* ✔ Modification allowed
* ✔ Distribution allowed
* ✔ Patent grant included
* ✔ Requires attribution and license notice
* ✖ No warranty provided

See: [https://www.apache.org/licenses/LICENSE-2.0](https://www.apache.org/licenses/LICENSE-2.0)


