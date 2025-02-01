-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 24, 2022 at 01:53 PM
-- Server version: 8.0.28-0ubuntu0.20.04.3
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `svs_v-2.1`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_give_coin_histories`
--

CREATE TABLE `admin_give_coin_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `wallet_id` bigint NOT NULL,
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_receive_token_transaction_histories`
--

CREATE TABLE `admin_receive_token_transaction_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `deposit_id` bigint NOT NULL,
  `unique_code` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `fees` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `to_address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_hash` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `slug`, `value`, `created_at`, `updated_at`) VALUES
(1, 'coin_price', '2.50', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(2, 'coin_name', 'SVS', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(3, 'app_title', 'SVS', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(4, 'maximum_withdrawal_daily', '3', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(5, 'mail_from', 'svs@svscoin.org', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(6, 'admin_coin_address', 'address', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(7, 'base_coin_type', 'BTC', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(8, 'minimum_withdrawal_amount', '0.005', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(9, 'maximum_withdrawal_amount', '12', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(10, 'maintenance_mode', 'no', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(11, 'logo', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(12, 'login_logo', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(13, 'landing_logo', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(14, 'favicon', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(15, 'copyright_text', 'Copyright@2020', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(16, 'pagination_count', '10', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(17, 'point_rate', '1', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(18, 'lang', 'en', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(19, 'company_name', 'Test Company', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(20, 'primary_email', 'test@email.com', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(21, 'sms_getway_name', 'twillo', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(22, 'twillo_secret_key', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(23, 'twillo_auth_token', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(24, 'twillo_number', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(25, 'ssl_verify', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(26, 'mail_driver', 'SMTP', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(27, 'mail_host', 'smtp.mailtrap.io', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(28, 'mail_port', '2525', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(29, 'mail_username', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(30, 'mail_password', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(31, 'mail_encryption', 'null', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(32, 'mail_from_address', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(33, 'braintree_client_token', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(34, 'braintree_environment', 'sandbox', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(35, 'braintree_merchant_id', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(36, 'braintree_public_key', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(37, 'braintree_private_key', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(38, 'clickatell_api_key', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(39, 'number_of_confirmation', '6', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(40, 'referral_commission_percentage', '10', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(41, 'referral_signup_reward', '10', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(42, 'max_affiliation_level', '10', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(43, 'coin_api_user', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(44, 'coin_api_pass', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(45, 'coin_api_host', 'test5', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(46, 'coin_api_port', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(47, 'send_fees_type', '1', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(48, 'send_fees_fixed', '0', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(49, 'send_fees_percentage', '0', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(50, 'max_send_limit', '0', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(51, 'deposit_time', '1', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(52, 'COIN_PAYMENT_PUBLIC_KEY', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(53, 'COIN_PAYMENT_PRIVATE_KEY', 'test', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(54, 'COIN_PAYMENT_CURRENCY', 'BTC', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(55, 'ipn_merchant_id', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(56, 'ipn_secret', '', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(57, 'payment_method_coin_payment', '1', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(58, 'payment_method_bank_deposit', '1', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(59, 'membership_bonus_type', '1', '2022-03-24 07:47:33', '2022-03-24 07:47:33'),
(60, 'membership_bonus_fixed', '0', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(61, 'membership_bonus_percentage', '0', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(62, 'chain_link', '', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(63, 'contract_address', '', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(64, 'wallet_address', '', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(65, 'private_key', '', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(66, 'contract_decimal', '18', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(67, 'gas_limit', '43000', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(68, 'contract_coin_name', 'ETH', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(69, 'kyc_enable_for_withdrawal', '0', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(70, 'kyc_nid_enable_for_withdrawal', '0', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(71, 'kyc_passport_enable_for_withdrawal', '0', '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(72, 'kyc_driving_enable_for_withdrawal', '0', '2022-03-24 07:47:34', '2022-03-24 07:47:34');

-- --------------------------------------------------------

--
-- Table structure for table `affiliation_codes`
--

CREATE TABLE `affiliation_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `code` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `affiliation_histories`
--

CREATE TABLE `affiliation_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `child_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `system_fees` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `transaction_id` bigint DEFAULT NULL,
  `level` int NOT NULL,
  `wallet_id` bigint DEFAULT NULL,
  `coin_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_type` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` bigint UNSIGNED NOT NULL,
  `account_holder_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `swift_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buy_coin_histories`
--

CREATE TABLE `buy_coin_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint NOT NULL,
  `user_id` bigint NOT NULL,
  `coin` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `btc` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `doller` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `admin_confirmation` tinyint NOT NULL DEFAULT '0',
  `confirmations` int NOT NULL DEFAULT '0',
  `bank_sleep` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_id` int DEFAULT NULL,
  `coin_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `referral_bonus` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `bonus` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `fees` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `referral_level` int DEFAULT NULL,
  `phase_id` bigint DEFAULT NULL,
  `stripe_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buy_coin_referral_histories`
--

CREATE TABLE `buy_coin_referral_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `wallet_id` bigint NOT NULL,
  `buy_id` bigint NOT NULL,
  `phase_id` bigint NOT NULL,
  `child_id` bigint NOT NULL,
  `level` int NOT NULL,
  `system_fees` decimal(13,8) NOT NULL DEFAULT '0.00000000',
  `amount` decimal(13,8) NOT NULL DEFAULT '0.00000000',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coins`
--

CREATE TABLE `coins` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `is_withdrawal` tinyint NOT NULL DEFAULT '1',
  `is_deposit` tinyint NOT NULL DEFAULT '1',
  `is_buy` tinyint NOT NULL DEFAULT '1',
  `is_sell` tinyint NOT NULL DEFAULT '1',
  `withdrawal_fees` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `maximum_withdrawal` decimal(19,8) NOT NULL DEFAULT '99999999.00000000',
  `minimum_withdrawal` decimal(19,8) NOT NULL DEFAULT '0.00000010',
  `minimum_sell_amount` decimal(19,8) NOT NULL DEFAULT '0.00000010',
  `minimum_buy_amount` decimal(19,8) NOT NULL DEFAULT '0.00000010',
  `sign` varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `trade_status` tinyint NOT NULL DEFAULT '1',
  `is_virtual_amount` tinyint(1) NOT NULL DEFAULT '0',
  `is_transferable` tinyint(1) NOT NULL DEFAULT '0',
  `is_wallet` tinyint(1) NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `is_currency` tinyint(1) NOT NULL DEFAULT '0',
  `is_base` tinyint(1) NOT NULL DEFAULT '1',
  `coin_icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coins`
--

INSERT INTO `coins` (`id`, `name`, `type`, `status`, `is_withdrawal`, `is_deposit`, `is_buy`, `is_sell`, `withdrawal_fees`, `maximum_withdrawal`, `minimum_withdrawal`, `minimum_sell_amount`, `minimum_buy_amount`, `sign`, `trade_status`, `is_virtual_amount`, `is_transferable`, `is_wallet`, `is_primary`, `is_currency`, `is_base`, `coin_icon`, `created_at`, `updated_at`) VALUES
(1, 'Bitcoin', 'BTC', 1, 1, 1, 1, 1, '0.00000000', '99999999.00000000', '0.00000010', '0.00000010', '0.00000010', NULL, 1, 0, 0, 0, NULL, 0, 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(2, 'Tether USD', 'USDT', 1, 1, 1, 1, 1, '0.00000000', '99999999.00000000', '0.00000010', '0.00000010', '0.00000010', NULL, 1, 0, 0, 0, NULL, 0, 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(3, 'Ether', 'ETH', 1, 1, 1, 1, 1, '0.00000000', '99999999.00000000', '0.00000010', '0.00000010', '0.00000010', NULL, 1, 0, 0, 0, NULL, 0, 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(4, 'Litecoin', 'LTC', 1, 1, 1, 1, 1, '0.00000000', '99999999.00000000', '0.00000010', '0.00000010', '0.00000010', NULL, 1, 0, 0, 0, NULL, 0, 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(5, 'Bitcoin Cash', 'BCH', 1, 1, 1, 1, 1, '0.00000000', '99999999.00000000', '0.00000010', '0.00000010', '0.00000010', NULL, 1, 0, 0, 0, NULL, 0, 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(6, 'Dash', 'DASH', 1, 1, 1, 1, 1, '0.00000000', '99999999.00000000', '0.00000010', '0.00000010', '0.00000010', NULL, 1, 0, 0, 0, NULL, 0, 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(7, 'SVS', 'SVS', 1, 1, 1, 1, 1, '0.00000000', '99999999.00000000', '0.00000010', '0.00000010', '0.00000010', NULL, 1, 0, 0, 0, NULL, 0, 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(8, 'Ltct coin', 'LTCT', 1, 1, 1, 1, 1, '0.00000000', '99999999.00000000', '0.00000010', '0.00000010', '0.00000010', NULL, 1, 0, 0, 0, NULL, 0, 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34');

-- --------------------------------------------------------

--
-- Table structure for table `coin_requests`
--

CREATE TABLE `coin_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `amount` decimal(13,8) NOT NULL DEFAULT '0.00000000',
  `sender_user_id` bigint NOT NULL,
  `receiver_user_id` bigint NOT NULL,
  `sender_wallet_id` bigint NOT NULL,
  `receiver_wallet_id` bigint NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_us`
--

CREATE TABLE `contact_us` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `co_wallet_withdraw_approvals`
--

CREATE TABLE `co_wallet_withdraw_approvals` (
  `id` bigint UNSIGNED NOT NULL,
  `temp_withdraw_id` bigint NOT NULL,
  `wallet_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_pages`
--

CREATE TABLE `custom_pages` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_order` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deposite_transactions`
--

CREATE TABLE `deposite_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fees` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `sender_wallet_id` bigint DEFAULT NULL,
  `receiver_wallet_id` bigint UNSIGNED NOT NULL,
  `address_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `btc` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `doller` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `updated_by` bigint DEFAULT NULL,
  `from_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmations` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estimate_gas_fees_transaction_histories`
--

CREATE TABLE `estimate_gas_fees_transaction_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `unique_code` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deposit_id` bigint NOT NULL,
  `wallet_id` bigint NOT NULL,
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `fees` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `coin_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BTC',
  `admin_address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_hash` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint UNSIGNED NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `author` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ico_phases`
--

CREATE TABLE `ico_phases` (
  `id` bigint UNSIGNED NOT NULL,
  `phase_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `fees` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `rate` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `bonus` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `status` tinyint NOT NULL DEFAULT '1',
  `affiliation_level` int DEFAULT NULL,
  `affiliation_percentage` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `language`, `created_at`, `updated_at`) VALUES
(1, NULL, 'en', '2022-03-24 07:47:29', '2022-03-24 07:47:29');

-- --------------------------------------------------------

--
-- Table structure for table `membership_bonuses`
--

CREATE TABLE `membership_bonuses` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `bonus_amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership_bonus_distribution_histories`
--

CREATE TABLE `membership_bonus_distribution_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `plan_id` bigint NOT NULL,
  `wallet_id` bigint NOT NULL,
  `membership_id` bigint NOT NULL,
  `distribution_date` date NOT NULL,
  `bonus_amount` decimal(13,8) NOT NULL DEFAULT '0.00000000',
  `plan_current_bonus` decimal(13,8) NOT NULL DEFAULT '0.00000000',
  `bonus_type` tinyint NOT NULL DEFAULT '0',
  `bonus_amount_btc` decimal(13,8) NOT NULL DEFAULT '0.00000000',
  `bonus_coin_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SVS',
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership_clubs`
--

CREATE TABLE `membership_clubs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `plan_id` bigint DEFAULT NULL,
  `wallet_id` bigint DEFAULT NULL,
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` bigint UNSIGNED NOT NULL,
  `plan_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int NOT NULL DEFAULT '0',
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bonus_type` tinyint NOT NULL DEFAULT '1',
  `bonus` decimal(13,8) NOT NULL DEFAULT '0.00000000',
  `bonus_coin_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SVS',
  `status` tinyint NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `membership_plans`
--

INSERT INTO `membership_plans` (`id`, `plan_name`, `duration`, `amount`, `image`, `bonus_type`, `bonus`, `bonus_coin_type`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Silver', 30, '500.00000000', NULL, 2, '2.00000000', 'SVS', 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(2, 'Gold', 30, '1000.00000000', NULL, 2, '5.00000000', 'SVS', 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34'),
(3, 'Platinum', 30, '2000.00000000', NULL, 2, '10.00000000', 'SVS', 1, NULL, '2022-03-24 07:47:34', '2022-03-24 07:47:34');

-- --------------------------------------------------------

--
-- Table structure for table `membership_transaction_histories`
--

CREATE TABLE `membership_transaction_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `club_id` bigint DEFAULT NULL,
  `user_id` bigint NOT NULL,
  `wallet_id` bigint NOT NULL,
  `amount` decimal(13,8) NOT NULL DEFAULT '0.00000000',
  `type` tinyint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_11_000000_create_failed_jobs_table', 1),
(2, '2014_10_12_000000_create_users_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(5, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(6, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(7, '2016_06_01_000004_create_oauth_clients_table', 1),
(8, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1),
(9, '2018_08_29_200844_create_languages_table', 1),
(10, '2018_08_29_205156_create_translations_table', 1),
(11, '2019_06_24_092552_create_wallets_table', 1),
(12, '2019_07_24_092057_create_referrals_table', 1),
(13, '2019_07_24_092303_create_user_settings_table', 1),
(14, '2019_07_24_092331_create_admin_settings_table', 1),
(15, '2019_07_24_092409_create_activity_logs_table', 1),
(16, '2019_07_24_092511_create_wallet_address_histories_table', 1),
(17, '2019_07_24_103207_create_user_verification_codes_table', 1),
(18, '2019_10_17_075927_create_affiliation_codes_table', 1),
(19, '2019_10_17_080002_create_affiliation_histories_table', 1),
(20, '2019_10_17_080031_create_referral_users_table', 1),
(21, '2020_04_29_080822_create_verification_details_table', 1),
(22, '2020_04_29_081029_create_banks_table', 1),
(23, '2020_04_29_081134_create_buy_coin_histories_table', 1),
(24, '2020_04_29_081343_create_deposite_transactions_table', 1),
(25, '2020_04_29_081451_create_withdraw_histories_table', 1),
(26, '2020_06_11_133803_create_membership_clubs_table', 1),
(27, '2020_06_11_134228_create_membership_plans_table', 1),
(28, '2020_06_11_134611_create_membership_bonuses_table', 1),
(29, '2020_06_11_134742_create_membership_bonus_distribution_histories_table', 1),
(30, '2020_06_11_134823_create_membership_transaction_histories_table', 1),
(31, '2020_06_17_123519_create_faqs_table', 1),
(32, '2020_06_19_095619_create_send_mail_records_table', 1),
(33, '2020_06_19_183647_create_notifications_table', 1),
(34, '2020_06_21_152330_create_referral_sign_bonus_histories_table', 1),
(35, '2020_06_23_065105_add_wallet_id_at_bonus', 1),
(36, '2020_06_24_080256_create_websockets_statistics_entries_table', 1),
(37, '2020_07_01_111249_create_admin_give_coin_histories_table', 1),
(38, '2020_07_03_092949_create_ico_phases_table', 1),
(39, '2020_07_03_112940_add_phaseid_at_buy_coin', 1),
(40, '2020_07_06_053213_create_buy_coin_referral_histories_table', 1),
(41, '2020_07_26_091257_create_coin_requests_table', 1),
(42, '2020_09_25_095154_add_coin_type_at_wallets', 1),
(43, '2020_09_25_105747_create_coins_table', 1),
(44, '2020_09_29_062329_add_wallet_id_at_membership_clubs', 1),
(45, '2020_09_30_062649_add_coin_type_at_withdraw', 1),
(46, '2020_09_30_065234_add_coin_type_at_wallet_address', 1),
(47, '2020_10_02_060429_add_coin_type_at_plan', 1),
(48, '2020_10_02_063542_add_coin_type_at_plan_bonus_dis_history', 1),
(49, '2020_10_20_112652_add_stripe_token_at_buy_coin_histories', 1),
(50, '2021_01_13_093659_create_custom_pages_table', 1),
(51, '2021_01_16_064548_create_contact_us_table', 1),
(52, '2021_03_04_065920_create_wallet_swap_histories_table', 1),
(53, '2021_04_19_123622_add_columns_in_wallets', 1),
(54, '2021_04_19_124055_create_wallet_co_users_table', 1),
(55, '2021_04_19_125002_create_temp_withdraws_table', 1),
(56, '2021_04_19_125104_create_co_wallet_withdraw_approvals_table', 1),
(57, '2021_04_21_083450_add_user_id_in_withdraw_histories', 1),
(58, '2021_04_23_055746_add_column_at_coin', 1),
(59, '2021_04_26_075520_add_coin_id_at_wallets', 1),
(60, '2021_10_19_121437_add_new_column_users', 1),
(61, '2022_01_14_091807_add_pk_at_wallet_address_table', 1),
(62, '2022_01_27_064540_create_estimate_gas_fees_transaction_histories_table', 1),
(63, '2022_01_27_072747_create_admin_receive_token_transaction_histories_table', 1),
(64, '2022_03_24_061547_add_from_address_to_deposit', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_body` longtext COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `client_id` int NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `client_id` int NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` int UNSIGNED NOT NULL,
  `client_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `parent_user_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_sign_bonus_histories`
--

CREATE TABLE `referral_sign_bonus_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `parent_id` bigint NOT NULL,
  `wallet_id` bigint NOT NULL,
  `amount` decimal(13,8) NOT NULL DEFAULT '0.00000000',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_users`
--

CREATE TABLE `referral_users` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `send_mail_records`
--

CREATE TABLE `send_mail_records` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `email_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_withdraws`
--

CREATE TABLE `temp_withdraws` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `wallet_id` bigint NOT NULL,
  `withdraw_id` bigint DEFAULT NULL,
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `id` int UNSIGNED NOT NULL,
  `language_id` int UNSIGNED NOT NULL,
  `group` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_code` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` int NOT NULL DEFAULT '2',
  `status` int NOT NULL DEFAULT '1',
  `country_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_verified` tinyint NOT NULL DEFAULT '0',
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` tinyint NOT NULL DEFAULT '1',
  `birth_date` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `g2f_enabled` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `google2fa_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint NOT NULL DEFAULT '0',
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `device_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` tinyint NOT NULL DEFAULT '1',
  `push_notification_status` tinyint NOT NULL DEFAULT '1',
  `email_notification_status` tinyint NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `verification_codes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'various verification codes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `reset_code`, `role`, `status`, `country_code`, `phone`, `phone_verified`, `country`, `gender`, `birth_date`, `photo`, `g2f_enabled`, `google2fa_secret`, `is_verified`, `password`, `language`, `device_id`, `device_type`, `push_notification_status`, `email_notification_status`, `remember_token`, `created_at`, `updated_at`, `verification_codes`) VALUES
(1, 'Mr.', 'Admin', 'admin@email.com', NULL, 1, 1, NULL, NULL, 0, NULL, 1, NULL, NULL, '0', NULL, 1, '$2y$10$P2p/Rgv04SmZcs01cpRjquzwhLlhAD4K9qDujoxyd9ve5z4i0piXe', 'en', NULL, 1, 1, 1, NULL, '2022-03-24 07:47:33', '2022-03-24 07:47:33', NULL),
(2, 'Mr', 'User', 'user@email.com', NULL, 2, 1, NULL, NULL, 0, NULL, 1, NULL, NULL, '0', NULL, 1, '$2y$10$XEIgHJwCg1WeMbmYbyWBjuLAWuN/8pQCbBkypQCpYV.BH/DLXeahy', 'en', NULL, 1, 1, 1, NULL, '2022-03-24 07:47:33', '2022-03-24 07:47:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_verification_codes`
--

CREATE TABLE `user_verification_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `expired_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_details`
--

CREATE TABLE `verification_details` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `field_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coin_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SVS',
  `coin_id` int DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `is_primary` tinyint NOT NULL DEFAULT '0',
  `balance` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `referral_balance` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `name`, `coin_type`, `coin_id`, `status`, `is_primary`, `balance`, `referral_balance`, `created_at`, `updated_at`, `key`, `type`) VALUES
(1, 1, 'BTC Wallet', 'BTC', 1, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(2, 1, 'USDT Wallet', 'USDT', 2, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(3, 1, 'ETH Wallet', 'ETH', 3, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(4, 1, 'LTC Wallet', 'LTC', 4, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(5, 1, 'BCH Wallet', 'BCH', 5, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(6, 1, 'DASH Wallet', 'DASH', 6, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(7, 1, 'SVS Wallet', 'SVS', 7, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(8, 1, 'LTCT Wallet', 'LTCT', 8, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(9, 2, 'BTC Wallet', 'BTC', 1, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(10, 2, 'USDT Wallet', 'USDT', 2, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(11, 2, 'ETH Wallet', 'ETH', 3, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(12, 2, 'LTC Wallet', 'LTC', 4, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(13, 2, 'BCH Wallet', 'BCH', 5, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(14, 2, 'DASH Wallet', 'DASH', 6, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(15, 2, 'SVS Wallet', 'SVS', 7, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1),
(16, 2, 'LTCT Wallet', 'LTCT', 8, 1, 0, '0.00000000', '0.00000000', '2022-03-24 07:47:34', '2022-03-24 07:47:34', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `wallet_address_histories`
--

CREATE TABLE `wallet_address_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pk` text COLLATE utf8mb4_unicode_ci,
  `coin_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BTC',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_co_users`
--

CREATE TABLE `wallet_co_users` (
  `id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_swap_histories`
--

CREATE TABLE `wallet_swap_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `from_wallet_id` bigint NOT NULL,
  `to_wallet_id` bigint NOT NULL,
  `from_coin_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_coin_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `converted_amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `rate` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `websockets_statistics_entries`
--

CREATE TABLE `websockets_statistics_entries` (
  `id` int UNSIGNED NOT NULL,
  `app_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `peak_connection_count` int NOT NULL,
  `websocket_message_count` int NOT NULL,
  `api_message_count` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdraw_histories`
--

CREATE TABLE `withdraw_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `coin_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BTC',
  `btc` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `doller` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `address_type` tinyint NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_hash` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiver_wallet_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmations` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fees` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `used_gas` decimal(19,8) NOT NULL DEFAULT '0.00000000',
  `status` tinyint NOT NULL DEFAULT '0',
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `admin_give_coin_histories`
--
ALTER TABLE `admin_give_coin_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_receive_token_transaction_histories`
--
ALTER TABLE `admin_receive_token_transaction_histories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_receive_token_transaction_histories_unique_code_unique` (`unique_code`);

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `affiliation_codes`
--
ALTER TABLE `affiliation_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `affiliation_codes_code_unique` (`code`),
  ADD KEY `affiliation_codes_user_id_foreign` (`user_id`);

--
-- Indexes for table `affiliation_histories`
--
ALTER TABLE `affiliation_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `affiliation_histories_user_id_foreign` (`user_id`),
  ADD KEY `affiliation_histories_child_id_foreign` (`child_id`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buy_coin_histories`
--
ALTER TABLE `buy_coin_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buy_coin_referral_histories`
--
ALTER TABLE `buy_coin_referral_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coins`
--
ALTER TABLE `coins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coins_type_unique` (`type`),
  ADD UNIQUE KEY `coins_is_primary_unique` (`is_primary`);

--
-- Indexes for table `coin_requests`
--
ALTER TABLE `coin_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `co_wallet_withdraw_approvals`
--
ALTER TABLE `co_wallet_withdraw_approvals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_pages`
--
ALTER TABLE `custom_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposite_transactions`
--
ALTER TABLE `deposite_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estimate_gas_fees_transaction_histories`
--
ALTER TABLE `estimate_gas_fees_transaction_histories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `estimate_gas_fees_transaction_histories_unique_code_unique` (`unique_code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ico_phases`
--
ALTER TABLE `ico_phases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `membership_bonuses`
--
ALTER TABLE `membership_bonuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `membership_bonus_distribution_histories`
--
ALTER TABLE `membership_bonus_distribution_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `membership_clubs`
--
ALTER TABLE `membership_clubs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `membership_transaction_histories`
--
ALTER TABLE `membership_transaction_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_personal_access_clients_client_id_index` (`client_id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrals_user_id_foreign` (`user_id`);

--
-- Indexes for table `referral_sign_bonus_histories`
--
ALTER TABLE `referral_sign_bonus_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_users`
--
ALTER TABLE `referral_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_users_user_id_unique` (`user_id`),
  ADD KEY `referral_users_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `send_mail_records`
--
ALTER TABLE `send_mail_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_withdraws`
--
ALTER TABLE `temp_withdraws`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translations_language_id_foreign` (`language_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_reset_code_unique` (`reset_code`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_settings_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_verification_codes`
--
ALTER TABLE `user_verification_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `verification_details`
--
ALTER TABLE `verification_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verification_details_user_id_foreign` (`user_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallets_user_id_foreign` (`user_id`);

--
-- Indexes for table `wallet_address_histories`
--
ALTER TABLE `wallet_address_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wallet_co_users`
--
ALTER TABLE `wallet_co_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wallet_swap_histories`
--
ALTER TABLE `wallet_swap_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_swap_histories_user_id_foreign` (`user_id`);

--
-- Indexes for table `websockets_statistics_entries`
--
ALTER TABLE `websockets_statistics_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdraw_histories`
--
ALTER TABLE `withdraw_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `withdraw_histories_wallet_id_foreign` (`wallet_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_give_coin_histories`
--
ALTER TABLE `admin_give_coin_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_receive_token_transaction_histories`
--
ALTER TABLE `admin_receive_token_transaction_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `affiliation_codes`
--
ALTER TABLE `affiliation_codes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `affiliation_histories`
--
ALTER TABLE `affiliation_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `buy_coin_histories`
--
ALTER TABLE `buy_coin_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `buy_coin_referral_histories`
--
ALTER TABLE `buy_coin_referral_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coins`
--
ALTER TABLE `coins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `coin_requests`
--
ALTER TABLE `coin_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `co_wallet_withdraw_approvals`
--
ALTER TABLE `co_wallet_withdraw_approvals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_pages`
--
ALTER TABLE `custom_pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposite_transactions`
--
ALTER TABLE `deposite_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estimate_gas_fees_transaction_histories`
--
ALTER TABLE `estimate_gas_fees_transaction_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ico_phases`
--
ALTER TABLE `ico_phases`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `membership_bonuses`
--
ALTER TABLE `membership_bonuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membership_bonus_distribution_histories`
--
ALTER TABLE `membership_bonus_distribution_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membership_clubs`
--
ALTER TABLE `membership_clubs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `membership_transaction_histories`
--
ALTER TABLE `membership_transaction_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_sign_bonus_histories`
--
ALTER TABLE `referral_sign_bonus_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_users`
--
ALTER TABLE `referral_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `send_mail_records`
--
ALTER TABLE `send_mail_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_withdraws`
--
ALTER TABLE `temp_withdraws`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `translations`
--
ALTER TABLE `translations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_verification_codes`
--
ALTER TABLE `user_verification_codes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verification_details`
--
ALTER TABLE `verification_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `wallet_address_histories`
--
ALTER TABLE `wallet_address_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_co_users`
--
ALTER TABLE `wallet_co_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_swap_histories`
--
ALTER TABLE `wallet_swap_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websockets_statistics_entries`
--
ALTER TABLE `websockets_statistics_entries`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdraw_histories`
--
ALTER TABLE `withdraw_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `affiliation_codes`
--
ALTER TABLE `affiliation_codes`
  ADD CONSTRAINT `affiliation_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `affiliation_histories`
--
ALTER TABLE `affiliation_histories`
  ADD CONSTRAINT `affiliation_histories_child_id_foreign` FOREIGN KEY (`child_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `affiliation_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_users`
--
ALTER TABLE `referral_users`
  ADD CONSTRAINT `referral_users_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `referral_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `translations`
--
ALTER TABLE `translations`
  ADD CONSTRAINT `translations_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`);

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verification_details`
--
ALTER TABLE `verification_details`
  ADD CONSTRAINT `verification_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_swap_histories`
--
ALTER TABLE `wallet_swap_histories`
  ADD CONSTRAINT `wallet_swap_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdraw_histories`
--
ALTER TABLE `withdraw_histories`
  ADD CONSTRAINT `withdraw_histories_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
