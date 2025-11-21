# Japan Travel

Portal wisata Jepang dengan katalog destinasi, toko oleh-oleh, ulasan pengunjung, serta dashboard admin. Mendukung tema terang/gelap dan pilihan bahasa Indonesia/Inggris.

![Tampilan Japan Travel](japantravel/japanese-travel.jpg)

## Fitur Utama
- Destinasi wisata: landing page, detail dengan fasilitas, peta, dan ulasan.
- Toko oleh-oleh: listing produk, stok, keranjang, dan checkout (order + item).
- Ulasan pengunjung: rating 1–5 dan komentar untuk setiap destinasi (login).
- Dashboard & peran: pengguna biasa melihat riwayat belanja; admin kelola destinasi (places) dan souvenir, serta memantau pesanan/stock rendah.
- Dukungan bahasa ganda: toggle ID/EN di seluruh halaman publik/auth.
- Tema terang/gelap: tombol 🌗, preferensi tersimpan di localStorage.
- Autentikasi lengkap: register, login, reset password, verifikasi email, profil.

## Teknologi
- Laravel 12 (PHP >= 8.2), Breeze, Tailwind CSS, Alpine.js, Vite.
- MariaDB/MySQL.

## Persiapan & Instalasi
```bash
git clone <repo-url>
cd japanesetravel
composer install
npm install
cp .env.example .env  # atau sesuaikan .env yang sudah ada
php artisan key:generate
```

Konfigurasikan `.env` untuk database, contoh:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=japantravel
DB_USERNAME=root
DB_PASSWORD=ppkpi
```

## Database
Jalankan migrasi:
```bash
php artisan migrate
```

Opsional – impor data demo (admin, 8 destinasi, 8 souvenir, ulasan, pesanan):
```bash
mysql -u root -p japantravel < japantravel/japantravel.sql
```
Kredensial admin demo:  
`admin@japantravel.com` / `password`

## Menjalankan Aplikasi
Mode pengembangan (watcher Vite):
```bash
php artisan serve
npm run dev
```

Build produksi:
```bash
npm run build
php artisan serve
```

## Pengujian
```bash
php artisan test
```

## Catatan Penggunaan
- Toggle bahasa ID/EN ada di navbar halaman publik dan auth.
- Toggle tema 🌗 tersedia di navbar publik, detail destinasi, toko, keranjang, dan halaman auth; preferensi disimpan otomatis.
- Upload gambar destinasi/souvenir saat input admin; field `image` dibiarkan kosong di data demo.

## Lisensi
MIT (mengikuti lisensi bawaan Laravel).
