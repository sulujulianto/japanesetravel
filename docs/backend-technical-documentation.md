# Dokumentasi Teknis Backend

## 1. Ringkasan Sistem

Japan Travel adalah aplikasi referensi full-stack dengan dua domain yang sengaja dipisahkan:

- **Travel discovery:** katalog destinasi, detail, jadwal, ulasan pengguna terverifikasi, dan pertanyaan opsional melalui WhatsApp.
- **Souvenir commerce:** katalog produk, cart, checkout, pembayaran, histori order, inventory, serta operasional admin.

Aplikasi tidak menjual tiket dan tidak memproses booking perjalanan. Midtrans dan PayPal hanya digunakan untuk alur pembelian suvenir.

Target rilis saat ini adalah **portofolio local-first**. Repository menyediakan kontrak deployment, tetapi tidak mengklaim sudah live, menangani pembayaran nyata, atau terbukti beroperasi pada skala produksi.

## 2. Stack dan Batas Arsitektur

| Area | Implementasi |
|---|---|
| Backend | PHP 8.3, Laravel 12 |
| Frontend modern | Inertia.js 3, Vue 3 Composition API, TypeScript |
| Frontend server-rendered | Blade dengan JavaScript legacy yang terisolasi |
| UI/build | Tailwind CSS, Vite |
| Database | MariaDB/MySQL; SQLite untuk quick review dan quality job CI |
| Payment | Midtrans Snap dan PayPal Checkout |
| Ops opsional | Predis, Sentry Laravel, Docker/Railway assets |
| Quality | PHPUnit, Larastan/PHPStan, Pint, vue-tsc, ESLint, Composer/npm audit |

Frontend menggunakan migrasi hibrida yang disengaja:

| Area | Renderer saat ini |
|---|---|
| Beranda publik | Inertia + Vue + TypeScript |
| Seluruh area admin | Inertia + Vue + TypeScript |
| Katalog destinasi/detail | Blade |
| Shop dan cart | Blade |
| Auth, dashboard, profil, alamat | Blade |
| Checkout dan histori order pengguna | Blade |

`resources/views/app.blade.php` menjadi root Inertia, sedangkan view Blade lain tetap aktif untuk modul yang belum dimigrasikan. Kedua jalur memakai backend, brand configuration, locale, theme token, dan domain rules yang sama.

## 3. Struktur Utama

### Routing

- `routes/web.php`: public pages, cart, user area, admin, checkout, callback, dan webhook.
- `routes/auth.php`: autentikasi pengguna.
- `routes/console.php`: scheduled payment-payload pruning.

### Domain models

| Model | Tanggung jawab |
|---|---|
| `User` | Identitas login, role, relasi profil/alamat/order |
| `UserProfile` | Data personal tambahan terenkripsi |
| `UserAddress` | Beberapa alamat pengiriman dan default-address invariant |
| `Place` | Konten destinasi bilingual dan jadwal |
| `PlaceReview` | Ulasan unik per pengguna/destinasi |
| `Souvenir` | Produk, harga, stok, dan media |
| `Order` | Status order, checkout key, snapshot pelanggan/alamat, dan penanda pemulihan stok |
| `OrderItem` | Snapshot produk serta referensi produk opsional |
| `Payment` | Provider, status, amount/currency, reference, dan payload terbatas |
| `PaymentWebhookEvent` | Idempotency dan audit webhook |
| `InventoryMovement` | Ledger perubahan stok dan snapshot audit |

### Domain enums

- `OrderStatus`: `pending`, `processing`, `completed`, `cancelled`.
- `PaymentStatus`: `pending`, `paid`, `failed`, `expired`, `refunded`.
- `PaymentWebhookStatus`: status payment ditambah hasil nonpersisten `ignored`.
- `PaymentProvider`: `midtrans`, `paypal`.
- `UserRole`: `user`, `admin`.

Enum menjaga string database tetap kompatibel sekaligus memusatkan daftar nilai dan aturan transisi.

### Services

- `Payments/PaymentService`: orkestrasi driver dan transisi payment/order.
- `Payments/Drivers/*`: boundary Midtrans dan PayPal.
- `Payments/PaymentAmount`: perbandingan amount/currency terkanonisasi.
- `Payments/PaymentPayload`: whitelist dan pembatasan payload yang boleh disimpan.
- `Inventory/InventoryService`: mutasi stok transaksional dan ledger.
- `Orders/OrderInventoryService`: pemulihan stok order exactly-once.
- `UserAddressService`: default-address invariant dan operasi alamat milik pengguna.

## 4. Autentikasi, Otorisasi, dan Data Pengguna

### Pemisahan konteks autentikasi

- Guard `web` digunakan untuk pengguna biasa.
- Guard `admin` digunakan untuk area admin.
- Login pengguna menolak role admin; login admin menolak role user.
- `AdminSessionCookie` memilih nama cookie berbeda untuk path user dan admin.
- Logout satu guard tidak membatalkan guard lainnya.
- Endpoint login memakai rate limiter terpisah.

Route bisnis pengguna berada di `auth:web` dan `verified`. Route operasional admin berada di `auth:admin` dan middleware role `admin`.

### Profil dan alamat

- Satu pengguna memiliki maksimal satu `UserProfile` melalui unique constraint.
- Pengguna dapat memiliki beberapa `UserAddress`.
- Operasi alamat memverifikasi ownership di server.
- Hanya satu alamat yang menjadi default; alamat tunggal tidak boleh dibiarkan tanpa default.
- Admin dapat melihat data relevan secara read-only, bukan mengubah alamat pengguna.
- Nilai personal profil dan alamat menggunakan encrypted casts saat tersimpan.

### Penghapusan akun

- Profil dan alamat mengikuti siklus hidup akun.
- Order dipertahankan untuk integritas transaksi.
- Relasi hidup ke pengguna dilepas.
- Snapshot pelanggan terenkripsi menjaga konteks historis tanpa mempertahankan ketergantungan pada akun aktif.

## 5. Destination dan Review Flow

- `places` merupakan katalog destinasi, bukan inventori tiket.
- Konten destinasi dan suvenir menggunakan nilai terjemahan ID/EN.
- Review hanya dapat dibuat oleh pengguna authenticated dan verified.
- Route review dibatasi dengan `throttle:6,1`.
- Application guard memberi respons duplikasi yang ramah.
- Unique constraint `(place_id, user_id)` menjadi lapisan terakhir terhadap request bersamaan.
- `TRAVEL_WHATSAPP_NUMBER` opsional dan harus berupa digit internasional tanpa `+` atau spasi.
- CTA tidak membuat travel order dan tidak mengirim transaksi ke payment gateway.

## 6. Cart dan Checkout

### Cart

- Cart disimpan pada sesi pengguna.
- Item yang tidak lagi tersedia dibersihkan.
- Quantity divalidasi dan dibatasi ke stok yang tersedia.
- Harga dan total akhir tidak dipercaya dari browser; nilai authoritative dibaca kembali dari database.

### Checkout transaction

Checkout hanya tersedia bagi pengguna verified dan menggunakan alamat milik pengguna. Alur inti:

1. Validasi checkout token, provider, cart, dan alamat.
2. Tolak atau replay hasil request dengan idempotency key yang sama.
3. Mulai database transaction.
4. Ambil produk secara deterministik dan gunakan row lock.
5. Kurangi stok melalui `InventoryService` dan tulis ledger.
6. Simpan order, item snapshot, customer snapshot, dan shipping-address snapshot.
7. Buat payment `pending`.
8. Commit transaksi internal sebelum berinteraksi dengan provider.
9. Buat transaksi provider dan simpan hasil yang telah dibatasi.

Jika pembuatan transaksi provider gagal, payment ditandai `failed`, order dibatalkan, stok dipulihkan, dan pesan error untuk pengguna tidak membocorkan exception mentah.

### Checkout idempotency

- Token bersifat session-scoped dan disimpan sebagai `checkout_idempotency_key`.
- Database uniqueness mencegah dua order memakai key yang sama.
- Replay aman mengembalikan order yang sudah dibuat.
- Urutan penguncian produk deterministik mengurangi peluang deadlock.

## 7. Inventory Ledger

`InventoryService::adjust()` adalah jalur utama perubahan stok operasional:

- produk di-lock dengan `lockForUpdate`;
- stock-after tidak boleh negatif;
- `reference` unik mencegah adjustment yang sama diterapkan dua kali;
- ledger menyimpan delta, stock-before, stock-after, type, order, actor, product snapshot, dan metadata;
- admin restock/deduct serta order reservation/restoration dapat diaudit melalui model yang sama.

`OrderInventoryService::restore()`:

- mengunci order;
- berhenti jika `stock_restored_at` sudah terisi;
- mengelompokkan quantity per produk;
- mengunci produk dalam urutan ID;
- membuat reference restoration deterministik per order/produk;
- menandai `stock_restored_at` setelah pemulihan selesai.

Kombinasi penanda order dan unique ledger reference memberikan perlindungan exactly-once pada level aplikasi/database.

## 8. Payment Architecture

### Provider boundary

`PaymentService` memilih `MidtransSnapDriver` atau `PayPalCheckoutDriver` berdasarkan `PaymentProvider`. Driver mengonversi protocol provider menjadi:

- `PaymentGatewayResult` untuk pembuatan payment;
- `PaymentWebhookData` untuk webhook/capture terverifikasi.

Controller tidak menyimpan seluruh respons provider dan tidak menentukan transisi domain secara mandiri.

### Midtrans

- Signature `sha512` diverifikasi menggunakan server key.
- Event identity memasukkan transaction ID dan normalized status agar progression sah tetap dapat diproses.
- Amount dan currency callback dibandingkan dengan payment yang diharapkan.

### PayPal

- Checkout order dibuat melalui REST API.
- Webhook diverifikasi melalui `verify-webhook-signature` menggunakan webhook ID dari environment.
- Event ID PayPal menjadi idempotency identity.
- Capture reference, amount, dan currency harus cocok sebelum status finansial berubah.
- Return/cancel URL dibangun melalui named routes dan bergantung pada `APP_URL` yang benar.

### Webhook idempotency dan state guard

- Unique `(provider, event_id)` mencegah event identik diproses ulang.
- Duplicate event mengembalikan hasil aman tanpa efek finansial ganda.
- `ignored` adalah hasil parsing/non-action, bukan nilai yang disimpan sebagai `PaymentStatus`.
- Order terminal tidak dapat direvive atau diturunkan oleh callback terlambat.
- Transition payment mengikuti `PaymentStatus::allowedTransitions()`.

### Amount dan currency integrity

`PaymentAmount`:

- menormalisasi bilangan ke dua digit desimal;
- menolak format negatif/invalid dan precision nonzero di atas dua digit;
- menormalisasi currency menjadi tiga huruf uppercase;
- mensyaratkan amount dan currency sama persis setelah normalisasi.

PayPal dapat menggunakan currency selain IDR dengan `PAYPAL_EXCHANGE_RATE` manual. Konfigurasi tersebut harus diverifikasi secara operasional dan tidak diklaim sebagai kurs real-time.

## 9. State Machines

### Order

| From | Allowed target |
|---|---|
| `pending` | `pending`, `processing`, `cancelled` |
| `processing` | `processing`, `completed`, `cancelled` |
| `completed` | `completed` |
| `cancelled` | `cancelled` |

`processing` dan `completed` dihitung sebagai status pendapatan pada dashboard. `completed` dan `cancelled` bersifat terminal.

### Payment

| From | Allowed target |
|---|---|
| `pending` | `paid`, `failed`, `expired`, `refunded` |
| `failed` | `paid` |
| `expired` | `paid` |
| `paid` | `refunded` |
| `refunded` | tidak ada |

Status `failed`, `expired`, dan `refunded` memicu pembatalan serta pemulihan stok hanya ketika order masih `pending`. Callback yang datang terlambat tidak boleh merusak order yang sudah diproses atau selesai.

## 10. Payload Minimization dan Retention

`PaymentPayload` hanya mempertahankan field yang disetujui, misalnya provider/event reference, status, amount, currency, provider status, dan URL redirect terbatas. Nilai nonscalar diabaikan dan string dibatasi panjangnya.

- Exception mentah tidak disimpan sebagai payload pengguna.
- Migration meredaksi payload legacy.
- Index retention mendukung pembersihan terjadwal.
- Command `payments:prune-payloads` mengosongkan body payload yang melewati retention period tanpa menghapus audit record payment/webhook.
- Scheduler menjalankan command setiap hari pukul `03:10` dengan `withoutOverlapping()`.
- Retention default berasal dari `payments.payload_retention_days` dan dapat dioverride aman antara 1–3650 hari.

Scheduler harus benar-benar dijalankan oleh cron/scheduler service agar pruning terjadi di luar pengujian lokal.

## 11. Media Safety

- Upload dibatasi ke MIME yang disetujui, maksimum 2 MB, dan dimensi 6000×6000 sebelum pemrosesan.
- Gambar yang didukung dikonversi ke WebP.
- Path database bersifat relatif.
- Penghapusan hanya diizinkan pada `uploads/places` dan `uploads/souvenirs` di media disk yang dikonfigurasi.
- Absolute path, traversal, dan path di luar boundary diabaikan.
- Demo asset di `public/images` tidak diperlakukan sebagai upload yang boleh dihapus admin.

## 12. Security Controls

- Password hashing dan reset-password token Laravel.
- CSRF untuk route web normal; hanya webhook provider yang dikecualikan.
- Server-side auth, role enforcement, ownership checks, dan Form Request validation.
- Rate limiter pada login, review, dan webhook.
- Eloquent/Query Builder parameterization dan mass-assignment protection.
- Encrypted casts untuk data personal dan snapshot.
- `SecurityHeaders` menyediakan CSP serta header browser protection lainnya.
- Secure-cookie/HTTPS behavior dikonfigurasi untuk environment nonlokal.
- Seeder destruktif dan akun demo ditolak di luar `local`/`testing`.
- Dependency audit, CodeQL JavaScript/TypeScript, dependency review, dan secret scan dijalankan melalui GitHub Actions.

CodeQL yang ada tidak menganalisis PHP. PHP dicakup melalui Larastan/PHPStan, test suite, dependency audit, validation, dan review kode; ini bukan pengganti PHP-focused SAST atau penetration test.

## 13. Testing dan CI

Baseline terverifikasi pada commit `b2cff65`:

- 258 tests dan 2.498 assertions;
- Pint;
- Larastan/PHPStan level 6 tanpa error di luar committed baseline;
- Vue TypeScript check dan ESLint tanpa warning;
- Vite production build;
- Composer dan npm audit;
- SQLite, MariaDB 10.11, dan MariaDB 11.8;
- CodeQL, dependency review, dan secret scan.

Perintah lengkap dan batas interpretasi tersedia di [Testing Summary](testing-summary.md). Test mocked provider membuktikan kontrak internal aplikasi, bukan credential atau dashboard eksternal.

## 14. Kontrak Deployment Opsional

Deployment sengaja ditunda dan bukan blocker portofolio local-first. Repository tetap menyediakan:

- multi-stage Dockerfile;
- Railway web, migration, worker, dan scheduler scripts;
- `/up` health endpoint;
- pemisahan runtime startup dan `migrate --force`;
- panduan volume untuk media lokal;
- konfigurasi mail/payment/WhatsApp berbasis environment.

Baseline Railway terdokumentasi:

- session/cache/queue menggunakan database;
- worker script menjalankan connection `database` secara eksplisit;
- media `public` memerlukan volume pada `/var/www/html/storage/app/public`;
- strategi volume lokal ditujukan untuk satu web replica;
- object storage, Redis, dan Sentry activation belum dibuktikan secara operasional;
- demo seeders tidak boleh dijalankan pada staging/production.

Lihat [Deployment Checklist](deployment-checklist.md). Dokumen tersebut adalah runbook masa depan, bukan bukti deployment selesai.

## 15. Keterbatasan yang Diketahui

- Tidak ada direct travel booking/ticketing.
- Tidak ada deployment publik atau bukti penggunaan production.
- Tidak ada validasi akun sandbox Midtrans/PayPal dalam repository.
- Tidak ada bukti deliverability SMTP.
- Tidak ada true multi-process concurrency test.
- Tidak ada browser E2E, accessibility audit, load test, atau performance budget otomatis.
- PHPStan masih memakai baseline technical debt.
- Dockerfile tidak mendefinisikan Docker `HEALTHCHECK`; runbook memakai Railway HTTP health check `/up`.
- Worker Railway masih mengunci connection `database`.
- Media lokal belum mendukung multiple web replicas.
- Redis, Sentry runtime, dan object storage belum diaktifkan atau divalidasi.

## 16. Prioritas Lanjutan

Prioritas local-first:

1. Jalankan manual smoke/regression test pada commit rilis.
2. Cocokkan dan perbarui screenshot yang berubah material.
3. Kurangi PHPStan baseline dalam batch kecil.
4. Tambahkan browser/accessibility smoke test bila resource memungkinkan.
5. Ukur coverage hanya pada environment yang reproducible.

Deployment, sandbox provider, transactional mail, object storage, dan observability runtime tetap merupakan peningkatan opsional berbasis akun eksternal.
