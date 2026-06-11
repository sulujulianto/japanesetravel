# Test Execution Evidence

This document records manual and automated evidence that has actually been executed. Do not pre-fill Pass/Fail results for scenarios that have not been checked.

## Execution Table

| Test ID  | Scenario                  | Date       | Tester               | Environment             | Result | Evidence link/path             | Notes                                                                                                                                                                                                                                |
| -------- | ------------------------- | ---------- | -------------------- | ----------------------- | ------ | ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| VIS-001  | Final local visual review | 2026-06-12 | Sulu Edward Julianto | Local, commit `dbec28c` | Pass   | `docs/screenshots/`            | Homepage, destination catalog/detail, review form, shop, cart, checkout, user orders, admin pages, mobile view, and dark mode were captured.                                                                                         |
| AUTO-001 | Full local quality gate   | 2026-06-12 | Sulu Edward Julianto | Local, commit `dbec28c` | Pass   | Terminal output from local run | `composer validate`, `composer audit`, `npm audit --audit-level=high`, `npm run build`, `php artisan test`, Pint, PHPStan, `php artisan view:cache`, and `git diff --check` passed. Laravel test result: 132 tests / 633 assertions. |

Allowed result values:

* Pass
* Fail
* Blocked
* Not executed

## Screenshot Evidence Checklist

* [x] Homepage desktop
* [x] Destination catalog/detail
* [x] Review form and duplicate feedback
* [x] Shop/cart/checkout
* [x] User order list/detail
* [x] Admin login/dashboard/order/catalog
* [x] Mobile 320–390 px
* [x] Dark mode
* [ ] `/up` and deployment health
* [ ] Media before/after redeploy
* [ ] Mail delivery, redacted
* [ ] Payment sandbox callback/webhook, redacted

## Screenshot Files

| View                | Evidence                                     |
| ------------------- | -------------------------------------------- |
| Homepage            | `docs/screenshots/01-homepage.png`           |
| Destination catalog | `docs/screenshots/02-destinations.png`       |
| Destination detail  | `docs/screenshots/03-destination-detail.png` |
| Review form         | `docs/screenshots/04-review-form.png`        |
| Souvenir shop       | `docs/screenshots/05-souvenir-shop.png`      |
| Cart                | `docs/screenshots/06-cart.png`               |
| Checkout sandbox    | `docs/screenshots/07-checkout-sandbox.png`   |
| User orders         | `docs/screenshots/08-user-orders.png`        |
| Admin login         | `docs/screenshots/09-admin-login.png`        |
| Admin dashboard     | `docs/screenshots/10-admin-dashboard.png`    |
| Admin order detail  | `docs/screenshots/11-admin-order-detail.png` |
| Admin places        | `docs/screenshots/12-admin-places.png`       |
| Admin souvenirs     | `docs/screenshots/13-admin-souvenirs.png`    |
| Mobile responsive   | `docs/screenshots/14-mobile-responsive.png`  |
| Dark mode           | `docs/screenshots/15-dark-mode.png`          |

## Do Not Commit

* Environment files
* Provider credentials
* Session cookies
* Full webhook signatures
* Personal email/address data
* Unredacted provider payloads

## Remaining Evidence To Capture After Deployment

The following checks are intentionally left open until a public staging/demo environment exists:

* `/up` health endpoint check
* Public demo URL smoke test
* Media persistence before/after redeploy
* Mail delivery with redacted evidence
* Payment sandbox callback/webhook with secrets redacted
