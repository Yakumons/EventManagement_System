-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2026 at 12:44 PM
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
-- Database: `ev_sys`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `created_at`) VALUES
(1, 'cdcdsafd', 'fdsafdsafasdf', '2026-03-21 01:20:37'),
(2, 'IMPORTANT', 'Napaka ganda ng fiance ko <33', '2026-03-21 14:07:54'),
(3, 'Hello', 'omskiry', '2026-03-21 14:41:32'),
(4, 'Bilat', 'seraman', '2026-03-25 05:34:41'),
(6, 'OKIEE NA ATA', 'SUPERGALING SO MUCH', '2026-03-25 11:31:15');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `participant_limit` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `location`, `participant_limit`, `created_by`, `poster`, `start_time`, `end_time`) VALUES
(12, 'hi ', 'goodluck', '2026-03-21', 'caloocan', 22, NULL, 'a904ac74-ded9-4ba5-b8e9-b1e37f335a4e.jpg', NULL, NULL),
(23, 'cause what the fuck is this', 'crimstin baby ko', '2026-03-21', 'Barangay 179, Caloocan', 20, NULL, 'event_poster_69bea601d22055.95579809.png', '10:06:00', '23:06:00'),
(24, 'Last check', 'is this Victory Lap?', '2026-03-21', 'Barangay 176, Caloocan', 1, NULL, 'event_poster_69beb1b9e89f20.95253698.png', '14:00:00', '22:56:00'),
(25, 'CUTEALLY', 'sobraaa', '2026-03-26', 'hausnijajang', 10000, NULL, 'event_poster_69c373b4e5b0e7.87957629.jfif', '01:00:00', '23:59:00'),
(26, 'final testing', 'checking', '2026-03-27', 'Recomville', 5, NULL, 'event_poster_69c3c72c1f20f0.50535928.jfif', '10:00:00', '23:59:00');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `qr_code` varchar(255) DEFAULT NULL,
  `attendance` enum('absent','present') DEFAULT 'absent',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `user_id`, `event_id`, `status`, `qr_code`, `attendance`, `registration_date`) VALUES
(4, 2, 12, 'pending', 'QR69beb56b2fa0a', 'absent', '2026-03-21 15:12:43'),
(5, 2, 23, 'pending', 'QR69beb57f2a601', 'absent', '2026-03-21 15:13:03'),
(6, 2, 24, 'pending', 'QR69beb617bfc52', 'absent', '2026-03-21 15:15:35'),
(7, 2, 25, 'pending', 'QR69c376c232516', 'absent', '2026-03-25 05:46:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@email.com', '$2y$10$I3euyefXa6OTF6CWN6FdgOW1vbd5BG5WtKp7nhaPUMv53iCVnxU72', 'admin', '2026-03-18 21:38:02'),
(2, 'user', 'user@email.com', '$2y$10$hCYHVPSm95Nc.Cjv.gSt1.foHINIMueczGE/qvQD9xVS3mG2tl3J2', 'user', '2026-03-18 21:38:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
