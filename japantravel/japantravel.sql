-- DATABASE: JAPAN TRAVEL (CLEAN DEMO DATA)
-- Hanya admin yang tersisa, data lain rapi untuk demo. Password admin: 'password'.

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
-- ========================================================
INSERT INTO `users` (`id`, `username`, `email`, `password`, `avatar`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@japantravel.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/atwOhvBeJbYy', 'avatar1.png', 'admin', NOW(), NOW());

-- ========================================================
-- 3. ISI DATA WISATA (Places - Real Data)
-- Gambar (image) dikosongkan (NULL) agar Anda bisa upload sendiri nanti
-- ========================================================
INSERT INTO `places` (`id`, `name`, `slug`, `description`, `image`, `address`, `facilities`, `open_days`, `open_hours`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Tokyo Tower', 'tokyo-tower', 'Menara ikonik setinggi 333 meter di pusat kota Tokyo yang menawarkan pemandangan kota terbaik, terutama saat malam.', NULL, '4 Chome-2-8 Shibakoen, Minato City, Tokyo', 'Observatory Deck, Cafe, Souvenir Shop, Elevator, WiFi', 'Senin - Minggu', '09:00 - 23:00', 1, NOW(), NOW()),
(2, 'Fushimi Inari Taisha', 'fushimi-inari-taisha', 'Kuil Shinto dengan ribuan gerbang torii merah yang membentuk lorong ikonik menuju Gunung Inari.', NULL, '68 Fukakusa Yabunouchicho, Fushimi Ward, Kyoto', 'Hiking Trail, Rest Area, Vending Machine, Prayer Spot', 'Setiap Hari', '24 Jam', 1, NOW(), NOW()),
(3, 'Dotonbori Osaka', 'dotonbori-osaka', 'Pusat kuliner dan hiburan malam di Osaka, rumah bagi Glico Man dan jajanan kaki lima legendaris.', NULL, 'Dotonbori, Chuo Ward, Osaka', 'Street Food, Restaurants, Shopping, Photo Spots', 'Setiap Hari', '10:00 - 00:00', 1, NOW(), NOW()),
(4, 'Kinkaku-ji (Golden Pavilion)', 'kinkaku-ji', 'Kuil Zen berlapis emas di tepi kolam yang memantulkan keindahan paviliun.', NULL, '1 Kinkakujicho, Kita Ward, Kyoto', 'Zen Garden, Tea House, Souvenir Shop', 'Senin - Minggu', '09:00 - 17:00', 1, NOW(), NOW()),
(5, 'Arashiyama Bamboo Grove', 'arashiyama-bamboo', 'Hutan bambu alami di Kyoto dengan jalur jalan setapak yang tenang dan menyejukkan.', NULL, 'Arashiyama, Ukyo Ward, Kyoto', 'Walking Path, Rickshaw Ride, Temple Access', 'Setiap Hari', '24 Jam', 1, NOW(), NOW()),
(6, 'Senso-ji Temple', 'senso-ji-temple', 'Kuil tertua di Tokyo dengan lampion Kaminarimon dan jalan belanja Nakamise.', NULL, '2 Chome-3-1 Asakusa, Taito City, Tokyo', 'Shopping Street, Fortune Telling, Prayer Hall', 'Senin - Minggu', '06:00 - 17:00', 1, NOW(), NOW()),
(7, 'Shibuya Crossing', 'shibuya-crossing', 'Penyeberangan tersibuk di dunia dengan suasana neon dan landmark Hachiko.', NULL, 'Shibuya City, Tokyo', 'Shopping Malls, Cafes, Photo Spot', 'Setiap Hari', '24 Jam', 1, NOW(), NOW()),
(8, 'Mount Fuji (5th Station)', 'mount-fuji', 'Stasiun ke-5 Gunung Fuji, titik awal hiking dengan panorama pegunungan dan danau.', NULL, 'Fujinomiya, Shizuoka', 'Hiking Start Point, Restaurant, Post Office, Souvenir Shop', 'Musiman (Cek Cuaca)', '08:00 - 18:00', 1, NOW(), NOW());

-- ========================================================
-- 4. ISI DATA TOKO (Souvenirs - Real Products)
-- ========================================================
INSERT INTO `souvenirs` (`id`, `name`, `description`, `price`, `stock`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Tokyo Banana', 'Kue bolu lembut dengan isian krim custard pisang khas Tokyo.', 150000.00, 50, NULL, NOW(), NOW()),
(2, 'Matcha KitKat', 'KitKat rasa teh hijau Uji Matcha, manis sekaligus harum.', 85000.00, 120, NULL, NOW(), NOW()),
(3, 'Kimono Yukata', 'Kimono musim panas berbahan katun sejuk, nyaman untuk jalan-jalan.', 450000.00, 20, NULL, NOW(), NOW()),
(4, 'Gundam Model Kit (HG)', 'Model kit Gundam HG 1/144 dengan detail tajam, cocok untuk pemula.', 300000.00, 10, NULL, NOW(), NOW()),
(5, 'Omamori (Jimat Keberuntungan)', 'Jimat keberuntungan dari kuil, motif klasik Jepang.', 75000.00, 150, NULL, NOW(), NOW()),
(6, 'Japanese Sensu (Kipas Lipat)', 'Kipas lipat motif sakura dan Gunung Fuji, ringan dan elegan.', 120000.00, 40, NULL, NOW(), NOW()),
(7, 'Daruma Doll (Merah)', 'Boneka Daruma simbol tekad dan keberuntungan, warna merah klasik.', 180000.00, 25, NULL, NOW(), NOW()),
(8, 'Maneki Neko (Kucing Hoki)', 'Patung kucing keramik tangan melambai, populer untuk menarik rezeki.', 250000.00, 18, NULL, NOW(), NOW());

-- ========================================================
-- 5. ISI ULASAN PENGUNJUNG (Reviews)
-- Agar halaman detail tidak sepi
-- ========================================================
INSERT INTO `place_reviews` (`id`, `place_id`, `user_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5, 'Pemandangan malam dari dek observasi sangat memukau.', NOW(), NOW()),
(2, 2, 1, 5, 'Lorong torii merahnya terasa tenang dan fotogenik.', NOW(), NOW()),
(3, 3, 1, 4, 'Kuliner di Dotonbori mantap, ramai tapi seru.', NOW(), NOW()),
(4, 4, 1, 5, 'Kuil berlapis emasnya cantik sekali saat cuaca cerah.', NOW(), NOW());

-- ========================================================
-- 6. ISI DATA TRANSAKSI (Orders)
-- Agar Dashboard Admin ada grafik penjualannya
-- ========================================================
INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 480000.00, 'completed', 'Contoh pesanan selesai.', '2024-11-01 10:00:00', '2024-11-01 12:00:00'),
(2, 1, 690000.00, 'pending', 'Menunggu pembayaran.', '2024-11-15 09:30:00', '2024-11-15 09:30:00');

INSERT INTO `order_items` (`id`, `order_id`, `souvenir_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
-- Order 1: 2x Tokyo Banana + 1x Daruma
(1, 1, 1, 2, 150000.00, '2024-11-01 10:00:00', '2024-11-01 10:00:00'),
(2, 1, 7, 1, 180000.00, '2024-11-01 10:00:00', '2024-11-01 10:00:00'),
-- Order 2: 1x Yukata + 2x Sensu
(3, 2, 3, 1, 450000.00, '2024-11-15 09:30:00', '2024-11-15 09:30:00'),
(4, 2, 6, 2, 120000.00, '2024-11-15 09:30:00', '2024-11-15 09:30:00');

COMMIT;
