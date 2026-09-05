# Test Execution Evidence

This document records automated and manual evidence that was actually executed. Each row is historical and remains bound to its named commit; a passing row must not be treated as proof for a later working tree.

## Execution Table

| Test ID | Scenario | Date | Tester | Environment | Result | Evidence | Notes |
|---|---|---|---|---|---|---|---|
| VIS-001 | Local visual review | 2026-06-12 | Sulu Edward Julianto | Local, commit `dbec28c` | Pass | [`docs/screenshots/`](../screenshots) | Fifteen public, user, admin, mobile, and dark-mode screenshots were captured. They remain historical evidence until compared with the current UI. |
| AUTO-001 | Full local quality gate | 2026-06-12 | Sulu Edward Julianto | Local, commit `dbec28c` | Pass | Preserved terminal output | Composer validation/audit, npm audit, build, PHPUnit, Pint, PHPStan, view cache, and whitespace checks passed; 132 tests / 633 assertions. |
| AUTO-002 | Dependency and database CI baseline | 2026-08-23 | Sulu Edward Julianto and GitHub Actions | Branch `work/vue-commerce-phase-0`, commits `6cd88d6` and `350cad5` | Pass | GitHub Actions runs `32643309689`, `32643309728`; PR #38 Security run `32644038102` | Dependency audits passed; 132 tests / 633 assertions ran on SQLite and MariaDB 10.11/11.8; CodeQL JavaScript/TypeScript, Gitleaks, and dependency review passed. The push dependency-review job was skipped by design. |
| AUTO-003 | Domain and transaction hardening baseline | 2026-09-05 | Sulu Edward Julianto and GitHub Actions | `main`, commit [`b2cff65`](https://github.com/sulujulianto/japanesetravel/commit/b2cff650df07a41fd666e4da45bed490148f841b) | Pass | [PR #75 checks](https://github.com/sulujulianto/japanesetravel/pull/75/checks) | 258 tests / 2,498 assertions; PHPStan, Pint, Vue TypeScript, ESLint, production build, Composer/npm audits, SQLite, MariaDB 10.11/11.8, CodeQL, dependency review, and secret scans passed. Twelve checks succeeded, one push-only dependency-review check was skipped by design, and none failed or remained pending. |

Allowed result values:

- Pass
- Fail
- Blocked
- Not executed

## Visual Evidence Inventory

| View | Evidence |
|---|---|
| Homepage | `docs/screenshots/01-homepage.png` |
| Destination catalog | `docs/screenshots/02-destinations.png` |
| Destination detail | `docs/screenshots/03-destination-detail.png` |
| Review form | `docs/screenshots/04-review-form.png` |
| Souvenir shop | `docs/screenshots/05-souvenir-shop.png` |
| Cart | `docs/screenshots/06-cart.png` |
| Checkout sandbox | `docs/screenshots/07-checkout-sandbox.png` |
| User orders | `docs/screenshots/08-user-orders.png` |
| Admin login | `docs/screenshots/09-admin-login.png` |
| Admin dashboard | `docs/screenshots/10-admin-dashboard.png` |
| Admin order detail | `docs/screenshots/11-admin-order-detail.png` |
| Admin places | `docs/screenshots/12-admin-places.png` |
| Admin souvenirs | `docs/screenshots/13-admin-souvenirs.png` |
| Mobile responsive | `docs/screenshots/14-mobile-responsive.png` |
| Dark mode | `docs/screenshots/15-dark-mode.png` |

## Current Local-First Evidence Gate

- [x] Commit-bound automated baseline exists.
- [x] Fifteen screenshot files exist with known historical provenance.
- [x] Public, user, admin, mobile, and dark-mode views are represented.
- [ ] Compare the screenshot set against the final portfolio-release commit.
- [ ] Refresh materially outdated screenshots and record a new `VIS` row.
- [ ] Execute the current manual critical-path checklist and record tester/date/commit.
- [ ] Confirm README and documentation rendering on GitHub after the pull request is opened.

## Deferred External Evidence

These items are optional and are not part of the current local-first release gate:

- public URL and `/up` health evidence;
- media persistence across a remote redeploy;
- transactional email delivery;
- Midtrans/PayPal account-level sandbox callbacks;
- production monitoring, backup, and rollback evidence.

They must remain unclaimed until a real environment and redacted evidence exist.

## Do Not Commit

- Environment files or secret values
- Provider credentials
- Session cookies
- Full webhook signatures
- Personal email, phone, or address data
- Unredacted provider payloads or exception dumps
