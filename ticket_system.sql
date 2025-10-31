-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Oct 30, 2025 at 03:56 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ticket_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `status` enum('Open','In Progress','Resolved') COLLATE utf8mb4_general_ci DEFAULT 'Open',
  `priority` enum('Low','Medium','High') COLLATE utf8mb4_general_ci DEFAULT 'Low',
  `created_by` int DEFAULT NULL,
  `assigned_to` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `title`, `description`, `status`, `priority`, `created_by`, `assigned_to`, `created_at`, `updated_at`, `deleted_at`, `deleted_by`) VALUES
(1, 'problem1', 'issue 1', 'Resolved', 'Medium', 2, 2, '2025-10-09 16:31:23', '2025-10-28 13:48:15', '2025-10-28 19:18:15', NULL),
(4, 'test ticket', 'problem 1', 'In Progress', 'Medium', 2, 3, '2025-10-12 10:18:46', '2025-10-29 16:12:37', NULL, NULL),
(7, 'pro', 'dikkat hogyi', 'Open', 'Low', 5, NULL, '2025-10-14 10:54:08', '2025-10-29 03:53:55', '2025-10-29 09:23:55', NULL),
(8, 'ticket 2', 'problem 2', 'Open', 'Medium', 5, 5, '2025-10-14 11:00:09', '2025-10-29 08:00:14', NULL, NULL),
(9, 'TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest', 'TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTestTestTestTestTestTestTestTestTestTestTestTestTestTest TestTe', 'Open', 'Low', 5, 5, '2025-10-17 09:48:36', '2025-10-29 07:57:17', NULL, NULL),
(10, 'prob', 'lem', 'Open', 'Low', 5, NULL, '2025-10-28 06:51:18', '2025-10-28 06:51:18', NULL, NULL),
(11, 'prob', 'lem', 'Open', 'Low', 5, 2, '2025-10-28 07:05:47', '2025-10-28 12:58:55', '2025-10-28 12:38:10', NULL),
(12, 'problem 5', 'hello', 'Open', 'Medium', 5, NULL, '2025-10-28 12:57:21', '2025-10-28 12:57:21', NULL, NULL),
(13, 'tst', 'testing', 'Open', 'Medium', 7, 7, '2025-10-28 13:01:55', '2025-10-28 13:03:36', NULL, NULL),
(14, '1', '\' OR \'1\'=\'1', 'Open', 'Low', 5, NULL, '2025-10-28 13:10:51', '2025-10-29 03:46:02', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_comments`
--

DROP TABLE IF EXISTS `ticket_comments`;
CREATE TABLE IF NOT EXISTS `ticket_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text COLLATE utf8mb4_general_ci NOT NULL,
  `action_type` enum('Comment','Reassign') COLLATE utf8mb4_general_ci DEFAULT 'Comment',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_comments`
--

INSERT INTO `ticket_comments` (`id`, `ticket_id`, `user_id`, `comment`, `action_type`, `created_at`) VALUES
(1, 1, 2, 'bad ticket', 'Comment', '2025-10-12 10:10:15'),
(2, 4, 2, 'done', 'Comment', '2025-10-12 10:19:49'),
(3, 5, 4, 'make these change', 'Comment', '2025-10-13 07:51:50'),
(4, 5, 4, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-13 07:52:00'),
(5, 5, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-13 11:42:41'),
(6, 5, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-13 11:45:18'),
(7, 5, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-13 11:45:24'),
(8, 5, 5, 'Reassigned to hello', 'Reassign', '2025-10-13 11:52:02'),
(9, 1, 5, 'comment', 'Comment', '2025-10-13 16:04:29'),
(10, 1, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-13 16:04:46'),
(11, 6, 5, 'this is comment', 'Comment', '2025-10-14 04:41:15'),
(12, 6, 5, 'Reassigned to Kapil Rawat — this is note', 'Reassign', '2025-10-14 04:41:32'),
(13, 8, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-16 07:06:24'),
(14, 8, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-16 07:34:14'),
(15, 8, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-16 07:34:22'),
(16, 8, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-16 07:37:38'),
(17, 9, 5, 'Reassigned to Kapil Rawat — OKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOK', 'Reassign', '2025-10-17 09:50:25'),
(18, 9, 5, 'Reassigned to Kapil Rawat — OKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOKOK', 'Reassign', '2025-10-17 09:51:24'),
(19, 9, 5, 'Reassigned to hello', 'Reassign', '2025-10-17 09:52:30'),
(20, 9, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-17 09:52:55'),
(21, 9, 5, 'Reassigned to ADMIN', 'Reassign', '2025-10-17 09:53:00'),
(22, 9, 5, 'Reassigned to sanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsa', 'Reassign', '2025-10-17 09:53:15'),
(23, 9, 5, 'Reassigned to Kapil Rawat', 'Reassign', '2025-10-17 09:53:23'),
(24, 11, 5, 'fix it', 'Comment', '2025-10-28 12:58:41'),
(25, 11, 5, 'Reassigned to Kapil Rawat — do changes', 'Reassign', '2025-10-28 12:58:55'),
(26, 13, 5, 'Reassigned to test2 — do changes', 'Reassign', '2025-10-28 13:02:49'),
(27, 13, 5, 'no', 'Comment', '2025-10-28 13:02:58'),
(28, 4, 2, 'Status changed to \'In Progress\', Priority changed to \'High\'', '', '2025-10-29 07:43:29'),
(29, 4, 2, 'Status changed to \'In Progress\', Priority changed to \'High\', Assigned_to changed to \'5\'', '', '2025-10-29 07:43:43'),
(30, 4, 2, 'Status changed to \'In Progress\', Priority changed to \'Medium\', Assigned_to changed to \'5\'', '', '2025-10-29 07:43:57'),
(31, 9, 2, 'Reassigned to ADMIN', '', '2025-10-29 07:57:00'),
(32, 9, 2, 'Status => Open', '', '2025-10-29 07:57:09'),
(33, 9, 2, 'Priority => Low', '', '2025-10-29 07:57:17'),
(34, 4, 2, 'Status => Resolved, Priority => Low — it is hard', '', '2025-10-29 07:58:18'),
(35, 4, 2, 'hello from comment', 'Comment', '2025-10-29 07:59:09'),
(36, 8, 2, 'Reassigned to ADMIN', '', '2025-10-29 08:00:14'),
(37, 4, 2, 'Status => Open', '', '2025-10-29 08:12:09'),
(38, 4, 2, 'Reassigned to Kapil Rawat', '', '2025-10-29 08:12:32'),
(39, 4, 2, 'Status => Resolved', '', '2025-10-29 08:14:49'),
(40, 4, 2, 'Reassigned to ADMIN — Note: do changes', '', '2025-10-29 08:45:05'),
(41, 4, 2, 'Status => In Progress, Priority => Medium, Reassigned to sanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsa', '', '2025-10-29 12:17:03'),
(42, 4, 2, 'Reassigned to hello', '', '2025-10-29 16:12:37');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_history`
--

DROP TABLE IF EXISTS `ticket_history`;
CREATE TABLE IF NOT EXISTS `ticket_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `from_user` int DEFAULT NULL,
  `to_user` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `from_user` (`from_user`),
  KEY `to_user` (`to_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'hello', 'hello56@gmail.com', '$2y$10$rLgj0x.tVyNfR03lWrWzFuxTgTalvOiDvLW.7PPS070VQZy/h3dry', 'user'),
(2, 'Kapil Rawat', 'krkapil45@gmail.com', '$2y$10$Q5aBgDn5on98IACKAQG77OerSmtcxv/X5qz/foSOFAGefiZoHGdbC', 'user'),
(3, 'hello', 'hello@gmail.com', '$2y$10$q.jMshzQMBiWY0AYGcPtCe0N08pULlBPtYJS/tyB6Uk3gYGtJEAGu', 'user'),
(4, 'sanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsanskarsa', 'hello12@gmail.com', '$2y$10$m8eQTjnRxn4LmlVbslzvc.CaUP72UOmzsp8PO5fy64YmEkTXniUiK', 'user'),
(5, 'ADMIN', 'admin@gmail.com', '$2y$10$0L23J0ADcitj5UG8LrwLYuMzsjbutnBz9QvQ3vkxfRCRbufNcXkkK', 'admin'),
(6, 'test', 'test@gmail.com', '$2y$10$GcWpRJJKmt8tbkqwM/Y41evpv34Fv2OsHxaPXgQsTR7BvAbLKOGGi', 'user'),
(7, 'test2', 'test2@gmail.com', '$2y$10$x7QA9FQ4OE33ZhuS.NasuOafkX9AZ5MQuMm4BB6OTDsqxX7dMby6W', 'user');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
