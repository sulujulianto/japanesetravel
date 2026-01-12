<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../database/seeders/DemoData.php';

use Database\Seeders\DemoData;

const PASSWORD_HASH = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/atwOhvBeJbYy';

function slugify(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

    return trim($slug ?? '', '-');
}

function sqlValue(mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    $escaped = str_replace(['\\', "'"], ['\\\\', "''"], (string) $value);

    return "'" . $escaped . "'";
}

function formatMoney(float|int $value): string
{
    return number_format((float) $value, 2, '.', '');
}

function formatDate(DateTimeImmutable $date): string
{
    return $date->format('Y-m-d H:i:s');
}

function dateWithRandomTime(DateTimeImmutable $date): DateTimeImmutable
{
    return $date->setTime(mt_rand(7, 21), mt_rand(0, 59), 0);
}

function insertRows($handle, string $table, array $columns, array $rows): void
{
    if (empty($rows)) {
        return;
    }

    fwrite($handle, 'INSERT INTO `' . $table . '` (`' . implode('`, `', $columns) . "`) VALUES\n");
    $lines = [];
    foreach ($rows as $row) {
        $values = [];
        foreach ($columns as $column) {
            $values[] = sqlValue($row[$column] ?? null);
        }
        $lines[] = '(' . implode(', ', $values) . ')';
    }
    fwrite($handle, implode(",\n", $lines) . ";\n\n");
}

$baseNow = new DateTimeImmutable('2025-02-15 10:00:00', new DateTimeZone('UTC'));

$users = [];
$userId = 1;
mt_srand(2024);
$adminCreated = dateWithRandomTime($baseNow->sub(new DateInterval('P40D')));
$users[] = [
    'id' => $userId++,
    'username' => 'admin',
    'email' => 'admin@japantravel.com',
    'password' => PASSWORD_HASH,
    'avatar' => 'avatar1.png',
    'role' => 'admin',
    'last_seen' => null,
    'email_verified_at' => formatDate($adminCreated->add(new DateInterval('PT2H'))),
    'remember_token' => null,
    'created_at' => formatDate($adminCreated),
    'updated_at' => formatDate($adminCreated),
];

foreach (DemoData::users() as $user) {
    $createdAt = dateWithRandomTime($baseNow->sub(new DateInterval('P' . mt_rand(10, 120) . 'D')));
    $users[] = [
        'id' => $userId++,
        'username' => $user['username'],
        'email' => $user['email'],
        'password' => PASSWORD_HASH,
        'avatar' => 'avatar1.png',
        'role' => 'user',
        'last_seen' => null,
        'email_verified_at' => formatDate($createdAt->add(new DateInterval('PT1H'))),
        'remember_token' => null,
        'created_at' => formatDate($createdAt),
        'updated_at' => formatDate($createdAt),
    ];
}

$places = [];
mt_srand(2023);
foreach (DemoData::places() as $index => $place) {
    $createdAt = dateWithRandomTime($baseNow->sub(new DateInterval('P' . mt_rand(15, 180) . 'D')));
    $places[] = [
        'id' => $index + 1,
        'name' => json_encode(['id' => $place['name_id'], 'en' => $place['name_en']], JSON_UNESCAPED_SLASHES),
        'slug' => slugify($place['name_en']) . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
        'description' => json_encode(['id' => $place['description_id'], 'en' => $place['description_en']], JSON_UNESCAPED_SLASHES),
        'image' => null,
        'video_url' => null,
        'gallery' => null,
        'address' => $place['address'],
        'facilities' => $place['facilities'],
        'open_days' => $place['open_days'],
        'open_hours' => $place['open_hours'],
        'opening_hours' => null,
        'created_by' => 1,
        'created_at' => formatDate($createdAt),
        'updated_at' => formatDate($createdAt),
    ];
}

$souvenirs = [];
mt_srand(2022);
foreach (DemoData::souvenirs() as $index => $souvenir) {
    $createdAt = dateWithRandomTime($baseNow->sub(new DateInterval('P' . mt_rand(20, 150) . 'D')));
    $souvenirs[] = [
        'id' => $index + 1,
        'name' => json_encode(['id' => $souvenir['name_id'], 'en' => $souvenir['name_en']], JSON_UNESCAPED_SLASHES),
        'description' => json_encode(['id' => $souvenir['description_id'], 'en' => $souvenir['description_en']], JSON_UNESCAPED_SLASHES),
        'price' => formatMoney($souvenir['price']),
        'stock' => $souvenir['stock'],
        'image' => null,
        'created_at' => formatDate($createdAt),
        'updated_at' => formatDate($createdAt),
    ];
}

$reviews = [];
$reviewId = 1;
$reviewTemplates = DemoData::reviewTemplates();
$reviewers = array_values(array_filter($users, fn ($user) => $user['role'] === 'user'));
$reviewerCount = count($reviewers);
mt_srand(2026);
foreach ($places as $place) {
    $reviewCount = mt_rand(2, 5);
    for ($i = 0; $i < $reviewCount; $i++) {
        $reviewer = $reviewers[mt_rand(0, $reviewerCount - 1)];
        $template = $reviewTemplates[mt_rand(0, count($reviewTemplates) - 1)];
        $date = dateWithRandomTime($baseNow->sub(new DateInterval('P' . mt_rand(1, 120) . 'D')));

        $reviews[] = [
            'id' => $reviewId++,
            'place_id' => $place['id'],
            'user_id' => $reviewer['id'],
            'rating' => $template['rating'],
            'comment' => $template['comment'],
            'created_at' => formatDate($date),
            'updated_at' => formatDate($date),
        ];
    }
}

$orders = [];
$orderItems = [];
$payments = [];
$orderId = 1;
$orderItemId = 1;
$paymentId = 1;

$inventory = $souvenirs;
$orderStatuses = ['pending', 'processing', 'completed'];
$pendingPaymentStatuses = ['pending', 'expired', 'failed'];

mt_srand(2027);
for ($i = 0; $i < 18; $i++) {
    $user = $reviewers[mt_rand(0, $reviewerCount - 1)];
    $orderDate = dateWithRandomTime($baseNow->sub(new DateInterval('P' . mt_rand(1, 120) . 'D')));
    $status = $orderStatuses[mt_rand(0, count($orderStatuses) - 1)];

    $itemsCount = mt_rand(1, 3);
    $itemIndexes = [];
    while (count($itemIndexes) < $itemsCount) {
        $itemIndexes[mt_rand(0, count($inventory) - 1)] = true;
    }

    $total = 0;
    $itemsData = [];
    foreach (array_keys($itemIndexes) as $index) {
        $item = &$inventory[$index];
        $qty = mt_rand(1, 3);
        if ($item['stock'] < $qty) {
            continue;
        }
        $total += $item['price'] * $qty;
        $itemsData[] = [
            'souvenir_id' => $item['id'],
            'quantity' => $qty,
            'price' => $item['price'],
            'product_name' => json_decode($item['name'], true)['en'] ?? 'Souvenir',
            'product_price' => $item['price'],
            'product_image' => null,
        ];
        $item['stock'] -= $qty;
    }

    if (empty($itemsData)) {
        continue;
    }

    $order = [
        'id' => $orderId,
        'user_id' => $user['id'],
        'total_price' => formatMoney($total),
        'status' => $status,
        'note' => 'Pesanan demo untuk kebutuhan portofolio.',
        'admin_note' => null,
        'created_at' => formatDate($orderDate),
        'updated_at' => formatDate($orderDate),
    ];

    $provider = mt_rand(0, 1) === 0 ? 'midtrans' : 'paypal';
    $paymentStatus = $status === 'pending'
        ? $pendingPaymentStatuses[mt_rand(0, count($pendingPaymentStatuses) - 1)]
        : 'paid';

    if ($paymentStatus === 'paid' && $order['status'] === 'pending') {
        $order['status'] = 'processing';
    }

    $paidAt = $paymentStatus === 'paid'
        ? formatDate($orderDate->add(new DateInterval('PT' . mt_rand(1, 24) . 'H')))
        : null;

    $orders[] = $order;

    foreach ($itemsData as $itemData) {
        $orderItems[] = [
            'id' => $orderItemId++,
            'order_id' => $orderId,
            'souvenir_id' => $itemData['souvenir_id'],
            'quantity' => $itemData['quantity'],
            'price' => $itemData['price'],
            'product_name' => $itemData['product_name'],
            'product_price' => $itemData['product_price'],
            'product_image' => $itemData['product_image'],
            'created_at' => formatDate($orderDate),
            'updated_at' => formatDate($orderDate),
        ];
    }

    $payments[] = [
        'id' => $paymentId++,
        'order_id' => $orderId,
        'provider' => $provider,
        'provider_ref' => 'ORD-' . str_pad((string) $orderId, 3, '0', STR_PAD_LEFT) . '-DEMO',
        'status' => $paymentStatus,
        'amount' => formatMoney($total),
        'currency' => 'IDR',
        'payload_json' => json_encode(['seeded' => true], JSON_UNESCAPED_SLASHES),
        'paid_at' => $paidAt,
        'created_at' => formatDate($orderDate),
        'updated_at' => formatDate($orderDate),
    ];

    $orderId++;
}

$souvenirs = array_values($inventory);

$outputPath = __DIR__ . '/../japantravel/japantravel.sql';
$handle = fopen($outputPath, 'wb');

fwrite($handle, "-- JAPAN TRAVEL PORTFOLIO DEMO SQL\n");
fwrite($handle, "-- Generated by scripts/generate_demo_sql.php\n\n");
fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
fwrite($handle, "START TRANSACTION;\n");
fwrite($handle, "SET time_zone = \"+00:00\";\n\n");
fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
fwrite($handle, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n\n");

fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
fwrite($handle, "DROP TABLE IF EXISTS `payment_webhook_events`, `payments`, `order_items`, `orders`, `place_reviews`, `souvenirs`, `places`, `sessions`, `password_reset_tokens`, `cache_locks`, `cache`, `failed_jobs`, `job_batches`, `jobs`, `users`;\n");
fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n\n");

fwrite($handle, "CREATE TABLE `users` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `username` varchar(50) NOT NULL,\n");
fwrite($handle, "  `email` varchar(120) NOT NULL,\n");
fwrite($handle, "  `password` varchar(255) NOT NULL,\n");
fwrite($handle, "  `avatar` varchar(100) NOT NULL DEFAULT 'avatar1.png',\n");
fwrite($handle, "  `role` enum('user','admin') NOT NULL DEFAULT 'user',\n");
fwrite($handle, "  `last_seen` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `email_verified_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `remember_token` varchar(100) DEFAULT NULL,\n");
fwrite($handle, "  `created_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `updated_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  UNIQUE KEY `users_username_unique` (`username`),\n");
fwrite($handle, "  UNIQUE KEY `users_email_unique` (`email`)\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `password_reset_tokens` (\n");
fwrite($handle, "  `email` varchar(255) NOT NULL,\n");
fwrite($handle, "  `token` varchar(255) NOT NULL,\n");
fwrite($handle, "  `created_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`email`)\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `sessions` (\n");
fwrite($handle, "  `id` varchar(255) NOT NULL,\n");
fwrite($handle, "  `user_id` bigint unsigned DEFAULT NULL,\n");
fwrite($handle, "  `ip_address` varchar(45) DEFAULT NULL,\n");
fwrite($handle, "  `user_agent` text,\n");
fwrite($handle, "  `payload` longtext NOT NULL,\n");
fwrite($handle, "  `last_activity` int NOT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  KEY `sessions_user_id_index` (`user_id`),\n");
fwrite($handle, "  KEY `sessions_last_activity_index` (`last_activity`)\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `cache` (\n");
fwrite($handle, "  `key` varchar(255) NOT NULL,\n");
fwrite($handle, "  `value` mediumtext NOT NULL,\n");
fwrite($handle, "  `expiration` int NOT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`key`)\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `cache_locks` (\n");
fwrite($handle, "  `key` varchar(255) NOT NULL,\n");
fwrite($handle, "  `owner` varchar(255) NOT NULL,\n");
fwrite($handle, "  `expiration` int NOT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`key`)\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `jobs` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `queue` varchar(255) NOT NULL,\n");
fwrite($handle, "  `payload` longtext NOT NULL,\n");
fwrite($handle, "  `attempts` tinyint unsigned NOT NULL,\n");
fwrite($handle, "  `reserved_at` int unsigned DEFAULT NULL,\n");
fwrite($handle, "  `available_at` int unsigned NOT NULL,\n");
fwrite($handle, "  `created_at` int unsigned NOT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  KEY `jobs_queue_index` (`queue`)\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `job_batches` (\n");
fwrite($handle, "  `id` varchar(255) NOT NULL,\n");
fwrite($handle, "  `name` varchar(255) NOT NULL,\n");
fwrite($handle, "  `total_jobs` int NOT NULL,\n");
fwrite($handle, "  `pending_jobs` int NOT NULL,\n");
fwrite($handle, "  `failed_jobs` int NOT NULL,\n");
fwrite($handle, "  `failed_job_ids` longtext NOT NULL,\n");
fwrite($handle, "  `options` mediumtext DEFAULT NULL,\n");
fwrite($handle, "  `cancelled_at` int DEFAULT NULL,\n");
fwrite($handle, "  `created_at` int NOT NULL,\n");
fwrite($handle, "  `finished_at` int DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`)\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `failed_jobs` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `uuid` varchar(255) NOT NULL,\n");
fwrite($handle, "  `connection` text NOT NULL,\n");
fwrite($handle, "  `queue` text NOT NULL,\n");
fwrite($handle, "  `payload` longtext NOT NULL,\n");
fwrite($handle, "  `exception` longtext NOT NULL,\n");
fwrite($handle, "  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `places` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `name` json NOT NULL,\n");
fwrite($handle, "  `slug` varchar(180) NOT NULL,\n");
fwrite($handle, "  `description` json DEFAULT NULL,\n");
fwrite($handle, "  `image` varchar(255) DEFAULT NULL,\n");
fwrite($handle, "  `video_url` varchar(255) DEFAULT NULL,\n");
fwrite($handle, "  `gallery` text DEFAULT NULL,\n");
fwrite($handle, "  `address` varchar(255) DEFAULT NULL,\n");
fwrite($handle, "  `facilities` text DEFAULT NULL,\n");
fwrite($handle, "  `open_days` varchar(100) DEFAULT NULL,\n");
fwrite($handle, "  `open_hours` varchar(100) DEFAULT NULL,\n");
fwrite($handle, "  `opening_hours` json DEFAULT NULL,\n");
fwrite($handle, "  `created_by` bigint unsigned DEFAULT NULL,\n");
fwrite($handle, "  `created_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `updated_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  UNIQUE KEY `places_slug_unique` (`slug`),\n");
fwrite($handle, "  KEY `places_created_by_index` (`created_by`),\n");
fwrite($handle, "  CONSTRAINT `places_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `place_reviews` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `place_id` bigint unsigned NOT NULL,\n");
fwrite($handle, "  `user_id` bigint unsigned NOT NULL,\n");
fwrite($handle, "  `rating` tinyint unsigned NOT NULL,\n");
fwrite($handle, "  `comment` text DEFAULT NULL,\n");
fwrite($handle, "  `created_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `updated_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  KEY `place_reviews_place_id_index` (`place_id`),\n");
fwrite($handle, "  KEY `place_reviews_user_id_index` (`user_id`),\n");
fwrite($handle, "  CONSTRAINT `place_reviews_place_id_foreign` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE,\n");
fwrite($handle, "  CONSTRAINT `place_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `souvenirs` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `name` json NOT NULL,\n");
fwrite($handle, "  `description` json DEFAULT NULL,\n");
fwrite($handle, "  `price` decimal(12,2) NOT NULL,\n");
fwrite($handle, "  `stock` int NOT NULL DEFAULT 0,\n");
fwrite($handle, "  `image` varchar(255) DEFAULT NULL,\n");
fwrite($handle, "  `created_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `updated_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  KEY `souvenirs_stock_index` (`stock`)\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `orders` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `user_id` bigint unsigned NOT NULL,\n");
fwrite($handle, "  `total_price` decimal(12,2) NOT NULL,\n");
fwrite($handle, "  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',\n");
fwrite($handle, "  `note` text DEFAULT NULL,\n");
fwrite($handle, "  `admin_note` varchar(500) DEFAULT NULL,\n");
fwrite($handle, "  `created_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `updated_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  KEY `orders_user_id_index` (`user_id`),\n");
fwrite($handle, "  KEY `orders_created_at_index` (`created_at`),\n");
fwrite($handle, "  KEY `orders_status_index` (`status`),\n");
fwrite($handle, "  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `order_items` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `order_id` bigint unsigned NOT NULL,\n");
fwrite($handle, "  `souvenir_id` bigint unsigned DEFAULT NULL,\n");
fwrite($handle, "  `quantity` int NOT NULL,\n");
fwrite($handle, "  `price` decimal(12,2) NOT NULL,\n");
fwrite($handle, "  `product_name` varchar(255) NOT NULL DEFAULT '',\n");
fwrite($handle, "  `product_price` decimal(12,2) NOT NULL DEFAULT 0,\n");
fwrite($handle, "  `product_image` varchar(255) DEFAULT NULL,\n");
fwrite($handle, "  `created_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `updated_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  KEY `order_items_order_id_index` (`order_id`),\n");
fwrite($handle, "  KEY `order_items_souvenir_id_index` (`souvenir_id`),\n");
fwrite($handle, "  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,\n");
fwrite($handle, "  CONSTRAINT `order_items_souvenir_id_foreign` FOREIGN KEY (`souvenir_id`) REFERENCES `souvenirs` (`id`) ON DELETE SET NULL\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `payments` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `order_id` bigint unsigned NOT NULL,\n");
fwrite($handle, "  `provider` varchar(20) NOT NULL,\n");
fwrite($handle, "  `provider_ref` varchar(255) DEFAULT NULL,\n");
fwrite($handle, "  `status` varchar(20) NOT NULL DEFAULT 'pending',\n");
fwrite($handle, "  `amount` decimal(12,2) NOT NULL,\n");
fwrite($handle, "  `currency` varchar(10) NOT NULL,\n");
fwrite($handle, "  `payload_json` json DEFAULT NULL,\n");
fwrite($handle, "  `paid_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `created_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `updated_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  UNIQUE KEY `payments_provider_provider_ref_unique` (`provider`,`provider_ref`),\n");
fwrite($handle, "  KEY `payments_order_id_index` (`order_id`),\n");
fwrite($handle, "  KEY `payments_status_index` (`status`),\n");
fwrite($handle, "  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

fwrite($handle, "CREATE TABLE `payment_webhook_events` (\n");
fwrite($handle, "  `id` bigint unsigned NOT NULL AUTO_INCREMENT,\n");
fwrite($handle, "  `payment_id` bigint unsigned NOT NULL,\n");
fwrite($handle, "  `provider` varchar(20) NOT NULL,\n");
fwrite($handle, "  `event_id` varchar(120) NOT NULL,\n");
fwrite($handle, "  `status` varchar(20) NOT NULL,\n");
fwrite($handle, "  `payload_json` json DEFAULT NULL,\n");
fwrite($handle, "  `created_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  `updated_at` timestamp NULL DEFAULT NULL,\n");
fwrite($handle, "  PRIMARY KEY (`id`),\n");
fwrite($handle, "  UNIQUE KEY `payment_webhook_events_provider_event_id_unique` (`provider`,`event_id`),\n");
fwrite($handle, "  KEY `payment_webhook_events_payment_id_status_index` (`payment_id`,`status`),\n");
fwrite($handle, "  CONSTRAINT `payment_webhook_events_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE\n");
fwrite($handle, ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n");

insertRows($handle, 'users', [
    'id',
    'username',
    'email',
    'password',
    'avatar',
    'role',
    'last_seen',
    'email_verified_at',
    'remember_token',
    'created_at',
    'updated_at',
], $users);

insertRows($handle, 'places', [
    'id',
    'name',
    'slug',
    'description',
    'image',
    'video_url',
    'gallery',
    'address',
    'facilities',
    'open_days',
    'open_hours',
    'opening_hours',
    'created_by',
    'created_at',
    'updated_at',
], $places);

insertRows($handle, 'souvenirs', [
    'id',
    'name',
    'description',
    'price',
    'stock',
    'image',
    'created_at',
    'updated_at',
], $souvenirs);

insertRows($handle, 'place_reviews', [
    'id',
    'place_id',
    'user_id',
    'rating',
    'comment',
    'created_at',
    'updated_at',
], $reviews);

insertRows($handle, 'orders', [
    'id',
    'user_id',
    'total_price',
    'status',
    'note',
    'admin_note',
    'created_at',
    'updated_at',
], $orders);

insertRows($handle, 'order_items', [
    'id',
    'order_id',
    'souvenir_id',
    'quantity',
    'price',
    'product_name',
    'product_price',
    'product_image',
    'created_at',
    'updated_at',
], $orderItems);

insertRows($handle, 'payments', [
    'id',
    'order_id',
    'provider',
    'provider_ref',
    'status',
    'amount',
    'currency',
    'payload_json',
    'paid_at',
    'created_at',
    'updated_at',
], $payments);

fwrite($handle, "COMMIT;\n");

fclose($handle);
