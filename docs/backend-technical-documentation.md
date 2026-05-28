# Backend Technical Documentation

## 1) Project Overview
Japan Travel adalah **destination discovery platform with reviews and souvenir e-commerce**.

Ruang lingkup aktif saat ini:
- Katalog destinasi (`places`) + detail + review user.
- Toko souvenir end-to-end (cart, checkout, order, payment, webhook, admin operations).

Yang **belum** aktif:
- Ticketing/booking pembelian tiket destinasi belum diimplementasikan.

## 2) Tech Stack
- Laravel 12
- PHP 8.3
- MariaDB/MySQL
- Blade
- Tailwind CSS
- Alpine.js
- Vite
- Midtrans Snap
- PayPal Checkout
- Sentry-ready configuration (`bootstrap/app.php` + `config/sentry.php`)
- Dockerfile + Railway scripts (`railway/*.sh`)

## 3) Main Modules
- User authentication & profile.
- Admin authentication (guard terpisah).
- Destination/place catalog.
- Place review submission.
- Souvenir shop listing/filtering.
- Cart management.
- Checkout & order creation.
- Order history & retry payment.
- Payment orchestration (Midtrans/PayPal).
- Payment webhooks.
- Admin dashboard: places, souvenirs, stock restock, order status, media upload.

## 4) Architecture Overview
- Routes: `routes/web.php`, `routes/auth.php`
- Controllers:
  - Public/business: `HomeController`, `ReviewController`, `ShopController`, `CartController`, `CheckoutController`, `PaymentController`
  - Admin: `Admin/*Controller`
- Models: `User`, `Place`, `PlaceReview`, `Souvenir`, `Order`, `OrderItem`, `Payment`, `PaymentWebhookEvent`
- Services (payment): `PaymentService`, `PaymentGatewayResult`, `PaymentWebhookData`, drivers `MidtransSnapDriver`, `PayPalCheckoutDriver`
- Middleware: auth redirect, admin guard, admin session cookie split, localization, security headers
- Form Requests: admin login, restock, update order status, profile/auth defaults
- Views: Blade pages untuk public/user/admin
- Tests: Feature tests + Unit sanity test
- Deployment scripts: `railway/start-web.sh`, `railway/init-app.sh`, `railway/run-worker.sh`, `railway/run-cron.sh`

## 5) Authentication and Authorization
- `web` guard untuk user biasa.
- `admin` guard untuk area admin.
- Route bisnis penting berada di middleware `auth:web` + `verified`.
- `User` mengimplementasikan `MustVerifyEmail`.
- Admin gate:
  - `auth:admin`
  - middleware `admin` yang memvalidasi role admin.
- Isolasi sesi admin/user:
  - cookie sesi terpisah (`web_cookie` vs `admin_cookie`) via `AdminSessionCookie` middleware.

## 6) Destination / Place Flow
- `places` adalah katalog destinasi, **bukan** produk tiket.
- Flow:
  - Listing/home page.
  - Place detail page.
  - Review list per place.
  - Review submit oleh user authenticated + verified.
- Hardening review:
  - Route review memakai `throttle:6,1`.
  - Guard anti-duplicate pada application layer:
    - 1 user hanya boleh 1 review per place.
- Residual recommendation:
  - Tambah unique DB index `(place_id, user_id)` untuk race-condition safety di tahap terpisah.

## 7) Souvenir E-Commerce Flow
- Shop listing/filter/sort.
- Cart add/update/remove:
  - validasi stok real-time
  - stale item cleanup
  - clamp quantity ke stok tersedia
- Checkout:
  - sanitasi cart sebelum transaksi
  - lock stok (`lockForUpdate`) saat order creation
  - create `orders`, `order_items`, `payments`
- Order item snapshot:
  - `product_name`, `product_price`, `product_image`
  - `souvenir_id` nullable untuk kompatibilitas histori jika produk dihapus
- Gateway failure compensation:
  - stok dikembalikan
  - order di-cancel
  - payment ditandai failed
  - cart dipertahankan agar user bisa retry
- Retry payment:
  - cegah duplikasi pending payment
  - reuse pending payment redirect URL jika tersedia
  - terminal order (completed/cancelled) ditolak

## 8) Payment Architecture
- `PaymentService` memilih driver berdasarkan provider (`midtrans` atau `paypal`).
- `PaymentGatewayResult` menyatukan output create payment.
- `PaymentWebhookData` menyatukan payload webhook lintas provider.

### Midtrans
- Signature verification (`sha512`) dari payload.
- Event identity lifecycle-aware:
  - `event_id = transaction_id + ":" + normalized_status`
  - memungkinkan progression `pending -> paid` pada transaction yang sama.

### PayPal
- Checkout order creation via API.
- Webhook signature verification via endpoint verify PayPal.
- Webhook idempotency menggunakan event ID PayPal.
- Return callback:
  - capture success path guarded agar terminal order tidak dioverwrite.
  - capture failure ditangani aman (no HTTP 500), payment gagal ditandai, user mendapat flash error aman.

### Idempotency
- Tabel `payment_webhook_events` dengan unique `(provider, event_id)`.
- Duplicate webhook event status sama tidak dieksekusi ulang.

## 9) Order / Payment State Machine
| Event / Action | Payment Status | Order Before | Order After | Allowed / Blocked |
|---|---|---|---|---|
| Checkout gateway create success | `pending` | n/a | `pending` | Allowed |
| Checkout gateway create failure | `failed` | `pending` | `cancelled` + stock restored | Allowed |
| Retry with existing pending payment + redirect URL | tetap `pending` (reuse) | `pending` | tetap `pending` | Allowed |
| Retry after `failed/expired` payment | new payment `pending` | `pending` | tetap `pending` | Allowed |
| Retry for `completed` order | no new payment | `completed` | tetap `completed` | Blocked |
| Retry for `cancelled` order | no new payment | `cancelled` | tetap `cancelled` | Blocked |
| Webhook `paid` | `paid` | `pending` | `processing` | Allowed |
| Webhook `paid` on `processing/completed/cancelled` | `paid` | terminal/non-pending | no downgrade/revive | Guarded |
| Webhook `failed/expired/refunded/cancelled` | status sesuai webhook | `pending` | `cancelled` | Allowed |
| Webhook `failed/expired/refunded/cancelled` on `processing/completed` | status payment boleh berubah | non-pending | order tetap | Guarded |
| PayPal return capture success | `paid` | `pending` | `processing` | Allowed |
| PayPal return capture success on `completed/cancelled` | `paid` | terminal | tetap terminal | Guarded |
| PayPal return capture failure | `failed` (jika pending) | status order apa pun | tidak dipromote | Allowed, safe failure |
| Admin: `pending -> processing` | n/a | `pending` | `processing` | Allowed |
| Admin: `pending -> cancelled` | n/a | `pending` | `cancelled` | Allowed |
| Admin: `processing -> completed` | n/a | `processing` | `completed` | Allowed |
| Admin: `processing -> cancelled` | n/a | `processing` | `cancelled` | Allowed |
| Admin invalid transition (termasuk completed/cancelled -> lainnya) | n/a | terminal/invalid source | unchanged | Blocked |

## 10) Security Hardening
- CSRF untuk route web normal.
- Route bisnis penting di `auth:web` + `verified`.
- Admin route di `auth:admin` + admin middleware.
- Payment webhook:
  - signature verification
  - throttle
  - idempotency table
- Security headers middleware (CSP, X-Frame-Options, dll).
- Upload validation (mime/size) + image optimization WebP.
- Quality/security gates:
  - `composer audit` (backend dependency advisories)
  - `npm audit` (frontend dependency advisories)
  - PHPStan/Larastan
  - Laravel Pint
  - GitHub Actions CI

## 11) Data Integrity Hardening
- Stock locking saat checkout (`lockForUpdate`).
- Stock compensation saat gateway create gagal.
- Cart stale cleanup sebelum update/checkout.
- Order item snapshot untuk ketahanan histori.
- `souvenir_id` nullable untuk kompatibilitas produk terhapus.
- Retry payment duplicate prevention.
- Admin order transition guard.
- Webhook order terminal guard.

## 12) Testing Strategy
Coverage utama saat ini:
- Auth flows (`tests/Feature/Auth/*`)
- `AdminAccessTest`
- `AdminSessionIsolationTest`
- `AdminMediaUploadTest`
- `AdminOrderStatusTransitionTest`
- `CartTest`
- `CheckoutTest`
- `PaymentWebhookTest`
- `PlaceReviewTest`
- `OrderItemImageCompatibilityTest`
- `LocaleTest`
- `ProfileTest`

Current baseline:
- **77 tests**
- **315 assertions**

## 13) CI and Quality Gates
CI menjalankan:
- `composer validate`
- `composer audit`
- `npm audit --audit-level=high`
- `php artisan test`
- `vendor/bin/pint --test`
- `vendor/bin/phpstan analyse --no-progress`
- `npm run build`

## 14) Deployment Readiness
- Dockerfile tersedia.
- Railway scripts tersedia.
- Status deployment Railway: **postponed** (belum go-live).
- Env production yang dibutuhkan sudah terdokumentasi di `README.md` dan `.env.example`.
- Database target yang disarankan: MySQL service (selaras lokal MariaDB/MySQL).
- Storage/media caveat:
  - default local/public bersifat ephemeral di container.
  - perlu volume mount atau object storage (S3-compatible).
- Redis/Sentry:
  - konfigurasi siap, aktivasi operasional ditunda tahap berikutnya.

## 15) Known Limitations
- Ticketing/booking belum diimplementasikan.
- Anti-duplicate place review masih application-layer; unique DB index direkomendasikan.
- Belum ada test concurrency “true parallel” (misal multi-request race test).
- Railway deployment belum dieksekusi.
- Redis belum diaktifkan.
- Sentry belum diaktifkan di production runtime.
- Media storage masih local/public bila tanpa volume/object storage.

## 16) Future Roadmap
- Fase UI/design refinement (`design.md`).
- Implementasi ticketing/booking system terpisah.
- Aktivasi Redis (cache/session/queue).
- Aktivasi Sentry production.
- Load/performance testing (misal k6).
- Migrasi media ke object storage/S3.
- Penambahan unique DB index `place_reviews(place_id, user_id)`.
- Pengurangan bertahap PHPStan baseline (technical debt cleanup).
