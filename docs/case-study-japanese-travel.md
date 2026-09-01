# Case Study: JapanTravel

## Problem

A travel portfolio project can easily become either a static destination gallery or an over-scoped booking platform. JapanTravel needed a focused scope that demonstrated Laravel application skills without pretending to operate a real travel agency or production payment system.

## Context

The project was built as a junior-developer portfolio application on a resource-limited Linux laptop. The intended reviewer should be able to clone it, run it with SQLite or MySQL, inspect meaningful tests, and understand the operational limitations.

The chosen product boundary is:

- Destination discovery and verified-user reviews.
- Travel inquiries through an optional WhatsApp CTA.
- Direct commerce only for souvenir products.
- Separate user and admin workspaces.

## Solution

JapanTravel combines:

- A bilingual editorial destination catalog.
- Practical destination detail pages.
- One-review-per-user-per-destination feedback.
- A stock-aware souvenir cart and checkout flow.
- Midtrans and PayPal payment abstractions.
- User order history and payment retry controls.
- Admin catalog, inventory, order, and dashboard tools.

## Implementation

- Laravel routes and middleware separate public, verified-user, admin, and webhook responsibilities.
- Blade/Tailwind provides responsive light/dark interfaces.
- Spatie Translatable stores destination and souvenir copy in Indonesian and English.
- `Format` provides locale-aware dates, numbers, ratings, and IDR display.
- Checkout uses a database transaction and row locks before decrementing stock.
- Order items store product snapshots to protect historical display.
- Gateway creation failure compensates stock and marks the order/payment safely.
- Payment drivers implement a common interface.
- Webhook events use provider/event uniqueness to prevent duplicate processing.
- Admin uploads validate MIME, size, and dimensions before optional WebP conversion.

## Architecture Decisions

### Inquiry instead of travel booking

Travel booking involves availability, pricing contracts, cancellation policy, supplier integration, and regulatory concerns. The project deliberately keeps travel services as inquiries and limits direct payment to souvenirs.

### Separate admin guard

Admin and user sessions use separate guards and cookie names. This makes authorization boundaries visible and testable without introducing a complex role/permission package.

### Database-backed local baseline

Database sessions, cache, and queues are easy to explain and deploy for a single-replica portfolio service. Redis is documented as a future operational option rather than claimed as active.

### Local media plus persistent volume

The initial Railway strategy uses the existing public disk with a persistent volume. It is simple for a single replica but intentionally documented as unsuitable for horizontal scaling.

### Automated tests before external claims

Feature tests prove application contracts around stock, status transitions, authorization, and webhook handling. They do not prove external account configuration, delivery, or staging persistence; those require manual evidence.

## Security Considerations

- CSRF on normal web forms.
- Narrow webhook CSRF exemptions.
- Signature verification and webhook idempotency.
- Login, review, and webhook rate limits.
- Separate admin/user authentication boundaries.
- Verified-user review requirement.
- Database review uniqueness constraint.
- Stock locking and failure compensation.
- Media dimension/size checks and restricted delete paths.
- Production guards on destructive/public-credential seeders.
- Environment-only credentials.

## Testing Approach

The complete suite covers authentication, localization, catalog behavior, reviews, cart, checkout, payment retries, webhooks, admin access, media handling, order transitions, formatting, public shell contracts, and centralized brand configuration. Test and assertion totals are intentionally not copied here because they change with each phase; the output of `composer test` on the reviewed commit is the source of truth.

Pint, PHPStan/Larastan, Composer Audit, npm Audit, and the Vite production build form additional quality gates. PHPStan currently uses a baseline, which is treated as technical debt rather than hidden.

## Deployment Approach

The repository includes:

- Multi-stage Docker build.
- Railway web, migration, worker, and scheduler scripts.
- `/up` health endpoint.
- Persistent media volume guidance.
- Mail/payment/WhatsApp environment notes.
- A staging smoke-test checklist.

No live deployment is claimed until a real URL and evidence are added.

## Limitations

- No direct travel booking or ticketing.
- No production mail provider configured by default.
- No live payment credentials or completed sandbox evidence in the repository.
- Manual PayPal exchange-rate configuration.
- No measured code coverage percentage.
- No browser automation or accessibility audit.
- Local media strategy targets one web replica.
- PHPStan baseline remains.

## What I Learned

- How to define a smaller, defensible product scope.
- How database transactions and row locks protect stock.
- Why external callbacks need signature verification and idempotency.
- Why payment state and order state should not be treated as the same value.
- How snapshots preserve order history.
- How deployment concerns such as storage, mail, proxy trust, and health checks affect application behavior.
- How to distinguish automated proof from external/manual validation.

## Future Improvements

- Public staging deployment with recorded smoke-test evidence.
- Midtrans and PayPal sandbox validation.
- Transactional mail delivery verification.
- Browser/accessibility tests.
- Object storage before multi-replica deployment.
- Incremental PHPStan baseline reduction.
- Measured coverage in CI.

## Interview Explanation

> “I built JapanTravel as a scoped Laravel portfolio application. Travel content and inquiries are separate from souvenir commerce, so I avoided claiming a full booking platform. The technical areas I focused on were authorization boundaries, stock-safe checkout, payment abstractions, webhook idempotency, localized presentation, admin operations, and reproducible local setup. Automated tests prove internal behavior, while the repository clearly labels deployment, payment sandbox, mail delivery, screenshots, and video as manual owner actions until evidence exists.”
