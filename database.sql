-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2024 at 07:38 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dsr_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_groups`
--

CREATE TABLE `account_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `note_no` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_groups`
--

INSERT INTO `account_groups` (`id`, `account_group_id`, `name`, `note_no`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Expense', NULL, NULL, '2023-12-26 19:35:29', '2023-12-26 19:35:29'),
(2, NULL, 'Sales Revenue', NULL, NULL, '2023-12-26 20:41:35', '2024-05-20 07:49:51'),
(3, NULL, 'Assets', NULL, NULL, '2023-12-26 20:46:14', '2023-12-26 20:48:05'),
(4, NULL, 'Equity', NULL, NULL, '2023-12-26 20:46:53', '2023-12-26 20:46:53'),
(5, NULL, 'Liabilities', NULL, NULL, '2023-12-26 20:47:38', '2023-12-26 20:47:38'),
(6, 3, 'Current Assets', NULL, NULL, '2024-05-18 15:22:53', '2024-05-18 15:22:53'),
(7, 3, 'Fixed Assets', NULL, NULL, '2024-05-18 15:23:10', '2024-05-18 16:46:53'),
(8, 6, 'Cash and Cash Equivalents', NULL, NULL, '2024-05-18 15:23:32', '2024-05-18 15:23:32'),
(9, 6, 'Accounts Receivable', NULL, NULL, '2024-05-18 15:23:46', '2024-05-18 15:23:46'),
(10, 6, 'Inventory', NULL, NULL, '2024-05-18 15:23:59', '2024-05-18 15:23:59'),
(11, 7, 'Property, Plant, and Equipment (Net)', NULL, NULL, '2024-05-18 15:24:19', '2024-05-18 15:24:19'),
(12, 7, 'Intangible Assets', NULL, NULL, '2024-05-18 15:24:31', '2024-05-18 15:24:31'),
(13, 7, 'Other Non-Current Assets', NULL, NULL, '2024-05-18 15:24:43', '2024-05-18 15:24:43'),
(14, 5, 'Current Liabilities', NULL, NULL, '2024-05-18 15:25:10', '2024-05-18 15:25:10'),
(15, 5, 'Non-Current Liabilities', NULL, NULL, '2024-05-18 15:25:20', '2024-05-18 15:25:20'),
(16, 14, 'Accounts Payable', NULL, NULL, '2024-05-18 15:25:43', '2024-05-18 15:25:43'),
(17, 14, 'Short-Term Debt', NULL, NULL, '2024-05-18 15:25:54', '2024-05-18 15:25:54'),
(18, 14, 'Accrued Liabilities', NULL, NULL, '2024-05-18 15:26:05', '2024-05-18 15:26:05'),
(19, 14, 'Other Current Liabilities', NULL, NULL, '2024-05-18 15:26:17', '2024-05-18 15:26:17'),
(20, 15, 'Long-Term Debt', NULL, NULL, '2024-05-18 15:26:30', '2024-05-18 15:26:30'),
(21, 15, 'Deferred Tax Liabilities', NULL, NULL, '2024-05-18 15:26:42', '2024-05-18 15:26:42'),
(22, 15, 'Other Non-Current Liabilities', NULL, NULL, '2024-05-18 15:26:52', '2024-05-18 15:26:52'),
(23, 4, 'Share Capital', NULL, NULL, '2024-05-18 15:27:06', '2024-05-18 16:22:20'),
(24, 4, 'Retained Earnings', NULL, NULL, '2024-05-18 15:27:18', '2024-05-18 15:27:18'),
(25, 2, 'Sales', NULL, NULL, '2024-05-18 16:04:15', '2024-05-18 17:49:26'),
(26, 1, 'Sales Returns and Allowances', NULL, NULL, '2024-05-18 16:04:40', '2024-05-20 08:04:28'),
(27, 1, 'Indirect Expenses', NULL, NULL, '2024-05-18 16:06:34', '2024-05-18 16:06:34'),
(28, 1, 'Direct Expense', NULL, NULL, '2024-05-18 16:06:51', '2024-05-18 16:06:51'),
(29, 27, 'Cost of Goods Sold (COGS)', NULL, NULL, '2024-05-18 16:08:01', '2024-05-18 16:08:01'),
(30, 1, 'Administrative Expenses', NULL, NULL, '2024-05-18 16:10:04', '2024-05-18 16:10:04'),
(31, 1, 'VAT Expenses', NULL, NULL, '2024-05-18 17:51:08', '2024-05-18 17:51:08'),
(32, 1, 'Selling and Distribution Expenses', NULL, NULL, '2024-05-18 17:59:09', '2024-05-18 17:59:09'),
(33, 1, 'Financial Expenses', NULL, NULL, '2024-05-18 18:00:54', '2024-05-18 18:00:54'),
(34, NULL, 'Other Income', NULL, NULL, '2024-05-18 18:02:58', '2024-05-18 18:02:58'),
(35, 1, 'Income Tax Expense', NULL, NULL, '2024-05-18 18:17:46', '2024-05-18 18:17:46');

-- --------------------------------------------------------

--
-- Table structure for table `account_heads`
--

CREATE TABLE `account_heads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_mode` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=none,1=bank,2=cash,3=Mobile banking',
  `name` varchar(255) NOT NULL,
  `bank_commission_percent` double(10,2) NOT NULL DEFAULT 0.00,
  `code` bigint(20) NOT NULL,
  `opening_balance` double(100,2) NOT NULL DEFAULT 0.00,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_heads`
--

INSERT INTO `account_heads` (`id`, `account_group_id`, `supplier_id`, `payment_mode`, `name`, `bank_commission_percent`, `code`, `opening_balance`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 8, NULL, 2, 'Cash', 0.00, 1001, 0.00, NULL, '2023-11-16 10:51:48', '2024-01-05 04:06:02'),
(2, 29, NULL, 0, 'Cost of Goods Sold', 0.00, 1002, 0.00, NULL, '2023-11-16 10:51:48', '2024-05-20 07:08:24'),
(5, 8, NULL, 3, 'bKash', 1.49, 1004, 0.00, NULL, '2023-11-18 07:21:37', '2023-12-30 20:55:05'),
(26, 8, NULL, 1, 'POS (UCB)', 1.80, 1008, 0.00, NULL, '2023-11-25 02:41:49', '2023-12-30 20:54:20'),
(27, 8, NULL, 1, 'POS (City Bank)', 3.50, 1009, 0.00, NULL, '2023-11-25 02:42:08', '2023-12-30 20:54:31'),
(28, 8, NULL, 1, 'POS (DBBL)', 1.50, 1010, 0.00, NULL, '2023-11-25 02:42:21', '2023-12-30 20:54:41'),
(29, 8, NULL, 1, 'POS (EBL)', 1.50, 1011, 0.00, NULL, '2023-11-25 02:42:36', '2023-12-30 20:54:52'),
(100, 10, NULL, 0, 'Product Purchase Utilize', 0.00, 1063, 0.00, NULL, '2023-12-26 21:16:00', '2024-05-18 15:39:04'),
(101, 8, NULL, 2, 'Petty Cash', 0.00, 1064, 0.00, NULL, '2023-12-26 21:54:40', '2023-12-26 21:54:40'),
(108, 24, NULL, 0, 'Owner\'s Equity', 0.00, 1071, 0.00, NULL, '2024-01-09 18:31:10', '2024-05-18 15:37:29'),
(113, 10, NULL, 0, 'Inventory', 0.00, 1072, 0.00, NULL, '2024-05-18 16:21:29', '2024-05-20 06:49:21'),
(114, 2, NULL, 0, 'Sales Revenue', 0.00, 1073, 0.00, NULL, '2024-05-20 07:02:53', '2024-05-20 07:02:53'),
(116, 26, NULL, 0, 'Sales Returns and Allowances', 0.00, 1075, 0.00, NULL, '2024-05-20 07:51:18', '2024-05-20 07:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1=active,0=inactive',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '1=Purchase,Sales',
  `supplier_id` bigint(20) DEFAULT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `commission_type` tinyint(4) NOT NULL COMMENT '1=Percent,2=Flat',
  `commission_percent` double(8,2) NOT NULL DEFAULT 0.00,
  `commission_base_amount` double(8,2) NOT NULL,
  `commission_amount` double(8,2) NOT NULL DEFAULT 0.00,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `distribution_orders`
--

CREATE TABLE `distribution_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '1 = Distribution Sales,2 = Distribution Damage Product return',
  `order_no` varchar(255) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `dsr_id` bigint(20) UNSIGNED NOT NULL,
  `total` double(100,2) NOT NULL,
  `paid` double(100,2) NOT NULL,
  `due` double(100,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `date` date NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `close_status` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `distribution_order_items`
--

CREATE TABLE `distribution_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `distribution_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_code` bigint(20) NOT NULL,
  `damage_quantity` double(50,2) NOT NULL DEFAULT 0.00,
  `damage_return_quantity` double(50,2) NOT NULL DEFAULT 0.00,
  `distribute_quantity` double(8,2) NOT NULL,
  `sale_quantity` double(8,2) NOT NULL DEFAULT 0.00,
  `return_quantity` double(8,2) NOT NULL DEFAULT 0.00,
  `purchase_unit_price` double(50,2) NOT NULL,
  `selling_unit_price` double(50,2) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `inventories`
--

CREATE TABLE `inventories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_code` bigint(20) UNSIGNED NOT NULL,
  `quantity` double(100,2) NOT NULL,
  `average_purchase_unit_price` double(50,2) NOT NULL,
  `last_purchase_unit_price` double(50,2) NOT NULL,
  `selling_price` double(50,2) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` int(11) NOT NULL COMMENT '1=purchase,2=sale',
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `purchase_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `distribution_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_code` bigint(20) UNSIGNED NOT NULL,
  `quantity` double(8,2) NOT NULL,
  `unit_price` double(50,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `date` date NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(11, '2023_10_01_224221_create_clients_table', 4),
(12, '2023_10_01_224251_create_products_table', 4),
(13, '2023_10_02_211846_create_purchase_orders_table', 4),
(14, '2023_10_02_211947_create_purchase_order_items_table', 4),
(15, '2023_10_02_212001_create_inventories_table', 4),
(16, '2023_10_02_212006_create_inventory_logs_table', 4),
(17, '2023_11_15_164827_create_units_table', 5),
(20, '2023_11_16_163517_create_transactions_table', 6),
(21, '2023_11_16_163621_create_account_heads_table', 6),
(22, '2023_11_18_161905_create_vouchers_table', 7),
(25, '2023_12_25_185927_create_account_groups_table', 9),
(28, '2024_01_01_160140_create_permission_tables', 10),
(29, '2024_05_19_003643_create_brands_table', 11),
(30, '2024_05_19_233039_create_distribution_orders_table', 12),
(31, '2024_05_19_233100_create_distribution_order_items_table', 12),
(32, '2024_05_19_233039_create_sale_orders_table', 13),
(33, '2024_05_19_233100_create_sale_order_items_table', 13),
(34, '2024_05_30_222739_create_commissions_table', 14);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_permissions`
--

INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) VALUES
(13, 'App\\Models\\User', 2),
(13, 'App\\Models\\User', 3),
(13, 'App\\Models\\User', 5),
(14, 'App\\Models\\User', 2),
(14, 'App\\Models\\User', 3),
(14, 'App\\Models\\User', 5),
(15, 'App\\Models\\User', 2),
(15, 'App\\Models\\User', 3),
(15, 'App\\Models\\User', 5),
(16, 'App\\Models\\User', 2),
(16, 'App\\Models\\User', 3),
(16, 'App\\Models\\User', 5),
(40, 'App\\Models\\User', 1),
(40, 'App\\Models\\User', 2),
(40, 'App\\Models\\User', 3),
(40, 'App\\Models\\User', 5),
(42, 'App\\Models\\User', 2),
(42, 'App\\Models\\User', 3),
(42, 'App\\Models\\User', 5),
(46, 'App\\Models\\User', 2),
(46, 'App\\Models\\User', 3),
(46, 'App\\Models\\User', 5),
(47, 'App\\Models\\User', 2),
(47, 'App\\Models\\User', 3),
(47, 'App\\Models\\User', 5),
(48, 'App\\Models\\User', 2),
(48, 'App\\Models\\User', 3),
(48, 'App\\Models\\User', 5),
(49, 'App\\Models\\User', 2),
(49, 'App\\Models\\User', 3),
(49, 'App\\Models\\User', 5),
(50, 'App\\Models\\User', 2),
(50, 'App\\Models\\User', 3),
(50, 'App\\Models\\User', 5),
(51, 'App\\Models\\User', 2),
(51, 'App\\Models\\User', 3),
(51, 'App\\Models\\User', 5),
(52, 'App\\Models\\User', 2),
(52, 'App\\Models\\User', 3),
(52, 'App\\Models\\User', 5),
(53, 'App\\Models\\User', 2),
(53, 'App\\Models\\User', 3),
(53, 'App\\Models\\User', 5),
(54, 'App\\Models\\User', 2),
(54, 'App\\Models\\User', 3),
(54, 'App\\Models\\User', 5),
(55, 'App\\Models\\User', 2),
(55, 'App\\Models\\User', 3),
(55, 'App\\Models\\User', 5),
(56, 'App\\Models\\User', 2),
(56, 'App\\Models\\User', 3),
(56, 'App\\Models\\User', 5),
(57, 'App\\Models\\User', 2),
(57, 'App\\Models\\User', 3),
(57, 'App\\Models\\User', 5),
(58, 'App\\Models\\User', 2),
(58, 'App\\Models\\User', 3),
(58, 'App\\Models\\User', 5),
(59, 'App\\Models\\User', 2),
(59, 'App\\Models\\User', 3),
(59, 'App\\Models\\User', 5),
(60, 'App\\Models\\User', 2),
(60, 'App\\Models\\User', 3),
(60, 'App\\Models\\User', 5),
(61, 'App\\Models\\User', 2),
(61, 'App\\Models\\User', 3),
(61, 'App\\Models\\User', 5),
(62, 'App\\Models\\User', 2),
(62, 'App\\Models\\User', 3),
(62, 'App\\Models\\User', 5),
(63, 'App\\Models\\User', 2),
(63, 'App\\Models\\User', 3),
(63, 'App\\Models\\User', 5),
(64, 'App\\Models\\User', 2),
(64, 'App\\Models\\User', 3),
(64, 'App\\Models\\User', 5),
(65, 'App\\Models\\User', 2),
(65, 'App\\Models\\User', 3),
(65, 'App\\Models\\User', 5),
(66, 'App\\Models\\User', 2),
(66, 'App\\Models\\User', 3),
(66, 'App\\Models\\User', 5),
(67, 'App\\Models\\User', 2),
(67, 'App\\Models\\User', 3),
(67, 'App\\Models\\User', 5),
(68, 'App\\Models\\User', 2),
(68, 'App\\Models\\User', 3),
(68, 'App\\Models\\User', 5),
(69, 'App\\Models\\User', 2),
(69, 'App\\Models\\User', 3),
(69, 'App\\Models\\User', 5),
(70, 'App\\Models\\User', 1),
(70, 'App\\Models\\User', 2),
(70, 'App\\Models\\User', 3),
(70, 'App\\Models\\User', 5),
(71, 'App\\Models\\User', 2),
(71, 'App\\Models\\User', 3),
(71, 'App\\Models\\User', 5),
(72, 'App\\Models\\User', 2),
(72, 'App\\Models\\User', 3),
(72, 'App\\Models\\User', 5),
(73, 'App\\Models\\User', 2),
(73, 'App\\Models\\User', 3),
(73, 'App\\Models\\User', 5),
(74, 'App\\Models\\User', 2),
(74, 'App\\Models\\User', 3),
(74, 'App\\Models\\User', 5),
(75, 'App\\Models\\User', 2),
(75, 'App\\Models\\User', 3),
(75, 'App\\Models\\User', 5),
(76, 'App\\Models\\User', 2),
(76, 'App\\Models\\User', 3),
(76, 'App\\Models\\User', 5),
(77, 'App\\Models\\User', 2),
(77, 'App\\Models\\User', 3),
(77, 'App\\Models\\User', 5),
(78, 'App\\Models\\User', 2),
(78, 'App\\Models\\User', 3),
(78, 'App\\Models\\User', 5),
(79, 'App\\Models\\User', 2),
(79, 'App\\Models\\User', 3),
(79, 'App\\Models\\User', 5),
(80, 'App\\Models\\User', 2),
(80, 'App\\Models\\User', 3),
(80, 'App\\Models\\User', 5),
(81, 'App\\Models\\User', 2),
(81, 'App\\Models\\User', 3),
(81, 'App\\Models\\User', 5),
(82, 'App\\Models\\User', 2),
(82, 'App\\Models\\User', 3),
(82, 'App\\Models\\User', 5),
(83, 'App\\Models\\User', 2),
(83, 'App\\Models\\User', 3),
(83, 'App\\Models\\User', 5),
(84, 'App\\Models\\User', 2),
(84, 'App\\Models\\User', 3),
(84, 'App\\Models\\User', 5),
(85, 'App\\Models\\User', 2),
(85, 'App\\Models\\User', 3),
(85, 'App\\Models\\User', 5),
(86, 'App\\Models\\User', 2),
(86, 'App\\Models\\User', 3),
(86, 'App\\Models\\User', 5),
(87, 'App\\Models\\User', 2),
(87, 'App\\Models\\User', 3),
(87, 'App\\Models\\User', 5),
(88, 'App\\Models\\User', 2),
(88, 'App\\Models\\User', 3),
(88, 'App\\Models\\User', 5),
(89, 'App\\Models\\User', 2),
(89, 'App\\Models\\User', 3),
(89, 'App\\Models\\User', 5),
(90, 'App\\Models\\User', 2),
(90, 'App\\Models\\User', 3),
(90, 'App\\Models\\User', 5),
(91, 'App\\Models\\User', 2),
(91, 'App\\Models\\User', 3),
(91, 'App\\Models\\User', 5),
(92, 'App\\Models\\User', 2),
(92, 'App\\Models\\User', 3),
(92, 'App\\Models\\User', 5),
(93, 'App\\Models\\User', 2),
(93, 'App\\Models\\User', 3),
(93, 'App\\Models\\User', 5),
(94, 'App\\Models\\User', 2),
(94, 'App\\Models\\User', 3),
(94, 'App\\Models\\User', 5),
(105, 'App\\Models\\User', 2),
(105, 'App\\Models\\User', 3),
(105, 'App\\Models\\User', 5),
(107, 'App\\Models\\User', 3),
(107, 'App\\Models\\User', 5),
(108, 'App\\Models\\User', 3),
(108, 'App\\Models\\User', 5),
(109, 'App\\Models\\User', 3),
(109, 'App\\Models\\User', 5),
(110, 'App\\Models\\User', 3),
(110, 'App\\Models\\User', 5),
(111, 'App\\Models\\User', 3),
(111, 'App\\Models\\User', 5),
(112, 'App\\Models\\User', 3),
(112, 'App\\Models\\User', 5),
(113, 'App\\Models\\User', 3),
(113, 'App\\Models\\User', 5),
(114, 'App\\Models\\User', 3),
(114, 'App\\Models\\User', 5),
(115, 'App\\Models\\User', 3),
(115, 'App\\Models\\User', 5),
(116, 'App\\Models\\User', 3),
(116, 'App\\Models\\User', 5),
(117, 'App\\Models\\User', 3),
(117, 'App\\Models\\User', 5),
(118, 'App\\Models\\User', 3),
(118, 'App\\Models\\User', 5),
(119, 'App\\Models\\User', 3),
(119, 'App\\Models\\User', 5),
(120, 'App\\Models\\User', 3),
(120, 'App\\Models\\User', 5),
(121, 'App\\Models\\User', 3),
(121, 'App\\Models\\User', 5),
(122, 'App\\Models\\User', 3),
(122, 'App\\Models\\User', 5),
(123, 'App\\Models\\User', 3),
(123, 'App\\Models\\User', 5),
(124, 'App\\Models\\User', 3),
(124, 'App\\Models\\User', 5),
(125, 'App\\Models\\User', 3),
(125, 'App\\Models\\User', 5),
(126, 'App\\Models\\User', 3),
(126, 'App\\Models\\User', 5),
(127, 'App\\Models\\User', 3),
(127, 'App\\Models\\User', 5),
(128, 'App\\Models\\User', 3),
(128, 'App\\Models\\User', 5),
(129, 'App\\Models\\User', 3),
(129, 'App\\Models\\User', 5),
(130, 'App\\Models\\User', 3),
(130, 'App\\Models\\User', 5),
(131, 'App\\Models\\User', 3),
(131, 'App\\Models\\User', 5),
(132, 'App\\Models\\User', 3),
(132, 'App\\Models\\User', 5),
(133, 'App\\Models\\User', 3),
(133, 'App\\Models\\User', 5),
(134, 'App\\Models\\User', 3),
(134, 'App\\Models\\User', 5),
(135, 'App\\Models\\User', 3),
(135, 'App\\Models\\User', 5),
(136, 'App\\Models\\User', 3),
(136, 'App\\Models\\User', 5),
(137, 'App\\Models\\User', 3),
(137, 'App\\Models\\User', 5),
(138, 'App\\Models\\User', 3),
(138, 'App\\Models\\User', 5);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('ctashiqkhan@gmail.com', '$2y$10$1Usb0JwtJyy4VDI/x27O7.SDcIVb5FeYzWmpCPYfeReD.HgzuKXj.', '2023-12-25 19:05:34');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `parent_id`, `sort`, `guard_name`, `created_at`, `updated_at`) VALUES
(13, 'user', NULL, 2, 'web', '2024-01-01 12:19:23', '2024-01-01 12:19:23'),
(14, 'user_create', 13, NULL, 'web', '2024-01-01 12:19:23', '2024-01-01 12:19:23'),
(15, 'user_edit', 13, NULL, 'web', '2024-01-01 12:19:23', '2024-01-01 12:19:23'),
(16, 'user_delete', 13, NULL, 'web', '2024-01-01 12:19:23', '2024-01-01 12:19:23'),
(40, 'reports', NULL, 9, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(42, 'receipt_and_payment', 40, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(46, 'ledger', 40, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(47, 'trial_balance', 40, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(48, 'purchase_settings', NULL, 3, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(49, 'supplier', 48, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(50, 'supplier_create', 49, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(51, 'supplier_edit', 49, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(52, 'supplier_delete', 49, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(53, 'product_unit', 48, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(54, 'product_unit_create', 53, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(55, 'product_unit_edit', 53, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(56, 'product_unit_delete', 53, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(57, 'product', 48, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(58, 'product_create', 57, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(59, 'product_edit', 57, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(60, 'product_delete', 57, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(61, 'purchase', NULL, 4, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(62, 'purchase_list', 61, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(63, 'purchase_create', 61, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(64, 'purchase_edit', 62, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(65, 'purchase_delete', 62, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(66, 'purchase_payment', 62, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(67, 'inventory', NULL, 5, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(68, 'inventory_log', 67, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(69, 'stock_utilized', 67, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(70, 'accounts', NULL, 8, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(71, 'account_group', 70, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(72, 'account_group_create', 71, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(73, 'account_group_edit', 71, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(74, 'account_group_delete', 71, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(75, 'account_head', 70, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(76, 'account_head_create', 75, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(77, 'account_head_edit', 75, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(78, 'account_head_delete', 75, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(79, 'payment_modes', 70, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(80, 'payment_modes_create', 79, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(81, 'payment_modes_edit', 79, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(82, 'payment_modes_delete', 79, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(83, 'journal_voucher', 70, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(84, 'journal_voucher_create', 83, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(85, 'journal_voucher_edit', 83, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(86, 'journal_voucher_delete', 83, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(87, 'payment_voucher', 70, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(88, 'payment_voucher_create', 87, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(89, 'payment_voucher_edit', 87, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(90, 'payment_voucher_delete', 87, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(91, 'receipt_voucher', 70, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(92, 'receipt_voucher_create', 91, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(93, 'receipt_voucher_edit', 91, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(94, 'receipt_voucher_delete', 91, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(105, 'dashboard', NULL, 1, 'web', '2024-01-01 12:19:23', '2024-01-01 12:19:23'),
(107, 'brand', 57, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(108, 'brand_create', 57, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(109, 'brand_edit', 57, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(110, 'brand_delete', 57, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(111, 'contra_voucher', 70, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(112, 'contra_voucher_create', 111, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(113, 'contra_voucher_edit', 111, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(114, 'contra_voucher_delete', 111, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(115, 'schedule_of_accounts', 40, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(116, 'income_statement', 40, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(117, 'balance_sheet', 40, NULL, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(118, 'distribution', NULL, 7, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(119, 'sr', 134, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(120, 'dsr_create', 119, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(121, 'dsr_edit', 119, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(122, 'dsr_delete', 119, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(123, 'distribution_list', 118, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(124, 'distribution_edit', 123, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(125, 'distribution_delete', 123, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(126, 'distribution_payment_receive', 123, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(127, 'distribution_create', 118, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(128, 'distribution_damage_product_return_list', 118, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(129, 'distribution_damage_product_return_edit', 128, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(130, 'distribution_damage_product_return_delete', 128, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(131, 'distribution_damage_product_return_payment', 128, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(132, 'distribution_damage_product_return_create', 118, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(133, 'distribution_day_close', 123, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(134, 'distribution_settings', NULL, 6, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(135, 'customer', 134, 2, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(136, 'customer_create', 135, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(137, 'customer_edit', 135, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24'),
(138, 'customer_delete', 135, 1, 'web', '2024-01-01 12:19:24', '2024-01-01 12:19:24');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `brand_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `purchase_price` double(50,2) NOT NULL DEFAULT 0.00,
  `selling_price` double(50,2) NOT NULL DEFAULT 0.00,
  `code` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1=active,0=inactive',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_no` varchar(255) DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `total` double(100,2) NOT NULL,
  `paid` double(100,2) NOT NULL,
  `due` double(100,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `date` date NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_code` bigint(20) NOT NULL,
  `quantity` double(8,2) NOT NULL,
  `product_unit_price` double(50,2) NOT NULL,
  `product_selling_unit_price` double(50,2) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_orders`
--

CREATE TABLE `sale_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `distribution_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_no` varchar(255) DEFAULT NULL,
  `company_id` bigint(20) DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Refer Supplier table type 3 data',
  `total` double(100,2) NOT NULL,
  `paid` double(100,2) NOT NULL,
  `due` double(100,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `date` date NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_order_items`
--

CREATE TABLE `sale_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `distribution_order_id` bigint(20) DEFAULT NULL,
  `sale_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_code` bigint(20) NOT NULL,
  `sale_quantity` double(8,2) NOT NULL DEFAULT 0.00,
  `purchase_unit_price` double(50,2) NOT NULL,
  `selling_unit_price` double(50,2) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=Supplier,2=DSR,3=Customer',
  `company_id` int(11) DEFAULT NULL COMMENT 'refer to this table',
  `name` varchar(255) NOT NULL,
  `shop_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile_no` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `opening_balance` double(100,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL COMMENT '1=active,0=inactive',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `payment_type_id` bigint(20) DEFAULT NULL,
  `payment_account_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cheque_no` varchar(255) DEFAULT NULL,
  `source_account_head_id` int(11) DEFAULT NULL,
  `target_account_head_id` int(11) DEFAULT NULL,
  `account_head_id` bigint(20) NOT NULL,
  `voucher_no_group_sl` bigint(20) NOT NULL,
  `voucher_no` varchar(255) NOT NULL,
  `voucher_type` tinyint(4) NOT NULL COMMENT '1=Journal Voucher,2=Payment Voucher,3=Collection Voucher',
  `transaction_type` tinyint(4) NOT NULL COMMENT '1=debit,2=credit',
  `amount` double(100,2) NOT NULL,
  `bank_commission` double(10,2) NOT NULL DEFAULT 0.00,
  `account_head_payee_depositor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `company_id` bigint(20) DEFAULT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `purchase_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `distribution_order_id` bigint(20) DEFAULT NULL,
  `sale_order_id` bigint(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` bigint(20) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1=active,0=inactive',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile_no` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `signature_photo` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `theme_mode` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `mobile_no`, `profile_photo`, `signature_photo`, `email_verified_at`, `password`, `status`, `theme_mode`, `remember_token`, `deleted_at`, `created_at`, `updated_at`) VALUES
(3, 'Ashik khan', 'ashik', 'ctashiqkhan@gmail.com', '01726979426', 'uploads/user/profile_photo/d129831c-1604-11ef-8cce-c85b76f9398e.jpg', 'uploads/user/signature_photo/10a25060-1be5-11ef-8db2-c85b76f9398e.png', NULL, '$2a$12$XHzJv/kLjtqwm.25XXDzoeowudu78CVNd7mx/NgSginYfyWX3Hgpu', 1, 1, '3aTXrGgsAw8OD5CRTP0YPtJJE0kZHXCSU985UdmLAZA0HMxInSZhvQVUpEll', NULL, '2023-11-02 21:17:49', '2024-06-05 03:43:52'),
(5, 'Admin', 'admin', NULL, NULL, NULL, NULL, NULL, '$2y$10$Ud4GBoWRx.XQtijl2g12aOEqCXpfB09zmpDDRMoD77xn0Pq7bC2ei', 1, 1, 'rUX0pQz7trZBleRvGjN16W4gTTDQcpx1Q5WlHOVDkzqfVTAFKO28xyxjCytX', NULL, '2024-05-20 20:41:57', '2024-05-20 20:43:14');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `payment_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_account_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cheque_no` varchar(255) DEFAULT NULL,
  `voucher_no_group_sl` bigint(20) NOT NULL,
  `voucher_no` varchar(255) NOT NULL,
  `voucher_type` tinyint(4) NOT NULL COMMENT '1=Journal Voucher,2=Payment Voucher,3=Collection Voucher',
  `amount` double(100,2) NOT NULL,
  `bank_commission` double(10,2) NOT NULL DEFAULT 0.00,
  `account_head_payee_depositor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `company_id` bigint(20) DEFAULT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `purchase_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `distribution_order_id` int(11) DEFAULT NULL,
  `sale_order_id` bigint(20) DEFAULT NULL,
  `inventory_log_id` int(11) DEFAULT NULL,
  `supporting_document` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `collection_receive_status` int(11) NOT NULL DEFAULT 0,
  `due_payment` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_groups`
--
ALTER TABLE `account_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account_heads`
--
ALTER TABLE `account_heads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `distribution_orders`
--
ALTER TABLE `distribution_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `distribution_order_items`
--
ALTER TABLE `distribution_order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventories`
--
ALTER TABLE `inventories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sale_orders`
--
ALTER TABLE `sale_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_order_items`
--
ALTER TABLE `sale_order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_mobile_no_unique` (`mobile_no`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_groups`
--
ALTER TABLE `account_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `account_heads`
--
ALTER TABLE `account_heads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distribution_orders`
--
ALTER TABLE `distribution_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distribution_order_items`
--
ALTER TABLE `distribution_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_orders`
--
ALTER TABLE `sale_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_order_items`
--
ALTER TABLE `sale_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
