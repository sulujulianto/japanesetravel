# Regression Test Scenarios

| Scenario | Related feature | Risk | Steps | Expected result | Automation status | Suggested test file |
|---|---|---|---|---|---|---|
| Checkout stock handling | Cart/checkout/inventory | Overselling or stock loss | Add known stock; checkout; simulate gateway success and creation failure; inspect stock | Success decrements once; creation failure restores all quantities; stale cart is rejected | Automated | `tests/Feature/CheckoutTest.php` |
| Payment webhook idempotency | Payment/webhook | Duplicate fulfillment/status updates | Deliver valid event; replay same provider/event ID | One event record; duplicate causes no second state effect | Automated internally; sandbox replay manual | `tests/Feature/PaymentWebhookTest.php` |
| Admin order status transition | Admin orders | Invalid fulfillment state | Try every valid and invalid transition, including terminal states | Only configured transition graph succeeds | Automated | `tests/Feature/AdminOrderStatusTransitionTest.php` |
| Review unique constraint | Reviews | Duplicate review/race | Submit twice through controller; insert duplicate directly; use different place/user controls | Duplicate same user/place rejected; valid combinations succeed | Automated | `tests/Feature/PlaceReviewTest.php` |
| Media upload path safety | Admin media | Arbitrary file deletion or resource exhaustion | Upload valid/oversized image; replace; attempt outside/traversal delete path | Validation rejects oversized image; only approved directories can be deleted | Automated | `tests/Feature/AdminMediaUploadTest.php` |
| Locale switching | i18n/formatting | Mixed language, wrong currency/date/cache | Switch ID/EN across public/user/admin; inspect chart cache | Copy/format follow locale; chart labels do not leak between locales | Automated broadly; visual manual | `tests/Feature/LocaleTest.php` |
| Cross-user order access | User orders | Data exposure | Create order for user B; request as user A | `403`; no order detail rendered | Automated | `tests/Feature/CheckoutTest.php` |

## Release Use

Run automated scenarios on every pull request. Run manual portions before publishing a staging URL or recording portfolio evidence.

