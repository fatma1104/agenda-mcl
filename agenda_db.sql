-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 12:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agenda_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `calendars`
--

CREATE TABLE `calendars` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `color` varchar(7) DEFAULT '#4361ee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendars`
--

INSERT INTO `calendars` (`id`, `user_id`, `name`, `description`, `created_at`, `color`) VALUES
(1, 3, 'entretient', '', '2025-05-12 15:55:50', '#4361ee'),
(2, 5, ' email', '', '2025-05-12 16:18:55', '#ed07b0'),
(3, 5, 'm click ', '', '2025-05-12 19:45:27', '#44ee50'),
(4, 5, 'hayet', '', '2025-05-13 11:48:46', '#db5533');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `start`, `end`, `user_id`, `calendar_id`, `description`, `color`) VALUES
(5, 'confirmation  .', '2025-05-12 20:27:00', '2025-05-14 09:27:00', 5, 2, '', NULL),
(6, 'envoie', '2025-05-14 08:42:00', '2025-05-14 20:42:00', 5, 2, '', NULL),
(7, 'mclick ', '2025-05-22 09:30:00', '2025-05-15 10:30:00', 3, 1, 'entretient jeudi mclick ', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shared_calendars`
--

CREATE TABLE `shared_calendars` (
  `id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  `shared_with_user_id` int(11) NOT NULL,
  `access_level` enum('lecture','edition') DEFAULT 'lecture',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shared_calendars`
--

INSERT INTO `shared_calendars` (`id`, `calendar_id`, `shared_with_user_id`, `access_level`, `created_at`, `updated_at`) VALUES
(2, 1, 5, 'lecture', '2025-05-12 20:02:29', '2025-05-12 20:02:29'),
(3, 2, 3, 'lecture', '2025-05-12 20:02:29', '2025-05-12 20:02:29'),
(4, 1, 5, 'edition', '2025-05-13 10:47:47', '2025-05-13 10:47:47'),
(5, 3, 3, 'edition', '2025-05-13 10:49:45', '2025-05-13 10:49:45'),
(6, 2, 5, 'edition', '2025-05-14 22:26:16', '2025-05-14 22:26:16');

-- --------------------------------------------------------

--
-- Table structure for table `shared_events`
--

CREATE TABLE `shared_events` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `shared_with_user_id` int(11) NOT NULL,
  `shared_by_user_id` int(11) DEFAULT NULL,
  `access_level` enum('lecture','edition') DEFAULT 'lecture',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shared_events`
--

INSERT INTO `shared_events` (`id`, `event_id`, `shared_with_user_id`, `shared_by_user_id`, `access_level`, `created_at`, `updated_at`) VALUES
(1, 7, 5, 3, 'lecture', '2025-05-13 00:25:52', '2025-05-13 00:25:52'),
(2, 5, 3, 5, 'edition', '2025-05-13 22:29:34', '2025-05-13 22:29:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(3, 'fatma zohra berchiche', 'berchichefatmazohra@gmail.com', '$2y$10$yVk3H9O0yyqIBbwLx/ne3e9l38u4.LtfhQgu7ora5iPAnbIKQdZ62'),
(5, 'hayet ', 'hayet@gmail.com', '$2y$10$m1Ojyl68ePNxXFxeoHEtSO8sS3bDU7DefND3p0br2yX1nHdOy1KUa');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calendars`
--
ALTER TABLE `calendars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_calendar` (`calendar_id`);

--
-- Indexes for table `shared_calendars`
--
ALTER TABLE `shared_calendars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calendar_id` (`calendar_id`),
  ADD KEY `shared_with_user_id` (`shared_with_user_id`);

--
-- Indexes for table `shared_events`
--
ALTER TABLE `shared_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `shared_with_user_id` (`shared_with_user_id`),
  ADD KEY `fk_shared_by_user` (`shared_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calendars`
--
ALTER TABLE `calendars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `shared_calendars`
--
ALTER TABLE `shared_calendars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `shared_events`
--
ALTER TABLE `shared_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `calendars`
--
ALTER TABLE `calendars`
  ADD CONSTRAINT `calendars_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_calendar` FOREIGN KEY (`calendar_id`) REFERENCES `calendars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shared_calendars`
--
ALTER TABLE `shared_calendars`
  ADD CONSTRAINT `shared_calendars_ibfk_1` FOREIGN KEY (`calendar_id`) REFERENCES `calendars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shared_calendars_ibfk_2` FOREIGN KEY (`shared_with_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shared_events`
--
ALTER TABLE `shared_events`
  ADD CONSTRAINT `fk_shared_by_user` FOREIGN KEY (`shared_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shared_events_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shared_events_ibfk_2` FOREIGN KEY (`shared_with_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
