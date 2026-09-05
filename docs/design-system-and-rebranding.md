# Sistem Desain dan Rebranding / Design System and Rebranding

## Bahasa Indonesia

### Tujuan

Halaman publik, akun, toko, dan admin memakai identitas visual yang sama. Admin tetap lebih padat karena berorientasi pekerjaan, tetapi warna, tipografi, spacing, status, focus state, dan pola interaksinya berasal dari sistem yang sama.

### Sumber konfigurasi merek

Identitas tidak boleh ditulis langsung di komponen Vue, layout Blade, atau integrasi pembayaran.

| Nilai | Sumber | Contoh |
|---|---|---|
| Nama produk | `APP_NAME` | `Japan Travel` |
| Marka pendek | `BRAND_MARK` | `JT` |
| Nama legal | `BRAND_LEGAL_NAME` | `Japan Travel` |
| Wilayah Indonesia | `BRAND_REGION_ID` | `Jepang` |
| Wilayah Inggris | `BRAND_REGION_EN` | `Japan` |

`App\Support\Brand` adalah API tunggal untuk membaca konfigurasi tersebut. `HandleInertiaRequests` membagikannya melalui prop `app`, sementara Blade membaca helper yang sama.

Mengubah identitas merek tidak mengubah data destinasi, foto, harga, kebijakan, atau katalog. Pemisahan ini mencegah konfigurasi kecil berpura-pura menjadi CMS.

### Token visual

Token berada di `resources/css/app.css`. Gunakan variabel semantik, bukan warna heksadesimal baru di halaman.

- `--public-canvas`: latar dokumen.
- `--public-surface` dan `--public-surface-elevated`: permukaan konten.
- `--public-surface-muted`: bidang pendukung.
- `--public-hero`, `--public-navigation`, dan `--public-footer`: bidang struktural.
- `--public-ink` dan `--public-muted`: teks utama dan sekunder.
- `--public-accent`: aksi utama dan identitas merek.
- `--public-secondary`: aksi pendukung.
- `--public-border`, `--public-focus`, dan `--public-shadow`: batas, fokus, dan elevasi.

Token `auth-*` dan `admin-*` memetakan nilai sistem publik agar tema terang/gelap tetap konsisten.

### Aturan komponen

- Gunakan satu primary action per area visual.
- Secondary action tidak boleh bersaing dengan primary action.
- Kartu memakai `ui-surface`; bidang pendukung memakai `ui-surface-muted`.
- Navigasi, hero, kartu, formulir, dan footer memakai permukaan solid.
- Transparansi dibatasi untuk backdrop dialog/menu.
- Judul memakai `ui-heading`; label bagian memakai `ui-eyebrow`.
- Gunakan system font stack; jangan menambah ketergantungan font hanya untuk dekorasi.
- Motion singkat memakai `opacity`/`transform` dan menghormati `prefers-reduced-motion`.
- Jangan membuat vertical scrollbar kedua di dalam konten utama.
- Sidebar harus kembali menjadi alur dokumen yang masuk akal pada mobile.
- Error, focus, disabled, loading, empty, dan success state harus terlihat tanpa bergantung pada warna saja.

### Batas frontend saat ini

| Area | Implementasi |
|---|---|
| Beranda publik | Inertia + Vue + TypeScript |
| Admin login/dashboard | Inertia + Vue + TypeScript |
| Admin users/orders/inventory | Inertia + Vue + TypeScript |
| Admin places/souvenirs | Inertia + Vue + TypeScript |
| Destinasi dan shop publik | Blade |
| Auth, dashboard, profil, alamat | Blade |
| Cart, checkout, order pengguna | Blade |

Komponen Vue berada di `resources/js/Components`, layout di `resources/js/Layouts`, dan page modules di `resources/js/Pages`. Type contract admin/public berada di `resources/js/types`. JavaScript lama dibatasi di `resources/js/legacy.js` untuk view Blade yang belum dimigrasikan.

Migrasi harus dilakukan per modul dengan Inertia contract test, type-check, lint, build, dan visual review. Menghapus Blade sekaligus bukan tujuan tersendiri; stabilitas checkout, auth, dan localization lebih penting daripada persentase migrasi.

### Aturan konsistensi lintas renderer

- Brand, locale, route, dan domain state berasal dari backend yang sama.
- Jangan menggandakan nilai status order/payment ke daftar string frontend baru.
- Vue menerima serialized props eksplisit; jangan mengirim model Eloquent lengkap tanpa boundary.
- Blade dan Vue harus memakai token tema yang sama.
- Theme state harus konsisten dan tidak menimbulkan flash yang mengganggu.
- Teks ID/EN harus tetap semantik, bukan sekadar mengganti label tanpa format locale.

### Pemeriksaan wajib

```bash
composer test
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
vendor/bin/pint --test
npm run type-check
npm run lint
npm run build
git diff --check
```

Audit visual mencakup mobile/desktop, light/dark, Bahasa Indonesia/Inggris, keyboard focus, form error, loading/empty state, dan console browser. Pastikan tidak ada hydration mismatch atau overflow horizontal.

## English

### Purpose

Public, account, shop, and admin screens share one visual identity. Admin remains denser because it is task-oriented, but color, typography, spacing, status, focus, and interaction patterns come from the same system.

### Brand source of truth

Brand identity must not be hard-coded in Vue components, Blade layouts, or payment integrations. `APP_NAME`, `BRAND_MARK`, `BRAND_LEGAL_NAME`, `BRAND_REGION_ID`, and `BRAND_REGION_EN` are read through `App\Support\Brand`. Inertia receives them through the shared `app` prop, while Blade reads the same helper.

A brand change does not migrate destinations, images, pricing, policies, or catalog data. This boundary keeps configuration predictable instead of turning it into an incomplete CMS.

### Visual rules

- Use semantic variables from `resources/css/app.css`; avoid page-level hexadecimal colors.
- Use one primary action per visual area.
- Keep navigation, hero, card, form, and footer surfaces opaque.
- Reserve transparency for dialog or menu backdrops.
- Use the shared surface, heading, eyebrow, focus, and status patterns.
- Keep motion short and respect `prefers-reduced-motion`.
- Avoid nested vertical scrolling and horizontal mobile overflow.
- Make focus, error, disabled, loading, empty, and success states understandable without color alone.

### Current frontend boundary

The public home page and all admin modules use Inertia, Vue 3 Composition API, and TypeScript. Public catalog, customer authentication, cart, checkout, orders, and profile modules still use Blade. Legacy JavaScript is isolated in `resources/js/legacy.js`.

Both renderers share backend brand, locale, theme tokens, routes, and domain rules. Migrate one module at a time with contract tests, type checking, linting, builds, and visual review. Removing Blade is not a goal by itself; preserving authentication, checkout, and localization behavior is more important.

### Required checks

```bash
composer test
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
vendor/bin/pint --test
npm run type-check
npm run lint
npm run build
git diff --check
```

Visual review covers mobile/desktop, light/dark, Indonesian/English, keyboard focus, form errors, loading/empty states, and browser-console output.
