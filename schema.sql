-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 11. Jan 2026 um 18:55
-- Server-Version: 10.4.28-MariaDB
-- PHP-Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `webshop`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `street` varchar(150) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `first_name`, `last_name`, `street`, `postal_code`, `city`, `country`, `created_at`, `is_default`) VALUES
(1, 4, 'admin', 'admin', 'straße 1', '1220', 'Wien', 'Österreich', '2026-01-04 14:42:10', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Bücher & Skripten', NULL),
(3, 'Elektronik', NULL),
(4, 'Taschen & Rucksäcke', NULL),
(6, 'Zubehör & Organisation', NULL),
(7, 'Sonstiges', NULL),
(8, 'Computer', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `created_at`) VALUES
(1, 2, 600.00, NULL, '2025-12-30 14:27:01'),
(2, 2, 600.00, NULL, '2025-12-30 14:27:35'),
(3, 2, 600.00, NULL, '2025-12-30 14:30:22'),
(4, 2, 600.00, NULL, '2025-12-30 14:32:25'),
(5, 2, 39.80, NULL, '2025-12-30 15:15:59'),
(6, 2, 59.70, NULL, '2025-12-30 15:30:46'),
(7, 2, 19.90, NULL, '2025-12-30 15:35:38'),
(8, 2, 19.90, NULL, '2025-12-30 16:10:22'),
(9, 6, 19.90, NULL, '2025-12-30 16:19:57'),
(10, 6, 19.90, NULL, '2025-12-30 16:26:34'),
(11, 2, 19.90, NULL, '2025-12-30 17:13:53'),
(12, 2, 39.88, NULL, '2025-12-30 21:11:52'),
(13, 4, 19.90, NULL, '2025-12-30 21:45:21'),
(14, 2, 29.89, NULL, '2026-01-04 13:32:58'),
(16, 4, 9.99, 'paid', '2026-01-04 17:04:28'),
(18, 7, 10000.00, 'paid', '2026-01-04 17:13:23'),
(20, 4, 59.70, 'paid', '2026-01-04 18:33:21'),
(21, 4, 100.00, 'paid', '2026-01-05 14:07:09'),
(22, 4, 69.77, 'paid', '2026-01-05 14:18:55'),
(23, 2, 69.80, 'paid', '2026-01-06 19:09:54'),
(25, 4, 10079.89, 'paid', '2026-01-09 13:30:05'),
(26, 4, 600.00, 'paid', '2026-01-10 16:35:47'),
(28, 4, 100.00, 'paid', '2026-01-10 16:37:56'),
(34, 4, 11029.89, 'paid', '2026-01-11 00:27:53'),
(35, 4, 1000.00, 'paid', '2026-01-11 00:29:11'),
(36, 4, 7000.00, 'paid', '2026-01-11 00:29:46'),
(37, 4, 19.90, 'paid', '2026-01-11 00:37:54'),
(38, 4, 3000.00, 'paid', '2026-01-11 00:45:29'),
(39, 4, 1000.00, 'paid', '2026-01-11 00:45:42'),
(41, 4, 1000.00, 'paid', '2026-01-11 00:47:29');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `delivery_status` enum('neu','in_bearbeitung','versendet','zugestellt') NOT NULL DEFAULT 'neu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `delivery_status`) VALUES
(1, 4, 1, 1, 600.00, 'neu'),
(2, 5, 3, 2, 19.90, 'neu'),
(3, 6, 3, 3, 19.90, 'neu'),
(4, 7, 3, 1, 19.90, 'neu'),
(5, 8, 3, 1, 19.90, 'neu'),
(6, 9, 3, 1, 19.90, 'neu'),
(7, 10, 3, 1, 19.90, 'neu'),
(8, 11, 3, 1, 19.90, 'neu'),
(9, 12, 3, 1, 19.90, 'versendet'),
(10, 12, 4, 1, 9.99, 'versendet'),
(11, 12, 5, 1, 9.99, 'versendet'),
(12, 13, 3, 1, 19.90, 'neu'),
(13, 14, 3, 1, 19.90, 'neu'),
(14, 14, 4, 1, 9.99, 'versendet'),
(16, 16, 4, 1, 9.99, 'versendet'),
(18, 18, 6, 1, 10000.00, 'zugestellt'),
(20, 20, 3, 3, 19.90, 'in_bearbeitung'),
(21, 21, 7, 1, 100.00, 'neu'),
(22, 22, 3, 2, 19.90, 'neu'),
(23, 22, 4, 3, 9.99, 'neu'),
(24, 23, 3, 2, 19.90, 'zugestellt'),
(25, 23, 5, 3, 10.00, 'zugestellt'),
(30, 25, 3, 1, 19.90, 'zugestellt'),
(31, 25, 4, 1, 9.99, 'zugestellt'),
(32, 25, 5, 1, 50.00, 'zugestellt'),
(33, 25, 6, 1, 10000.00, 'zugestellt'),
(34, 26, 10, 6, 100.00, 'neu'),
(35, 28, 10, 1, 100.00, 'zugestellt'),
(46, 34, 3, 1, 19.90, 'neu'),
(47, 34, 4, 1, 9.99, 'neu'),
(48, 34, 6, 1, 10000.00, 'neu'),
(49, 34, 11, 1, 1000.00, 'neu'),
(50, 35, 11, 1, 1000.00, 'neu'),
(51, 36, 11, 7, 1000.00, 'neu'),
(52, 0, 3, 1, 19.90, 'neu'),
(53, 37, 3, 1, 19.90, 'neu'),
(54, 38, 11, 3, 1000.00, 'neu'),
(55, 39, 11, 1, 1000.00, 'neu'),
(57, 41, 11, 1, 1000.00, 'neu');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_holder` varchar(100) NOT NULL,
  `card_last4` char(4) NOT NULL,
  `card_brand` varchar(50) NOT NULL,
  `expiry_month` tinyint(4) NOT NULL,
  `expiry_year` smallint(6) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `card_holder`, `card_last4`, `card_brand`, `expiry_month`, `expiry_year`, `is_default`, `created_at`) VALUES
(1, 4, '123', '2312', '123', 12, 2043, 1, '2026-01-10 19:50:19'),
(2, 4, '123', '123', '123', 12, 22222, 0, '2026-01-10 19:55:34');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `image`, `category_id`) VALUES
(139, 'Prüfungsheld Analysis', 14.90, 'Kompaktes Skript zur Prüfungsvorbereitung', 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=800&h=600&fit=crop&auto=format', 1),
(140, 'Mathe Basics kompakt', 12.50, 'Grundlagen verständlich erklärt', 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=800&h=600&fit=crop&auto=format', 1),
(141, 'Programmieren leicht gemacht', 18.90, 'Einführung für Anfänger', 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=800&h=600&fit=crop&auto=format', 1),
(142, 'Lernskript Wirtschaft', 16.00, 'Zusammenfassung wichtiger Inhalte', 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=800&h=600&fit=crop&auto=format', 1),
(143, 'Formelsammlung Technik', 11.90, 'Alle relevanten Formeln auf einen Blick', 'https://images.unsplash.com/photo-1457694587812-e8bf29a43845?w=800&h=600&fit=crop&auto=format', 1),
(144, 'Prüfungstraining Statistik', 15.50, 'Übungsaufgaben mit Lösungen', 'https://images.unsplash.com/photo-1528207776546-365bb710ee93?w=800&h=600&fit=crop&auto=format', 1),
(145, 'Grundlagen der Informatik', 19.90, 'Ideal für den Studienstart', 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?w=800&h=600&fit=crop&auto=format', 1),
(146, 'Projektmanagement kompakt', 17.90, 'Praxisnah und übersichtlich', 'https://images.unsplash.com/photo-1509266272358-7701da638078?w=800&h=600&fit=crop&auto=format', 1),
(147, 'Recht für Studierende', 13.90, 'Wichtige Gesetze einfach erklärt', 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=800&h=600&fit=crop&auto=format', 1),
(148, 'Wissenschaftlich schreiben', 16.90, 'Tipps für Seminar- und Abschlussarbeiten', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&h=600&fit=crop&auto=format', 1),
(154, 'CampusBook Pro 14', 899.00, 'Leichter Laptop für Studium und Alltag', 'https://picsum.photos/id/180/800/600', 8),
(155, 'ThinkNote X15', 749.00, 'Solider Allround-Laptop mit SSD', 'https://picsum.photos/id/180/800/600', 8),
(156, 'StudyPad 11', 399.00, 'Tablet ideal für Mitschriften', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&h=600&fit=crop&auto=format', 3),
(158, 'Ergo Maus', 29.90, 'Ergonomische Maus für lange Sessions', 'https://images.unsplash.com/photo-1580894908361-967195033215?w=800&h=600&fit=crop&auto=format', 3),
(159, 'USB-C Dock', 69.90, 'Anschlussstation für mehrere Geräte', 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&h=600&fit=crop&auto=format', 3),
(160, 'Webcam HD', 59.90, 'Perfekt für Online-Vorlesungen', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&h=600&fit=crop&auto=format', 3),
(161, 'Polaroid Kamera', 39.90, 'Verbesserte Ergonomie am Schreibtisch', 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=800&h=600&fit=crop&auto=format', 3),
(163, 'Noise Cancelling Headset', 129.00, 'Fokus beim Lernen', 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800&h=600&fit=crop&auto=format', 3),
(169, 'Premium Kugelschreiber', 4.90, 'Angenehmes Schreibgefühl', 'https://images.unsplash.com/photo-1519682337058-a94d519337bc?w=800&h=600&fit=crop&auto=format', NULL),
(170, 'Textmarker Set Pastell', 6.90, 'Ideal für Lernunterlagen', 'https://images.unsplash.com/photo-1508061253366-f7da158b6d46?w=800&h=600&fit=crop&auto=format', NULL),
(171, 'Notizblock A4 kariert', 3.50, 'Perfekt für Vorlesungen', 'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=800&h=600&fit=crop&auto=format', NULL),
(172, 'Fineliner Set 6er', 7.90, 'Präzise Linien für Skizzen', 'https://images.unsplash.com/photo-1498079022511-d15614cb1c02?w=800&h=600&fit=crop&auto=format', NULL),
(173, 'Bleistift HB 10er', 2.90, 'Klassiker für Schule & Studium', 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&h=600&fit=crop&auto=format', NULL),
(174, 'Collegeblock A5', 2.50, 'Handlich und praktisch', 'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?w=800&h=600&fit=crop&auto=format', NULL),
(175, 'Radierer Soft Grip', 1.50, 'Sauberes Radieren ohne Schmieren', 'https://images.unsplash.com/photo-1487014679447-9f8336841d58?w=800&h=600&fit=crop&auto=format', NULL),
(176, 'Lineal Aluminium 30cm', 3.90, 'Stabil und präzise', 'https://images.unsplash.com/photo-1509021436665-8f07dbf5bf1d?w=800&h=600&fit=crop&auto=format', NULL),
(177, 'Heftstreifen Set', 1.90, 'Lose Blätter einfach abheften', 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?w=800&h=600&fit=crop&auto=format', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL,
  `text` text DEFAULT NULL,
  `helpful` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `author_name`, `rating`, `text`, `helpful`, `created_at`) VALUES
(1, 3, 4, 'admin', 5, 'passt', 3, '2026-01-06 19:11:44'),
(2, 7, 4, 'admin', 3, 'sollte nicht verifizieren', 2, '2026-01-04 19:17:20'),
(3, 3, 8, 'Test Test', 0, 'test?', 3, '2026-01-04 19:36:30'),
(4, 4, 4, 'admin', 1, 'arsch', 1, '2026-01-04 20:17:56'),
(5, 6, 4, 'admin', 1, 'müll', 2, '2026-01-04 20:30:25'),
(6, 3, 2, 'tester', 1, 'kake', 1, '2026-01-05 13:54:33'),
(7, 3, 9, 'big', 5, 'nice', 2, '2026-01-06 19:20:46'),
(8, 8, 4, 'admin', 5, 'eh ok', 1, '2026-01-09 13:11:04'),
(9, 5, 4, 'admin', 5, 'tetstetstetstetstets', 0, '2026-01-10 23:46:10');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `review_helpful_votes`
--

CREATE TABLE `review_helpful_votes` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `review_helpful_votes`
--

INSERT INTO `review_helpful_votes` (`id`, `review_id`, `user_id`, `created_at`) VALUES
(1, 2, 4, '2026-01-04 19:21:40'),
(8, 2, 8, '2026-01-04 19:22:14'),
(11, 3, 8, '2026-01-04 19:36:37'),
(12, 1, 8, '2026-01-04 19:36:39'),
(17, 3, 4, '2026-01-04 20:15:45'),
(18, 1, 4, '2026-01-04 20:15:48'),
(22, 4, 4, '2026-01-04 20:17:59'),
(23, 5, 4, '2026-01-04 20:30:28'),
(25, 5, 2, '2026-01-04 20:30:48'),
(31, 1, 2, '2026-01-05 13:53:27'),
(32, 3, 2, '2026-01-05 13:53:29'),
(35, 6, 2, '2026-01-05 13:54:36'),
(41, 7, 9, '2026-01-06 19:20:50'),
(43, 8, 4, '2026-01-09 13:11:09'),
(45, 7, 4, '2026-01-10 23:47:10');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stock`
--

CREATE TABLE `stock` (
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `stock`
--

INSERT INTO `stock` (`product_id`, `quantity`, `updated_at`) VALUES
(139, 45, '2026-01-11 18:14:32'),
(140, 29, '2026-01-11 18:14:32'),
(141, 48, '2026-01-11 18:14:32'),
(142, 14, '2026-01-11 18:14:32'),
(143, 11, '2026-01-11 18:14:32'),
(144, 8, '2026-01-11 18:14:32'),
(145, 50, '2026-01-11 18:14:32'),
(146, 37, '2026-01-11 18:14:32'),
(147, 29, '2026-01-11 18:14:32'),
(148, 29, '2026-01-11 18:14:32'),
(154, 30, '2026-01-11 18:14:32'),
(155, 44, '2026-01-11 18:14:32'),
(156, 0, '2026-01-11 18:11:33'),
(158, 0, '2026-01-11 18:11:14'),
(159, 0, '2026-01-11 18:11:06'),
(160, 0, '2026-01-11 18:10:57'),
(161, 0, '2026-01-11 18:10:49'),
(163, 0, '2026-01-11 18:10:32'),
(169, 7, '2026-01-11 18:14:32'),
(170, 36, '2026-01-11 18:14:32'),
(171, 17, '2026-01-11 18:14:32'),
(172, 17, '2026-01-11 18:14:32'),
(173, 32, '2026-01-11 18:14:32'),
(174, 13, '2026-01-11 18:14:32'),
(175, 11, '2026-01-11 18:14:32'),
(176, 10, '2026-01-11 18:14:32'),
(177, 15, '2026-01-11 18:14:32');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `email`, `name`, `password`, `role`, `active`) VALUES
(1, 'oidr@gmail.com', NULL, '$2y$10$ukecdBVrwnmVgAPEJKqrXOnXQ4fQ7Xq8GpGD63HpIyOWPgaELYF2y', 'user', 1),
(2, 'test@test.at', NULL, '$2y$10$Xr6ObL8ByHWbPCTVS1wguuxf5RwStLSAkS0H8Mkn2DckXlMGw5haa', 'user', 1),
(3, 'oktay@oktay.at', NULL, '$2y$10$fhXXV3rCefaGtK.LmghijO800eBL4opgNZx5CZHnQr7KOdwAX/u7W', 'user', 1),
(4, 'admin@admin.at', NULL, '$2y$10$hyGRO/7.llDEY2.2Cps1L.wh1CYL.BJ6rOqWqo4hZHHPeucCYjMGK', 'admin', 1),
(5, 'max@muster.at', NULL, '$2y$10$cnFbr/ET/oWVvOj/4nxwXuSSLvjAJQJIG8FnjBJlKdyreN9IxFNtm', 'user', 0),
(6, 't@t.at', NULL, '$2y$10$Cx/MGG9qh05O/y7a.LUZ1.4.kAKMt3SQOLp4bRceyT0dd769jX3LK', 'user', 1),
(7, 'test@test2.at', NULL, '$2y$10$Wymcs0ASZWC4HvKV0uLp.e/EytOq/ES72v0nMj9DFvoU/w3B45nli', 'user', 1),
(8, 'test2@test2.at', NULL, '$2y$10$LcEhX2nFl6lxCAsLzr1uW.qOWraDRevju48EZz6Zi.0CPSase5y6a', 'user', 1),
(9, 'luke.jaros@gmail.com', NULL, '$2y$10$ZyGrZMjQmR0v5PZB2vfQfuvcaB0kJ.s0Xif8jUC2b3FxBd7sqMzwe', 'user', 1),
(10, 'ljaro.beats@gmail.com', NULL, '$2y$10$VtUgij.R0Xts3AmkSjJsu.TYvnQnvCIc4lWZaghrVN/mg86DtzEOe', 'user', 1);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_category_name` (`name`);

--
-- Indizes für die Tabelle `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indizes für die Tabelle `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_user` (`user_id`);

--
-- Indizes für die Tabelle `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_category` (`category_id`);

--
-- Indizes für die Tabelle `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `review_helpful_votes`
--
ALTER TABLE `review_helpful_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`review_id`,`user_id`),
  ADD KEY `fk_review_helpful_user` (`user_id`);

--
-- Indizes für die Tabelle `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`product_id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT für Tabelle `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT für Tabelle `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT für Tabelle `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=244;

--
-- AUTO_INCREMENT für Tabelle `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `review_helpful_votes`
--
ALTER TABLE `review_helpful_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints der Tabelle `review_helpful_votes`
--
ALTER TABLE `review_helpful_votes`
  ADD CONSTRAINT `fk_review_helpful_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_helpful_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
