# Case Study: Japan Travel

## Executive Summary

Japan Travel is a local-first full-stack portfolio application that combines bilingual destination discovery with souvenir commerce. The project demonstrates production-oriented Laravel engineering without claiming to be a live travel agency, a complete booking platform, or a production payment service.

The main engineering focus is not page count. It is the protection of domain invariants around authorization, stock, checkout replay, payment state, webhook idempotency, customer privacy, and historical order integrity.

## Problem

Travel portfolio projects often become either static galleries or unrealistic booking systems. The first option provides little backend depth; the second implies supplier contracts, live availability, pricing, cancellation, regulatory, and payment capabilities that a portfolio project cannot honestly prove.

Japan Travel needed a narrower product boundary that was useful, demonstrable, and technically substantial:

- destination discovery and verified-user reviews;
- optional travel inquiries through WhatsApp;
- direct commerce only for souvenir products;
- independent customer and administrator workspaces;
- reproducible local review without paid infrastructure.

## Product Scope

### Included

- Indonesian/English destination content, schedules, search, filters, and ratings.
- Verified-user reviews with application and database duplicate protection.
- Souvenir catalog, session cart, address-aware checkout, payment retry, and order history.
- Midtrans Snap and PayPal Checkout adapters.
- User profile and multiple shipping-address management.
- Admin dashboards and management for users, destinations, souvenirs, inventory, and orders.

### Explicitly excluded

- direct travel booking or ticket sales;
- supplier availability and dynamic travel pricing;
- production payment, mail, monitoring, and storage claims;
- multi-tenant commerce and multi-replica media architecture.

## Architecture

The application uses a Laravel modern-monolith approach:

- Laravel 12 owns routing, authorization, validation, transactions, and domain behavior.
- The public home page and admin workspace use Inertia, Vue 3 Composition API, and TypeScript.
- Customer authentication, catalog, cart, checkout, order, and profile pages remain on Blade during incremental migration.
- MariaDB/MySQL is the intended relational database family; SQLite supports quick local review and the fast CI quality job.
- Payment providers implement a shared interface under `app/Services/Payments/`.
- Backed enums centralize persisted order, payment, webhook, provider, and role values.

The hybrid frontend is deliberate. Migrating module by module with contract tests provides more evidence of safe modernization than an all-at-once rewrite that risks checkout, authentication, and localization regressions.

## Key Engineering Challenges

### 1. Independent customer and admin sessions

A role check alone does not isolate browser session behavior. The application uses separate guards, cookie names, login endpoints, and logout flows. Feature and unit tests verify that signing out one context does not invalidate the other.

### 2. Stock-safe and replay-safe checkout

Checkout validates address ownership, locks inventory rows, applies deterministic stock mutations, and uses a session-scoped idempotency token. Replayed requests return the existing outcome instead of creating duplicate orders or charging stock twice.

### 3. Payment and order state integrity

Payment state is not treated as interchangeable with order state. Domain enums define allowed values and transitions. Provider callbacks must pass signature/verification, amount, currency, event-identity, and capture-reference checks before a financial transition is accepted.

### 4. Exactly-once inventory restoration

Terminal payment failures and administrative cancellation restore reserved stock through centralized transactional logic. An inventory ledger and uniqueness controls prevent duplicate compensation while preserving an audit trail.

### 5. Durable order history with private data

Product, customer, and shipping data can change or be deleted after checkout. Orders therefore retain immutable snapshots. Sensitive customer and address snapshots are encrypted, and deleting an account removes the live association without erasing transactional history.

### 6. Bounded payment evidence

Raw provider responses and exceptions can contain excessive or sensitive data. Stored payment payloads are reduced to approved summaries, redacted, and expired under an explicit retention policy while audit records remain available.

### 7. Safe media lifecycle

Admin uploads validate MIME type, size, and dimensions, convert supported images to WebP, and restrict deletion to approved upload paths. This protects both application resources and local filesystem boundaries.

## Security and Reliability Controls

- Server-side authentication, authorization, validation, CSRF protection, and password hashing.
- Separate customer/admin authentication contexts and rate-limited login endpoints.
- Verified-user restrictions and database uniqueness for reviews.
- Transactional checkout, row locking, idempotency, and guarded state transitions.
- Provider verification plus amount/currency/capture integrity checks.
- Encrypted profile, address, customer, and shipping data at rest.
- Restricted upload and deletion behavior.
- Demo seeders blocked outside local/testing environments.
- CI dependency audits, JavaScript/TypeScript CodeQL, dependency review, and secret scanning.

These controls demonstrate engineering practice; they do not replace an independent security assessment or real production operations.

## Verification Strategy

The verified `b2cff65` baseline completed:

- 258 PHPUnit tests with 2,498 assertions;
- Pint formatting validation;
- Larastan/PHPStan level 6 with no findings outside the committed baseline;
- Vue TypeScript checking and zero-warning ESLint;
- Vite production build;
- Composer and npm dependency audits;
- SQLite, MariaDB 10.11, and MariaDB 11.8 CI jobs;
- CodeQL, dependency review, and secret scanning.

Tests cover internal application contracts. External provider accounts, SMTP deliverability, public deployment, and remote media persistence remain outside the verified evidence boundary.

## Trade-offs

| Decision | Benefit | Cost or limitation |
|---|---|---|
| Inquiry-only travel services | Honest and defensible product scope | No booking engine |
| Laravel modern monolith | Cohesive transactions and simpler local setup | Not a distributed architecture case study |
| Incremental Vue/Inertia migration | Lower regression risk and testable contracts | Temporary Blade/Vue hybrid |
| Separate admin guard/cookie | Explicit, testable session isolation | Additional auth configuration |
| Database-backed operational defaults | Easy single-instance explanation | Redis/queue scaling is not demonstrated |
| Local media storage | Simple local review | Requires object storage before horizontal scaling |
| PHPStan baseline | Allows incremental static-analysis adoption | Accepted technical debt remains visible |
| Local-first evidence | No hosting cost and reproducible review | Recruiters cannot use a live URL |

## Results

The project evolved beyond a CRUD storefront into a portfolio codebase with:

- explicit transaction and payment invariants;
- independent authorization contexts;
- auditable inventory history;
- durable encrypted order snapshots;
- reproducible multi-database CI;
- documented security boundaries and limitations;
- a bilingual, local-first review path.

The outcome is suitable as an intermediate-level full-stack engineering case study. It is not presented as a mature commercial platform or evidence of production scale.

## Known Limitations

- No public deployment or production-usage evidence.
- No external Midtrans/PayPal sandbox-account evidence.
- No transactional SMTP deliverability evidence.
- No browser E2E suite, automated accessibility audit, load test, or performance budget.
- No measured coverage percentage.
- PHPStan baseline debt remains.
- Local media targets one web replica; object storage is not implemented.
- PayPal IDR conversion depends on a manually configured exchange rate.

## Next Improvements

Local-first priorities:

- record a final manual regression pass on the current commit;
- refresh materially outdated screenshots;
- add lightweight browser and accessibility smoke checks when resources allow;
- reduce PHPStan baseline debt in coherent batches;
- measure coverage only in a reproducible environment.

Public deployment, provider sandbox validation, transactional email, and object storage remain optional future work rather than release blockers.

## Interview Summary

> “I built Japan Travel as a scoped Laravel modern monolith. Travel discovery and inquiries are intentionally separate from souvenir commerce, so I do not claim a full booking platform. I focused on user/admin isolation, stock-safe and replay-safe checkout, payment integrity, webhook idempotency, exactly-once inventory restoration, encrypted historical snapshots, incremental Vue migration, and reproducible CI. The repository clearly separates automated proof from external or production validation.”
