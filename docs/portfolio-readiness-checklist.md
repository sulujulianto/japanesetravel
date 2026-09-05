# Portfolio Readiness Checklist

## Release Definition

The current release target is **portfolio-ready and local-first**. Public deployment is intentionally deferred and is not a blocker as long as the repository remains reproducible, evidence-backed, and explicit about what has not been validated.

Legend:

- `[x]` verified repository artifact or commit-bound evidence exists.
- `[ ]` action is still required for the local-first release.
- **Deferred** means optional external-account work outside the current release gate.

## Repository

- [x] Indonesian primary README and English README exist.
- [x] README links point to existing repository artifacts.
- [x] MIT license and project-specific Composer metadata exist.
- [x] Composer and npm dependency locks are committed.
- [x] `.env.example` uses placeholders instead of real credentials.
- [x] GitHub Actions CI and security workflows exist.
- [x] Issue templates and a documentation index exist.
- [ ] Add a concise GitHub repository description and relevant topics.
- [ ] Review open dependency PRs; merge, defer, or close each one based on compatibility rather than version novelty.

## Reproducible Local Review

- [x] MariaDB/MySQL setup is documented.
- [x] SQLite quick-review setup is documented.
- [x] Curated demo data and local demo accounts are documented.
- [x] Demo seeders are environment-guarded.
- [x] Media symlink and PHP-extension requirements are documented.
- [x] Mail, payment, WhatsApp, and storage limitations are visible.
- [ ] Re-run the documented setup from a clean clone or disposable directory before tagging the portfolio release.

## Automated Quality

- [x] Feature and unit tests cover public, user, admin, transaction, and security-sensitive paths.
- [x] Laravel Pint and Larastan/PHPStan are configured.
- [x] Vue TypeScript and ESLint gates are configured.
- [x] Composer/npm audits and Vite production build run in CI.
- [x] CI exercises SQLite, MariaDB 10.11, and MariaDB 11.8.
- [x] CodeQL, dependency review, and secret scan are configured.
- [x] The `b2cff65` snapshot records 258 tests and 2,498 assertions.
- [ ] Run all quality and security checks for the final documentation branch.
- [ ] Reduce the PHPStan baseline incrementally when changes form a coherent, low-risk batch.
- [ ] Measure coverage only if a reproducible PCOV/Xdebug environment is available; no percentage is required for this release.

## Portfolio Presentation

- [x] Bilingual project overview exists.
- [x] Engineering case study exists.
- [x] A complete 15-file screenshot set exists as historical evidence.
- [x] Screenshot naming, provenance, and redaction rules exist.
- [x] A 1–2 minute demo-video script exists.
- [x] Safe portfolio/CV positioning avoids production and real-payment claims.
- [ ] Compare screenshots with the final local commit and refresh only materially outdated views.
- [ ] Record a localhost walkthrough if practical; this is useful but not required for the repository-only release.

## QA Evidence

- [x] Test-case matrix, regression scenarios, and manual checklist exist.
- [x] Commit-bound automated evidence exists.
- [x] Historical visual evidence identifies its capture commit.
- [x] Simulated bug, ticket, and incident samples are explicitly labelled.
- [ ] Execute and record a final local smoke test for public, user, and admin critical paths.
- [ ] Confirm that all new evidence is redacted and contains no personal data or credentials.

## Deployment — Deferred

Deployment assets remain useful evidence of operational awareness:

- [x] Multi-stage Dockerfile and Railway scripts exist.
- [x] Migration is separated from normal runtime.
- [x] `/up`, environment configuration, persistent-media strategy, and production-safe seeding are documented.

The following items are **deferred and non-blocking** for the local-first release:

- creating a public hosting/database service;
- attaching a persistent media volume;
- running remote migrations and HTTPS smoke tests;
- publishing a live-demo URL;
- proving remote backup, rollback, or multi-replica behavior.

## External Integrations — Deferred

- [x] Mail requirements, optional WhatsApp behavior, payment matrices, and safe Postman templates are documented.
- [x] Automated tests cover application-side payment contracts and webhook behavior.

The following items are **not claimed and are non-blocking**:

- transactional email deliverability;
- Midtrans/PayPal account-level sandbox validation;
- real provider callback evidence;
- an approved production WhatsApp number;
- production monitoring or alert delivery.

## Final Local-First Publication Gate

- [x] No live-deployment, real-payment, travel-booking, or production-usage claim is made.
- [x] Known limitations remain visible.
- [x] Historical and current evidence are clearly distinguished.
- [x] Public documentation contains no intentional credentials or personal data.
- [ ] Final local manual smoke test is recorded.
- [ ] Complete quality gates pass on the branch.
- [ ] GitHub security checks pass on the pull request.
- [ ] Repository description/topics are reviewed.
- [ ] Final README and documentation render correctly on GitHub.
- [ ] The merged `main` commit is tagged as the portfolio release only after the items above pass.
