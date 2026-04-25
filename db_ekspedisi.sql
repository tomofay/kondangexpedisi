-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2026 at 05:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ekspedisi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_tasks`
--

CREATE TABLE `admin_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `action_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`action_data`)),
  `result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`result`)),
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_tasks`
--

INSERT INTO `admin_tasks` (`id`, `task_type`, `title`, `description`, `assigned_to`, `created_by`, `status`, `priority`, `action_data`, `result`, `started_at`, `completed_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'approve_rate_card', 'Approve Rate Card Update - Zone JABODETABEK', 'Perubahan tarif regular service untuk zona Jabodetabek.', 21, 21, 'pending', 'high', '{\"rate_card_id\":1,\"approval_id\":1}', NULL, NULL, NULL, NULL, '2026-04-11 23:59:42', '2026-04-11 23:59:42'),
(2, 'reassign_shipment', 'Reassign Stuck Shipment KND-20260412-001', 'Reasign pengiriman terhambat ke kurir alternatif.', 21, 21, 'pending', 'high', '{\"shipment_id\":1}', NULL, NULL, NULL, NULL, '2026-04-11 23:59:42', '2026-04-11 23:59:42'),
(3, 'follow_up_payment', 'Follow-up Pembayaran Pending > 24 jam', 'Hubungi pelanggan untuk konfirmasi pembayaran.', NULL, 21, 'pending', 'medium', '{\"payment_count\":5}', NULL, NULL, NULL, NULL, '2026-04-11 23:59:42', '2026-04-11 23:59:42'),
(4, 'approve_rate_card', 'Approve Rate Card Update - Zone JABODETABEK', 'Perubahan tarif regular service untuk zona Jabodetabek.', 21, 21, 'pending', 'high', '{\"rate_card_id\":1,\"approval_id\":1}', NULL, NULL, NULL, NULL, '2026-04-12 00:08:57', '2026-04-12 00:08:57'),
(5, 'reassign_shipment', 'Reassign Stuck Shipment KND-20260412-001', 'Reasign pengiriman terhambat ke kurir alternatif.', 21, 21, 'pending', 'high', '{\"shipment_id\":1}', NULL, NULL, NULL, NULL, '2026-04-12 00:08:57', '2026-04-12 00:08:57'),
(6, 'follow_up_payment', 'Follow-up Pembayaran Pending > 24 jam', 'Hubungi pelanggan untuk konfirmasi pembayaran.', NULL, 21, 'pending', 'medium', '{\"payment_count\":5}', NULL, NULL, NULL, NULL, '2026-04-12 00:08:57', '2026-04-12 00:08:57'),
(7, 'approve_rate_card', 'Approve Rate Card Update - Zone JABODETABEK', 'Perubahan tarif regular service untuk zona Jabodetabek.', 21, 21, 'pending', 'high', '{\"rate_card_id\":1,\"approval_id\":1}', NULL, NULL, NULL, NULL, '2026-04-12 00:29:24', '2026-04-12 00:29:24'),
(8, 'reassign_shipment', 'Reassign Stuck Shipment KND-20260412-001', 'Reasign pengiriman terhambat ke kurir alternatif.', 21, 21, 'pending', 'high', '{\"shipment_id\":1}', NULL, NULL, NULL, NULL, '2026-04-12 00:29:24', '2026-04-12 00:29:24'),
(9, 'follow_up_payment', 'Follow-up Pembayaran Pending > 24 jam', 'Hubungi pelanggan untuk konfirmasi pembayaran.', NULL, 21, 'pending', 'medium', '{\"payment_count\":5}', NULL, NULL, NULL, NULL, '2026-04-12 00:29:24', '2026-04-12 00:29:24'),
(10, 'approve_rate_card', 'Approve Rate Card Update - Zone JABODETABEK', 'Perubahan tarif regular service untuk zona Jabodetabek.', 21, 21, 'pending', 'high', '{\"rate_card_id\":1,\"approval_id\":1}', NULL, NULL, NULL, NULL, '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(11, 'reassign_shipment', 'Reassign Stuck Shipment KND-20260412-001', 'Reasign pengiriman terhambat ke kurir alternatif.', 21, 21, 'pending', 'high', '{\"shipment_id\":1}', NULL, NULL, NULL, NULL, '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(12, 'follow_up_payment', 'Follow-up Pembayaran Pending > 24 jam', 'Hubungi pelanggan untuk konfirmasi pembayaran.', NULL, 21, 'pending', 'medium', '{\"payment_count\":5}', NULL, NULL, NULL, NULL, '2026-04-12 05:14:45', '2026-04-12 05:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `subject_type` varchar(255) NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `before_state` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_state`)),
  `after_state` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_state`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `action`, `subject_type`, `subject_id`, `actor_id`, `before_state`, `after_state`, `notes`, `created_at`, `updated_at`) VALUES
(2, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 06:59:42', '2026-04-11 15:59:42'),
(3, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 17:59:42', '2026-04-11 20:59:42'),
(4, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 07:59:42', '2026-04-11 10:59:42'),
(5, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 09:59:42', '2026-04-11 07:59:42'),
(6, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-0\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 21:59:42', '2026-04-11 12:59:42'),
(7, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-1\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 16:59:42', '2026-04-11 20:59:42'),
(8, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-2\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 12:59:42', '2026-04-11 03:59:42'),
(9, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 15:08:57', '2026-04-11 07:08:57'),
(10, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 10:08:57', '2026-04-11 10:08:57'),
(11, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 18:08:57', '2026-04-11 15:08:57'),
(12, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 14:08:57', '2026-04-11 15:08:57'),
(13, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-0\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 23:08:57', '2026-04-11 23:08:57'),
(14, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-1\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 06:08:57', '2026-04-11 20:08:57'),
(15, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-2\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 06:08:57', '2026-04-11 18:08:57'),
(16, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 20:29:24', '2026-04-11 07:29:24'),
(17, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 16:29:24', '2026-04-11 22:29:24'),
(18, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 23:29:24', '2026-04-11 21:29:24'),
(19, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 11:29:24', '2026-04-11 06:29:24'),
(20, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-0\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 12:29:24', '2026-04-11 11:29:24'),
(21, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-1\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 08:29:24', '2026-04-11 04:29:24'),
(22, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-2\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 23:29:24', '2026-04-11 19:29:24'),
(23, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 12:14:45', '2026-04-11 17:14:45'),
(24, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-12 03:14:45', '2026-04-12 01:14:45'),
(25, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 21:14:45', '2026-04-12 03:14:45'),
(26, 'auth.login_failed', 'App\\Models\\User', 21, NULL, NULL, '{\"ip\":\"127.0.0.1\",\"source\":\"seeder\"}', 'Seeded login failure sample.', '2026-04-11 11:14:45', '2026-04-11 19:14:45'),
(27, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-0\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-12 04:14:45', '2026-04-12 02:14:45'),
(28, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-1\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-12 03:14:45', '2026-04-11 20:14:45'),
(29, 'payment.midtrans_callback_failed', 'App\\Models\\Payment', 0, NULL, NULL, '{\"payload\":{\"order_id\":\"SEEDER-FAIL-2\"}}', 'Simulasi callback Midtrans gagal dari seeder.', '2026-04-11 19:14:45', '2026-04-11 17:14:45'),
(30, 'shipment.update', 'App\\Models\\Shipment', 17, 26, '{\"courier_id\":23,\"vehicle_id\":4,\"status_id\":1,\"payment_status\":\"pending\",\"estimated_delivery_at\":\"2026-04-15T12:14:45.000000Z\",\"delivered_at\":null,\"notes\":\"Data pengiriman contoh untuk simulasi operasional Indonesia.\"}', '{\"courier_id\":null,\"vehicle_id\":4,\"status_id\":1,\"payment_status\":\"paid\",\"estimated_delivery_at\":\"2026-04-15T19:14:00.000000Z\",\"delivered_at\":null,\"notes\":\"Data pengiriman contoh untuk simulasi operasional Indonesia.\"}', 'Shipment diperbarui secara manual.', '2026-04-12 05:44:39', '2026-04-12 05:44:39'),
(31, 'shipment.create', 'App\\Models\\Shipment', 21, 26, '[]', '{\"tracking_number\":\"SXP-JKT-HQ-20260412-9KPQK\",\"branch_id\":1,\"status_id\":1,\"payment_status\":\"unpaid\",\"total_amount\":\"28750.00\"}', 'Shipment dibuat.', '2026-04-12 05:52:40', '2026-04-12 05:52:40'),
(32, 'payment.create', 'App\\Models\\Payment', 21, 26, '[]', '{\"shipment_id\":21,\"customer_id\":null,\"method\":\"cash\",\"status\":\"pending\",\"amount\":\"136000.00\"}', 'Payment baru dibuat.', '2026-04-12 05:54:02', '2026-04-12 05:54:02'),
(33, 'payment.update', 'App\\Models\\Payment', 21, 26, '{\"status\":\"pending\",\"method\":\"cash\",\"notes\":null}', '{\"status\":\"settlement\",\"method\":\"cash\",\"notes\":null}', 'Payment diperbarui.', '2026-04-12 05:54:09', '2026-04-12 05:54:09'),
(34, 'payment.update', 'App\\Models\\Payment', 17, 22, '{\"status\":\"pending\",\"method\":\"midtrans\",\"notes\":\"Pembayaran contoh untuk kebutuhan demo aplikasi.\"}', '{\"status\":\"settlement\",\"method\":\"midtrans\",\"notes\":\"Pembayaran contoh untuk kebutuhan demo aplikasi.\"}', 'Payment diperbarui.', '2026-04-12 05:57:20', '2026-04-12 05:57:20'),
(35, 'payment.auto_create', 'App\\Models\\Payment', 22, 26, '[]', '{\"shipment_id\":22,\"customer_id\":null,\"method\":\"midtrans\",\"status\":\"pending\",\"amount\":\"150592.00\"}', 'Payment pending otomatis dibuat dari shipment baru.', '2026-04-12 06:18:02', '2026-04-12 06:18:02'),
(36, 'shipment.create', 'App\\Models\\Shipment', 22, 26, '[]', '{\"tracking_number\":\"SXP-JKT-HQ-20260412-II7OR\",\"branch_id\":1,\"status_id\":1,\"payment_status\":\"pending\",\"total_amount\":\"150592.00\"}', 'Shipment dibuat.', '2026-04-12 06:18:02', '2026-04-12 06:18:02'),
(37, 'payment.update', 'App\\Models\\Payment', 22, 26, '{\"status\":\"pending\",\"method\":\"midtrans\",\"notes\":\"Payment otomatis dibuat saat shipment dibuat berdasarkan perhitungan rate card.\"}', '{\"status\":\"settlement\",\"method\":\"midtrans\",\"notes\":\"Payment otomatis dibuat saat shipment dibuat berdasarkan perhitungan rate card.\"}', 'Payment diperbarui.', '2026-04-12 06:18:17', '2026-04-12 06:18:17');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `code`, `name`, `city`, `phone`, `email`, `address`, `zone_id`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'JKT-HQ', 'Cabang Pusat Jakarta', 'Jakarta', '021-555-1000', 'jakarta@kondangekspedisi.test', 'Jl. Medan Merdeka Selatan No. 10, Jakarta Pusat', 1, 1, '2026-04-11 23:59:37', '2026-04-12 00:29:19', NULL),
(2, 'SBY-01', 'Cabang Surabaya', 'Surabaya', '031-555-2000', 'surabaya@kondangekspedisi.test', 'Jl. Basuki Rahmat No. 78, Surabaya', 2, 1, '2026-04-11 23:59:37', '2026-04-12 00:29:19', NULL),
(3, 'BDG-01', 'Cabang Bandung', 'Bandung', '022-555-3000', 'bandung@kondangekspedisi.test', 'Jl. Asia Afrika No. 30, Bandung', 2, 1, '2026-04-11 23:59:37', '2026-04-12 00:29:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branch_balances`
--

CREATE TABLE `branch_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `balance_date` date NOT NULL,
  `opening_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cash_in` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cash_out` decimal(14,2) NOT NULL DEFAULT 0.00,
  `closing_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courier_earnings`
--

CREATE TABLE `courier_earnings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `courier_id` bigint(20) UNSIGNED NOT NULL,
  `shipment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `earning_date` date NOT NULL,
  `base_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `bonus` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `name`, `email`, `email_verified_at`, `phone`, `password`, `address`, `city`, `photo`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 27, 'Pelanggan Demo', 'pelanggan.demo@kondangekspedisi.test', '2026-04-12 05:14:41', '081220000111', '$2y$12$tuur0giIMEq9i0.SsYV18Otbxt2aPQ9wHOZmstgeS/dv9vnTMauFq', 'Jl. Melati No. 5, Cempaka Putih, Jakarta Pusat', 'Jakarta', NULL, NULL, '2026-04-11 23:59:39', '2026-04-12 05:14:42'),
(2, NULL, 'Andi Pratama', 'andi.pratama@contoh.id', '2026-04-12 05:14:42', '081220000112', '$2y$12$YbV.p0LnYI9fNNmTJRYXwucX4o1W8yWzmA42Mm19hk.mLGiifG1c.', 'Jl. Kencana No. 8, Kebayoran Baru, Jakarta Selatan', 'Jakarta', NULL, NULL, '2026-04-11 23:59:39', '2026-04-12 05:14:42'),
(3, NULL, 'Siti Rahmawati', 'siti.rahmawati@contoh.id', '2026-04-12 05:14:42', '081220000113', '$2y$12$xObZZjQsNLwdaQFJ978KhehpmZbzHIagKqAMbEa8wwip7OufnUAn.', 'Jl. Mawar Merah No. 12, Cimahi Tengah', 'Bandung', NULL, NULL, '2026-04-11 23:59:39', '2026-04-12 05:14:42'),
(4, NULL, 'Budi Santoso', 'budi.santoso@contoh.id', '2026-04-12 05:14:42', '081220000114', '$2y$12$rED9Cf6x0SZ7f4XJ/s9tye3vC4EkuEsGFkUz4Un8wNocNm70bk8e2', 'Jl. Ikan Dorang No. 44, Gubeng', 'Surabaya', NULL, NULL, '2026-04-11 23:59:40', '2026-04-12 05:14:42'),
(5, NULL, 'Dewi Lestari', 'dewi.lestari@contoh.id', '2026-04-12 05:14:42', '081220000115', '$2y$12$LqHtA6OXa2I0MuS4HuKLm.JwSSIVAkhj9b6Ux/DeRcWjT/2TC056y', 'Jl. Sriwijaya No. 17, Denpasar Barat', 'Denpasar', NULL, NULL, '2026-04-11 23:59:40', '2026-04-12 05:14:43'),
(6, NULL, 'Rizky Maulana', 'rizky.maulana@contoh.id', '2026-04-12 05:14:43', '081220000116', '$2y$12$olGTrxylZahdmz5ahcQciuEotwiZuRDheG36GXbYAsyonhIX6LOQa', 'Jl. Ahmad Yani No. 66, Pontianak Kota', 'Pontianak', NULL, NULL, '2026-04-11 23:59:40', '2026-04-12 05:14:43'),
(7, NULL, 'Nadia Putri', 'nadia.putri@contoh.id', '2026-04-12 05:14:43', '081220000117', '$2y$12$r/O5WkoooiA6MDHtky6nyec9td0P2mnnPX.j9wbW.Aya36WdW.MF2', 'Jl. Panakkukang Mas No. 28, Makassar', 'Makassar', NULL, NULL, '2026-04-11 23:59:40', '2026-04-12 05:14:43'),
(8, NULL, 'Fajar Nugroho', 'fajar.nugroho@contoh.id', '2026-04-12 05:14:43', '081220000118', '$2y$12$u.HC129HuBvQ7BJnp8P/POjh14R2rPjpgRCC8mSwOYQVkfAEC9pNO', 'Jl. Diponegoro No. 120, Semarang Tengah', 'Semarang', NULL, NULL, '2026-04-11 23:59:41', '2026-04-12 05:14:44'),
(9, NULL, 'Intan Permata', 'intan.permata@contoh.id', '2026-04-12 05:14:44', '081220000119', '$2y$12$MxVAMlV9i2vaaSsnsOynyewhJyBsEVFu0SYaKWq0WFvfuwobJIUxe', 'Jl. Riau No. 22, Pekanbaru', 'Pekanbaru', NULL, NULL, '2026-04-11 23:59:41', '2026-04-12 05:14:44'),
(10, NULL, 'Hendra Kurniawan', 'hendra.kurniawan@contoh.id', '2026-04-12 05:14:44', '081220000120', '$2y$12$/P2VeeTYGbDDdXPmkbPA6.GCtIZh9LIkS38Pan4OpHUtKOkl6K5F.', 'Jl. Sam Ratulangi No. 9, Manado', 'Manado', NULL, NULL, '2026-04-11 23:59:41', '2026-04-12 05:14:44');

-- --------------------------------------------------------

--
-- Table structure for table `error_logs`
--

CREATE TABLE `error_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `error_type` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `stack_trace` text DEFAULT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `file_name` varchar(255) DEFAULT NULL,
  `line_number` int(11) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `severity` varchar(20) NOT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `error_logs`
--

INSERT INTO `error_logs` (`id`, `error_type`, `module`, `message`, `stack_trace`, `context`, `file_name`, `line_number`, `user_id`, `severity`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 'exception', 'payment', 'Payment gateway timeout', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'high', NULL, '2026-04-11 22:59:42', '2026-04-11 23:59:42'),
(2, 'exception', 'report', 'Database connection timeout', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'low', NULL, '2026-04-11 08:59:42', '2026-04-11 23:59:42'),
(3, 'exception', 'user', 'File upload size exceeds limit', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'low', NULL, '2026-04-11 01:59:42', '2026-04-11 23:59:42'),
(4, 'exception', 'report', 'Invalid payment signature', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'high', NULL, '2026-04-11 07:59:42', '2026-04-11 23:59:42'),
(5, 'exception', 'user', 'Email delivery failed', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'critical', NULL, '2026-04-11 07:59:42', '2026-04-11 23:59:42'),
(6, 'exception', 'user', 'Payment gateway timeout', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'low', NULL, '2026-04-11 09:08:57', '2026-04-12 00:08:57'),
(7, 'exception', 'payment', 'Database connection timeout', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'critical', NULL, '2026-04-11 06:08:57', '2026-04-12 00:08:57'),
(8, 'exception', 'report', 'File upload size exceeds limit', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'medium', NULL, '2026-04-11 18:08:57', '2026-04-12 00:08:57'),
(9, 'exception', 'report', 'Invalid payment signature', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'medium', NULL, '2026-04-11 02:08:57', '2026-04-12 00:08:57'),
(10, 'exception', 'report', 'Email delivery failed', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'critical', NULL, '2026-04-11 04:08:57', '2026-04-12 00:08:57'),
(11, 'exception', 'user', 'Payment gateway timeout', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'medium', NULL, '2026-04-11 03:29:24', '2026-04-12 00:29:24'),
(12, 'exception', 'user', 'Database connection timeout', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'medium', NULL, '2026-04-11 08:29:24', '2026-04-12 00:29:24'),
(13, 'exception', 'shipment', 'File upload size exceeds limit', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'high', NULL, '2026-04-11 02:29:24', '2026-04-12 00:29:24'),
(14, 'exception', 'payment', 'Invalid payment signature', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'critical', NULL, '2026-04-11 08:29:24', '2026-04-12 00:29:24'),
(15, 'exception', 'report', 'Email delivery failed', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'low', NULL, '2026-04-11 12:29:24', '2026-04-12 00:29:24'),
(16, 'exception', 'payment', 'Payment gateway timeout', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'critical', NULL, '2026-04-11 18:14:45', '2026-04-12 05:14:45'),
(17, 'exception', 'shipment', 'Database connection timeout', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'critical', NULL, '2026-04-11 12:14:45', '2026-04-12 05:14:45'),
(18, 'exception', 'user', 'File upload size exceeds limit', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'high', NULL, '2026-04-11 21:14:45', '2026-04-12 05:14:45'),
(19, 'exception', 'shipment', 'Invalid payment signature', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'low', NULL, '2026-04-11 12:14:45', '2026-04-12 05:14:45'),
(20, 'exception', 'report', 'Email delivery failed', 'Sample stack trace from seeder', NULL, NULL, NULL, NULL, 'high', NULL, '2026-04-11 18:14:45', '2026-04-12 05:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `integration_statuses`
--

CREATE TABLE `integration_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `status` enum('healthy','degraded','down') NOT NULL DEFAULT 'healthy',
  `success_count` int(11) NOT NULL DEFAULT 0,
  `failure_count` int(11) NOT NULL DEFAULT 0,
  `last_success_at` timestamp NULL DEFAULT NULL,
  `last_failure_at` timestamp NULL DEFAULT NULL,
  `last_error_message` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `integration_statuses`
--

INSERT INTO `integration_statuses` (`id`, `service_name`, `status`, `success_count`, `failure_count`, `last_success_at`, `last_failure_at`, `last_error_message`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 'midtrans', 'healthy', 256, 4, '2026-04-12 05:09:45', '2026-04-12 02:14:45', NULL, '{\"version\":\"2.0\",\"endpoint\":\"https:\\/\\/api.sandbox.midtrans.com\"}', '2026-04-11 23:59:42', '2026-04-12 05:14:45'),
(2, 'email', 'healthy', 142, 1, '2026-04-12 05:04:45', '2026-04-10 05:14:45', NULL, '{\"provider\":\"mailtrap\",\"queue\":\"redis\"}', '2026-04-11 23:59:42', '2026-04-12 05:14:45'),
(3, 'backup', 'healthy', 30, 0, '2026-04-12 03:14:45', NULL, NULL, '{\"storage\":\"s3\",\"frequency\":\"daily\"}', '2026-04-11 23:59:42', '2026-04-12 05:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_contents`
--

CREATE TABLE `landing_page_contents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `section` enum('hero','feature','testimonial','faq','cta','contact','statistic') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `cta_label` varchar(255) DEFAULT NULL,
  `cta_url` varchar(255) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landing_page_contents`
--

INSERT INTO `landing_page_contents` (`id`, `section`, `title`, `subtitle`, `content`, `image_url`, `cta_label`, `cta_url`, `sort_order`, `is_active`, `metadata`, `created_at`, `updated_at`, `deleted_at`) VALUES
(29, 'hero', 'Kondang Ekspedisi', 'Kirim paket ke seluruh Indonesia dengan proses cepat dan aman.', 'Mulai dari dokumen, produk UMKM, hingga barang bernilai tinggi, semua bisa dipantau real-time.', NULL, 'Lacak Paket', '#tracking', 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(30, 'feature', 'Penjemputan Terjadwal', 'Pickup rutin harian', 'Kurir kami siap menjemput paket di rumah, kantor, maupun gudang Anda sesuai jadwal.', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(31, 'feature', 'Tracking Transparan', 'Timeline status lengkap', 'Setiap perpindahan paket tercatat agar pelanggan bisa memantau proses kirim dengan jelas.', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(32, 'feature', 'Tarif Kompetitif', 'Skema zona dan layanan', 'Perhitungan ongkir fleksibel berdasarkan zona tujuan, berat paket, dan jenis layanan.', NULL, NULL, NULL, 3, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(33, 'testimonial', 'Toko Sembako Berkah Jaya', NULL, 'Pengiriman antarkota untuk stok toko jadi lebih teratur. Status paketnya juga jelas.', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(34, 'testimonial', 'UMKM Batik Nusantara', NULL, 'Respon admin cepat, kurir ramah, dan pelanggan kami puas karena paket sampai tepat waktu.', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(35, 'faq', 'Bagaimana cara cek nomor resi?', NULL, 'Masukkan nomor resi pada form tracking publik untuk melihat status terbaru pengiriman.', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(36, 'faq', 'Apakah tersedia pembayaran online?', NULL, 'Ya, pembayaran online tersedia melalui Midtrans Sandbox untuk kebutuhan demo sistem.', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(37, 'cta', 'Kelola pengiriman lebih mudah bersama Kondang Ekspedisi', 'Satu dashboard untuk order, tracking, dan laporan.', NULL, NULL, 'Masuk Dashboard', '/login', 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(38, 'contact', 'Layanan Pelanggan', NULL, '0812-9000-7000', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(39, 'contact', 'Email Resmi', NULL, 'halo@kondangekspedisi.test', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(40, 'statistic', 'Shipment Terselesaikan', NULL, '2.500+', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(41, 'statistic', 'Kota Terlayani', NULL, '120+', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(42, 'statistic', 'Akurasi Tracking', NULL, '99.4%', NULL, NULL, NULL, 3, 1, '{\"bahasa\":\"id\"}', '2026-04-12 00:29:24', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(43, 'hero', 'Kondang Ekspedisi', 'Kirim paket ke seluruh Indonesia dengan proses cepat dan aman.', 'Mulai dari dokumen, produk UMKM, hingga barang bernilai tinggi, semua bisa dipantau real-time.', NULL, 'Lacak Paket', '#tracking', 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(44, 'feature', 'Penjemputan Terjadwal', 'Pickup rutin harian', 'Kurir kami siap menjemput paket di rumah, kantor, maupun gudang Anda sesuai jadwal.', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(45, 'feature', 'Tracking Transparan', 'Timeline status lengkap', 'Setiap perpindahan paket tercatat agar pelanggan bisa memantau proses kirim dengan jelas.', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(46, 'feature', 'Tarif Kompetitif', 'Skema zona dan layanan', 'Perhitungan ongkir fleksibel berdasarkan zona tujuan, berat paket, dan jenis layanan.', NULL, NULL, NULL, 3, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(47, 'testimonial', 'Toko Sembako Berkah Jaya', NULL, 'Pengiriman antarkota untuk stok toko jadi lebih teratur. Status paketnya juga jelas.', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(48, 'testimonial', 'UMKM Batik Nusantara', NULL, 'Respon admin cepat, kurir ramah, dan pelanggan kami puas karena paket sampai tepat waktu.', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(49, 'faq', 'Bagaimana cara cek nomor resi?', NULL, 'Masukkan nomor resi pada form tracking publik untuk melihat status terbaru pengiriman.', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(50, 'faq', 'Apakah tersedia pembayaran online?', NULL, 'Ya, pembayaran online tersedia melalui Midtrans Sandbox untuk kebutuhan demo sistem.', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(51, 'cta', 'Kelola pengiriman lebih mudah bersama Kondang Ekspedisi', 'Satu dashboard untuk order, tracking, dan laporan.', NULL, NULL, 'Masuk Dashboard', '/login', 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(52, 'contact', 'Layanan Pelanggan', NULL, '0812-9000-7000', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(53, 'contact', 'Email Resmi', NULL, 'halo@kondangekspedisi.test', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(54, 'statistic', 'Shipment Terselesaikan', NULL, '2.500+', NULL, NULL, NULL, 1, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(55, 'statistic', 'Kota Terlayani', NULL, '120+', NULL, NULL, NULL, 2, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL),
(56, 'statistic', 'Akurasi Tracking', NULL, '99.4%', NULL, NULL, NULL, 3, 1, '{\"bahasa\":\"id\"}', '2026-04-12 05:14:44', '2026-04-12 05:14:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_12_041349_create_branches_table', 1),
(5, '2026_04_12_041349_create_customers_table', 1),
(6, '2026_04_12_041350_create_vehicles_table', 1),
(7, '2026_04_12_041350_create_zones_table', 1),
(8, '2026_04_12_041351_create_rate_cards_table', 1),
(9, '2026_04_12_041351_create_shipment_statuses_table', 1),
(10, '2026_04_12_041351_create_shipments_table', 1),
(11, '2026_04_12_041352_create_payments_table', 1),
(12, '2026_04_12_041352_create_shipment_items_table', 1),
(13, '2026_04_12_041353_create_landing_page_contents_table', 1),
(14, '2026_04_12_041353_create_shipment_trackings_table', 1),
(15, '2026_04_12_041354_create_branch_balances_table', 1),
(16, '2026_04_12_041354_create_courier_earnings_table', 1),
(17, '2026_04_12_041355_add_profile_fields_to_users_table', 1),
(18, '2026_04_12_044721_add_user_id_to_customers_table', 1),
(19, '2026_04_12_045729_create_audit_logs_table', 1),
(20, '2026_04_12_150000_create_rate_card_approvals_table', 1),
(21, '2026_04_12_150001_create_integration_statuses_table', 1),
(22, '2026_04_12_150002_create_error_logs_table', 1),
(23, '2026_04_12_150003_create_admin_tasks_table', 1),
(24, '2026_04_12_150004_create_reports_table', 1),
(25, '2026_04_12_150005_add_activity_tracking_to_users', 1),
(26, '2026_04_12_220000_add_zone_id_to_branches_table', 2),
(27, '2026_04_12_223000_add_soft_deletes_to_crud_tables', 3),
(28, '2026_04_13_001000_add_origin_destination_zone_to_rate_cards', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shipment_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `method` enum('midtrans','cash','transfer','e_wallet','cod') NOT NULL DEFAULT 'midtrans',
  `status` enum('pending','settlement','deny','expire','cancel','refund','failed') NOT NULL DEFAULT 'pending',
  `amount` decimal(12,2) NOT NULL,
  `midtrans_order_id` varchar(255) DEFAULT NULL,
  `midtrans_transaction_id` varchar(255) DEFAULT NULL,
  `snap_token` text DEFAULT NULL,
  `snap_redirect_url` text DEFAULT NULL,
  `payment_type` varchar(255) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `va_number` varchar(60) DEFAULT NULL,
  `fraud_status` varchar(30) DEFAULT NULL,
  `signature_key` varchar(255) DEFAULT NULL,
  `transaction_time` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `gateway_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_payload`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `shipment_id`, `customer_id`, `processed_by`, `method`, `status`, `amount`, `midtrans_order_id`, `midtrans_transaction_id`, `snap_token`, `snap_redirect_url`, `payment_type`, `bank_name`, `va_number`, `fraud_status`, `signature_key`, `transaction_time`, `paid_at`, `gateway_payload`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 8, 22, 'midtrans', 'settlement', 130270.00, 'ORDER-KND-20260412-001', '873ca82b-b625-40ce-9664-afaef35855f0', 'R0vOsMHO3ql07dw1kN5YWyjkrYAR8KxO', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/McWYciXHlrFsQ1rXsU9v', 'bank_transfer', 'bca', '6704995256', 'accept', '687067a79f59cf0db4d936fe727c018fcdae33476dea83b09f14e559a5c2a20b', '2026-04-09 00:14:44', '2026-04-11 12:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(2, 2, 7, 22, 'midtrans', 'settlement', 113605.00, 'ORDER-KND-20260412-002', '0bf0aa6c-47f5-42b0-badf-49b4ebee9451', 'LHH20pGADUwspYg61IrBaTi9CQZFB4Pm', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/7nZqpnAOp7UN47Tgsi9i', 'bank_transfer', 'bca', '3284834850', 'accept', 'c3786b437af8b0eddb775792b0cff28bd53722441081fdde561044556eb2e32a', '2026-04-09 19:14:44', '2026-04-11 17:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(3, 3, 5, 22, 'midtrans', 'settlement', 162399.00, 'ORDER-KND-20260412-003', 'ee8be982-2c57-4764-93a5-f74a5eb757ca', 'NzT5bguJnGK1NbLFhXBl2T4nHVXbISkv', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/oaQcP2oqMMseuF1SbWcy', 'bank_transfer', 'bca', '2510504753', 'accept', 'e09d939fbb96717098e28e7c049266259df5c5716c43ba613306bc1028a9a3cd', '2026-04-11 03:14:44', '2026-04-10 08:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(4, 4, 1, 22, 'midtrans', 'settlement', 153044.00, 'ORDER-KND-20260412-004', 'c4cb4cc6-f7b9-4d25-8b7b-c0ac334a44ea', 'Q6OwA2qntBGsMD2bp7t6AUqWDqmkODKp', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/Usfbnz0ANzqSsylS8eHp', 'bank_transfer', 'bca', '2041408329', 'accept', '4f660632e8d7925f8dd9b4f7bb9ede3e84432f6955352c3a8796cb67802b6ea1', '2026-04-10 23:14:44', '2026-04-10 15:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(5, 5, 2, 22, 'midtrans', 'settlement', 168448.00, 'ORDER-KND-20260412-005', 'd6865781-f21c-4e94-8507-c39d67e7b17b', 'ETFwd1WmHWpj3PagvCV4YONlMAEBHPwT', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/1QBCR0rIwGj9hbn7c6XY', 'bank_transfer', 'bca', '8716282618', 'accept', '2d63fe514d290b94f8364d6fd4664549a3f967fd635a6aba4e0b8fe8d6c33695', '2026-04-08 12:14:44', '2026-04-10 15:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(6, 6, 10, 22, 'midtrans', 'settlement', 123350.00, 'ORDER-KND-20260412-006', '184e6981-e9ec-4f35-81e0-cf8ae8a794aa', 'vJo0IC08IOlIiNl416BoUaMlnzbc4MzU', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/vBbXMK3fEV5NCfSelu4g', 'bank_transfer', 'bca', '6303357601', 'accept', 'aa51eb650ec1f581cdefd44acd386aa12c72ff1e23d6d00e2f95eb3b4349530b', '2026-04-12 04:14:44', '2026-04-11 22:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(7, 7, 4, 22, 'midtrans', 'settlement', 121985.00, 'ORDER-KND-20260412-007', 'ba45f794-de55-4811-8360-8a168e0197a4', 'FYaDEVX3F3ZnSz3cehh7CYslltIhp7fo', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/hmQUhok1RzqqRCxCsiDA', 'bank_transfer', 'bca', '2978389957', 'accept', 'bd3d63c389da59e73c9ead40178509d3f9ebe06f4f9c4a432f625944b1e77010', '2026-04-10 21:14:44', '2026-04-11 19:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(8, 8, 9, 22, 'midtrans', 'settlement', 184547.00, 'ORDER-KND-20260412-008', 'e12da2c5-dfee-4e9a-a31f-0423f7bc39eb', 'S6uJCmgPnajKH4DsW2yXhtTBdTJ1roto', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/KT44pQW5w6mARKe0Q6ym', 'bank_transfer', 'bca', '2638599293', 'accept', '0a8328cdbb994c94d19f10e0d036391646d73f5d7facc24d79e7f25235b53784', '2026-04-10 16:14:44', '2026-04-11 11:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(9, 9, 8, 22, 'midtrans', 'settlement', 176122.00, 'ORDER-KND-20260412-009', 'e0c07eab-ff06-4ec4-8990-f6f67e7a9ea0', 'mPkGalJquUVtjmwzCIKsylXgQZ9LTIMM', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/smzwLtwxwH1YOTfDxd4K', 'bank_transfer', 'bca', '8448770149', 'accept', 'cb01dce6f06ad152088fe5433165e3feea87aff80f559aee98797ccba0c11a64', '2026-04-11 05:14:44', '2026-04-10 21:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(10, 10, 2, 22, 'midtrans', 'settlement', 112301.00, 'ORDER-KND-20260412-010', '1f157507-eb5e-4e14-afac-fe2698b76b37', 'oyxBoEGd9Hbp34E9hb8sis11kwW84Kj8', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/zlXbqBuFf1mhRMkNDZw7', 'bank_transfer', 'bca', '6235175138', 'accept', '67383ad852a7b85dbe5593571c217a11011c25fdd72c7358eb1c12176e05d186', '2026-04-10 16:14:44', '2026-04-11 17:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(11, 11, 7, 22, 'midtrans', 'settlement', 63053.00, 'ORDER-KND-20260412-011', '28061f28-9e43-4aaf-9702-58a10f5fb597', 'vQKrwsTzrr504nur6TgKi570mQOwRgqz', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/TG155RTU6W5JRfI6GofX', 'bank_transfer', 'bca', '5035737583', 'accept', '564c280029307ba6d46934d9f5f511859ccf38b94c94fdb0405f5d72dabf8c4a', '2026-04-09 14:14:44', '2026-04-12 04:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(12, 12, 3, 22, 'midtrans', 'settlement', 35887.00, 'ORDER-KND-20260412-012', '5dc18d6a-070e-4d0e-aee9-c6d3deb68907', 'azqlI5p3kJvZiIuPnR5vnxqs8a3YcUGr', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/4XcNth65ve0HpGuwmftM', 'bank_transfer', 'bca', '9808373848', 'accept', 'b83e68e2b5cfef6c4cdf53ca4960868f9df786c2b4c779556e3fc8786700289f', '2026-04-09 00:14:44', '2026-04-10 18:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(13, 13, 4, 22, 'midtrans', 'settlement', 42655.00, 'ORDER-KND-20260412-013', 'f68e23b0-e9e0-4603-b8e2-ba4c3a78a820', 'DDa1fp3uSMV974Ejmylg3FRKNMyUt31B', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/WZpehBkilOMjyPlxKzGw', 'bank_transfer', 'bca', '8790916630', 'accept', '0725133ea22f4862d3ac42024322d959733345654278634a06b849d369fe4311', '2026-04-11 04:14:44', '2026-04-10 07:14:44', '{\"sumber\":\"seeder\",\"status\":\"settlement\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(14, 14, 9, 22, 'midtrans', 'pending', 55129.00, 'ORDER-KND-20260412-014', '378b7963-a682-4f08-8ac2-2e317e299947', 'p9ojxjamA4INDP302jEMc3JZA42cAsBl', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/LejoEn1tu5tKoaNgJs6B', 'bank_transfer', 'bca', '2510691475', 'accept', '815023f16ffb4055c0876db2f77441b5ba7ea256e8af30b4035fb5df06c2346d', '2026-04-09 23:14:44', NULL, '{\"sumber\":\"seeder\",\"status\":\"pending\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(15, 15, 3, 22, 'midtrans', 'pending', 143455.00, 'ORDER-KND-20260412-015', 'fd7149fa-3b08-4424-990d-aa9972300c8f', 'hsrisAlMA3CIQXBpHshDWNjmY1IVKkF5', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/gKdHNGdl3eLVfHXRIuRw', 'bank_transfer', 'bca', '9388531990', 'accept', '04c1a34f2fbf5bed62a51551e2a117adca746925f4b4208b4a8d5bb14946baf8', '2026-04-11 03:14:45', NULL, '{\"sumber\":\"seeder\",\"status\":\"pending\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:42', '2026-04-12 05:14:45', NULL),
(16, 16, 5, 22, 'midtrans', 'pending', 68572.00, 'ORDER-KND-20260412-016', '23d3a4b6-3992-4984-be38-1108a3da7189', 'El5drb82BMGDUHemKbN6m8ppsYplqqZD', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/iZqiLF9e7sq6uMpqufIr', 'bank_transfer', 'bca', '3492964113', 'accept', 'db30ede02bdd66890ce59a2aa3fc831e2c001f272fc59b9ac0682408840b6246', '2026-04-09 12:14:45', NULL, '{\"sumber\":\"seeder\",\"status\":\"pending\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:42', '2026-04-12 05:14:45', NULL),
(17, 17, 7, 22, 'midtrans', 'settlement', 48265.00, 'ORDER-KND-20260412-017', '97640851-22f2-42e9-98be-84cc69750062', 'WmUrXqWDgCvLWaWNXjzG9YlWngttQoIX', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/IZXdUCxXxoi2yDIUWvDP', 'bank_transfer', 'bca', '2139626467', 'accept', '13ae2bef83dd761cfc9a6a5d1815955d90225bff292caf9115542f6af2e47057', '2026-04-11 19:14:45', NULL, '{\"sumber\":\"seeder\",\"status\":\"pending\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:42', '2026-04-12 05:57:20', NULL),
(18, 18, 1, 22, 'midtrans', 'cancel', 68127.00, 'ORDER-KND-20260412-018', 'f2917123-5746-492d-aa08-44bcfe3e673f', 'vuDl1kIrLYVjt46fdK9KgVcFQBZ5bInT', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/xdNqF68x9JmJwcqKDzPr', 'bank_transfer', 'bca', '1512451533', 'accept', '78b10408a11b7c68c6d9d782391220d6d5834dea73b536fabe113bc0ca11cdd8', '2026-04-08 06:14:45', NULL, '{\"sumber\":\"seeder\",\"status\":\"cancel\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:42', '2026-04-12 05:14:45', NULL),
(19, 19, 8, 22, 'midtrans', 'cancel', 62049.00, 'ORDER-KND-20260412-019', '9baf8d37-9ae8-42dc-a791-707b2e06a5a3', 'RnQsDsepeKd13qUr1NqtrYdriLjn0wlm', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/oIu1OACYGWtCTgiLp7oM', 'bank_transfer', 'bca', '9786181971', 'accept', '11409156bbb17f5d534a2ebe5803697f65dd98ddd720b98d1eb4e0e6566db229', '2026-04-10 10:14:45', NULL, '{\"sumber\":\"seeder\",\"status\":\"cancel\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:42', '2026-04-12 05:14:45', NULL),
(20, 20, 6, 22, 'midtrans', 'refund', 36286.00, 'ORDER-KND-20260412-020', '67d5c170-c7ce-43ab-bafe-f00ef2269fc6', 'tSXJurLI1tpn4I9Q80QitJfaz5DQb5Rl', 'https://app.sandbox.midtrans.com/snap/v2/vtweb/n4VFRYWvAzm7Le3iLasW', 'bank_transfer', 'bca', '5117086238', 'accept', '6ddc076e8f5ce6749495dd665e4417bb098762050a48d66010ced0aeee697a70', '2026-04-10 21:14:45', NULL, '{\"sumber\":\"seeder\",\"status\":\"refund\"}', 'Pembayaran contoh untuk kebutuhan demo aplikasi.', '2026-04-11 23:59:42', '2026-04-12 05:14:45', NULL),
(21, 21, NULL, 26, 'cash', 'settlement', 136000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 05:54:02', '2026-04-12 05:54:09', NULL),
(22, 22, NULL, 26, 'midtrans', 'settlement', 150592.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Payment otomatis dibuat saat shipment dibuat berdasarkan perhitungan rate card.', '2026-04-12 06:18:02', '2026-04-12 06:18:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rate_cards`
--

CREATE TABLE `rate_cards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `zone_id` bigint(20) UNSIGNED NOT NULL,
  `origin_zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `destination_zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `service_type` enum('regular','express','same_day','economy') NOT NULL DEFAULT 'regular',
  `min_weight_kg` decimal(10,2) NOT NULL,
  `max_weight_kg` decimal(10,2) DEFAULT NULL,
  `base_price` decimal(12,2) NOT NULL,
  `per_kg_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `insurance_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_cards`
--

INSERT INTO `rate_cards` (`id`, `zone_id`, `origin_zone_id`, `destination_zone_id`, `service_type`, `min_weight_kg`, `max_weight_kg`, `base_price`, `per_kg_price`, `insurance_fee`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 'regular', 0.00, 1.00, 21000.00, 5250.00, 2250.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:39', NULL),
(2, 1, 1, 1, 'express', 0.00, 1.00, 26880.00, 5250.00, 2250.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:39', NULL),
(3, 1, 1, 1, 'same_day', 0.00, 1.00, 33600.00, 6038.00, 2250.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:39', NULL),
(4, 1, 1, 1, 'economy', 0.00, 1.00, 17850.00, 5250.00, 2025.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:39', NULL),
(5, 2, 2, 2, 'regular', 0.00, 1.00, 28000.00, 7000.00, 3000.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:40', NULL),
(6, 2, 2, 2, 'express', 0.00, 1.00, 35840.00, 7000.00, 3000.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:40', NULL),
(7, 2, 2, 2, 'same_day', 0.00, 1.00, 44800.00, 8050.00, 3000.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:40', NULL),
(8, 2, 2, 2, 'economy', 0.00, 1.00, 23800.00, 7000.00, 2700.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:40', NULL),
(9, 3, 3, 3, 'regular', 0.00, 1.00, 35000.00, 8750.00, 3750.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:40', NULL),
(10, 3, 3, 3, 'express', 0.00, 1.00, 44800.00, 8750.00, 3750.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:40', NULL),
(11, 3, 3, 3, 'economy', 0.00, 1.00, 29750.00, 8750.00, 3375.00, 1, '2026-04-11 23:59:37', '2026-04-12 05:14:40', NULL),
(12, 2, 1, 2, 'economy', 0.00, 1.00, 29274.00, 8610.00, 3321.00, 1, '2026-04-12 05:14:39', '2026-04-12 05:14:39', NULL),
(13, 2, 1, 2, 'regular', 0.00, 1.00, 34440.00, 8610.00, 3690.00, 1, '2026-04-12 05:14:39', '2026-04-12 05:14:39', NULL),
(14, 2, 1, 2, 'express', 0.00, 1.00, 44083.00, 8610.00, 3690.00, 1, '2026-04-12 05:14:39', '2026-04-12 05:14:39', NULL),
(15, 2, 1, 2, 'same_day', 0.00, 1.00, 55104.00, 9902.00, 3690.00, 1, '2026-04-12 05:14:39', '2026-04-12 05:14:39', NULL),
(16, 3, 1, 3, 'economy', 0.00, 1.00, 37128.00, 10920.00, 4212.00, 1, '2026-04-12 05:14:39', '2026-04-12 05:14:39', NULL),
(17, 3, 1, 3, 'regular', 0.00, 1.00, 43680.00, 10920.00, 4680.00, 1, '2026-04-12 05:14:39', '2026-04-12 05:14:39', NULL),
(18, 3, 1, 3, 'express', 0.00, 1.00, 55910.00, 10920.00, 4680.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(19, 3, 1, 3, 'same_day', 0.00, 1.00, 69888.00, 12558.00, 4680.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(20, 1, 2, 1, 'economy', 0.00, 1.00, 27846.00, 8190.00, 3159.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(21, 1, 2, 1, 'regular', 0.00, 1.00, 32760.00, 8190.00, 3510.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(22, 1, 2, 1, 'express', 0.00, 1.00, 41933.00, 8190.00, 3510.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(23, 1, 2, 1, 'same_day', 0.00, 1.00, 52416.00, 9419.00, 3510.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(24, 3, 2, 3, 'economy', 0.00, 1.00, 36414.00, 10710.00, 4131.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(25, 3, 2, 3, 'regular', 0.00, 1.00, 42840.00, 10710.00, 4590.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(26, 3, 2, 3, 'express', 0.00, 1.00, 54835.00, 10710.00, 4590.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(27, 3, 2, 3, 'same_day', 0.00, 1.00, 68544.00, 12317.00, 4590.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(28, 1, 3, 1, 'economy', 0.00, 1.00, 34272.00, 10080.00, 3888.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(29, 1, 3, 1, 'regular', 0.00, 1.00, 40320.00, 10080.00, 4320.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(30, 1, 3, 1, 'express', 0.00, 1.00, 51610.00, 10080.00, 4320.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(31, 1, 3, 1, 'same_day', 0.00, 1.00, 64512.00, 11592.00, 4320.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(32, 2, 3, 2, 'economy', 0.00, 1.00, 34986.00, 10290.00, 3969.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(33, 2, 3, 2, 'regular', 0.00, 1.00, 41160.00, 10290.00, 4410.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(34, 2, 3, 2, 'express', 0.00, 1.00, 52685.00, 10290.00, 4410.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(35, 2, 3, 2, 'same_day', 0.00, 1.00, 65856.00, 11834.00, 4410.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL),
(36, 3, 3, 3, 'same_day', 0.00, 1.00, 56000.00, 10063.00, 3750.00, 1, '2026-04-12 05:14:40', '2026-04-12 05:14:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rate_card_approvals`
--

CREATE TABLE `rate_card_approvals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rate_card_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`changes`)),
  `reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_card_approvals`
--

INSERT INTO `rate_card_approvals` (`id`, `rate_card_id`, `requested_by`, `approved_by`, `status`, `changes`, `reason`, `notes`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 1, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"18000.00\",\"new\":19800},\"per_kg_price\":{\"old\":\"4500.00\",\"new\":4950}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-11 23:59:42', '2026-04-11 23:59:42'),
(2, 2, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"26000.00\",\"new\":28600.000000000004},\"per_kg_price\":{\"old\":\"6000.00\",\"new\":6600.000000000001}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-11 23:59:42', '2026-04-11 23:59:42'),
(3, 3, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"34000.00\",\"new\":37400},\"per_kg_price\":{\"old\":\"7000.00\",\"new\":7700.000000000001}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-11 23:59:42', '2026-04-11 23:59:42'),
(4, 1, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"18000.00\",\"new\":19800},\"per_kg_price\":{\"old\":\"4500.00\",\"new\":4950}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-12 00:08:57', '2026-04-12 00:08:57'),
(5, 2, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"26000.00\",\"new\":28600.000000000004},\"per_kg_price\":{\"old\":\"6000.00\",\"new\":6600.000000000001}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-12 00:08:57', '2026-04-12 00:08:57'),
(6, 3, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"34000.00\",\"new\":37400},\"per_kg_price\":{\"old\":\"7000.00\",\"new\":7700.000000000001}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-12 00:08:57', '2026-04-12 00:08:57'),
(7, 1, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"18000.00\",\"new\":19800},\"per_kg_price\":{\"old\":\"4500.00\",\"new\":4950}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-12 00:29:24', '2026-04-12 00:29:24'),
(8, 2, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"26000.00\",\"new\":28600.000000000004},\"per_kg_price\":{\"old\":\"6000.00\",\"new\":6600.000000000001}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-12 00:29:24', '2026-04-12 00:29:24'),
(9, 3, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"34000.00\",\"new\":37400},\"per_kg_price\":{\"old\":\"7000.00\",\"new\":7700.000000000001}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-12 00:29:24', '2026-04-12 00:29:24'),
(10, 1, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"21000.00\",\"new\":23100.000000000004},\"per_kg_price\":{\"old\":\"5250.00\",\"new\":5775.000000000001}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(11, 2, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"26880.00\",\"new\":29568.000000000004},\"per_kg_price\":{\"old\":\"5250.00\",\"new\":5775.000000000001}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(12, 3, 21, NULL, 'pending', '{\"base_price\":{\"old\":\"33600.00\",\"new\":36960},\"per_kg_price\":{\"old\":\"6038.00\",\"new\":6641.8}}', 'Penyesuaian harga sesuai inflasi Q1 2026.', NULL, NULL, '2026-04-12 05:14:45', '2026-04-12 05:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `frequency` enum('daily','weekly','monthly','manual') NOT NULL DEFAULT 'manual',
  `recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`recipients`)),
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`filters`)),
  `format` enum('csv','pdf','excel') NOT NULL DEFAULT 'pdf',
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `generated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `record_count` int(11) DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `report_type`, `title`, `frequency`, `recipients`, `filters`, `format`, `file_path`, `status`, `generated_by`, `error_message`, `record_count`, `generated_at`, `next_run_at`, `created_at`, `updated_at`) VALUES
(1, 'kpi_snapshot', 'KPI Snapshot - 2026-04-11', 'daily', '[\"admin@kondangekspedisi.test\",\"manager@kondangekspedisi.test\"]', '{\"date\":\"2026-04-11\"}', 'pdf', 'reports/kpi_snapshot_20260411.pdf', 'completed', 21, NULL, 150, '2026-04-10 23:59:42', NULL, '2026-04-11 23:59:42', '2026-04-11 23:59:42'),
(2, 'daily_export', 'Daily Shipment Export - 2026-04-12', 'daily', '[\"operations@kondangekspedisi.test\"]', '{\"date\":\"2026-04-12\"}', 'csv', 'reports/shipments_20260412.csv', 'completed', 21, NULL, 42, '2026-04-11 23:59:42', NULL, '2026-04-11 23:59:42', '2026-04-11 23:59:42'),
(3, 'kpi_snapshot', 'KPI Snapshot - 2026-04-11', 'daily', '[\"admin@kondangekspedisi.test\",\"manager@kondangekspedisi.test\"]', '{\"date\":\"2026-04-11\"}', 'pdf', 'reports/kpi_snapshot_20260411.pdf', 'completed', 21, NULL, 150, '2026-04-11 00:08:57', NULL, '2026-04-12 00:08:57', '2026-04-12 00:08:57'),
(4, 'daily_export', 'Daily Shipment Export - 2026-04-12', 'daily', '[\"operations@kondangekspedisi.test\"]', '{\"date\":\"2026-04-12\"}', 'csv', 'reports/shipments_20260412.csv', 'completed', 21, NULL, 42, '2026-04-12 00:08:57', NULL, '2026-04-12 00:08:57', '2026-04-12 00:08:57'),
(5, 'kpi_snapshot', 'KPI Snapshot - 2026-04-11', 'daily', '[\"admin@kondangekspedisi.test\",\"manager@kondangekspedisi.test\"]', '{\"date\":\"2026-04-11\"}', 'pdf', 'reports/kpi_snapshot_20260411.pdf', 'completed', 21, NULL, 150, '2026-04-11 00:29:24', NULL, '2026-04-12 00:29:24', '2026-04-12 00:29:24'),
(6, 'daily_export', 'Daily Shipment Export - 2026-04-12', 'daily', '[\"operations@kondangekspedisi.test\"]', '{\"date\":\"2026-04-12\"}', 'csv', 'reports/shipments_20260412.csv', 'completed', 21, NULL, 42, '2026-04-12 00:29:24', NULL, '2026-04-12 00:29:24', '2026-04-12 00:29:24'),
(7, 'kpi_snapshot', 'KPI Snapshot - 2026-04-11', 'daily', '[\"admin@kondangekspedisi.test\",\"manager@kondangekspedisi.test\"]', '{\"date\":\"2026-04-11\"}', 'pdf', 'reports/kpi_snapshot_20260411.pdf', 'completed', 21, NULL, 150, '2026-04-11 05:14:45', NULL, '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(8, 'daily_export', 'Daily Shipment Export - 2026-04-12', 'daily', '[\"operations@kondangekspedisi.test\"]', '{\"date\":\"2026-04-12\"}', 'csv', 'reports/shipments_20260412.csv', 'completed', 21, NULL, 42, '2026-04-12 05:14:45', NULL, '2026-04-12 05:14:45', '2026-04-12 05:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('a3tzM2YFSH8UjL7ZuzHLbpX24qfqVAiNd9dgcZct', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiajU5OEhpdTZQZEFXcUFST0VpN2lPbG1FMklRZmlKVVMxMUFBbGpEMCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo3OiJsYW5kaW5nIjt9fQ==', 1775997416),
('PPUSC19c14eptviTBHwxFLsKaBq2Mm6FbwnmjIyJ', 26, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 OPR/129.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWXFCSDF0SlRlZmQxRkpRaWVSMElPRUtMdzFEaXJoUDdoWWs0cDJ2aSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zaGlwbWVudHMvMjIvbGFiZWwiO3M6NToicm91dGUiO3M6MTU6InNoaXBtZW50cy5sYWJlbCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI2O30=', 1776000887);

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tracking_number` varchar(40) NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `courier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vehicle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` bigint(20) UNSIGNED NOT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_phone` varchar(30) NOT NULL,
  `sender_address` text NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `recipient_phone` varchar(30) NOT NULL,
  `recipient_address` text NOT NULL,
  `service_type` varchar(40) NOT NULL DEFAULT 'regular',
  `total_weight_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_volume` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `insurance_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_cod` tinyint(1) NOT NULL DEFAULT 0,
  `cod_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('unpaid','pending','paid','failed','refunded') NOT NULL DEFAULT 'unpaid',
  `current_status_at` timestamp NULL DEFAULT NULL,
  `estimated_delivery_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `tracking_number`, `customer_id`, `branch_id`, `courier_id`, `vehicle_id`, `zone_id`, `status_id`, `sender_name`, `sender_phone`, `sender_address`, `recipient_name`, `recipient_phone`, `recipient_address`, `service_type`, `total_weight_kg`, `total_volume`, `subtotal_amount`, `insurance_amount`, `admin_fee`, `total_amount`, `is_cod`, `cod_amount`, `payment_status`, `current_status_at`, `estimated_delivery_at`, `delivered_at`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'KND-20260412-001', 8, 2, 24, 2, 2, 4, 'Toko Kelontong Sinar Rejeki', '081290001001', 'Jl. Mangga Dua Raya No. 14, Jakarta', 'Fajar Nugroho', '081220000118', 'Jl. Diponegoro No. 120, Semarang Tengah', 'same_day', 0.60, 38.00, 124770.00, 3000.00, 2500.00, 130270.00, 0, 0.00, 'paid', '2026-04-09 13:14:44', '2026-04-13 05:14:44', '2026-04-11 19:14:44', 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(2, 'KND-20260412-002', 7, 1, 23, 2, 1, 4, 'Butik Anggrek', '081290001003', 'Jl. Cihampelas No. 45, Bandung', 'Nadia Putri', '081220000117', 'Jl. Panakkukang Mas No. 28, Makassar', 'express', 2.80, 111.00, 108105.00, 3000.00, 2500.00, 113605.00, 0, 0.00, 'paid', '2026-04-10 22:14:44', '2026-04-17 05:14:44', '2026-04-11 16:14:44', 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(3, 'KND-20260412-003', 5, 2, 24, 4, 1, 4, 'Percetakan Mitra Usaha', '081290001005', 'Jl. Dipati Ukur No. 39, Bandung', 'Dewi Lestari', '081220000115', 'Jl. Sriwijaya No. 17, Denpasar Barat', 'economy', 5.00, 38.00, 156899.00, 3000.00, 2500.00, 162399.00, 0, 0.00, 'paid', '2026-04-09 18:14:44', '2026-04-13 05:14:44', '2026-04-11 10:14:44', 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(4, 'KND-20260412-004', 1, 1, 23, 2, 2, 4, 'Toko Kelontong Sinar Rejeki', '081290001001', 'Jl. Mangga Dua Raya No. 14, Jakarta', 'Pelanggan Demo', '081220000111', 'Jl. Melati No. 5, Cempaka Putih, Jakarta Pusat', 'express', 4.30, 77.00, 147544.00, 3000.00, 2500.00, 153044.00, 0, 0.00, 'paid', '2026-04-11 02:14:44', '2026-04-13 05:14:44', '2026-04-12 01:14:44', 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(5, 'KND-20260412-005', 2, 1, 23, 3, 2, 4, 'Butik Anggrek', '081290001003', 'Jl. Cihampelas No. 45, Bandung', 'Andi Pratama', '081220000112', 'Jl. Kencana No. 8, Kebayoran Baru, Jakarta Selatan', 'same_day', 1.40, 104.00, 162948.00, 3000.00, 2500.00, 168448.00, 0, 0.00, 'paid', '2026-04-10 14:14:44', '2026-04-14 05:14:44', '2026-04-11 22:14:44', 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(6, 'KND-20260412-006', 10, 1, 23, 2, 2, 2, 'Butik Anggrek', '081290001003', 'Jl. Cihampelas No. 45, Bandung', 'Hendra Kurniawan', '081220000120', 'Jl. Sam Ratulangi No. 9, Manado', 'regular', 1.20, 39.00, 117850.00, 3000.00, 2500.00, 123350.00, 0, 0.00, 'paid', '2026-04-11 00:14:44', '2026-04-14 05:14:44', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(7, 'KND-20260412-007', 4, 1, 23, 3, 3, 2, 'CV Laut Timur', '081290001004', 'Jl. Kenjeran No. 102, Surabaya', 'Budi Santoso', '081220000114', 'Jl. Ikan Dorang No. 44, Gubeng', 'same_day', 1.00, 137.00, 116485.00, 3000.00, 2500.00, 121985.00, 0, 0.00, 'paid', '2026-04-10 14:14:44', '2026-04-14 05:14:44', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(8, 'KND-20260412-008', 9, 2, 24, 6, 3, 2, 'Percetakan Mitra Usaha', '081290001005', 'Jl. Dipati Ukur No. 39, Bandung', 'Intan Permata', '081220000119', 'Jl. Riau No. 22, Pekanbaru', 'economy', 6.70, 39.00, 179047.00, 3000.00, 2500.00, 184547.00, 0, 0.00, 'paid', '2026-04-12 00:14:44', '2026-04-17 05:14:44', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(9, 'KND-20260412-009', 8, 1, 23, 2, 2, 2, 'Butik Anggrek', '081290001003', 'Jl. Cihampelas No. 45, Bandung', 'Fajar Nugroho', '081220000118', 'Jl. Diponegoro No. 120, Semarang Tengah', 'same_day', 7.70, 84.00, 170622.00, 3000.00, 2500.00, 176122.00, 0, 0.00, 'paid', '2026-04-10 21:14:44', '2026-04-15 05:14:44', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(10, 'KND-20260412-010', 2, 2, 24, 6, 1, 2, 'PT Nusantara Kriya', '081290001002', 'Jl. Gatot Subroto No. 88, Jakarta', 'Andi Pratama', '081220000112', 'Jl. Kencana No. 8, Kebayoran Baru, Jakarta Selatan', 'express', 3.10, 116.00, 106801.00, 3000.00, 2500.00, 112301.00, 0, 0.00, 'paid', '2026-04-11 04:14:44', '2026-04-13 05:14:44', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(11, 'KND-20260412-011', 7, 1, 23, 1, 2, 2, 'Toko Kelontong Sinar Rejeki', '081290001001', 'Jl. Mangga Dua Raya No. 14, Jakarta', 'Nadia Putri', '081220000117', 'Jl. Panakkukang Mas No. 28, Makassar', 'same_day', 4.20, 60.00, 57553.00, 3000.00, 2500.00, 63053.00, 0, 0.00, 'paid', '2026-04-11 17:14:44', '2026-04-17 05:14:44', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(12, 'KND-20260412-012', 3, 1, 23, 3, 2, 2, 'Toko Kelontong Sinar Rejeki', '081290001001', 'Jl. Mangga Dua Raya No. 14, Jakarta', 'Siti Rahmawati', '081220000113', 'Jl. Mawar Merah No. 12, Cimahi Tengah', 'economy', 3.90, 71.00, 30387.00, 3000.00, 2500.00, 35887.00, 0, 0.00, 'paid', '2026-04-10 23:14:44', '2026-04-16 05:14:44', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(13, 'KND-20260412-013', 4, 3, 25, 3, 3, 2, 'Percetakan Mitra Usaha', '081290001005', 'Jl. Dipati Ukur No. 39, Bandung', 'Budi Santoso', '081220000114', 'Jl. Ikan Dorang No. 44, Gubeng', 'same_day', 1.70, 69.00, 37155.00, 3000.00, 2500.00, 42655.00, 0, 0.00, 'paid', '2026-04-10 14:14:44', '2026-04-16 05:14:44', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(14, 'KND-20260412-014', 9, 2, 24, 4, 3, 1, 'PT Nusantara Kriya', '081290001002', 'Jl. Gatot Subroto No. 88, Jakarta', 'Intan Permata', '081220000119', 'Jl. Riau No. 22, Pekanbaru', 'express', 4.60, 115.00, 49629.00, 3000.00, 2500.00, 55129.00, 0, 0.00, 'pending', '2026-04-11 00:14:44', '2026-04-14 05:14:44', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:44', NULL),
(15, 'KND-20260412-015', 3, 3, 25, 1, 1, 1, 'PT Nusantara Kriya', '081290001002', 'Jl. Gatot Subroto No. 88, Jakarta', 'Siti Rahmawati', '081220000113', 'Jl. Mawar Merah No. 12, Cimahi Tengah', 'regular', 3.80, 50.00, 137955.00, 3000.00, 2500.00, 143455.00, 0, 0.00, 'pending', '2026-04-11 18:14:45', '2026-04-16 05:14:45', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:41', '2026-04-12 05:14:45', NULL),
(16, 'KND-20260412-016', 5, 2, 24, 2, 3, 1, 'Percetakan Mitra Usaha', '081290001005', 'Jl. Dipati Ukur No. 39, Bandung', 'Dewi Lestari', '081220000115', 'Jl. Sriwijaya No. 17, Denpasar Barat', 'same_day', 3.80, 131.00, 63072.00, 3000.00, 2500.00, 68572.00, 0, 0.00, 'pending', '2026-04-09 15:14:45', '2026-04-16 05:14:45', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:42', '2026-04-12 05:14:45', NULL),
(17, 'KND-20260412-017', 7, 1, NULL, 4, 3, 1, 'Toko Kelontong Sinar Rejeki', '081290001001', 'Jl. Mangga Dua Raya No. 14, Jakarta', 'Nadia Putri', '081220000117', 'Jl. Panakkukang Mas No. 28, Makassar', 'same_day', 2.20, 26.00, 42765.00, 3000.00, 2500.00, 48265.00, 0, 0.00, 'paid', '2026-04-09 22:14:45', '2026-04-15 12:14:00', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:42', '2026-04-12 05:44:39', NULL),
(18, 'KND-20260412-018', 1, 1, 23, 2, 3, 5, 'Butik Anggrek', '081290001003', 'Jl. Cihampelas No. 45, Bandung', 'Pelanggan Demo', '081220000111', 'Jl. Melati No. 5, Cempaka Putih, Jakarta Pusat', 'economy', 2.40, 45.00, 62627.00, 3000.00, 2500.00, 68127.00, 0, 0.00, 'pending', '2026-04-10 04:14:45', '2026-04-14 05:14:45', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:42', '2026-04-12 05:14:45', NULL),
(19, 'KND-20260412-019', 8, 3, 25, 4, 2, 5, 'Butik Anggrek', '081290001003', 'Jl. Cihampelas No. 45, Bandung', 'Fajar Nugroho', '081220000118', 'Jl. Diponegoro No. 120, Semarang Tengah', 'economy', 4.30, 38.00, 56549.00, 3000.00, 2500.00, 62049.00, 0, 0.00, 'pending', '2026-04-11 08:14:45', '2026-04-16 05:14:45', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:42', '2026-04-12 05:14:45', NULL),
(20, 'KND-20260412-020', 6, 3, 25, 4, 3, 6, 'Toko Kelontong Sinar Rejeki', '081290001001', 'Jl. Mangga Dua Raya No. 14, Jakarta', 'Rizky Maulana', '081220000116', 'Jl. Ahmad Yani No. 66, Pontianak Kota', 'regular', 1.20, 63.00, 30786.00, 3000.00, 2500.00, 36286.00, 0, 0.00, 'pending', '2026-04-09 17:14:45', '2026-04-14 05:14:45', NULL, 'Data pengiriman contoh untuk simulasi operasional Indonesia.', '2026-04-11 23:59:42', '2026-04-12 05:14:45', NULL),
(21, 'SXP-JKT-HQ-20260412-9KPQK', NULL, 1, NULL, NULL, 1, 1, 'galih', '081290001002', 'puro', 'tess', '081220000117', 'puri', 'regular', 1.00, 0.00, 26250.00, 0.00, 2500.00, 28750.00, 0, 0.00, 'unpaid', '2026-04-12 05:52:40', '2026-04-12 12:52:00', NULL, NULL, '2026-04-12 05:52:40', '2026-04-12 05:52:40', NULL),
(22, 'SXP-JKT-HQ-20260412-II7OR', NULL, 1, NULL, NULL, 2, 1, 'wdadwasa', '081290001002', 'wdasda', 'wawda', '081220000117', 'awdas', 'regular', 10.00, 0.00, 148092.00, 0.00, 2500.00, 150592.00, 0, 0.00, 'pending', '2026-04-12 06:18:02', '2026-04-12 13:17:00', NULL, NULL, '2026-04-12 06:18:02', '2026-04-12 06:18:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shipment_items`
--

CREATE TABLE `shipment_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shipment_id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `weight_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `length_cm` decimal(10,2) DEFAULT NULL,
  `width_cm` decimal(10,2) DEFAULT NULL,
  `height_cm` decimal(10,2) DEFAULT NULL,
  `declared_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipment_items`
--

INSERT INTO `shipment_items` (`id`, `shipment_id`, `item_name`, `quantity`, `weight_kg`, `length_cm`, `width_cm`, `height_cm`, `declared_value`, `description`, `created_at`, `updated_at`) VALUES
(96, 1, 'Suku Cadang Motor', 1, 2.80, 28.00, 22.00, 16.00, 700000.00, 'Komponen otomotif', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(97, 1, 'Batik Tulis', 2, 1.20, 38.00, 30.00, 10.00, 450000.00, 'Produk UMKM batik premium', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(98, 2, 'Dokumen Kontrak', 1, 0.40, 32.00, 24.00, 4.00, 150000.00, 'Berkas dokumen perusahaan', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(99, 2, 'Kosmetik Lokal', 3, 1.00, 24.00, 18.00, 12.00, 320000.00, 'Produk kecantikan UMKM', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(100, 3, 'Buku Pelajaran', 3, 2.10, 30.00, 24.00, 14.00, 280000.00, 'Paket buku sekolah', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(101, 3, 'Kosmetik Lokal', 3, 1.00, 24.00, 18.00, 12.00, 320000.00, 'Produk kecantikan UMKM', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(102, 4, 'Kosmetik Lokal', 1, 1.00, 24.00, 18.00, 12.00, 320000.00, 'Produk kecantikan UMKM', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(103, 5, 'Suku Cadang Motor', 2, 2.80, 28.00, 22.00, 16.00, 700000.00, 'Komponen otomotif', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(104, 6, 'Dokumen Kontrak', 3, 0.40, 32.00, 24.00, 4.00, 150000.00, 'Berkas dokumen perusahaan', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(105, 6, 'Peralatan Dapur', 2, 3.20, 42.00, 32.00, 24.00, 550000.00, 'Paket perlengkapan dapur', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(106, 7, 'Kosmetik Lokal', 2, 1.00, 24.00, 18.00, 12.00, 320000.00, 'Produk kecantikan UMKM', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(107, 8, 'Buku Pelajaran', 2, 2.10, 30.00, 24.00, 14.00, 280000.00, 'Paket buku sekolah', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(108, 8, 'Peralatan Dapur', 2, 3.20, 42.00, 32.00, 24.00, 550000.00, 'Paket perlengkapan dapur', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(109, 9, 'Dokumen Kontrak', 3, 0.40, 32.00, 24.00, 4.00, 150000.00, 'Berkas dokumen perusahaan', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(110, 9, 'Kosmetik Lokal', 3, 1.00, 24.00, 18.00, 12.00, 320000.00, 'Produk kecantikan UMKM', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(111, 10, 'Peralatan Dapur', 3, 3.20, 42.00, 32.00, 24.00, 550000.00, 'Paket perlengkapan dapur', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(112, 11, 'Batik Tulis', 2, 1.20, 38.00, 30.00, 10.00, 450000.00, 'Produk UMKM batik premium', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(113, 11, 'Peralatan Dapur', 1, 3.20, 42.00, 32.00, 24.00, 550000.00, 'Paket perlengkapan dapur', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(114, 12, 'Peralatan Dapur', 2, 3.20, 42.00, 32.00, 24.00, 550000.00, 'Paket perlengkapan dapur', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(115, 12, 'Buku Pelajaran', 1, 2.10, 30.00, 24.00, 14.00, 280000.00, 'Paket buku sekolah', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(116, 13, 'Kosmetik Lokal', 2, 1.00, 24.00, 18.00, 12.00, 320000.00, 'Produk kecantikan UMKM', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(117, 13, 'Kosmetik Lokal', 2, 1.00, 24.00, 18.00, 12.00, 320000.00, 'Produk kecantikan UMKM', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(118, 14, 'Peralatan Dapur', 2, 3.20, 42.00, 32.00, 24.00, 550000.00, 'Paket perlengkapan dapur', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(119, 14, 'Dokumen Kontrak', 2, 0.40, 32.00, 24.00, 4.00, 150000.00, 'Berkas dokumen perusahaan', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(120, 15, 'Suku Cadang Motor', 2, 2.80, 28.00, 22.00, 16.00, 700000.00, 'Komponen otomotif', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(121, 15, 'Dokumen Kontrak', 2, 0.40, 32.00, 24.00, 4.00, 150000.00, 'Berkas dokumen perusahaan', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(122, 16, 'Peralatan Dapur', 1, 3.20, 42.00, 32.00, 24.00, 550000.00, 'Paket perlengkapan dapur', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(123, 17, 'Batik Tulis', 3, 1.20, 38.00, 30.00, 10.00, 450000.00, 'Produk UMKM batik premium', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(124, 18, 'Dokumen Kontrak', 2, 0.40, 32.00, 24.00, 4.00, 150000.00, 'Berkas dokumen perusahaan', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(125, 19, 'Suku Cadang Motor', 1, 2.80, 28.00, 22.00, 16.00, 700000.00, 'Komponen otomotif', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(126, 19, 'Buku Pelajaran', 1, 2.10, 30.00, 24.00, 14.00, 280000.00, 'Paket buku sekolah', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(127, 20, 'Peralatan Dapur', 3, 3.20, 42.00, 32.00, 24.00, 550000.00, 'Paket perlengkapan dapur', '2026-04-12 05:14:45', '2026-04-12 05:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `shipment_statuses`
--

CREATE TABLE `shipment_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(40) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sequence` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `is_final` tinyint(1) NOT NULL DEFAULT 0,
  `badge_color` varchar(30) NOT NULL DEFAULT 'slate',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipment_statuses`
--

INSERT INTO `shipment_statuses` (`id`, `code`, `name`, `sequence`, `is_final`, `badge_color`, `created_at`, `updated_at`) VALUES
(1, 'pending', 'Menunggu Proses', 1, 0, 'amber', '2026-04-11 23:59:37', '2026-04-11 23:59:37'),
(2, 'in_transit', 'Dalam Perjalanan', 2, 0, 'blue', '2026-04-11 23:59:37', '2026-04-11 23:59:37'),
(3, 'out_for_delivery', 'Siap Diantar', 3, 0, 'indigo', '2026-04-11 23:59:37', '2026-04-11 23:59:37'),
(4, 'delivered', 'Terkirim', 4, 1, 'green', '2026-04-11 23:59:37', '2026-04-11 23:59:37'),
(5, 'cancelled', 'Dibatalkan', 5, 1, 'red', '2026-04-11 23:59:37', '2026-04-11 23:59:37'),
(6, 'returned', 'Dikembalikan', 6, 1, 'orange', '2026-04-11 23:59:37', '2026-04-11 23:59:37');

-- --------------------------------------------------------

--
-- Table structure for table `shipment_trackings`
--

CREATE TABLE `shipment_trackings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shipment_id` bigint(20) UNSIGNED NOT NULL,
  `status_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `event_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipment_trackings`
--

INSERT INTO `shipment_trackings` (`id`, `shipment_id`, `status_id`, `created_by`, `location`, `notes`, `event_at`, `created_at`, `updated_at`) VALUES
(142, 1, 1, 24, 'Surabaya', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 13:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(143, 1, 2, 24, 'Surabaya', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-11 17:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(144, 1, 3, 24, 'Surabaya', 'Paket sedang dibawa kurir untuk diantar ke alamat penerima.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(145, 1, 4, 24, 'Semarang', 'Paket telah diterima oleh penerima dalam kondisi baik.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(146, 2, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 13:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(147, 2, 2, 23, 'Jakarta', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-11 17:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(148, 2, 3, 23, 'Jakarta', 'Paket sedang dibawa kurir untuk diantar ke alamat penerima.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(149, 2, 4, 23, 'Makassar', 'Paket telah diterima oleh penerima dalam kondisi baik.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(150, 3, 1, 24, 'Surabaya', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 13:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(151, 3, 2, 24, 'Surabaya', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-11 17:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(152, 3, 3, 24, 'Surabaya', 'Paket sedang dibawa kurir untuk diantar ke alamat penerima.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(153, 3, 4, 24, 'Denpasar', 'Paket telah diterima oleh penerima dalam kondisi baik.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(154, 4, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 13:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(155, 4, 2, 23, 'Jakarta', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-11 17:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(156, 4, 3, 23, 'Jakarta', 'Paket sedang dibawa kurir untuk diantar ke alamat penerima.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(157, 4, 4, 23, 'Jakarta', 'Paket telah diterima oleh penerima dalam kondisi baik.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(158, 5, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 13:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(159, 5, 2, 23, 'Jakarta', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-11 17:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(160, 5, 3, 23, 'Jakarta', 'Paket sedang dibawa kurir untuk diantar ke alamat penerima.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(161, 5, 4, 23, 'Jakarta', 'Paket telah diterima oleh penerima dalam kondisi baik.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(162, 6, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(163, 6, 2, 23, 'Jakarta', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(164, 7, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(165, 7, 2, 23, 'Jakarta', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(166, 8, 1, 24, 'Surabaya', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(167, 8, 2, 24, 'Surabaya', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(168, 9, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(169, 9, 2, 23, 'Jakarta', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(170, 10, 1, 24, 'Surabaya', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(171, 10, 2, 24, 'Surabaya', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(172, 11, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(173, 11, 2, 23, 'Jakarta', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(174, 12, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(175, 12, 2, 23, 'Jakarta', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(176, 13, 1, 25, 'Bandung', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(177, 13, 2, 25, 'Bandung', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-12 01:14:44', '2026-04-12 05:14:44', '2026-04-12 05:14:44'),
(178, 14, 1, 24, 'Surabaya', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-12 01:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(179, 15, 1, 25, 'Bandung', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-12 01:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(180, 16, 1, 24, 'Surabaya', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-12 01:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(181, 17, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-12 01:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(182, 18, 1, 23, 'Jakarta', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(183, 18, 5, 23, 'Jakarta', 'Pengiriman dibatalkan sesuai permintaan pengirim.', '2026-04-12 01:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(184, 19, 1, 25, 'Bandung', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 21:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(185, 19, 5, 25, 'Bandung', 'Pengiriman dibatalkan sesuai permintaan pengirim.', '2026-04-12 01:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(186, 20, 1, 25, 'Bandung', 'Paket telah diterima di loket dan menunggu proses sortir.', '2026-04-11 17:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(187, 20, 2, 25, 'Bandung', 'Paket sedang dalam perjalanan menuju kota tujuan.', '2026-04-11 21:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(188, 20, 6, 25, 'Bandung', 'Paket dikembalikan ke pengirim karena kendala pengantaran.', '2026-04-12 01:14:45', '2026-04-12 05:14:45', '2026-04-12 05:14:45'),
(189, 21, 1, 26, 'Jakarta', 'Shipment dibuat dan menunggu proses pickup.', '2026-04-12 05:52:40', '2026-04-12 05:52:40', '2026-04-12 05:52:40'),
(190, 22, 1, 26, 'Jakarta', 'Shipment dibuat dan menunggu proses pickup.', '2026-04-12 06:18:02', '2026-04-12 06:18:02', '2026-04-12 06:18:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir','courier','manager','customer') NOT NULL DEFAULT 'customer',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `photo`, `is_active`, `email_verified_at`, `last_login_at`, `last_activity_at`, `last_login_ip`, `password`, `role`, `permissions`, `branch_id`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(21, 'Admin Sistem', 'admin@kondangekspedisi.test', NULL, NULL, NULL, 1, '2026-04-12 05:14:40', NULL, NULL, NULL, '$2y$12$uLm2qNZEQqA/nZ6WynV6z.rYBMkkH0XpOOlArA94qpiY56ZerrW.G', 'admin', NULL, 1, NULL, '2026-04-11 23:59:37', '2026-04-12 05:14:40', NULL),
(22, 'Kasir Jakarta', 'kasir.jakarta@kondangekspedisi.test', NULL, NULL, NULL, 1, '2026-04-12 05:14:40', NULL, NULL, NULL, '$2y$12$hghjHVKL3gmJfhl61kkS6.WCojFfbhQktoQZLppPJga0FThCWUJGy', 'kasir', NULL, 1, NULL, '2026-04-11 23:59:37', '2026-04-12 05:14:40', NULL),
(23, 'Kurir Jakarta', 'kurir.jakarta@kondangekspedisi.test', NULL, NULL, NULL, 1, '2026-04-12 05:14:40', NULL, NULL, NULL, '$2y$12$8Q0UjM3eJae8TMfZOCc5pevYt54q1mabxP5LqikdqQjIU9FSu/2Pi', 'courier', NULL, 1, NULL, '2026-04-11 23:59:38', '2026-04-12 05:14:40', NULL),
(24, 'Kurir Surabaya', 'kurir.surabaya@kondangekspedisi.test', NULL, NULL, NULL, 1, '2026-04-12 05:14:41', NULL, NULL, NULL, '$2y$12$iqc/cDXFfbzC.rU8tQ0PouiVrX3b3cK9B.UD.hZWQ3op98N8qcdc.', 'courier', NULL, 2, NULL, '2026-04-11 23:59:38', '2026-04-12 05:14:41', NULL),
(25, 'Kurir Bandung', 'kurir.bandung@kondangekspedisi.test', NULL, NULL, NULL, 1, '2026-04-12 05:14:41', NULL, NULL, NULL, '$2y$12$T3Ay1flKxchhDLl9KQ0dKO/5Fufi9LfN2do5AeLSXmxbPoIgvPMn6', 'courier', NULL, 3, NULL, '2026-04-11 23:59:38', '2026-04-12 05:14:41', NULL),
(26, 'Manajer Operasional', 'wisnubgalih4@gmail.com', NULL, NULL, NULL, 1, '2026-04-12 05:14:41', NULL, NULL, NULL, '$2y$12$8heJv8OnQR2uXQdNxsVFGO8D0siALscsI2aLgV15ypaq.1hGZOV6K', 'manager', NULL, 1, NULL, '2026-04-11 23:59:38', '2026-04-12 05:14:41', NULL),
(27, 'Pelanggan Demo', 'pelanggan.demo@kondangekspedisi.test', NULL, NULL, NULL, 1, '2026-04-12 05:14:41', NULL, NULL, NULL, '$2y$12$eYT1XKQ5y878TqB/WemyYeDkAZ0Ne3HSF4Y343u6oEv32rhqlXc.S', 'customer', NULL, 1, NULL, '2026-04-11 23:59:39', '2026-04-12 05:14:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `plate_number` varchar(30) NOT NULL,
  `type` enum('motorcycle','car','van','truck') NOT NULL,
  `capacity_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('available','in_use','maintenance','inactive') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `branch_id`, `name`, `plate_number`, `type`, `capacity_kg`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Motor Kurir Jakarta 01', 'B 4101 KDX', 'motorcycle', 80.00, 'available', '2026-04-11 23:59:41', '2026-04-11 23:59:41', NULL),
(2, 1, 'Van Operasional Jakarta 01', 'B 9201 KDX', 'van', 1000.00, 'available', '2026-04-11 23:59:41', '2026-04-11 23:59:41', NULL),
(3, 2, 'Motor Kurir Surabaya 01', 'L 5102 KDX', 'motorcycle', 90.00, 'available', '2026-04-11 23:59:41', '2026-04-11 23:59:41', NULL),
(4, 2, 'Mobil Box Surabaya 01', 'L 9302 KDX', 'truck', 1800.00, 'available', '2026-04-11 23:59:41', '2026-04-11 23:59:41', NULL),
(5, 3, 'Motor Kurir Bandung 01', 'D 6203 KDX', 'motorcycle', 85.00, 'available', '2026-04-11 23:59:41', '2026-04-11 23:59:41', NULL),
(6, 3, 'Van Operasional Bandung 01', 'D 9403 KDX', 'van', 950.00, 'available', '2026-04-11 23:59:41', '2026-04-11 23:59:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `multiplier` decimal(6,2) NOT NULL DEFAULT 1.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`id`, `code`, `name`, `description`, `multiplier`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'JABODETABEK', 'Jabodetabek', 'Area Jakarta, Bogor, Depok, Tangerang, dan Bekasi', 1.00, 1, '2026-04-11 23:59:37', '2026-04-11 23:59:37', NULL),
(2, 'JAWA-BALI', 'Jawa dan Bali', 'Pengiriman antar kota utama di Jawa dan Bali', 1.80, 1, '2026-04-11 23:59:37', '2026-04-11 23:59:37', NULL),
(3, 'SUMAPA', 'Sumatera, Kalimantan, Sulawesi, Papua', 'Pengiriman antarpulau di luar Jawa dan Bali', 2.60, 1, '2026-04-11 23:59:37', '2026-04-11 23:59:37', NULL),
(4, 'TMP-SOFT-1', 'Tmp Soft', NULL, 1.00, 1, '2026-04-12 00:36:45', '2026-04-12 01:19:04', '2026-04-12 01:19:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_tasks`
--
ALTER TABLE `admin_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_tasks_assigned_to_foreign` (`assigned_to`),
  ADD KEY `admin_tasks_created_by_foreign` (`created_by`),
  ADD KEY `admin_tasks_status_index` (`status`),
  ADD KEY `admin_tasks_task_type_index` (`task_type`),
  ADD KEY `admin_tasks_priority_index` (`priority`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_actor_id_foreign` (`actor_id`),
  ADD KEY `audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  ADD KEY `audit_logs_action_created_at_index` (`action`,`created_at`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branches_code_unique` (`code`),
  ADD KEY `branches_zone_id_foreign` (`zone_id`);

--
-- Indexes for table `branch_balances`
--
ALTER TABLE `branch_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_balances_branch_id_balance_date_unique` (`branch_id`,`balance_date`),
  ADD KEY `branch_balances_recorded_by_foreign` (`recorded_by`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `courier_earnings`
--
ALTER TABLE `courier_earnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courier_earnings_shipment_id_foreign` (`shipment_id`),
  ADD KEY `courier_earnings_courier_id_earning_date_index` (`courier_id`,`earning_date`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_phone_unique` (`phone`),
  ADD UNIQUE KEY `customers_email_unique` (`email`),
  ADD UNIQUE KEY `customers_user_id_unique` (`user_id`);

--
-- Indexes for table `error_logs`
--
ALTER TABLE `error_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `error_logs_user_id_foreign` (`user_id`),
  ADD KEY `error_logs_severity_created_at_index` (`severity`,`created_at`),
  ADD KEY `error_logs_module_error_type_index` (`module`,`error_type`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `integration_statuses`
--
ALTER TABLE `integration_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `integration_statuses_service_name_unique` (`service_name`),
  ADD KEY `integration_statuses_status_index` (`status`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `landing_page_contents`
--
ALTER TABLE `landing_page_contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `landing_page_contents_section_is_active_sort_order_index` (`section`,`is_active`,`sort_order`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_midtrans_order_id_unique` (`midtrans_order_id`),
  ADD KEY `payments_customer_id_foreign` (`customer_id`),
  ADD KEY `payments_processed_by_foreign` (`processed_by`),
  ADD KEY `payments_shipment_id_status_index` (`shipment_id`,`status`),
  ADD KEY `payments_midtrans_transaction_id_index` (`midtrans_transaction_id`);

--
-- Indexes for table `rate_cards`
--
ALTER TABLE `rate_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rate_cards_zone_id_service_type_is_active_index` (`zone_id`,`service_type`,`is_active`),
  ADD KEY `rate_cards_destination_zone_id_foreign` (`destination_zone_id`),
  ADD KEY `rate_cards_route_service_active_idx` (`origin_zone_id`,`destination_zone_id`,`service_type`,`is_active`);

--
-- Indexes for table `rate_card_approvals`
--
ALTER TABLE `rate_card_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rate_card_approvals_rate_card_id_foreign` (`rate_card_id`),
  ADD KEY `rate_card_approvals_approved_by_foreign` (`approved_by`),
  ADD KEY `rate_card_approvals_status_index` (`status`),
  ADD KEY `rate_card_approvals_requested_by_index` (`requested_by`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reports_generated_by_foreign` (`generated_by`),
  ADD KEY `reports_report_type_index` (`report_type`),
  ADD KEY `reports_status_index` (`status`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipments_tracking_number_unique` (`tracking_number`),
  ADD KEY `shipments_customer_id_foreign` (`customer_id`),
  ADD KEY `shipments_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `shipments_zone_id_foreign` (`zone_id`),
  ADD KEY `shipments_status_id_foreign` (`status_id`),
  ADD KEY `shipments_branch_id_status_id_index` (`branch_id`,`status_id`),
  ADD KEY `shipments_courier_id_status_id_index` (`courier_id`,`status_id`),
  ADD KEY `shipments_payment_status_created_at_index` (`payment_status`,`created_at`);

--
-- Indexes for table `shipment_items`
--
ALTER TABLE `shipment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipment_items_shipment_id_index` (`shipment_id`);

--
-- Indexes for table `shipment_statuses`
--
ALTER TABLE `shipment_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipment_statuses_code_unique` (`code`);

--
-- Indexes for table `shipment_trackings`
--
ALTER TABLE `shipment_trackings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipment_trackings_status_id_foreign` (`status_id`),
  ADD KEY `shipment_trackings_created_by_foreign` (`created_by`),
  ADD KEY `shipment_trackings_shipment_id_event_at_index` (`shipment_id`,`event_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_branch_id_foreign` (`branch_id`),
  ADD KEY `users_role_is_active_index` (`role`,`is_active`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicles_plate_number_unique` (`plate_number`),
  ADD KEY `vehicles_branch_id_status_index` (`branch_id`,`status`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zones_code_unique` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_tasks`
--
ALTER TABLE `admin_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `branch_balances`
--
ALTER TABLE `branch_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courier_earnings`
--
ALTER TABLE `courier_earnings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `error_logs`
--
ALTER TABLE `error_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `integration_statuses`
--
ALTER TABLE `integration_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `landing_page_contents`
--
ALTER TABLE `landing_page_contents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `rate_cards`
--
ALTER TABLE `rate_cards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `rate_card_approvals`
--
ALTER TABLE `rate_card_approvals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `shipment_items`
--
ALTER TABLE `shipment_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `shipment_statuses`
--
ALTER TABLE `shipment_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `shipment_trackings`
--
ALTER TABLE `shipment_trackings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_tasks`
--
ALTER TABLE `admin_tasks`
  ADD CONSTRAINT `admin_tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `admin_tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `branch_balances`
--
ALTER TABLE `branch_balances`
  ADD CONSTRAINT `branch_balances_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_balances_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `courier_earnings`
--
ALTER TABLE `courier_earnings`
  ADD CONSTRAINT `courier_earnings_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courier_earnings_shipment_id_foreign` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `error_logs`
--
ALTER TABLE `error_logs`
  ADD CONSTRAINT `error_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_shipment_id_foreign` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rate_cards`
--
ALTER TABLE `rate_cards`
  ADD CONSTRAINT `rate_cards_destination_zone_id_foreign` FOREIGN KEY (`destination_zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rate_cards_origin_zone_id_foreign` FOREIGN KEY (`origin_zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rate_cards_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rate_card_approvals`
--
ALTER TABLE `rate_card_approvals`
  ADD CONSTRAINT `rate_card_approvals_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rate_card_approvals_rate_card_id_foreign` FOREIGN KEY (`rate_card_id`) REFERENCES `rate_cards` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rate_card_approvals_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipments_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `shipment_statuses` (`id`),
  ADD CONSTRAINT `shipments_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipments_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `shipment_items`
--
ALTER TABLE `shipment_items`
  ADD CONSTRAINT `shipment_items_shipment_id_foreign` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipment_trackings`
--
ALTER TABLE `shipment_trackings`
  ADD CONSTRAINT `shipment_trackings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_trackings_shipment_id_foreign` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipment_trackings_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `shipment_statuses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
