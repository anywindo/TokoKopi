-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 29, 2025 at 07:34 AM
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
-- Database: `pwd_coffeeshop`
--
CREATE DATABASE pwd_coffeeshop;
USE pwd_coffeeshop;
-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `id_branch` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`id_branch`, `nama`, `alamat`) VALUES
(1, 'brew. Jogja', 'Jl. Kaliurang No. 123, Yogyakarta'),
(2, 'brew. Solo', 'Jl. Slamet Riyadi No. 45, Surakarta'),
(3, 'brew. Jakarta', 'Jl. Jenderal Sudirman No. 10, Jakarta Pusat'),
(4, 'brew. Bandung', 'Jl. Ir. H. Juanda No. 77, Bandung'),
(5, 'brew. Surabaya', 'Jl. Tunjungan No. 8, Surabaya');

-- --------------------------------------------------------

--
-- Table structure for table `omzet`
--

CREATE TABLE `omzet` (
  `id_laporan` int(11) NOT NULL,
  `id_pelapor` int(11) DEFAULT NULL,
  `id_branch` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `omzet` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `omzet`
--

INSERT INTO `omzet` (`id_laporan`, `id_pelapor`, `id_branch`, `tanggal`, `omzet`) VALUES
(1, 2, 1, '2025-11-20', 3550000),
(2, 2, 1, '2025-11-21', 4025000),
(3, 2, 1, '2025-11-22', 3780000),
(4, 2, 1, '2025-11-23', 4155000),
(5, 2, 1, '2025-11-24', 3890000),
(6, 3, 2, '2025-11-20', 2120000),
(7, 3, 2, '2025-11-21', 2305000),
(8, 3, 2, '2025-11-22', 1987500),
(9, 3, 2, '2025-11-23', 2450000),
(10, 3, 2, '2025-11-24', 2215000),
(11, 4, 3, '2025-11-20', 5250000),
(12, 4, 3, '2025-11-21', 5485000),
(13, 4, 3, '2025-11-22', 5632500),
(14, 4, 3, '2025-11-23', 5900000),
(15, 4, 3, '2025-11-24', 5727500),
(16, 5, 4, '2025-11-20', 2980000),
(17, 5, 4, '2025-11-21', 3105000),
(18, 5, 4, '2025-11-22', 3277500),
(19, 5, 4, '2025-11-23', 2890000),
(20, 5, 4, '2025-11-24', 3012500),
(21, 6, 5, '2025-11-20', 3400000),
(22, 6, 5, '2025-11-21', 3525000),
(23, 6, 5, '2025-11-22', 3657500),
(24, 6, 5, '2025-11-23', 3790000),
(25, 6, 5, '2025-11-24', 3922500),
(26, 2, 1, '2025-11-25', 4020000),
(27, 2, 1, '2025-11-26', 3950000),
(28, 2, 1, '2025-11-27', 4200500),
(29, 2, 1, '2025-11-28', 4010250),
(30, 3, 2, '2025-11-25', 2187500),
(31, 3, 2, '2025-11-26', 2260000),
(32, 3, 2, '2025-11-27', 2395000),
(33, 3, 2, '2025-11-28', 2327500),
(34, 4, 3, '2025-11-25', 5815000),
(35, 4, 3, '2025-11-26', 5690000),
(36, 4, 3, '2025-11-27', 6122500),
(37, 4, 3, '2025-11-28', 5987500),
(38, 5, 4, '2025-11-25', 3075000),
(39, 5, 4, '2025-11-26', 2952500),
(40, 5, 4, '2025-11-27', 3180000),
(41, 5, 4, '2025-11-28', 3237500),
(42, 6, 5, '2025-11-25', 3980000),
(43, 6, 5, '2025-11-26', 3865000),
(44, 6, 5, '2025-11-27', 4057500),
(45, 6, 5, '2025-11-28', 4172500),
(46, 4, 3, '2025-11-29', 8400000);

-- --------------------------------------------------------

--
-- Table structure for table `pemakaian`
--

CREATE TABLE `pemakaian` (
  `id_laporan` int(11) NOT NULL,
  `id_pelapor` int(11) DEFAULT NULL,
  `id_branch` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `arabica` float DEFAULT NULL,
  `robusta` float DEFAULT NULL,
  `liberica` float DEFAULT NULL,
  `decaf` float DEFAULT NULL,
  `susu` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemakaian`
--

INSERT INTO `pemakaian` (`id_laporan`, `id_pelapor`, `id_branch`, `tanggal`, `arabica`, `robusta`, `liberica`, `decaf`, `susu`) VALUES
(1, 2, 1, '2025-11-20', 3.5, 2, 0.8, 0.3, 11.5),
(2, 2, 1, '2025-11-21', 3.8, 2.1, 0.7, 0.4, 12),
(3, 2, 1, '2025-11-22', 3.6, 1.9, 0.9, 0.2, 11),
(4, 2, 1, '2025-11-23', 4, 2.2, 0.8, 0.3, 12.3),
(5, 2, 1, '2025-11-24', 3.7, 2, 0.6, 0.4, 11.8),
(6, 3, 2, '2025-11-20', 2.4, 1.5, 0.5, 0.1, 8.2),
(7, 3, 2, '2025-11-21', 2.6, 1.6, 0.4, 0.2, 8.5),
(8, 3, 2, '2025-11-22', 2.3, 1.4, 0.6, 0.1, 7.9),
(9, 3, 2, '2025-11-23', 2.7, 1.5, 0.5, 0.2, 8.7),
(10, 3, 2, '2025-11-24', 2.5, 1.6, 0.4, 0.2, 8.3),
(11, 4, 3, '2025-11-20', 4.8, 2.8, 1.1, 0.5, 15),
(12, 4, 3, '2025-11-21', 5, 2.9, 1, 0.6, 15.5),
(13, 4, 3, '2025-11-22', 4.9, 2.7, 1.2, 0.5, 14.8),
(14, 4, 3, '2025-11-23', 5.2, 3, 1.1, 0.6, 16),
(15, 4, 3, '2025-11-24', 5.1, 2.9, 1, 0.7, 15.7),
(16, 5, 4, '2025-11-20', 3, 1.9, 0.7, 0.3, 10.2),
(17, 5, 4, '2025-11-21', 3.2, 2, 0.6, 0.3, 10.8),
(18, 5, 4, '2025-11-22', 3.1, 1.8, 0.7, 0.2, 10),
(19, 5, 4, '2025-11-23', 3.3, 2.1, 0.6, 0.3, 11.1),
(20, 5, 4, '2025-11-24', 3, 2, 0.5, 0.4, 10.6),
(21, 6, 5, '2025-11-20', 3.4, 2.1, 0.8, 0.3, 11),
(22, 6, 5, '2025-11-21', 3.6, 2.2, 0.7, 0.3, 11.4),
(23, 6, 5, '2025-11-22', 3.5, 2, 0.9, 0.4, 11.8),
(24, 6, 5, '2025-11-23', 3.8, 2.3, 0.8, 0.4, 12.2),
(25, 6, 5, '2025-11-24', 3.7, 2.1, 0.7, 0.5, 12),
(26, 2, 1, '2025-11-25', 3.9, 2.1, 0.7, 0.3, 12.1),
(27, 2, 1, '2025-11-26', 3.6, 1.9, 0.8, 0.4, 11.4),
(28, 2, 1, '2025-11-27', 4.1, 2.2, 0.9, 0.3, 12.6),
(29, 2, 1, '2025-11-28', 3.8, 2, 0.7, 0.4, 11.9),
(30, 3, 2, '2025-11-25', 2.4, 1.5, 0.5, 0.2, 8.1),
(31, 3, 2, '2025-11-26', 2.6, 1.7, 0.4, 0.1, 8.4),
(32, 3, 2, '2025-11-27', 2.7, 1.6, 0.6, 0.2, 8.8),
(33, 3, 2, '2025-11-28', 2.5, 1.5, 0.5, 0.2, 8.3),
(34, 4, 3, '2025-11-25', 5, 2.8, 1.2, 0.6, 15.6),
(35, 4, 3, '2025-11-26', 4.7, 2.6, 1, 0.5, 14.9),
(36, 4, 3, '2025-11-27', 5.3, 3.1, 1.3, 0.6, 16.2),
(37, 4, 3, '2025-11-28', 5.1, 2.9, 1.1, 0.7, 15.8),
(38, 5, 4, '2025-11-25', 3.1, 2, 0.6, 0.3, 10.7),
(39, 5, 4, '2025-11-26', 2.9, 1.8, 0.7, 0.2, 10.1),
(40, 5, 4, '2025-11-27', 3.4, 2.1, 0.6, 0.3, 11),
(41, 5, 4, '2025-11-28', 3.2, 2, 0.5, 0.4, 10.9),
(42, 6, 5, '2025-11-25', 3.8, 2.2, 0.8, 0.4, 11.7),
(43, 6, 5, '2025-11-26', 3.5, 2, 0.9, 0.3, 11.2),
(44, 6, 5, '2025-11-27', 3.9, 2.3, 0.8, 0.4, 12.1),
(45, 6, 5, '2025-11-28', 3.6, 2.1, 0.7, 0.5, 11.6),
(46, 4, 3, '2025-11-29', 5, 5, 5, 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('manager','corporate') DEFAULT NULL,
  `telp` varchar(50) DEFAULT NULL,
  `id_branch` int(11) DEFAULT NULL,
  `photo` LONGBLOB DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `telp`, `id_branch`, `photo`) VALUES
(1, 'corp_main', '$2y$10$AF4ak4hdmVE.6BKXCu3J7eoUIoX4u8.CKNxvGLn8asRcEZF9HlTCy', 'corporate', '081200000001', NULL, NULL),
(2, 'mgr_jogja', '$2y$10$TfKNcqA22xUfzeQce6OADOepxR40ymtEygV0MDLbiGgHFw8SZBnRG', 'manager', '081300000001', 1, NULL),
(3, 'mgr_solo', '$2y$10$DxmJP/HLkWvDcQf/MYpDIeAP4QU.hJY2acEom3K.nwNTp5ZOC9oJq', 'manager', '081300000002', 2, NULL),
(4, 'mgr_jakarta', '$2y$10$lesfEA04StNMUFKp4pM8iuw2H11vOKxtprRbN/tyNbuVvr7/fsNd6', 'manager', '081300000003', 3, NULL),
(5, 'mgr_bandung', '$2y$10$Z5UQo.oZnr9KfbSdrSd6/uGBEJrAXhb0DxAns8PBVTp5GZMXJu.zu', 'manager', '081300000004', 4, NULL),
(6, 'mgr_surabaya', '$2y$10$o2AIKihmXwGhp8FKYZDwWOLI3r6YmUKVgXfDqIhqxP4cnLYcGM1.K', 'manager', '081300000005', 5, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`id_branch`);

--
-- Indexes for table `omzet`
--
ALTER TABLE `omzet`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_pelapor` (`id_pelapor`),
  ADD KEY `id_branch` (`id_branch`);

--
-- Indexes for table `pemakaian`
--
ALTER TABLE `pemakaian`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_pelapor` (`id_pelapor`),
  ADD KEY `id_branch` (`id_branch`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_branch` (`id_branch`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `id_branch` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `omzet`
--
ALTER TABLE `omzet`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `pemakaian`
--
ALTER TABLE `pemakaian`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `omzet`
--
ALTER TABLE `omzet`
  ADD CONSTRAINT `omzet_ibfk_1` FOREIGN KEY (`id_pelapor`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `omzet_ibfk_2` FOREIGN KEY (`id_branch`) REFERENCES `branch` (`id_branch`);

--
-- Constraints for table `pemakaian`
--
ALTER TABLE `pemakaian`
  ADD CONSTRAINT `pemakaian_ibfk_1` FOREIGN KEY (`id_pelapor`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `pemakaian_ibfk_2` FOREIGN KEY (`id_branch`) REFERENCES `branch` (`id_branch`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_branch`) REFERENCES `branch` (`id_branch`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
