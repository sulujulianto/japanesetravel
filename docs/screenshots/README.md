# Screenshot Evidence Checklist

**Status:** Owner action required — screenshots have not been captured as part of this repository task.

Store final screenshots in this directory and add README links only after each file exists.

## Naming Convention

| Filename | Required view |
|---|---|
| `01-homepage.png` | Homepage |
| `02-destinations.png` | Destination catalog |
| `03-destination-detail.png` | Destination detail |
| `04-review-form.png` | Verified-user review form |
| `05-souvenir-shop.png` | Souvenir catalog |
| `06-cart.png` | Cart and checkout controls |
| `07-checkout-sandbox.png` | Provider handoff or checkout state, secrets redacted |
| `08-user-orders.png` | User order history/detail |
| `09-admin-login.png` | Admin login |
| `10-admin-dashboard.png` | Admin dashboard |
| `11-admin-order-detail.png` | Admin order status management |
| `12-admin-places.png` | Destination management |
| `13-admin-souvenirs.png` | Souvenir management |
| `14-mobile-responsive.png` | 360 px mobile view |
| `15-dark-mode.png` | Representative dark-mode view |

## Capture Standard

- Desktop: 1440 × 900 or similar.
- Mobile: 360 × 800 or 390 × 844.
- Use the curated demo catalog.
- Keep browser zoom at 100%.
- Hide browser extensions, bookmarks, notifications, and local filesystem paths.
- Redact emails, provider references, tokens, and webhook payloads where appropriate.
- Use legal or owned images only.
- Prefer PNG for UI evidence.

## Review Checklist

- [ ] Text is readable and not clipped.
- [ ] Navbar/footer are visible where relevant.
- [ ] Light and dark surfaces have adequate contrast.
- [ ] Mobile screenshots have no horizontal overflow.
- [ ] Checkout evidence is clearly labelled sandbox.
- [ ] No fake payment-success state is staged.
- [ ] Screenshots match the current commit.

## README Linking

After a screenshot exists, add a standard Markdown image using its verified path under `docs/screenshots/`. No placeholder image link is committed because GitHub would render it as broken.

Record the capture date and commit SHA in [QA Execution Evidence](../qa/test-execution-evidence.md).
