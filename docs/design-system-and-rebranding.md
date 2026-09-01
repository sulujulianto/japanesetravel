# Sistem Desain dan Rebranding / Design System and Rebranding

## Bahasa Indonesia

### Tujuan

Antarmuka menggunakan satu identitas visual untuk halaman publik, akun, toko, dan admin. Admin tetap lebih padat karena berorientasi pekerjaan, tetapi memakai warna, tipografi, jarak, status, dan pola interaksi yang sama.

### Sumber konfigurasi merek

Identitas tidak boleh ditulis langsung di komponen Vue, layout Blade, atau integrasi pembayaran.

| Nilai | Sumber | Contoh |
|---|---|---|
| Nama produk | `APP_NAME` | `Japan Travel` |
| Marka pendek | `BRAND_MARK` | `JT` |
| Nama legal | `BRAND_LEGAL_NAME` | `Japan Travel` |
| Wilayah Indonesia | `BRAND_REGION_ID` | `Jepang` |
| Wilayah Inggris | `BRAND_REGION_EN` | `Japan` |

`App\Support\Brand` adalah API tunggal untuk membaca konfigurasi tersebut. Middleware Inertia membagikannya melalui prop `app`, sedangkan Blade memanggil helper yang sama.

Mengubah merek tidak otomatis mengubah data destinasi, foto, harga, kebijakan, atau konten katalog. Contohnya, mengganti wilayah menjadi Eropa tetap memerlukan penggantian data Jepang pada database. Pemisahan ini disengaja agar konfigurasi identitas tidak berpura-pura menjadi CMS.

### Token visual

Token berada di `resources/css/app.css`. Gunakan variabel semantik dan jangan menambahkan warna heksadesimal baru langsung pada halaman.

- `--public-canvas`: latar dokumen.
- `--public-surface` dan `--public-surface-elevated`: permukaan konten.
- `--public-surface-muted`: bidang pendukung.
- `--public-hero`, `--public-navigation`, dan `--public-footer`: bidang solid untuk struktur halaman.
- `--public-ink` dan `--public-muted`: teks utama dan sekunder.
- `--public-accent`: aksi utama dan identitas merek.
- `--public-secondary`: aksi perjalanan atau katalog pendukung.
- `--public-border`, `--public-focus`, dan `--public-shadow`: batas, fokus keyboard, dan elevasi.

Token `auth-*` dan `admin-*` memetakan nilai dari token publik. Dengan demikian, tema terang dan gelap berubah sebagai satu sistem.

### Aturan komponen

- Tombol utama hanya untuk satu aksi terpenting dalam satu area.
- Tombol sekunder tidak boleh bersaing secara visual dengan tombol utama.
- Kartu menggunakan `ui-surface`; bidang pendukung menggunakan `ui-surface-muted`.
- Permukaan navigasi, hero, kartu, formulir, dan footer harus solid. Transparansi hanya dipakai untuk backdrop dialog atau menu, bukan sebagai warna konten.
- Judul menggunakan `ui-heading`; label bagian menggunakan `ui-eyebrow`.
- Tipografi menggunakan system font stack agar proporsional, cepat dirender, dan tidak bergantung pada unduhan font eksternal.
- Animasi dibatasi pada `opacity` dan `transform`, berdurasi singkat, serta mengikuti `prefers-reduced-motion`.
- Halaman tidak boleh membuat scroll vertikal kedua di dalam konten utama.
- Sidebar boleh digunakan untuk pengelompokan informasi, tetapi tidak boleh memutus alur dokumen pada mobile.

### Batas arsitektur saat ini

Beranda publik sudah menggunakan Vue/Inertia. Katalog, autentikasi pengguna, keranjang, pesanan, dan profil masih menggunakan Blade. Karena itu, dua implementasi markup shell masih ada selama migrasi berlangsung. Keduanya wajib memakai `Brand` dan token CSS yang sama; jangan menyalin fungsi tema atau nilai warna ke implementasi baru.

Migrasi halaman ke Inertia harus dilakukan per modul dan disertai contract test. Menghapus seluruh Blade sekaligus akan memperbesar risiko regresi checkout, autentikasi, dan lokalisasi tanpa memberi manfaat langsung kepada pengguna.

### Pemeriksaan wajib

```bash
composer test
./vendor/bin/phpstan analyse --memory-limit=1G
./vendor/bin/pint --test
npm run type-check
npm run lint
npm run build
git diff --check
```

Audit visual dilakukan pada mobile dan desktop, tema terang dan gelap, serta Bahasa Indonesia dan Inggris. Pastikan hanya dokumen utama yang menggulir dan console browser tidak memuat error atau hydration mismatch.

## English

### Purpose

The public, account, shop, and admin interfaces share one visual identity. Admin screens remain denser because they are task-oriented, but they use the same color, typography, spacing, status, and interaction system.

### Brand source of truth

Brand identity must not be hard-coded in Vue components, Blade layouts, or payment integrations. `APP_NAME`, `BRAND_MARK`, `BRAND_LEGAL_NAME`, `BRAND_REGION_ID`, and `BRAND_REGION_EN` are read through `App\Support\Brand`. Inertia receives the same values through the shared `app` prop.

A brand change does not migrate catalog records, images, pricing, policies, or destination data. Changing the configured region to Europe still requires replacing Japanese catalog data. This boundary keeps brand configuration small and predictable instead of turning it into an incomplete CMS.

### Visual rules

- Use semantic CSS variables from `resources/css/app.css`; do not add page-level hexadecimal colors.
- Use one primary action per visual area.
- Use `ui-surface`, `ui-surface-muted`, `ui-heading`, and `ui-eyebrow` for shared hierarchy.
- Keep navigation, hero, card, form, and footer surfaces opaque. Reserve transparency for modal or menu backdrops.
- Use the system font stack to keep typography proportional and avoid an external font request.
- Keep motion short, use opacity or transform, and respect `prefers-reduced-motion`.
- Do not introduce a second vertical scrollbar inside page content.
- Validate mobile and desktop, light and dark themes, Indonesian and English, keyboard focus, and browser console output.

### Current migration boundary

The public home page uses Vue/Inertia, while several catalog and account pages still use Blade. Both shells therefore remain temporarily, but they share the same brand helper and design tokens. Migrate one module at a time with contract tests instead of removing all Blade views in one risky change.
