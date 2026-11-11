-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2026 at 03:05 AM
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
-- Database: `bimbel_aiueo`
--

-- --------------------------------------------------------

--
-- Table structure for table `guru_kelas`
--

CREATE TABLE `guru_kelas` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tahun_ajaran` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `honor_guru`
--

CREATE TABLE `honor_guru` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `bulan` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `total_hadir` int(11) DEFAULT 0,
  `honor_per_hadir` decimal(10,2) DEFAULT 50000.00,
  `total_honor` decimal(10,2) DEFAULT 0.00,
  `bonus` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','dibayar') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruangan` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id`, `kelas_id`, `hari`, `jam_mulai`, `jam_selesai`, `ruangan`, `created_at`) VALUES
(1, 1, 'Senin', '14:00:00', '15:30:00', 'Ruang 101', '2026-04-22 22:56:23'),
(2, 1, 'Rabu', '14:00:00', '15:30:00', 'Ruang 101', '2026-04-22 22:56:23'),
(3, 2, 'Selasa', '15:00:00', '16:30:00', 'Ruang 102', '2026-04-22 22:56:23'),
(5, 4, 'Jumat', '15:00:00', '16:30:00', 'Ruang 104', '2026-04-22 22:56:23'),
(6, 5, 'Rabu', '12:00:00', '14:00:00', 'Ruang 6', '2026-04-23 00:34:01'),
(7, 2, 'Minggu', '07:00:00', '09:00:00', 'Ruang 6', '2026-04-23 00:34:39'),
(8, 1, 'Selasa', '02:00:00', '03:00:00', 'R1', '2026-04-23 00:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `mata_pelajaran` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kuota` int(11) DEFAULT 20,
  `harga_per_bulan` decimal(10,2) NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `mata_pelajaran`, `deskripsi`, `kuota`, `harga_per_bulan`, `status`, `created_at`) VALUES
(1, 'Matematika A', 'Matematika', 'Kelas Matematika untuk tingkat dasar', 20, 350000.00, 'aktif', '2026-04-22 22:56:23'),
(2, 'Matematika B', 'Matematika', 'Kelas Matematika untuk tingkat lanjut', 15, 400000.00, 'aktif', '2026-04-22 22:56:23'),
(3, 'Fisika D', 'Fisika', 'Kelas Fisika dasar', 20, 350000.00, 'aktif', '2026-04-22 22:56:23'),
(4, 'Bahasa Inggris C', 'Bahasa Inggris', 'Kelas Bahasa Inggris conversation', 25, 300000.00, 'aktif', '2026-04-22 22:56:23'),
(5, 'if1', 'Informatika', 'kelas coding anak kecil', 20, 1000000.00, 'aktif', '2026-04-23 00:31:07');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `pendaftaran_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `metode_pembayaran` enum('transfer','tunai','kartu_kredit') DEFAULT 'transfer',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status` enum('pending','valid','ditolak') DEFAULT 'pending',
  `verifikasi_oleh` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `pendaftaran_id`, `siswa_id`, `jumlah`, `tanggal_bayar`, `metode_pembayaran`, `bukti_pembayaran`, `status`, `verifikasi_oleh`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 4, 4, 1000000.00, '2026-04-23', 'transfer', NULL, 'valid', 8, NULL, '2026-04-23 00:35:25', '2026-04-23 00:35:39'),
(2, 5, 4, 300000.00, '2026-04-23', 'transfer', NULL, 'valid', 8, NULL, '2026-04-23 00:36:05', '2026-04-23 00:38:50'),
(3, 3, 4, 350000.00, '2026-04-23', 'transfer', NULL, 'valid', 8, NULL, '2026-04-23 00:51:53', '2026-04-23 00:52:07');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tanggal_daftar` date NOT NULL,
  `status_pendaftaran` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `invoice_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `siswa_id`, `kelas_id`, `tanggal_daftar`, `status_pendaftaran`, `invoice_number`, `created_at`) VALUES
(1, 4, 2, '2026-04-23', 'pending', 'INV-20260423-3147', '2026-04-22 23:52:35'),
(2, 4, 3, '2026-04-23', 'pending', 'INV-20260423-1689', '2026-04-23 00:12:40'),
(3, 4, 1, '2026-04-23', 'disetujui', 'INV-20260423-3369', '2026-04-23 00:27:03'),
(4, 4, 5, '2026-04-23', 'disetujui', 'INV-20260423-5838', '2026-04-23 00:35:25'),
(5, 4, 4, '2026-04-23', 'disetujui', 'INV-20260423-3866', '2026-04-23 00:36:05');

-- --------------------------------------------------------

--
-- Table structure for table `presensi`
--

CREATE TABLE `presensi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jenis` enum('guru','siswa') NOT NULL,
  `status` enum('hadir','izin','alpha') NOT NULL,
  `waktu` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `presensi`
--

INSERT INTO `presensi` (`id`, `user_id`, `kelas_id`, `tanggal`, `jenis`, `status`, `waktu`, `created_at`) VALUES
(1, 9, NULL, '2026-04-23', 'guru', 'hadir', '2026-04-23 07:16:52', '2026-04-23 00:16:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nomor_telepon` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `nama_lengkap`, `nomor_telepon`, `alamat`, `role`, `status`, `created_at`, `updated_at`) VALUES
(4, 'bibi', '$2y$10$y8QIPwTjKSdzOuuqSWUNEuLf.LrtaXTeLNnLDH69qSDP74l1ft/lC', 'bibi@gmail.com', 'Sasa', '12132', 'gaga', 'siswa', 'aktif', '2026-04-22 22:59:37', '2026-04-22 22:59:37'),
(8, 'admin', '$2y$10$ccpxXMZT3nBtIKXmVTy2OeevF0hoalUtyuinDs/RIV6L5SxUVV.IW', 'admin@bimbel.com', 'Administrator Bimbel', NULL, NULL, 'admin', 'aktif', '2026-04-22 23:03:01', '2026-04-22 23:03:01'),
(9, 'budi.guru', '$2y$10$tYV/1H8B7xsMdepjeePWCeWkrtCywjkCprqAfAn6XQCZzT76Q/NaC', 'budi@bimbel.com', 'Budi Santoso, S.Pd', '081234567890', NULL, 'guru', 'aktif', '2026-04-22 23:03:01', '2026-04-22 23:03:01'),
(10, 'ani.guru', '$2y$10$tYV/1H8B7xsMdepjeePWCeWkrtCywjkCprqAfAn6XQCZzT76Q/NaC', 'ani@bimbel.com', 'Ani Setiani, S.Si', '081234567891', NULL, 'guru', 'aktif', '2026-04-22 23:03:01', '2026-04-22 23:03:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru_kelas`
--
ALTER TABLE `guru_kelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_guru_kelas` (`guru_id`,`kelas_id`,`tahun_ajaran`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `honor_guru`
--
ALTER TABLE `honor_guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_honor` (`guru_id`,`bulan`,`tahun`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_jadwal` (`kelas_id`,`hari`,`jam_mulai`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftaran_id` (`pendaftaran_id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `verifikasi_oleh` (`verifikasi_oleh`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `presensi`
--
ALTER TABLE `presensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_presensi` (`user_id`,`tanggal`,`kelas_id`,`jenis`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru_kelas`
--
ALTER TABLE `guru_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `honor_guru`
--
ALTER TABLE `honor_guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `presensi`
--
ALTER TABLE `presensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru_kelas`
--
ALTER TABLE `guru_kelas`
  ADD CONSTRAINT `guru_kelas_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guru_kelas_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `honor_guru`
--
ALTER TABLE `honor_guru`
  ADD CONSTRAINT `honor_guru_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pembayaran_ibfk_3` FOREIGN KEY (`verifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `presensi`
--
ALTER TABLE `presensi`
  ADD CONSTRAINT `presensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `presensi_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
