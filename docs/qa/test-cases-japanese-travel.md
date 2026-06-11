# JapanTravel Test Cases

**Execution status:** This document is a reusable QA plan. Rows marked “Not executed” are not evidence of a pass.

| Test ID | Feature | Preconditions | Steps | Expected result | Priority | Type | Automation status | Status |
|---|---|---|---|---|---|---|---|---|
| AUTH-001 | User registration | Fresh email; app running | Open `/register`; submit valid username/email/password/confirmation | User is created, authenticated, and redirected to dashboard/verification flow | P0 | Positive | Automated | Not executed manually |
| AUTH-002 | User login | Verified user exists | Open `/login`; enter valid credentials; submit | User session is regenerated and dashboard opens | P0 | Positive | Automated | Not executed manually |
| AUTH-003 | Email verification | Unverified user; mail log/provider available | Request verification; open signed link | Email timestamp is set and verified route becomes available | P0 | Positive | Automated internally; delivery manual | Not executed manually |
| PLACE-001 | Destination listing | At least one destination | Open `/places`; use search/rating/sort | Matching destination cards render; query remains valid | P1 | Positive/regression | Automated | Not executed manually |
| PLACE-002 | Destination detail | Known slug exists | Open `/place/{slug}` | Title, visit information, reviews, inquiry state, and shop CTA render | P1 | Positive | Automated partially | Not executed manually |
| REVIEW-001 | Review submission | Verified user; destination not reviewed by user | Submit rating/comment | Review is stored and success feedback appears | P0 | Positive | Automated | Not executed manually |
| REVIEW-002 | Duplicate review prevention | User already reviewed same destination | Submit a second review | Friendly duplicate message; no second row is created | P0 | Negative/regression | Automated at controller and DB level | Not executed manually |
| SHOP-001 | Souvenir listing/filter | Souvenirs exist | Open `/shop`; use existing search/filter/sort controls | Catalog responds without changing product data | P1 | Positive | Partial (render/format); filter manual | Not executed manually |
| CART-001 | Add to cart | Product has stock | Add quantity 1 | Cart session contains item; success message appears | P0 | Positive | Automated | Not executed manually |
| CART-002 | Update cart quantity | Item in cart; known stock | Submit quantity within and above stock | Valid quantity saves; excessive quantity is clamped with feedback | P0 | Positive/negative | Automated | Not executed manually |
| CHECKOUT-001 | Checkout | Verified user; valid cart; mocked/sandbox provider | Select provider; submit checkout | Order/items/payment created; stock decremented; redirect produced | P0 | Positive | Automated with mocked gateway | Not executed externally |
| CHECKOUT-002 | Payment creation failure | Valid cart; gateway configured to fail | Submit checkout | Order cancelled, payment failed, stock restored, cart retained, safe error shown | P0 | Negative/regression | Automated | Not executed manually |
| ORDER-001 | User order history | User owns orders | Open `/orders` and one detail | Only owned orders/items/payment status are shown | P0 | Positive | Automated partially | Not executed manually |
| ORDER-002 | Cross-user order access | User A and User B; order belongs to B | As A, open B's `/orders/{id}` | Response is `403`; no order data is exposed | P0 | Negative/security | Automated | Not executed manually |
| ADMIN-001 | Admin login | Admin-role account exists | Open `/admin/login`; submit credentials | Admin session opens `/admin`; user session remains separate | P0 | Positive/security | Automated partially | Not executed manually |
| ADMIN-002 | Admin access control | Normal user authenticated | Request `/admin` and admin update route | Redirect/forbidden; no admin action executes | P0 | Negative/security | Automated | Not executed manually |
| ADMIN-003 | Admin place CRUD | Admin authenticated | Create, edit, replace image, delete test destination | Validation persists expected data; media replacement/delete behaves safely | P1 | Positive/regression | Store/update media automated; full CRUD manual | Not executed manually |
| ADMIN-004 | Admin souvenir CRUD | Admin authenticated | Create, edit price/stock/image, delete test product | Data changes correctly without affecting unrelated orders | P1 | Positive/regression | Store/update media automated; full CRUD manual | Not executed manually |
| ADMIN-005 | Admin order status | Admin; orders in each state | Attempt valid and invalid transitions | Only pending→processing/cancelled and processing→completed/cancelled succeed | P0 | Positive/negative | Automated | Not executed manually |
| MEDIA-001 | Image upload validation | Admin; valid/invalid files | Upload valid JPEG/PNG; over-2MB/over-6000px/invalid type | Valid image stores as allowed format; invalid upload is rejected; no unsafe path deleted | P0 | Security/regression | Automated | Not executed manually |
| I18N-001 | Localization switch | Public and authenticated pages available | Switch ID/EN; inspect dates/prices/counts | UI copy and presentation formatting follow locale; DB content uses available translation | P1 | Regression | Automated broadly | Not executed manually |

## Execution Rules

- Use a disposable local database or approved staging environment.
- Record the commit SHA, tester, date, browser, and viewport.
- Never mark sandbox payment as passed without provider evidence.
- Never use real payment credentials or real money.
- Create a bug ticket for every failed P0/P1 test.
- Store screenshots according to `docs/qa/test-execution-evidence.md`.

