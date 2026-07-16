-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 16, 2026 at 02:07 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `machine_report`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `machines`
--

CREATE TABLE `machines` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `production_area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criticality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operational_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manufacturer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installation_date` date DEFAULT NULL,
  `commissioning_date` date DEFAULT NULL,
  `vendor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `lifecycle_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `machines`
--

INSERT INTO `machines` (`id`, `code`, `name`, `department`, `production_area`, `category`, `criticality`, `operational_status`, `manufacturer`, `model`, `serial_number`, `installation_date`, `commissioning_date`, `vendor`, `qr_code_path`, `created_at`, `updated_at`, `is_active`, `lifecycle_status`, `notes`, `created_by`) VALUES
(1, 'CNC-08', 'CNC Milling Center', 'Machining', 'Area A', 'Milling', 'high', 'breakdown', 'Siemens', 'X-500', 'SN-CNC08-2019', '2019-03-12', '2019-03-20', 'Siemens Industrial Solutions', 'images/qr-cnc-08.png', '2026-07-14 21:51:47', '2026-07-14 21:51:47', 1, 'ACTIVE', NULL, NULL),
(2, 'CNC-04', 'Precision Lathe Pro', 'Machining', 'Area B', 'Lathe', 'mission_critical', 'breakdown', 'Haas', 'VF-2', 'SN-VF2-2020', '2020-05-15', '2020-05-22', 'Haas Automation Inc.', 'images/qr-cnc-08.png', '2026-07-14 21:51:47', '2026-07-14 21:51:47', 1, 'ACTIVE', NULL, NULL),
(3, 'ARM-12', 'Robotic Welder X1', 'Assembly Center', 'Area C', 'Robot', 'high', 'maintenance', 'Fanuc', 'R-2000iC', 'SN-FANUC-2021', '2021-08-10', '2021-08-18', 'Fanuc America Corp', 'images/qr-cnc-08.png', '2026-07-14 21:51:47', '2026-07-14 21:51:47', 1, 'ACTIVE', NULL, NULL),
(4, 'PMP-08', 'Hydraulic Feed Pump', 'Maintenance', 'Area D', 'Pump', 'medium', 'idle', 'Rexroth', 'A4VSO', 'SN-REXROTH-2018', '2018-11-05', '2018-11-12', 'Bosch Rexroth Group', 'images/qr-cnc-08.png', '2026-07-14 21:51:47', '2026-07-14 21:51:47', 1, 'ACTIVE', NULL, NULL),
(5, 'DRL-19', 'Radial Drill Press', 'Maintenance', 'Workshop', 'Drilling', 'low', 'running', 'Carlton', '3A', 'SN-CARLTON-2015', '2015-06-20', '2015-06-25', 'Carlton Machine Tool Co', 'images/qr-cnc-08.png', '2026-07-14 21:51:47', '2026-07-14 21:51:47', 1, 'ACTIVE', NULL, NULL),
(6, 'CNC-101', 'Horizontal Milling CNC', 'Machining', NULL, 'CNC', 'medium', 'running', NULL, NULL, 'SN-101-CNC', NULL, NULL, NULL, NULL, '2026-07-14 21:53:08', '2026-07-14 21:53:40', 0, 'INACTIVE', NULL, NULL),
(7, 'N-BC.80', 'BUBUT CNC 80', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(8, 'N-BC.78', 'BUBUT CNC 78', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(9, 'N-BC.79', 'BUBUT CNC 79', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(10, 'N-BC.76', 'BUBUT CNC 76', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(11, 'N-BC.77', 'BUBUT CNC 77', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(12, 'N-BC.63', 'BUBUT CNC 63', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(13, 'N-BC.43', 'BUBUT CNC 43', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(14, 'N-BC.61', 'BUBUT CNC 61', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(15, 'N-BC.62', 'BUBUT CNC 62', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(16, 'N-BC.83', 'BUBUT CNC 83', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(17, 'N-BC.64', 'BUBUT CNC 64', 'Machining', 'BUBUT FITTING', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(18, 'O-BC.88', 'BUBUT CNC 88', 'Machining', 'BUBUT FLANGE BESI', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(19, 'O-BC.89', 'BUBUT CNC 89', 'Machining', 'BUBUT FLANGE BESI', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(20, 'O-BC.90', 'BUBUT CNC 90', 'Machining', 'BUBUT FLANGE BESI', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(21, 'O-BC.91', 'BUBUT CNC 91', 'Machining', 'BUBUT FLANGE BESI', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(22, 'O-BC.65', 'BUBUT CNC 65', 'Machining', 'BUBUT FLANGE BESI', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(23, 'O-BC.56', 'BUBUT CNC 56', 'Machining', 'BUBUT FLANGE BESI', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(24, 'O-BC.50', 'BUBUT CNC 50', 'Machining', 'BUBUT FLANGE BESI', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(25, 'O-BC.47', 'BUBUT CNC 47', 'Machining', 'BUBUT FLANGE BESI', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(26, 'O-BC.34', 'BUBUT CNC 34', 'Machining', 'BUBUT FLANGE BESI', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(27, 'R-BC.85', 'BUBUT CNC 85', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(28, 'R-BC.84', 'BUBUT CNC 84', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(29, 'R-BC.82', 'BUBUT CNC 82', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(30, 'R-BC.81', 'BUBUT CNC 81', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(31, 'R-BC.74', 'BUBUT CNC 74', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(32, 'R-BC.73', 'BUBUT CNC 73', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(33, 'R-BC.70', 'BUBUT CNC 70', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(34, 'R-BC.69', 'BUBUT CNC 69', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(35, 'R-BC.68', 'BUBUT CNC 68', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(36, 'R-BC.67', 'BUBUT CNC 67', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(37, 'R-BC.66', 'BUBUT CNC 66', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(38, 'R-BC.55', 'BUBUT CNC 55', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(39, 'R-BC.53', 'BUBUT CNC 53', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL),
(40, 'R-BC.41', 'BUBUT CNC 41', 'Machining', 'BUBUT CNC FLANGE', 'Lathe', 'medium', 'running', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-15 18:31:01', '2026-07-15 18:31:01', 1, 'ACTIVE', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `machine_components`
--

CREATE TABLE `machine_components` (
  `id` bigint UNSIGNED NOT NULL,
  `machine_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `machine_components`
--

INSERT INTO `machine_components` (`id`, `machine_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Spindle Unit', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(2, 1, 'Motor', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(3, 1, 'Lubrication System', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(4, 1, 'Cooling System', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(5, 1, 'PLC', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(6, 2, 'Spindle Unit', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(7, 2, 'Cooling System', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(8, 2, 'Servo X Axis', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(9, 2, 'Servo Z Axis', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(10, 3, 'Servo System', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(11, 3, 'PLC', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(12, 3, 'Motor', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(13, 3, 'Lubrication Unit', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(14, 4, 'Hydraulic Unit', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(15, 4, 'Motor', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(16, 4, 'Lubrication Unit', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(17, 5, 'Motor', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(18, 5, 'Lubrication Unit', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(19, 5, 'Spindle Unit', '2026-07-14 21:51:47', '2026-07-14 21:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `machine_documents`
--

CREATE TABLE `machine_documents` (
  `id` bigint UNSIGNED NOT NULL,
  `machine_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `machine_documents`
--

INSERT INTO `machine_documents` (`id`, `machine_id`, `type`, `file_name`, `file_path`, `created_at`, `updated_at`) VALUES
(1, 1, 'manual_book', 'manual_book_cnc_08.pdf', '/documents/manuals/cnc_08_manual.pdf', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(2, 1, 'electrical_diagram', 'electrical_schematic_v2.pdf', '/documents/schematics/cnc_08_electrical.pdf', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(3, 1, 'hydraulic_diagram', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(4, 1, 'parameter_backup', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(5, 1, 'vendor_document', 'warranty_cert.pdf', '/documents/warranty/cnc_08_warranty.pdf', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(6, 2, 'manual_book', 'manual_lathe_vf2.pdf', '/documents/manuals/haas_vf2_manual.pdf', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(7, 2, 'electrical_diagram', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(8, 2, 'hydraulic_diagram', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(9, 2, 'parameter_backup', 'params_backup_2023.txt', '/documents/backups/haas_vf2_params.txt', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(10, 2, 'vendor_document', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(11, 3, 'manual_book', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(12, 3, 'electrical_diagram', 'robotic_welder_electrical.pdf', '/documents/schematics/fanuc_welder_electrical.pdf', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(13, 3, 'hydraulic_diagram', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(14, 3, 'parameter_backup', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(15, 3, 'vendor_document', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(16, 4, 'manual_book', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(17, 4, 'electrical_diagram', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(18, 4, 'hydraulic_diagram', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(19, 4, 'parameter_backup', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(20, 4, 'vendor_document', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(21, 5, 'manual_book', 'carlton_3a_manual.pdf', '/documents/manuals/carlton_3a_manual.pdf', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(22, 5, 'electrical_diagram', 'carlton_3a_electrical.pdf', '/documents/schematics/carlton_3a_electrical.pdf', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(23, 5, 'hydraulic_diagram', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(24, 5, 'parameter_backup', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(25, 5, 'vendor_document', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `machine_photos`
--

CREATE TABLE `machine_photos` (
  `id` bigint UNSIGNED NOT NULL,
  `machine_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `machine_photos`
--

INSERT INTO `machine_photos` (`id`, `machine_id`, `type`, `file_name`, `file_path`, `created_at`, `updated_at`) VALUES
(1, 1, 'overall', 'cnc_08_overall.webp', 'images/cnc-08.webp', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(2, 1, 'name_plate', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(3, 1, 'electrical_cabinet', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(4, 1, 'hydraulic_unit', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(5, 2, 'overall', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(6, 2, 'name_plate', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(7, 2, 'electrical_cabinet', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(8, 2, 'hydraulic_unit', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(9, 3, 'overall', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(10, 3, 'name_plate', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(11, 3, 'electrical_cabinet', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(12, 3, 'hydraulic_unit', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(13, 4, 'overall', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(14, 4, 'name_plate', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(15, 4, 'electrical_cabinet', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(16, 4, 'hydraulic_unit', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(17, 5, 'overall', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(18, 5, 'name_plate', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(19, 5, 'electrical_cabinet', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(20, 5, 'hydraulic_unit', NULL, NULL, '2026-07-14 21:51:47', '2026-07-14 21:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `machine_required_spareparts`
--

CREATE TABLE `machine_required_spareparts` (
  `id` bigint UNSIGNED NOT NULL,
  `machine_id` bigint UNSIGNED NOT NULL,
  `warehouse_item_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `machine_required_spareparts`
--

INSERT INTO `machine_required_spareparts` (`id`, `machine_id`, `warehouse_item_code`, `created_at`, `updated_at`) VALUES
(1, 1, 'BRG-6204', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(2, 1, 'SEAL-TC-40', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(3, 1, 'HYD-OIL-46', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(4, 2, 'BRG-6204', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(5, 2, 'SEAL-TC-40', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(6, 3, 'RLY-24V', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(7, 3, 'BRG-NU22', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(8, 4, 'PMP-G1', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(9, 4, 'HYD-OIL-46', '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(10, 5, 'VBLT-A42', '2026-07-14 21:51:47', '2026-07-14 21:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_executions`
--

CREATE TABLE `maintenance_executions` (
  `id` bigint UNSIGNED NOT NULL,
  `maintenance_plan_id` bigint UNSIGNED NOT NULL,
  `machine_id` bigint UNSIGNED NOT NULL,
  `operator_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `overall_score` decimal(4,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting_review',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_executions`
--

INSERT INTO `maintenance_executions` (`id`, `maintenance_plan_id`, `machine_id`, `operator_name`, `started_at`, `completed_at`, `overall_score`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 4, 'R. Miller', '2026-07-14 20:16:48', '2026-07-14 20:51:48', 4.25, 'Kalibrasi aliran hidrolik selesai, tekanan diatur kembali ke standar operasional.', 'waiting_review', '2026-07-14 21:51:48', '2026-07-14 21:51:48');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_execution_answers`
--

CREATE TABLE `maintenance_execution_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `execution_id` bigint UNSIGNED NOT NULL,
  `checklist_item_id` bigint UNSIGNED NOT NULL,
  `score` int NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_execution_answers`
--

INSERT INTO `maintenance_execution_answers` (`id`, `execution_id`, `checklist_item_id`, `score`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 4, NULL, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(2, 1, 11, 5, NULL, '2026-07-14 21:51:48', '2026-07-14 21:51:48');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_execution_photos`
--

CREATE TABLE `maintenance_execution_photos` (
  `id` bigint UNSIGNED NOT NULL,
  `execution_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_plans`
--

CREATE TABLE `maintenance_plans` (
  `id` bigint UNSIGNED NOT NULL,
  `machine_id` bigint UNSIGNED NOT NULL,
  `maintenance_template_id` bigint UNSIGNED NOT NULL,
  `scheduled_date` date NOT NULL,
  `assigned_technician` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `generation_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Manual',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_plans`
--

INSERT INTO `maintenance_plans` (`id`, `machine_id`, `maintenance_template_id`, `scheduled_date`, `assigned_technician`, `priority`, `status`, `generation_source`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-07-15', 'R. Miller', 'critical', 'scheduled', 'Manual', 'Mesin mati akibat spindle overheating. Membutuhkan suku cadang pengganti yang saat ini kosong di WMS.', '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(2, 2, 2, '2026-07-15', NULL, 'high', 'draft', 'Manual', 'Penyelarasan bubut presisi tertunda karena teknisi belum ditentukan dan suku cadang silinder seal kosong.', '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(3, 3, 3, '2026-07-15', 'S. Chen', 'medium', 'approved', 'Manual', 'Pemeriksaan mingguan lengan robotik Fanuc. Suku cadang tersedia di WMS.', '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(4, 5, 5, '2026-07-15', 'R. Thompson', 'low', 'approved', 'Manual', 'Penyelarasan ketegangan belt tahunan mesin bor radial.', '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(5, 4, 4, '2026-07-18', NULL, 'medium', 'waiting_approval', 'Generated', 'Kalibrasi aliran hidrolik teratur. Dibuat secara otomatis oleh Reliability Engine.', '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(6, 4, 4, '2026-07-15', 'M. Fadil', 'medium', 'in_progress', 'Manual', 'Kalibrasi aliran hidrolik. Teknisi sedang berada di area utilitas.', '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(7, 4, 4, '2026-07-15', 'R. Miller', 'medium', 'completed', 'Manual', 'Kalibrasi aliran hidrolik selesai dilakukan.', '2026-07-14 21:51:48', '2026-07-14 21:51:48');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_templates`
--

CREATE TABLE `maintenance_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `machine_category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `maintenance_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimated_duration` int NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_templates`
--

INSERT INTO `maintenance_templates` (`id`, `name`, `description`, `machine_category`, `maintenance_type`, `estimated_duration`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Servis Bulanan CNC Milling', 'Prosedur pemeliharaan bulanan standar untuk pusat milling CNC. Berfokus pada keandalan spindle, tekanan coolant, dan sistem lubrikasi sumbu.', 'Milling Machine', 'Monthly', 120, 1, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(2, 'Penyelarasan Presisi Bubut', 'Paket penyelarasan sumbu presisi dan uji runout chuck bubut.', 'Lathe Machine', 'Quarterly', 150, 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(3, 'Pemeriksaan Mingguan Robot Las', 'Pemeriksaan integritas mekanis lengan robot dan uji parameter kelistrikan.', 'Industrial Robot', 'Weekly', 90, 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(4, 'Kalibrasi Aliran Pompa', 'Uji tekanan hidrolik dan analisis kontaminasi oli.', 'Pump', 'Semi Annual', 60, 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(5, 'Perawatan Umum Mesin Bor', 'Pemeriksaan kelonggaran bearing spindel bor dan ketegangan sabuk motor.', 'Drilling Machine', 'Annual', 45, 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_template_checklists`
--

CREATE TABLE `maintenance_template_checklists` (
  `id` bigint UNSIGNED NOT NULL,
  `maintenance_template_id` bigint UNSIGNED NOT NULL,
  `sequence` int NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_template_checklists`
--

INSERT INTO `maintenance_template_checklists` (`id`, `maintenance_template_id`, `sequence`, `title`, `description`, `is_required`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Kalibrasi Tekanan Cairan Pendingin (Coolant)', 'Uji tekanan pompa coolant dan bersihkan filter nozzle.', 1, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(2, 1, 2, 'Pemeriksaan Titik Lubrikasi Poros (Axis)', 'Lumasi semua guide way linear pada sumbu X, Y, Z.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(3, 1, 3, 'Pembersihan Serpihan Chip Conveyor', 'Bersihkan penumpukan serpihan logam di area conveyor bawah.', 0, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(4, 1, 4, 'Pengukuran Spindle Runout', 'Uji deviasi toleransi radial spindle menggunakan dial indicator.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(5, 2, 1, 'Pemeriksaan Penjajaran Chuck', 'Lakukan kalibrasi kelurusan chuck terhadap sumbu headstock.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(6, 2, 2, 'Lubrikasi Turret Alat', 'Periksa level oli indeks turret dan tambahkan gemuk jika diperlukan.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(7, 3, 1, 'Uji Jangkauan Gerak & Rem Servo', 'Jalankan routine gerak penuh sumbu 1-6 dan uji respon pengereman darurat.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(8, 3, 2, 'Pembersihan Nozzle Las & Kalibrasi Gas', 'Bersihkan slag dari tip nozzle las dan verifikasi laju aliran gas pelindung.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(9, 3, 3, 'Pemeriksaan Kabinet Kontrol Elektronik', 'Bersihkan debu dari kipas filter pendingin dan periksa kelonggaran kabel.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(10, 4, 1, 'Pengukuran Tekanan Pompa Hidrolik', 'Catat tekanan keluaran pada katup relief.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(11, 4, 2, 'Penggantian Oli Hidrolik Parsial', 'Kuras 10 liter oli lama dan tambahkan oli hidrolik baru.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(12, 5, 1, 'Penjajaran Sabuk (Belt Alignment) & Ketegangan', 'Sesuaikan ketegangan sabuk V-belt agar tidak selip.', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_template_spareparts`
--

CREATE TABLE `maintenance_template_spareparts` (
  `id` bigint UNSIGNED NOT NULL,
  `maintenance_template_id` bigint UNSIGNED NOT NULL,
  `warehouse_item_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_template_spareparts`
--

INSERT INTO `maintenance_template_spareparts` (`id`, `maintenance_template_id`, `warehouse_item_code`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 'BRG-6204', 2, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(2, 1, 'SEAL-TC-40', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(3, 2, 'BRG-6204', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(4, 2, 'SEAL-TC-40', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(5, 3, 'RLY-24V', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(6, 4, 'HYD-OIL-46', 2, '2026-07-14 21:51:48', '2026-07-14 21:51:48'),
(7, 5, 'VBLT-A42', 1, '2026-07-14 21:51:48', '2026-07-14 21:51:48');

-- --------------------------------------------------------

--
-- Table structure for table `master_departments`
--

CREATE TABLE `master_departments` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_departments`
--

INSERT INTO `master_departments` (`id`, `code`, `name`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'MACHINING', 'Machining', 1, 10, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(2, 'MAINTENANCE', 'Maintenance', 1, 20, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(3, 'ASSEMBLY', 'Assembly Center', 1, 30, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(4, 'WAREHOUSE', 'Warehouse', 1, 40, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(5, 'QC', 'QC', 1, 50, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(6, 'PPIC', 'PPIC', 1, 60, '2026-07-14 21:51:47', '2026-07-14 21:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `master_machine_categories`
--

CREATE TABLE `master_machine_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_machine_categories`
--

INSERT INTO `master_machine_categories` (`id`, `code`, `name`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'CNC', 'CNC', 1, 10, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(2, 'LATHE', 'Lathe', 1, 20, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(3, 'MILLING', 'Milling', 1, 30, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(4, 'DRILLING', 'Drilling', 1, 40, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(5, 'PUMP', 'Pump', 1, 50, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(6, 'COMPRESSOR', 'Compressor', 1, 60, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(7, 'ROBOT', 'Robot', 1, 70, '2026-07-14 21:51:47', '2026-07-14 21:51:47'),
(8, 'PRESS', 'Press', 1, 80, '2026-07-14 21:51:47', '2026-07-14 21:51:47');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_14_161543_create_machines_table', 1),
(5, '2026_07_14_161545_create_machine_components_table', 1),
(6, '2026_07_14_161545_create_machine_required_spareparts_table', 1),
(7, '2026_07_14_161546_create_machine_documents_table', 1),
(8, '2026_07_14_161547_create_machine_photos_table', 1),
(9, '2026_07_14_163700_create_maintenance_templates_table', 1),
(10, '2026_07_14_163705_create_maintenance_template_checklists_table', 1),
(11, '2026_07_14_163710_create_maintenance_template_spareparts_table', 1),
(12, '2026_07_14_163715_create_maintenance_plans_table', 1),
(13, '2026_07_15_000010_create_maintenance_executions_table', 1),
(14, '2026_07_15_000011_create_maintenance_execution_photos_table', 1),
(15, '2026_07_15_000012_create_maintenance_execution_answers_table', 1),
(16, '2026_07_15_000019_create_master_departments_and_categories_tables', 1),
(17, '2026_07_15_000020_update_machines_for_progressive_registration', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1TPy6JcoZOWsPRhiZG9br7MBYX4p5LigEEagtIMm', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJTeGt6emxxUlNaOU9Da1FrNzZveG1abzc4dWU2ZGI5aDJVZEtCMDBaIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJkYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1784091249),
('3RBAPbOdjPTe1zVFm53QkMlicYZSCEJyUswMtBDS', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIyQUVkU2Rvd0RESEY3eWhkYU5LYzlBVjZUNTgzQWVrR1ZmdDYzeHdlIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9tYWNoaW5lcz9zdGF0dXNfZmlsdGVyPUFMTCIsInJvdXRlIjoibWFjaGluZXMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1784166762),
('g1YULXLXvpxIAiNitKAl1BZEPTf7KYUUgJlsgTPx', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0', 'eyJfdG9rZW4iOiJURDRlNFdqeXdUV3gycmR3RDNPaER3WmVVeHlBbnpkOHZaeXg4NTlxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMVwvbWFjaGluZS1yZXBvcnRcL3B1YmxpY1wvaW5kZXgucGhwXC9wbGFubmluZyIsInJvdXRlIjoicGxhbm5pbmcuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1784167355),
('XWadoD6Q6gPLmVEE4JwRJb71ky4sTtdZKeCdOIuo', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJMVXNEMWNHTkZYSTcyZTZYTjlEbEhOd0tDWUF0REcxakNLajYyRVR2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL21hY2hpbmUtcmVwb3J0LnRlc3RcL21hY2hpbmVzXC9DTkMtMDgiLCJyb3V0ZSI6Im1hY2hpbmVzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1784166897);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'System Executive', 'admin@mrm.local', '2026-07-14 21:51:47', '$2y$12$yXjkRl.D5x0bLJ2chNffyuKNG3bbA47L4iN6NZRsGECGi6CM9iQAm', 'XMajPKkJUu', '2026-07-14 21:51:47', '2026-07-14 21:51:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

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
-- Indexes for table `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `machines_code_unique` (`code`),
  ADD KEY `machines_created_by_foreign` (`created_by`);

--
-- Indexes for table `machine_components`
--
ALTER TABLE `machine_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `machine_components_machine_id_foreign` (`machine_id`);

--
-- Indexes for table `machine_documents`
--
ALTER TABLE `machine_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `machine_documents_machine_id_foreign` (`machine_id`);

--
-- Indexes for table `machine_photos`
--
ALTER TABLE `machine_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `machine_photos_machine_id_foreign` (`machine_id`);

--
-- Indexes for table `machine_required_spareparts`
--
ALTER TABLE `machine_required_spareparts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `machine_required_spareparts_machine_id_foreign` (`machine_id`),
  ADD KEY `machine_required_spareparts_warehouse_item_code_index` (`warehouse_item_code`);

--
-- Indexes for table `maintenance_executions`
--
ALTER TABLE `maintenance_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_executions_maintenance_plan_id_foreign` (`maintenance_plan_id`),
  ADD KEY `maintenance_executions_machine_id_foreign` (`machine_id`);

--
-- Indexes for table `maintenance_execution_answers`
--
ALTER TABLE `maintenance_execution_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_execution_answers_execution_id_foreign` (`execution_id`),
  ADD KEY `maintenance_execution_answers_checklist_item_id_foreign` (`checklist_item_id`);

--
-- Indexes for table `maintenance_execution_photos`
--
ALTER TABLE `maintenance_execution_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_execution_photos_execution_id_foreign` (`execution_id`);

--
-- Indexes for table `maintenance_plans`
--
ALTER TABLE `maintenance_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_plans_machine_id_foreign` (`machine_id`),
  ADD KEY `maintenance_plans_maintenance_template_id_foreign` (`maintenance_template_id`);

--
-- Indexes for table `maintenance_templates`
--
ALTER TABLE `maintenance_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_template_checklists`
--
ALTER TABLE `maintenance_template_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_template_checklists_maintenance_template_id_foreign` (`maintenance_template_id`);

--
-- Indexes for table `maintenance_template_spareparts`
--
ALTER TABLE `maintenance_template_spareparts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_template_spareparts_maintenance_template_id_foreign` (`maintenance_template_id`);

--
-- Indexes for table `master_departments`
--
ALTER TABLE `master_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `master_departments_code_unique` (`code`);

--
-- Indexes for table `master_machine_categories`
--
ALTER TABLE `master_machine_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `master_machine_categories_code_unique` (`code`);

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
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `machines`
--
ALTER TABLE `machines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `machine_components`
--
ALTER TABLE `machine_components`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `machine_documents`
--
ALTER TABLE `machine_documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `machine_photos`
--
ALTER TABLE `machine_photos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `machine_required_spareparts`
--
ALTER TABLE `machine_required_spareparts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `maintenance_executions`
--
ALTER TABLE `maintenance_executions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `maintenance_execution_answers`
--
ALTER TABLE `maintenance_execution_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `maintenance_execution_photos`
--
ALTER TABLE `maintenance_execution_photos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_plans`
--
ALTER TABLE `maintenance_plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `maintenance_templates`
--
ALTER TABLE `maintenance_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `maintenance_template_checklists`
--
ALTER TABLE `maintenance_template_checklists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `maintenance_template_spareparts`
--
ALTER TABLE `maintenance_template_spareparts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `master_departments`
--
ALTER TABLE `master_departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `master_machine_categories`
--
ALTER TABLE `master_machine_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `machines`
--
ALTER TABLE `machines`
  ADD CONSTRAINT `machines_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `machine_components`
--
ALTER TABLE `machine_components`
  ADD CONSTRAINT `machine_components_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `machine_documents`
--
ALTER TABLE `machine_documents`
  ADD CONSTRAINT `machine_documents_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `machine_photos`
--
ALTER TABLE `machine_photos`
  ADD CONSTRAINT `machine_photos_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `machine_required_spareparts`
--
ALTER TABLE `machine_required_spareparts`
  ADD CONSTRAINT `machine_required_spareparts_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_executions`
--
ALTER TABLE `maintenance_executions`
  ADD CONSTRAINT `maintenance_executions_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_executions_maintenance_plan_id_foreign` FOREIGN KEY (`maintenance_plan_id`) REFERENCES `maintenance_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_execution_answers`
--
ALTER TABLE `maintenance_execution_answers`
  ADD CONSTRAINT `maintenance_execution_answers_checklist_item_id_foreign` FOREIGN KEY (`checklist_item_id`) REFERENCES `maintenance_template_checklists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_execution_answers_execution_id_foreign` FOREIGN KEY (`execution_id`) REFERENCES `maintenance_executions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_execution_photos`
--
ALTER TABLE `maintenance_execution_photos`
  ADD CONSTRAINT `maintenance_execution_photos_execution_id_foreign` FOREIGN KEY (`execution_id`) REFERENCES `maintenance_executions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_plans`
--
ALTER TABLE `maintenance_plans`
  ADD CONSTRAINT `maintenance_plans_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_plans_maintenance_template_id_foreign` FOREIGN KEY (`maintenance_template_id`) REFERENCES `maintenance_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_template_checklists`
--
ALTER TABLE `maintenance_template_checklists`
  ADD CONSTRAINT `maintenance_template_checklists_maintenance_template_id_foreign` FOREIGN KEY (`maintenance_template_id`) REFERENCES `maintenance_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_template_spareparts`
--
ALTER TABLE `maintenance_template_spareparts`
  ADD CONSTRAINT `maintenance_template_spareparts_maintenance_template_id_foreign` FOREIGN KEY (`maintenance_template_id`) REFERENCES `maintenance_templates` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
