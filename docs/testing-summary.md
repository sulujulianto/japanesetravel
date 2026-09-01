# Testing and Quality Summary

## Current Baseline

- Automated tests: run `composer test` on the exact commit under review
- Assertions: use the total reported by that run
- Test framework: PHPUnit through Laravel's test runner
- Static analysis: Larastan/PHPStan level 6 with a committed baseline
- Formatting: Laravel Pint
- Frontend build: Vite
- Dependency checks: Composer Audit and npm Audit

Counts in `docs/qa/test-execution-evidence.md` are historical evidence tied to
specific commits, not the current working tree. The CI suite runs the same core
tests against SQLite and MariaDB 10.11/11.8.

## Covered Areas

| Category | Representative coverage |
|---|---|
| Authentication | Registration, login/logout, failed login translation, password confirmation/reset/update |
| Email verification | Verification screen, valid/invalid links, verified-route protection |
| Authorization | User blocked from admin, user/admin session isolation, cross-user order access blocked |
| Destinations | Catalog rendering, search/filter/sort, detail rendering, localized rating/count |
| Reviews | Guest/unverified rejection, valid review, duplicate handling, database uniqueness |
| Shop/cart | Shop rendering, localized prices, add/update/remove, stock clamp, stale product cleanup |
| Checkout | Order/payment creation, row locking behavior, gateway failure compensation, payment retry guards |
| Webhooks | Midtrans/PayPal success/failure, signature path, idempotency, terminal-order guards |
| Orders | Snapshot image compatibility and localized user/admin order display |
| Admin | Access, media conversion/replacement/deletion, status transitions |
| Localization | ID/EN copy, formatting, pluralization, locale-isolated chart cache |
| Unit | Number/date/currency/rating/relative-time formatter and centralized brand configuration |

## Run the Suite

```bash
php artisan test
```

Targeted examples:

```bash
php artisan test --filter=Checkout
php artisan test --filter=PaymentWebhook
php artisan test --filter=AdminMediaUpload
php artisan test --filter=Locale
```

## Quality Gates

```bash
composer validate
composer audit
npm audit --audit-level=high
npm run build
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --no-progress
```

The same core checks run in `.github/workflows/ci.yml`.

## Coverage Status

No coverage percentage is claimed. Xdebug or PCOV is not required by the default lightweight setup, which is intentional for a 4 GB RAM development laptop.

To measure coverage on a suitable environment:

```bash
XDEBUG_MODE=coverage php artisan test --coverage
```

or, with PCOV installed and enabled:

```bash
php artisan test --coverage
```

Before publishing a percentage:

1. Record the PHP version and coverage driver.
2. Run the complete suite.
3. Save the command output or CI artifact.
4. State whether the number is line, branch, or method coverage.
5. Do not compare results from different drivers without explanation.

## Current Limitations

- External Midtrans/PayPal dashboards and credentials are mocked, not exercised by automated tests.
- Mail delivery tests verify Laravel behavior, not SMTP deliverability or spam placement.
- Railway Volume persistence requires a real redeploy/restart smoke test.
- No browser automation suite currently validates full responsive rendering.
- No true parallel race test exists for checkout/review concurrency.
- PHPStan uses a baseline, so “zero errors” means no errors outside the accepted baseline.
- No load, accessibility, or performance budget test is enforced.

## Recommended Next Tests

- Browser smoke tests for public, user, and admin critical paths.
- Accessibility checks for keyboard navigation and form errors.
- True concurrent checkout stock test.
- Sandbox contract test against a dedicated provider account.
- Upload persistence test across a real staging redeploy.
- Queue failure/retry behavior once asynchronous jobs are introduced.
- Incremental PHPStan baseline reduction.
