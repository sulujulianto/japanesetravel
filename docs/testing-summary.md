# Testing and Quality Summary

## Latest Verified Snapshot

The latest commit-bound baseline before this documentation update is:

| Field | Evidence |
|---|---|
| Commit | [`b2cff65`](https://github.com/sulujulianto/japanesetravel/commit/b2cff650df07a41fd666e4da45bed490148f841b) |
| Date | 2026-09-05 |
| PHPUnit | 258 tests, 2,498 assertions |
| Static analysis | Larastan/PHPStan level 6; no findings outside the committed baseline |
| Frontend | Vue TypeScript check, ESLint, and Vite production build passed |
| Formatting | Laravel Pint passed |
| Dependency checks | Composer audit and npm audit passed in CI |
| Database CI | SQLite, MariaDB 10.11, and MariaDB 11.8 |
| Security workflow | CodeQL JavaScript/TypeScript, dependency review, and secret scan passed |

The complete execution record is stored in [Test Execution Evidence](qa/test-execution-evidence.md). Counts are tied to a commit and must be rerun before being quoted for a later revision.

## Covered Areas

| Category | Representative coverage |
|---|---|
| Authentication | Registration, login/logout, password confirmation/reset/update, failed-login localization |
| Session isolation | Independent user/admin guards, cookies, login endpoints, and logout behavior |
| Email verification | Verification screen, signed links, invalid links, and verified-route protection |
| Authorization | Admin-route rejection, cross-user order/address access rejection, admin read-only user access |
| User data | Encrypted profile/address persistence, one-profile constraint, default-address invariants, cascading cleanup |
| Destinations | Catalog rendering, search/filter/sort, details, schedules, and localized ratings |
| Reviews | Guest/unverified rejection, valid review, duplicate protection, and database uniqueness |
| Shop and cart | Rendering, price formatting, add/update/remove, stock clamp, and stale-product cleanup |
| Checkout | Address ownership, immutable snapshots, row locking behavior, idempotency token, replay handling, and failure compensation |
| Payments | Midtrans/PayPal driver contracts, amount/currency integrity, capture references, retry guards, and safe payload retention |
| Webhooks | Signature/verification paths, event idempotency, guarded transitions, duplicate events, and terminal-state protection |
| Inventory | Auditable ledger, manual adjustments, reservation, and exactly-once restoration |
| Orders | Product/customer/address snapshots, account-deletion retention, localized user/admin display, and transition rules |
| Admin | Dashboard contracts, users, destinations, souvenirs, inventory, orders, pagination, and media lifecycle |
| Media | MIME/size/dimension validation, WebP conversion, replacement, and restricted deletion paths |
| Localization | Indonesian/English copy, formatting, pluralization, and locale-isolated chart cache |
| Domain types | Backed enums and transition contracts for roles, providers, orders, payments, and webhook results |

## Run the Complete Gates

```bash
composer validate --strict
composer audit --locked
npm audit --audit-level=high
npm run type-check
npm run lint
npm run build
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
git diff --check
```

Targeted examples:

```bash
php artisan test --filter=Checkout
php artisan test --filter=PaymentWebhook
php artisan test --filter=Inventory
php artisan test --filter=UserAddress
php artisan test --filter=AdminSession
```

`.github/workflows/ci.yml` runs the core checks on every push and pull request, on a weekly schedule, and on manual dispatch. The security workflow adds CodeQL, dependency review, and secret scanning.

## Evidence Interpretation

- A passing test proves the tested application contract, not production operation.
- PHPStan uses `phpstan-baseline.neon`; green means no findings outside accepted debt, not zero latent findings.
- SQLite provides a fast quality job, while MariaDB 10.11/11.8 jobs exercise the intended relational database family.
- Mocked payment tests prove internal mapping, validation, idempotency, and state behavior; they do not prove provider-account configuration.
- Existing screenshots prove a recorded visual review at their named commit; they are not automatically current after later UI changes.

## Coverage Status

No coverage percentage is claimed. A coverage driver is intentionally excluded from the default setup for a resource-limited development laptop.

On a suitable environment, measure with Xdebug:

```bash
XDEBUG_MODE=coverage php artisan test --coverage
```

or with PCOV installed and enabled:

```bash
php artisan test --coverage
```

Before publishing a percentage:

1. Record the commit, PHP version, and coverage driver.
2. Run the complete suite.
3. Preserve the terminal output or CI artifact.
4. State whether the result represents line, branch, or method coverage.
5. Do not compare results produced by different drivers without explanation.

## Known Test Gaps

- No external Midtrans/PayPal sandbox account has been exercised as repository evidence.
- Mail tests verify Laravel behavior, not SMTP deliverability or spam placement.
- No browser E2E suite currently checks complete responsive journeys.
- Checkout locking and replay behavior are covered deterministically, but no true multi-process race test is present.
- No automated accessibility audit, load test, or performance budget is enforced.
- Persistent media has not been tested across a real multi-replica or redeploy lifecycle.
- PHPStan baseline debt remains and should be reduced in coherent batches.

## Recommended Local-First Improvements

- Rerun the manual critical-path checklist against the current local commit.
- Add lightweight browser smoke coverage when laptop resources allow.
- Add keyboard-navigation and form-error accessibility checks.
- Add a true concurrent stock race test in an isolated test environment.
- Reduce the PHPStan baseline without adding new ignore rules.
- Measure coverage only when the result can be reproduced and explained.

Deployment, provider sandbox validation, SMTP delivery, and remote media persistence remain optional external-account work rather than blockers for the local-first portfolio release.
