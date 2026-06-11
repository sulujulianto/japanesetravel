# 1–2 Minute Demo Video Script

**Status:** Owner action required — pending recording.

Target duration: 90–120 seconds. Record at 1080p if practical, hide bookmarks/notifications, and use seeded local data or an approved staging deployment. Do not show secrets, payment credentials, personal email, or provider dashboards.

## Recording Preparation

- [ ] Run the application and Vite build/dev server.
- [ ] Seed curated demo content and `DevAccountSeeder` locally.
- [ ] Upload only legal images or keep placeholders.
- [ ] Prepare user and admin sessions in separate browser profiles/tabs.
- [ ] Keep payment provider screens out of the recording unless sandbox credentials are configured.
- [ ] Confirm language, theme, and viewport before recording.

## Scene-by-Scene Script

| Time | Scene | Action | Suggested narration |
|---|---|---|---|
| 0:00–0:08 | Opening/homepage | Show homepage hero and primary navigation. | “JapanTravel is a Laravel 12 portfolio project for Japanese destination discovery and souvenir commerce.” |
| 0:08–0:18 | Destination catalog | Open `/places`, demonstrate search/filter briefly. | “Visitors can browse bilingual destinations and narrow the catalog without entering a booking flow.” |
| 0:18–0:28 | Destination detail | Open a destination and show visit information and inquiry CTA. | “Each detail page presents practical visit information. Travel services use an optional WhatsApp inquiry, not direct ticketing.” |
| 0:28–0:36 | Review flow | Show verified-user review form and existing reviews. | “Verified users can submit one review per destination; duplicate protection exists in the application and database.” |
| 0:36–0:46 | Souvenir shop | Open `/shop`, show products, localized price, and stock state. | “Direct commerce is limited to souvenirs, with localized IDR formatting and stock-aware product controls.” |
| 0:46–0:56 | Cart and checkout | Add/update an item, show provider selection. Do not complete real payment. | “The cart validates stock before checkout and supports Midtrans or PayPal sandbox integration.” |
| 0:56–1:04 | Order history | Show user order list and one detail page. | “Authenticated users can review order items, payment status, and retry eligible payments.” |
| 1:04–1:15 | Admin login/dashboard | Switch to admin session and show operational metrics/charts. | “The separate admin guard provides an operational dashboard for orders, revenue, inventory, and catalog data.” |
| 1:15–1:27 | Admin management | Show order status panel and destination/souvenir management screens. | “Admins can manage content, upload validated media, restock products, and apply guarded order transitions.” |
| 1:27–1:38 | Testing/CI | Show terminal test summary or GitHub Actions result. | “The repository currently has 129 automated tests with build, formatting, static analysis, and dependency audits in CI.” |
| 1:38–1:50 | Honest limitations/closing | Return to homepage or README. | “External mail, payment sandbox accounts, persistent media, and public deployment still require owner configuration. The repository documents each manual verification step.” |

## Optional Shorter Cut

For a 60–75 second version, omit profile pages, reduce catalog browsing, and combine admin order/catalog management into one scene.

## Evidence Checklist

- [ ] Video link added to README only after upload.
- [ ] No secret or personal data visible.
- [ ] No claim of live payment without sandbox evidence.
- [ ] No claim of travel booking.
- [ ] CI/test statement matches the latest verified run.
- [ ] Limitations are stated verbally or on-screen.

