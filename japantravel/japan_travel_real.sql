-- DATABASE: JAPAN TRAVEL (REAL DATA VERSION)
-- Dibuat khusus agar Dashboard Admin terlihat hidup dan profesional.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ========================================================
-- 1. BERSIHKAN DATA LAMA (Reset agar bersih)
-- ========================================================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE place_reviews;
TRUNCATE TABLE souvenirs;
TRUNCATE TABLE places;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- 2. ISI DATA PENGGUNA (Users)
-- Password untuk SEMUA akun: 'password'
-- ========================================================
INSERT INTO `users` (`id`, `username`, `email`, `password`, `avatar`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@japantravel.com', '$2y$12$K7iS.p./2.a3.b4.c5.d6.e7.f8.g9.h0.i1.j2.k3.l4.m5.n6', 'avatar1.png', 'admin', NOW(), NOW()),
(2, 'yukiko_tanaka', 'yukiko@gmail.com', '$2y$12$K7iS.p./2.a3.b4.c5.d6.e7.f8.g9.h0.i1.j2.k3.l4.m5.n6', 'avatar2.png', 'user', NOW(), NOW()),
(3, 'budi_traveler', 'budi@yahoo.com', '$2y$12$K7iS.p./2.a3.b4.c5.d6.e7.f8.g9.h0.i1.j2.k3.l4.m5.n6', 'avatar3.png', 'user', NOW(), NOW()),
(4, 'john_doe_usa', 'john@example.com', '$2y$12$K7iS.p./2.a3.b4.c5.d6.e7.f8.g9.h0.i1.j2.k3.l4.m5.n6', 'avatar4.png', 'user', NOW(), NOW()),
(5, 'siti_nurhaliza', 'siti@mail.com', '$2y$12$K7iS.p./2.a3.b4.c5.d6.e7.f8.g9.h0.i1.j2.k3.l4.m5.n6', 'avatar5.png', 'user', NOW(), NOW());

-- ========================================================
-- 3. ISI DATA WISATA (Places - Real Data)
-- Gambar (image) dikosongkan (NULL) agar Anda bisa upload sendiri nanti
-- ========================================================
INSERT INTO `places` (`id`, `name`, `slug`, `description`, `image`, `address`, `facilities`, `open_days`, `open_hours`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Tokyo Tower', 'tokyo-tower', 'Menara ikonik setinggi 333 meter di pusat kota Tokyo. Terinspirasi dari Menara Eiffel, landmark ini menawarkan pemandangan kota yang menakjubkan dari dek observasinya, terutama saat malam hari ketika lampu kota menyala.', NULL, '4 Chome-2-8 Shibakoen, Minato City, Tokyo', 'Observatory Deck, Cafe, Souvenir Shop, Elevator, WiFi', 'Senin - Minggu', '09:00 - 23:00', 1, NOW(), NOW()),
(2, 'Fushimi Inari Taisha', 'fushimi-inari-taisha', 'Kuil Shinto yang terkenal dengan ribuan gerbang torii berwarna merah cerah yang berjejer di jalan setapak menuju Gunung Inari. Salah satu lokasi paling fotogenik dan spiritual di Kyoto. Datanglah pagi-pagi sekali untuk menghindari keramaian.', NULL, '68 Fukakusa Yabunouchicho, Fushimi Ward, Kyoto', 'Hiking Trail, Rest Area, Vending Machine, Prayer Spot', 'Setiap Hari', '24 Jam', 1, NOW(), NOW()),
(3, 'Dotonbori Osaka', 'dotonbori-osaka', 'Pusat kuliner dan hiburan malam di Osaka. Terkenal dengan papan reklame Glico Man yang menyala dan deretan jajanan kaki lima seperti Takoyaki dan Okonomiyaki yang lezat. Surga bagi pecinta makanan.', NULL, 'Dotonbori, Chuo Ward, Osaka', 'Street Food, Restaurants, Shopping, Photo Spots', 'Setiap Hari', '10:00 - 00:00', 1, NOW(), NOW()),
(4, 'Kinkaku-ji (Golden Pavilion)', 'kinkaku-ji', 'Kuil Zen di Kyoto yang lantai atasnya dilapisi lembaran emas murni. Terletak di tepi kolam yang indah, pantulan emas kuil di air menciptakan pemandangan yang sangat memukau.', NULL, '1 Kinkakujicho, Kita Ward, Kyoto', 'Zen Garden, Tea House, Souvenir Shop', 'Senin - Minggu', '09:00 - 17:00', 1, NOW(), NOW()),
(5, 'Arashiyama Bamboo Grove', 'arashiyama-bamboo', 'Hutan bambu alami yang menakjubkan di Kyoto. Berjalan di antara batang bambu yang menjulang tinggi sambil mendengarkan suara gesekan daun tertiup angin memberikan ketenangan luar biasa.', NULL, 'Arashiyama, Ukyo Ward, Kyoto', 'Walking Path, Rickshaw Ride, Temple Access', 'Setiap Hari', '24 Jam', 1, NOW(), NOW()),
(6, 'Senso-ji Temple', 'senso-ji-temple', 'Kuil tertua di Tokyo yang terletak di Asakusa. Terkenal dengan lampion merah raksasa di gerbang Kaminarimon dan jalan belanja Nakamise yang menjual berbagai oleh-oleh tradisional.', NULL, '2 Chome-3-1 Asakusa, Taito City, Tokyo', 'Shopping Street, Fortune Telling (Omikuji), Prayer Hall', 'Senin - Minggu', '06:00 - 17:00', 1, NOW(), NOW()),
(7, 'Shibuya Crossing', 'shibuya-crossing', 'Penyeberangan jalan tersibuk di dunia. Rasakan sensasi menyeberang bersama ribuan orang sekaligus di tengah cahaya neon raksasa Shibuya. Lokasi syuting berbagai film terkenal.', NULL, 'Shibuya City, Tokyo', 'Shopping Malls, Cafes, Photo Spot (Hachiko Statue)', 'Setiap Hari', '24 Jam', 1, NOW(), NOW()),
(8, 'Mount Fuji (5th Station)', 'mount-fuji', 'Gunung tertinggi dan paling suci di Jepang. Stasiun ke-5 adalah titik tertinggi yang bisa dicapai dengan bus/mobil, menawarkan pemandangan puncak gunung dan danau di bawahnya.', NULL, 'Fujinomiya, Shizuoka', 'Hiking Start Point, Restaurant, Post Office, Souvenir Shop', 'Musiman (Cek Cuaca)', '08:00 - 18:00', 1, NOW(), NOW()),
(9, 'Universal Studios Japan', 'usj-osaka', 'Taman hiburan kelas dunia di Osaka. Rumah bagi Super Nintendo World dan The Wizarding World of Harry Potter. Wajib dikunjungi bagi penggemar film dan anime.', NULL, '2 Chome-1-33 Sakurajima, Konohana Ward, Osaka', 'Theme Park Rides, Restaurants, Merchandise Shops, Lockers', 'Setiap Hari', '09:00 - 21:00', 1, NOW(), NOW()),
(10, 'Akihabara Electric Town', 'akihabara', 'Pusat budaya Otaku dan elektronik di Tokyo. Temukan toko anime, manga, figure, maid cafe, dan komponen komputer terbaru di sini.', NULL, 'Akihabara, Taito City, Tokyo', 'Electronics, Anime Shops, Maid Cafes, Game Centers', 'Setiap Hari', '10:00 - 22:00', 1, NOW(), NOW());

-- ========================================================
-- 4. ISI DATA TOKO (Souvenirs - Real Products)
-- ========================================================
INSERT INTO `souvenirs` (`id`, `name`, `description`, `price`, `stock`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Tokyo Banana', 'Kue bolu lembut berbentuk pisang dengan isian krim custard pisang yang manis. Oleh-oleh wajib dari Tokyo.', 150000.00, 50, NULL, NOW(), NOW()),
(2, 'Matcha KitKat', 'Cokelat KitKat dengan rasa teh hijau Jepang asli (Uji Matcha). Perpaduan manis dan pahit yang pas.', 85000.00, 100, NULL, NOW(), NOW()),
(3, 'Kimono Yukata', 'Pakaian tradisional Jepang musim panas yang ringan dan nyaman. Bahan katun sejuk, cocok untuk dipakai santai.', 450000.00, 20, NULL, NOW(), NOW()),
(4, 'Gundam Model Kit (HG)', 'Mainan rakitan robot Gundam skala 1/144 (High Grade). Detail bagus dan mudah dirakit pemula.', 300000.00, 15, NULL, NOW(), NOW()),
(5, 'Omamori (Jimat Keberuntungan)', 'Jimat tradisional dari kuil Jepang untuk keberuntungan, kesehatan, atau kesuksesan studi. Gantungan indah.', 75000.00, 200, NULL, NOW(), NOW()),
(6, 'Japanese Sensu (Kipas Lipat)', 'Kipas lipat tradisional Jepang dengan motif bunga Sakura atau pemandangan Gunung Fuji. Elegan dan praktis.', 120000.00, 40, NULL, NOW(), NOW()),
(7, 'Daruma Doll (Merah)', 'Boneka tradisional Jepang lambang ketekunan dan keberuntungan. Warnai satu mata saat menetapkan tujuan!', 180000.00, 30, NULL, NOW(), NOW()),
(8, 'Maneki Neko (Kucing Hoki)', 'Patung kucing keramik dengan tangan melambai untuk memanggil rezeki dan pelanggan. Cocok untuk pajangan.', 250000.00, 25, NULL, NOW(), NOW()),
(9, 'Ichiran Ramen (Instant)', 'Ramen instan premium dari kedai Ichiran yang legendaris. Bawa pulang rasa asli Tonkotsu Ramen ke rumah.', 200000.00, 60, NULL, NOW(), NOW()),
(10, 'Sake Premium (Botol Kecil)', 'Minuman beras fermentasi khas Jepang. Botol kecil 300ml, cocok untuk oleh-oleh atau pajangan.', 350000.00, 10, NULL, NOW(), NOW());

-- ========================================================
-- 5. ISI ULASAN PENGUNJUNG (Reviews)
-- Agar halaman detail tidak sepi
-- ========================================================
INSERT INTO `place_reviews` (`id`, `place_id`, `user_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 5, 'Pemandangannya sangat indah, apalagi saat malam hari! Lampu-lampu kotanya cantik sekali.', NOW(), NOW()),
(2, 1, 3, 4, 'Antriannya agak panjang saat akhir pekan, tapi worth it banget.', NOW(), NOW()),
(3, 2, 4, 5, 'Sangat spiritual dan menenangkan. Pastikan naik sampai ke puncak gunung, pemandangannya juara!', NOW(), NOW()),
(4, 3, 2, 5, 'Surganya makanan! Takoyaki di sini rasanya beda banget sama yang di Jakarta. Wajib coba!', NOW(), NOW()),
(5, 3, 5, 4, 'Sangat ramai dan berisik, tapi energinya luar biasa. Glico Man sign ikonik banget.', NOW(), NOW()),
(6, 6, 3, 5, 'Kuilnya megah sekali. Jangan lupa coba ramalan (Omikuji) di sana.', NOW(), NOW()),
(7, 9, 4, 5, 'Super Nintendo World bikin nostalgia parah! Harry Potter ride juga keren banget.', NOW(), NOW()),
(8, 10, 5, 5, 'Surga buat wibu seperti saya. Banyak figure murah dan langka.', NOW(), NOW());

-- ========================================================
-- 6. ISI DATA TRANSAKSI (Orders)
-- Agar Dashboard Admin ada grafik penjualannya
-- ========================================================
-- Order 1: Selesai (Paid)
INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 300000.00, 'completed', 'Pesanan oleh-oleh buat keluarga.', '2024-10-01 10:00:00', '2024-10-02 12:00:00'),
(2, 3, 535000.00, 'shipped', 'Tolong packing yang aman ya gan.', '2024-10-05 14:30:00', '2024-10-06 09:00:00'),
(3, 4, 1500000.00, 'paid', 'Pembayaran via Transfer Bank.', '2024-11-19 08:15:00', '2024-11-19 08:20:00'),
(4, 2, 85000.00, 'pending', 'Menunggu gajian.', NOW(), NOW());

-- Rincian Barang untuk Order di atas
INSERT INTO `order_items` (`id`, `order_id`, `souvenir_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
-- Order 1: 2x Tokyo Banana
(1, 1, 1, 2, 150000.00, '2024-10-01 10:00:00', '2024-10-01 10:00:00'),
-- Order 2: 1x Kimono + 1x Matcha KitKat
(2, 2, 3, 1, 450000.00, '2024-10-05 14:30:00', '2024-10-05 14:30:00'),
(3, 2, 2, 1, 85000.00, '2024-10-05 14:30:00', '2024-10-05 14:30:00'),
-- Order 3: 5x Gundam
(4, 3, 4, 5, 300000.00, '2024-11-19 08:15:00', '2024-11-19 08:15:00'),
-- Order 4: 1x Matcha Kitkat
(5, 4, 2, 1, 85000.00, NOW(), NOW());

COMMIT;