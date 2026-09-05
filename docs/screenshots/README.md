# Screenshot Evidence

## Current Status

Fifteen screenshot files are present. The recorded visual review was executed on 2026-06-12 against commit `dbec28c` and is preserved as historical evidence in [Test Execution Evidence](../qa/test-execution-evidence.md).

The images must not be described as current for a later commit until they have been compared with the final UI. Refresh only views that changed materially; do not replace valid historical evidence merely to change timestamps.

## Inventory and Naming Convention

| Filename | View | Present |
|---|---|---|
| `01-homepage.png` | Homepage | Yes |
| `02-destinations.png` | Destination catalog | Yes |
| `03-destination-detail.png` | Destination detail | Yes |
| `04-review-form.png` | Verified-user review form | Yes |
| `05-souvenir-shop.png` | Souvenir catalog | Yes |
| `06-cart.png` | Cart and checkout controls | Yes |
| `07-checkout-sandbox.png` | Checkout/provider handoff state, secrets redacted | Yes |
| `08-user-orders.png` | User order history/detail | Yes |
| `09-admin-login.png` | Admin login | Yes |
| `10-admin-dashboard.png` | Admin dashboard | Yes |
| `11-admin-order-detail.png` | Admin order status management | Yes |
| `12-admin-places.png` | Destination management | Yes |
| `13-admin-souvenirs.png` | Souvenir management | Yes |
| `14-mobile-responsive.png` | Representative 360 px mobile view | Yes |
| `15-dark-mode.png` | Representative dark-mode view | Yes |

## Capture Standard

- Desktop viewport: 1440 × 900 or similar.
- Mobile viewport: 360 × 800 or 390 × 844.
- Use the curated local demo dataset.
- Keep browser zoom at 100%.
- Hide extensions, bookmarks, notifications, and local filesystem paths.
- Redact emails, addresses, provider references, tokens, signatures, and webhook payloads.
- Use legal, owned, or properly attributed images.
- Prefer PNG for UI evidence.
- Never fabricate a successful payment or external-provider response.

## Review Checklist for a New Capture

- [ ] Record date, tester, environment, and exact commit SHA.
- [ ] Confirm text is readable and not clipped.
- [ ] Confirm navigation and footer behavior where relevant.
- [ ] Confirm adequate light/dark contrast.
- [ ] Confirm no horizontal overflow at the stated mobile viewport.
- [ ] Label checkout evidence as mocked/local/sandbox accurately.
- [ ] Confirm no credentials, personal data, or unredacted payloads are visible.
- [ ] Confirm filenames and README links match the inventory.
- [ ] Add a new `VIS` row to [Test Execution Evidence](../qa/test-execution-evidence.md).

## Current Refresh Gate

- [ ] Compare the public home page with the current Inertia/Vue implementation.
- [ ] Compare admin pages with the current Inertia/Vue implementation.
- [ ] Compare customer catalog, cart, checkout, order, and profile Blade pages.
- [ ] Replace only materially outdated files.
- [ ] Verify rendering in both [`README.md`](../../README.md) and [`README.en.md`](../../README.en.md).
