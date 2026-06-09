# Japan Travel

Portfolio-grade travel storefront untuk destinasi Jepang + toko souvenir, lengkap dengan admin dashboard modern, integrasi pembayaran, dan UX bilingual (ID/EN).

![Tampilan Japan Travel](japantravel/japanese-travel.jpg)

**Fitur Utama**
- Public: listing destinasi, detail + ulasan, toko souvenir, cart, checkout, riwayat pesanan.
- Admin: dashboard KPI + charts, manajemen destinasi/souvenir, low-stock tooling, manajemen order + payment.
- Auth terpisah: guard user (web) dan guard admin, login admin via `/admin/login`.
- Payment integration: Midtrans Snap (Indonesia) + PayPal Checkout (internasional) dengan webhook, verifikasi signature, dan idempotency. Kesiapan live tetap bergantung pada credential, konfigurasi provider, serta smoke test sandbox/production.
- i18n: auto-locale dari browser, toggle ID/EN, konten DB bilingual (spatie/laravel-translatable).
- Theme toggle: light/dark berbasis class, tersimpan di localStorage, tanpa flicker.
- Security: rate limiting login & webhook, security headers, session/cookie hardening, sesi admin terpisah.
- Performance: pagination, eager loading, caching listing, indeks DB untuk query populer.

**Teknologi**
- Laravel 12, Breeze, Tailwind CSS, Vite.
- MariaDB/MySQL.
- Midtrans Snap, PayPal Checkout.
- Chart.js.

**Persiapan Lokal**
1) Install dependency
```bash
composer install
npm install
```

2) Salin env
```bash
cp .env.example .env
php artisan key:generate
```

3) Atur database di `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=japantravel
DB_USERNAME=root
DB_PASSWORD=
```

**Setup Database**

### Local fresh demo setup

Gunakan hanya pada database lokal yang boleh dihapus dan diisi ulang:

```bash
php artisan migrate --seed
```

`DatabaseSeeder` memanggil `DemoSeeder`, yang mengosongkan tabel aplikasi sebelum membuat data demo. Seeder ini dibatasi untuk environment `local`/`testing` dan tidak boleh digunakan pada staging atau production.

Alternatif import SQL demo lokal:

```bash
mysql -u root -p japantravel < japantravel/japantravel.sql
```

Jika memakai import SQL, tidak perlu menjalankan migrasi atau demo seeder lagi.

### Akun demo dari fresh demo setup

- Admin: `admin@japantravel.com` / `password`
- User: `kei@japantravel.com` / `password`

### Local visual-review accounts

Setelah data souvenir lokal tersedia, buat akun dan pesanan khusus visual review dengan:

```bash
php artisan db:seed --class=DevAccountSeeder
```

- Admin: `admin.demo@japantravel.test` / `Password123!`
- User: `user.demo@japantravel.test` / `Password123!`

`DevAccountSeeder` hanya ditujukan untuk visual review pada environment `local`/`testing`, tidak memanggil payment gateway, dan menolak environment lain.

### Production database setup

Jalankan migrasi tanpa demo seed:

```bash
php artisan migrate --force
```

Jangan menjalankan `php artisan migrate --seed`, `DemoSeeder`, `DevAccountSeeder`, atau mengimpor SQL demo pada database production.

**Payment Setup (Sandbox)**

Checkout/payment hanya berlaku untuk produk souvenir. Konsultasi dan layanan perjalanan dilakukan melalui WhatsApp dan tidak masuk ke payment gateway.

Gunakan credential sandbox provider dan pertahankan kedua flag production sebagai `false`:

```env
APP_URL=https://your-public-staging-domain.example

MIDTRANS_SERVER_KEY=your_midtrans_sandbox_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_sandbox_client_key
MIDTRANS_IS_PRODUCTION=false

PAYPAL_CLIENT_ID=your_paypal_sandbox_client_id
PAYPAL_CLIENT_SECRET=your_paypal_sandbox_client_secret
PAYPAL_WEBHOOK_ID=your_paypal_sandbox_webhook_id
PAYPAL_IS_PRODUCTION=false
PAYPAL_CURRENCY=USD
PAYPAL_EXCHANGE_RATE=15000
```

`APP_URL` harus menunjuk ke origin publik HTTPS yang benar. Driver PayPal membentuk return/cancel URL dari route aplikasi, sehingga URL yang salah dapat mengembalikan pembeli ke host yang keliru.

Endpoint yang perlu didaftarkan atau dikonfigurasi pada dashboard provider:
- Midtrans notification/webhook: `POST ${APP_URL}/payments/webhook/midtrans`
- PayPal webhook: `POST ${APP_URL}/payments/webhook/paypal`
- PayPal return: `GET ${APP_URL}/payments/paypal/return` (dibuat otomatis saat checkout)
- PayPal cancel: `GET ${APP_URL}/payments/paypal/cancel` (dibuat otomatis saat checkout)

Untuk uji local, gunakan ngrok (contoh):
```bash
ngrok http 8000
```
Lalu set `APP_URL` ke origin HTTPS tunnel tersebut, bersihkan cache konfigurasi, dan daftarkan endpoint webhook yang sesuai. Jangan memakai credential production atau uang nyata untuk pengujian sandbox.

Checklist sandbox sebelum demo portfolio:

1. Isi credential sandbox Midtrans dan PayPal di environment deployment; jangan simpan secret di repository.
2. Pastikan `MIDTRANS_IS_PRODUCTION=false` dan `PAYPAL_IS_PRODUCTION=false`.
3. Pastikan `APP_URL` adalah URL staging/deployment HTTPS yang dapat diakses provider.
4. Daftarkan kedua webhook URL dan salin PayPal sandbox webhook ID ke `PAYPAL_WEBHOOK_ID`.
5. Jalankan checkout souvenir bernilai kecil dengan akun/test instrument sandbox masing-masing provider.
6. Pastikan redirect ke provider serta return/cancel PayPal kembali ke deployment yang benar.
7. Pastikan webhook diterima, signature lolos, payment berubah sesuai event, dan order hanya berpindah mengikuti state yang diizinkan.
8. Kirim ulang event webhook yang sama dan pastikan tidak membuat efek ganda.
9. Uji kegagalan pembuatan payment/cancel dan pastikan stok tidak berkurang permanen secara keliru.
10. Periksa log aplikasi/provider tanpa menyalin credential atau payload sensitif ke dokumentasi.

Sebelum live, buat credential/webhook production yang terpisah, ulangi smoke test terkontrol, verifikasi `PAYPAL_EXCHANGE_RATE` yang digunakan aplikasi, lalu baru ubah flag provider ke `true`. Jangan menganggap keberhasilan automated test sebagai bukti bahwa credential, webhook dashboard, DNS, HTTPS, atau akun provider production sudah benar.

**Mail Setup**

Local development boleh mempertahankan konfigurasi berikut:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@example.test
MAIL_FROM_NAME="${APP_NAME}"
```

Mailer `log` hanya menulis isi email ke log aplikasi dan tidak mengirim email ke inbox. Konfigurasi ini berguna untuk development, tetapi bukan konfigurasi production.

Production harus menggunakan SMTP valid atau provider email transactional seperti Mailgun, Postmark, Resend, maupun provider lain yang kompatibel dengan Laravel. Isi environment deployment sesuai dokumentasi provider; jangan menyimpan username, password, API key, atau secret mail di repository.

Contoh baseline SMTP production tanpa credential nyata:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.provider.example
MAIL_PORT=587
MAIL_USERNAME=your-production-smtp-username
MAIL_PASSWORD=your-production-smtp-password
MAIL_FROM_ADDRESS=no-reply@your-production-domain.example
MAIL_FROM_NAME="${APP_NAME}"
```

Gunakan `MAIL_SCHEME=smtps` dan port yang sesuai jika provider mensyaratkan implicit TLS. Project ini membaca `MAIL_SCHEME` melalui `config/mail.php`, bukan `MAIL_ENCRYPTION`.

Reset password dan verifikasi email bergantung pada delivery mail yang berfungsi. Setelah deployment:

1. Daftarkan atau gunakan akun test pada domain production.
2. Minta reset password dan pastikan email diterima.
3. Pastikan link reset valid dan dapat digunakan.
4. Kirim ulang email verifikasi dan pastikan link signed dapat dibuka.
5. Periksa folder spam serta reputasi/domain authentication pengirim sesuai panduan provider.

**i18n (ID/EN)**
- Auto-locale dari header browser.
- Toggle manual: `/lang/id` atau `/lang/en`.

**Travel Inquiry / Contact**
- Konsultasi perjalanan tersedia melalui CTA WhatsApp pada halaman detail destinasi; website tidak memproses booking atau pembelian jasa travel secara langsung.
- Pembayaran langsung di website hanya digunakan untuk produk souvenir.
- `TRAVEL_WHATSAPP_NUMBER` bersifat opsional. Isi dengan nomor format internasional berupa digit saja, tanpa `+`, spasi, atau pesan otomatis.
- Jika env tersebut kosong, CTA tetap tampil sebagai status informasional nonaktif dan tidak mengarah ke URL WhatsApp yang rusak.
- Jangan menampilkan email, telepon, atau alamat bisnis sebelum kanal tersebut benar-benar dikonfirmasi.

**Menjalankan Aplikasi**
```bash
php artisan serve
npm run dev
```

Build produksi:
```bash
npm run build
php artisan serve
```

**Testing & Quality**
```bash
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
php artisan test
composer audit
npm audit --audit-level=high
```
CI GitHub Actions menjalankan build, pint, phpstan, test, dan audit.

**Dokumentasi Teknis Backend**
- Lihat dokumen lengkap: [`docs/backend-technical-documentation.md`](docs/backend-technical-documentation.md)

**Deployment Checklist**
- Gunakan Railway checklist di bawah sebagai sumber utama. Docker image sudah menjalankan Composer production install dan Vite build.
- Jalankan migrasi sebagai one-off command dengan `php artisan migrate --force`; jangan seed database production.
- Web runtime menyiapkan permission storage, `storage:link`, `config:cache`, dan `view:cache`.
- `route:cache` tidak dijalankan oleh script saat ini; perlakukan sebagai optimasi follow-up setelah diuji pada image yang sama.
- Gunakan `/up` sebagai health check aplikasi.

## Railway Deployment (No-Deploy Checklist)

Panduan ini untuk menyiapkan deployment Railway secara aman, tanpa menjalankan deploy otomatis dari aplikasi.

**1. Services yang disarankan**
- Web service: Dockerfile project ini.
- MySQL service: gunakan MySQL Railway agar konsisten dengan lokal MariaDB/MySQL.
- Optional worker service: untuk queue database jika ada job asynchronous.
- Optional cron service: untuk scheduler jika dipakai.

**2. Start command per service**
- Web: `sh railway/start-web.sh`
- One-off migration (manual sebelum go-live): `sh railway/init-app.sh --migrate-only`
- Worker (optional): `sh railway/run-worker.sh`
- Cron (optional): `sh railway/run-cron.sh`
- Health check path: `/up`

Web start tidak menjalankan migrasi. Jalankan one-off migration setelah environment database siap dan sebelum menerima traffic pada release yang membawa migration baru.

**3. Environment variables wajib (tanpa nilai secret)**
- App: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `TRUSTED_PROXIES`
- Logging: `LOG_CHANNEL`, `LOG_STACK`, `LOG_LEVEL`
- Database: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (atau `DB_URL`)
- Session/cache/queue: `SESSION_DRIVER`, `SESSION_SECURE_COOKIE`, `CACHE_STORE`, `QUEUE_CONNECTION`
- Storage: `FILESYSTEM_DISK`, `MEDIA_DISK`
- Mail: `MAIL_MAILER`, `MAIL_SCHEME`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- Payment Midtrans: `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`
- Payment PayPal: `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_WEBHOOK_ID`, `PAYPAL_IS_PRODUCTION`, `PAYPAL_CURRENCY`, `PAYPAL_EXCHANGE_RATE`
- Monitoring: `SENTRY_LARAVEL_DSN` (+ optional `SENTRY_ENVIRONMENT`, `SENTRY_RELEASE`, `SENTRY_TRACES_SAMPLE_RATE`)

**4. Production baseline env**
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` dibuat satu kali dengan `php artisan key:generate --show`, lalu disimpan sebagai secret Railway. Jangan membuat key baru pada setiap deploy.
- `APP_URL` memakai origin Railway/custom-domain HTTPS tanpa trailing path.
- `SESSION_SECURE_COOKIE=true`
- `TRUSTED_PROXIES=*`
- `LOG_CHANNEL=stack`
- `LOG_STACK=stderr`
- `DB_CONNECTION=mysql` dengan credential MySQL Railway atau `DB_URL` yang sesuai.
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`
- `FILESYSTEM_DISK=local`
- `MEDIA_DISK=public`
- `MAIL_MAILER` bukan `log`; gunakan SMTP/provider transactional yang sudah dikonfigurasi.
- `MAIL_FROM_ADDRESS` dan `MAIL_FROM_NAME` sesuai identitas domain/aplikasi production.
- `TRAVEL_WHATSAPP_NUMBER` opsional; biarkan kosong sampai kanal resmi tersedia.
- Untuk portfolio/staging payment, gunakan credential sandbox dan pertahankan kedua flag production sebagai `false`.

**5. Migration / seed strategy**
- Jalankan migrasi dengan `--force` via one-off command (`sh railway/init-app.sh --migrate-only`).
- Command production setara adalah `php artisan migrate --force` tanpa opsi `--seed`.
- Jangan menjalankan `DatabaseSeeder`, `DemoSeeder`, `DevAccountSeeder`, atau import SQL demo pada production.
- `DatabaseSeeder` hanya mengisi demo data pada environment `local`/`testing`.
- `DevAccountSeeder` hanya untuk visual review pada environment `local`/`testing` dengan credential publik yang terdokumentasi.

**6. Storage / media upload**
- `MEDIA_DISK` mengontrol penyimpanan upload gambar destinasi dan souvenir. `FILESYSTEM_DISK` tetap mengontrol default filesystem Laravel untuk kebutuhan aplikasi lainnya.
- Baseline deployment portfolio di Railway:
  ```env
  FILESYSTEM_DISK=local
  MEDIA_DISK=public
  ```
- Buat Railway Volume pada web service dan mount tepat ke:
  ```text
  /var/www/html/storage/app/public
  ```
- Runtime harus tetap menjalankan `php artisan storage:link`. Script Railway project ini sudah menyiapkan direktori storage dan memastikan symlink tersebut tersedia.
- Jangan menjalankan `MEDIA_DISK=public` di production tanpa persistent volume. Filesystem container Railway bersifat ephemeral sehingga upload dapat hilang setelah restart/redeploy, sedangkan path lama masih tersimpan di database dan URL gambar dapat menjadi `404`.
- Selama memakai local volume, gunakan satu web replica. Multiple replicas dapat melihat filesystem yang berbeda dan bukan target arsitektur deployment portfolio awal ini.
- Siapkan backup atau export berkala untuk volume karena file media tidak tersimpan di database maupun Git.

Smoke test persistence setelah deployment:
1. Upload satu gambar destinasi.
2. Upload satu gambar souvenir.
3. Simpan dan buka kedua URL gambar untuk memastikan respons `200`.
4. Restart atau redeploy web service.
5. Pastikan kedua URL lama tetap merespons `200`.
6. Uji replace gambar dan delete record untuk memastikan lifecycle file tetap bekerja.

Object storage S3-compatible adalah opsi masa depan untuk kebutuhan CDN, multiple replicas, atau scaling. Project belum memasang adapter `league/flysystem-aws-s3-v3`, sehingga S3 belum siap hanya dengan mengganti environment variable. Migrasi tersebut memerlukan dependency, konfigurasi bucket/endpoint/public URL, dan pengujian URL serta lifecycle media secara terpisah.

**7. Runtime, cache, dan worker**
- Docker build memakai `composer install --no-dev --optimize-autoloader`, menghasilkan Vite assets melalui `npm ci && npm run build`, dan menyalin hasil build ke image final.
- `railway/start-web.sh` menjalankan runtime preparation sebelum Apache:
  - membuat direktori Laravel yang diperlukan;
  - memperbaiki permission `bootstrap/cache` dan `storage`;
  - membuat `public/storage` bila belum ada;
  - membersihkan lalu membangun `config:cache` dan `view:cache`.
- Runtime tidak menjalankan migration atau seed secara otomatis.
- Runtime tidak menjalankan `route:cache`. Ini bukan blocker, tetapi dapat dievaluasi terpisah setelah `php artisan route:cache` diuji dalam deployment image.
- `railway/run-worker.sh` saat ini menjalankan `queue:work database` secara eksplisit dan memakai `DB_QUEUE` untuk nama queue. Jangan mengubah `QUEUE_CONNECTION` ke Redis lalu menganggap script tersebut ikut berubah.
- Database migrations sudah menyediakan tabel session, cache, jobs, dan failed jobs untuk baseline driver database.

**8. Verifikasi sebelum deploy**
```bash
composer validate
composer audit
./vendor/bin/phpstan analyse --no-progress
php artisan test
npm ci
npm run build
```

**9. Final Railway deployment checklist**
1. Buat Railway project, web service dari Dockerfile, dan MySQL service.
2. Generate `APP_KEY` sekali, lalu isi seluruh environment tanpa menyimpan secret di Git.
3. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`, `TRUSTED_PROXIES=*`, dan `SESSION_SECURE_COOKIE=true`.
4. Hubungkan database dan gunakan baseline database untuk session/cache/queue.
5. Buat Railway Volume pada web service dan mount ke `/var/www/html/storage/app/public`.
6. Konfigurasikan mail provider staging/production; jangan gunakan `MAIL_MAILER=log` untuk flow email nyata.
7. Isi `TRAVEL_WHATSAPP_NUMBER` hanya bila kanal resmi tersedia.
8. Bila menguji pembayaran, gunakan credential sandbox, daftarkan webhook HTTPS, dan pertahankan production flags `false`.
9. Jalankan `sh railway/init-app.sh --migrate-only` sebagai one-off command. Jangan menjalankan seed demo atau import SQL demo.
10. Start web service dengan `sh railway/start-web.sh`; tambahkan worker/cron hanya jika memang dibutuhkan.
11. Set health check Railway ke `/up`.
12. Jalankan smoke test:
    - homepage, places, shop, dan health check merespons;
    - register/login, user dashboard, admin login, dan guard separation bekerja;
    - upload destinasi/souvenir bertahan setelah restart/redeploy;
    - cart dan checkout souvenir sandbox bekerja tanpa memakai uang nyata;
    - webhook diterima dan duplicate event tetap idempotent;
    - forgot password/email verification benar-benar terkirim;
    - WhatsApp tetap disabled bila nomor kosong atau membuka kanal resmi bila diisi;
    - footer, HTTPS redirect, session cookie, serta log error diperiksa.
13. Simpan backup database dan Railway Volume sebelum perubahan production berikutnya.

**Known deployment limitations**
- Dockerfile belum mendefinisikan Docker `HEALTHCHECK`; Railway harus memakai HTTP health check `/up`.
- `route:cache` belum menjadi bagian runtime script.
- Worker Railway hanya mendukung koneksi database dalam bentuk saat ini.
- S3 belum aktif; local media volume membatasi deployment ke satu web replica.
- Payment sandbox tetap membutuhkan konfigurasi eksternal pada dashboard Midtrans/PayPal.
- Redis dan Sentry runtime masih opsional/belum diaktifkan sebagai baseline.

**Regenerasi SQL Demo**
```bash
php scripts/generate_demo_sql.php
```

Command ini hanya untuk artefak demo lokal. Jangan menjalankannya atau mengimpor hasilnya ke database staging/production.

## Lisensi
MIT (mengikuti lisensi bawaan Laravel).
