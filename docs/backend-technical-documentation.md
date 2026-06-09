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

### Mail delivery untuk auth

- Konfigurasi mail mengikuti `config/mail.php` dan environment `MAIL_*`.
- Default `MAIL_MAILER=log` hanya menulis email ke log aplikasi; tidak ada email yang dikirim ke inbox pengguna.
- Reset password memakai notification Laravel, sedangkan email verification bergantung pada delivery notification ke alamat user.
- Production harus menggunakan SMTP valid atau transport provider transactional yang telah dipasang dan dikonfigurasi, misalnya Mailgun, Postmark, Resend, atau provider kompatibel lainnya.
- `MAIL_FROM_ADDRESS` dan `MAIL_FROM_NAME` harus memakai identitas pengirim production yang sah. Secret mail hanya disimpan pada environment deployment.
- Konfigurasi project memakai `MAIL_SCHEME` dari `config/mail.php`; `MAIL_ENCRYPTION` bukan env yang dibaca oleh konfigurasi saat ini.

Smoke test production:
1. Request reset password untuk akun test dan pastikan email diterima.
2. Buka link reset, pastikan token valid, lalu selesaikan perubahan password.
3. Request ulang email verification untuk user belum terverifikasi.
4. Pastikan email diterima, signed link valid, dan status user berubah menjadi verified.
5. Periksa spam placement dan konfigurasi domain pengirim sesuai panduan provider.

## 6) Destination / Place Flow
- `places` adalah katalog destinasi, **bukan** produk tiket.
- Flow:
  - Listing/home page.
  - Place detail page.
  - Review list per place.
  - Review submit oleh user authenticated + verified.
- Travel inquiry:
  - CTA WhatsApp memakai `TRAVEL_WHATSAPP_NUMBER` dalam format internasional digit-only.
  - Jika nomor belum tersedia, CTA ditampilkan sebagai state informasional nonaktif.
  - Link tidak menyisipkan pesan otomatis dan platform tidak memproses booking atau checkout jasa travel.
  - Checkout/payment aktif hanya untuk produk souvenir.
- Hardening review:
  - Route review memakai `throttle:6,1`.
  - Guard anti-duplicate pada application layer memberi respons ramah sebelum insert.
  - Unique constraint database `(place_id, user_id)` memastikan satu user hanya dapat memberi satu review per destinasi, termasuk saat request bersamaan.

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

### Environment dan sandbox readiness
- Credential gateway berasal dari `config/services.php`:
  - Midtrans: `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`.
  - PayPal: `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_WEBHOOK_ID`, `PAYPAL_IS_PRODUCTION`, `PAYPAL_CURRENCY`, `PAYPAL_EXCHANGE_RATE`.
- `MIDTRANS_IS_PRODUCTION=false` dan `PAYPAL_IS_PRODUCTION=false` adalah baseline sandbox. Credential sandbox dan production harus dipisahkan di environment deployment dan tidak boleh disimpan di repository.
- `APP_URL` harus berupa origin publik HTTPS yang benar. PayPal `return_url` dan `cancel_url` dibuat melalui named route Laravel, sehingga hasil URL bergantung pada konfigurasi URL aplikasi/proxy.
- Endpoint provider:
  - Midtrans notification: `POST /payments/webhook/midtrans`.
  - PayPal webhook: `POST /payments/webhook/paypal`.
  - PayPal return/cancel: `GET /payments/paypal/return` dan `GET /payments/paypal/cancel`.
- Midtrans memverifikasi `signature_key` menggunakan server key. Tidak ada env webhook secret Midtrans terpisah pada implementasi saat ini.
- PayPal memverifikasi event melalui API `verify-webhook-signature`; `PAYPAL_WEBHOOK_ID` harus berasal dari webhook pada app dan mode yang sama.
- PayPal mengonversi total IDR menggunakan `PAYPAL_CURRENCY` dan `PAYPAL_EXCHANGE_RATE`. Nilai rate saat ini bersifat konfigurasi manual dan harus diverifikasi secara operasional sebelum demo/live.
- Automated tests memalsukan gateway/API eksternal. Test tersebut membuktikan kontrak aplikasi, signature handling, idempotency, state guard, serta stock compensation, tetapi tidak membuktikan credential atau konfigurasi dashboard provider benar.
- Direct payment hanya untuk checkout souvenir. Konsultasi perjalanan melalui WhatsApp tidak membuat order travel dan tidak masuk ke gateway.

Sandbox test matrix minimum:

| Skenario | Midtrans | PayPal | Verifikasi aplikasi |
|---|---|---|---|
| Create checkout | sandbox credential | sandbox credential | redirect URL tersedia; order/payment `pending` |
| Return/cancel | provider redirect | return/cancel URL | user kembali ke deployment yang benar; tidak mengklaim paid sebelum konfirmasi |
| Success webhook | signed settlement/capture | verified completed event | payment `paid`; pending order menjadi `processing` |
| Failure/cancel | failed/expired/cancel event | denied/cancel event | status mengikuti guard; terminal order tidak direvive |
| Duplicate webhook | kirim ulang event sama | kirim ulang event ID sama | tidak ada efek ganda/idempotency record tetap aman |
| Gateway creation failure | simulasi provider gagal | simulasi provider gagal | payment `failed`, order `cancelled`, stok dikembalikan |

Operational checklist:
1. Gunakan `APP_URL` staging HTTPS dan credential sandbox.
2. Daftarkan webhook pada dashboard provider dan simpan PayPal sandbox webhook ID.
3. Jalankan checkout souvenir kecil dengan test instrument resmi provider; jangan gunakan uang nyata.
4. Verifikasi redirect, callback, webhook, status payment/order, duplicate delivery, dan stock compensation.
5. Tinjau log aplikasi/provider untuk kegagalan signature atau callback.
6. Untuk go-live, buat konfigurasi production terpisah dan ulangi smoke test terkontrol sebelum mengaktifkan kedua flag production.

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
- Upload validation membatasi MIME, ukuran file 2 MB, dan dimensi maksimum 6000×6000 piksel sebelum optimasi WebP.
- Penghapusan media dibatasi ke path relatif di `uploads/places` dan `uploads/souvenirs`; path traversal, absolute path, dan path di luar direktori tersebut diabaikan.
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
- **129 tests**
- **509 assertions**

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
- Detail environment dan runtime deployment tersedia di dokumen ini serta `.env.example`; `README.md` sengaja berfokus pada instalasi lokal dan ringkasan integrasi.
- Docker multi-stage:
  - Composer stage memasang dependency production dengan `--no-dev`, optimized autoload, dan package discovery.
  - Node stage memakai `npm ci` serta `npm run build`.
  - Image final memakai PHP 8.3 + Apache, extension aplikasi, Vite assets, dan permission awal Laravel.
- Railway runtime:
  - `start-web.sh` menjalankan `init-app.sh --runtime-only`, menyesuaikan Apache ke `$PORT`, lalu menjalankan Apache foreground.
  - Runtime membuat direktori storage/cache, memastikan `public/storage`, dan membangun `config:cache` serta `view:cache`.
  - Runtime sengaja tidak menjalankan migration/seed; migration dilakukan sebagai one-off `init-app.sh --migrate-only`.
  - `route:cache` belum dijalankan oleh runtime dan merupakan optimasi follow-up, bukan asumsi deployment saat ini.
- Laravel health endpoint tersedia pada `/up`. Dockerfile belum memiliki directive `HEALTHCHECK`, sehingga Railway perlu dikonfigurasi memakai HTTP health check tersebut.
- Baseline database drivers:
  - `SESSION_DRIVER=database`
  - `CACHE_STORE=database`
  - `QUEUE_CONNECTION=database`
  - migration project menyediakan tabel sessions, cache, jobs, dan failed jobs.
- `run-worker.sh` menjalankan koneksi `database` secara eksplisit serta membaca `DB_QUEUE` untuk nama queue. Mengubah `QUEUE_CONNECTION` ke Redis belum cukup tanpa menyesuaikan script pada fase terpisah.
- Production migration menggunakan `php artisan migrate --force` tanpa demo seed.
- Production mail wajib memakai SMTP/provider transactional yang valid; mailer `log` hanya untuk local/development dan tidak mengirim reset-password atau verification email.
- Payment belum boleh dianggap live-ready hanya karena automated tests lulus. Deployment harus menyelesaikan sandbox matrix, memakai `APP_URL` HTTPS, mendaftarkan webhook, memisahkan credential sandbox/production, dan melakukan smoke test terkontrol sebelum mengaktifkan mode production.
- `DatabaseSeeder` hanya menjalankan demo data pada environment `local`/`testing`.
- `DemoSeeder` bersifat destruktif dan hanya diizinkan pada environment `local`/`testing`.
- `DevAccountSeeder` memakai credential publik untuk visual review dan hanya diizinkan pada environment `local`/`testing`.
- Database target yang disarankan: MySQL service (selaras lokal MariaDB/MySQL).
- Production media strategy:
  - `MEDIA_DISK` menentukan disk untuk upload gambar destinasi dan souvenir; `FILESYSTEM_DISK` adalah default disk Laravel untuk kebutuhan lain.
  - Baseline Railway portfolio memakai `MEDIA_DISK=public` dan `FILESYSTEM_DISK=local`.
  - Media tersimpan sebagai relative path di database, dengan file fisik di `storage/app/public/uploads/places` atau `storage/app/public/uploads/souvenirs`.
  - URL publik mengandalkan symlink `public/storage` ke `storage/app/public`; runtime Railway tetap harus menjalankan `php artisan storage:link`.
  - Railway Volume harus dipasang pada `/var/www/html/storage/app/public` agar file bertahan setelah restart/redeploy.
  - Public disk tanpa persistent volume tidak production-safe: file dapat hilang sementara relative path tetap ada di database dan URL lama menjadi `404`.
  - Selama memakai volume lokal, deployment ditujukan untuk satu web replica dan memerlukan backup/export volume.
  - S3-compatible storage adalah roadmap, bukan konfigurasi yang siap dipakai sekarang. Adapter `league/flysystem-aws-s3-v3` belum terpasang dan migrasi membutuhkan konfigurasi bucket/endpoint/public URL serta pengujian tambahan.
- Redis/Sentry:
  - konfigurasi siap, aktivasi operasional ditunda tahap berikutnya.

## 15) Known Limitations
- Ticketing/booking belum diimplementasikan.
- Belum ada test concurrency “true parallel” (misal multi-request race test).
- Railway deployment belum dieksekusi.
- Dockerfile belum memiliki Docker `HEALTHCHECK`; gunakan Railway HTTP health check `/up`.
- `route:cache` belum menjadi bagian runtime preparation.
- Railway worker saat ini hardcoded ke queue connection `database`.
- Redis belum diaktifkan.
- Sentry belum diaktifkan di production runtime.
- Media production memerlukan Railway Volume; local/public tanpa volume tetap ephemeral dan baseline ini tidak mendukung multiple web replicas.
- Payment sandbox memerlukan credential, webhook, dan konfigurasi dashboard provider eksternal.

### Demo content
- `DemoSeeder` hanya boleh berjalan pada environment `local`/`testing` dan mengosongkan tabel aplikasi sebelum mengisi ulang data.
- Dataset portfolio dibuat kecil dan terkurasi: 10 destinasi, 10 produk oleh-oleh, serta maksimal tiga ulasan per destinasi.
- Slug destinasi bersifat deterministik agar URL demo stabil setelah seed ulang.
- Gambar demo sengaja dibiarkan kosong. Gunakan aset legal atau berlisensi dan unggah melalui admin untuk visual review.
- Production hanya menjalankan migration; jangan menjalankan demo seed atau mengimpor SQL demo.

## 16) Future Roadmap
- Perluasan gallery screenshot dan dokumentasi penggunaan untuk review portfolio.
- Implementasi ticketing/booking system terpisah.
- Aktivasi Redis (cache/session/queue).
- Aktivasi Sentry production.
- Load/performance testing (misal k6).
- Migrasi media ke object storage/S3 setelah adapter dan konfigurasi provider disiapkan.
- Pengurangan bertahap PHPStan baseline (technical debt cleanup).
