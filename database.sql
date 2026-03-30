-- ============================================
-- SKEMA DATABASE APLIKASI PENCATATAN HUTANG
-- ============================================

CREATE DATABASE IF NOT EXISTS `hutang_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `hutang_db`;

-- ----------------------------
-- Tabel Users
-- ----------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
  `avatar` MEDIUMTEXT NULL COMMENT 'Base64 encoded image',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabel Debitur (pihak yang berhutang)
-- ----------------------------
CREATE TABLE `debitur` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL COMMENT 'Pemilik catatan (admin/user pembuat)',
  `nama` VARCHAR(100) NOT NULL,
  `telepon` VARCHAR(20) NULL,
  `alamat` TEXT NULL,
  `catatan` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabel Hutang
-- ----------------------------
CREATE TABLE `hutang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Kode unik misal HT-20240001',
  `user_id` INT UNSIGNED NOT NULL COMMENT 'User pemilik catatan',
  `debitur_id` INT UNSIGNED NOT NULL,
  `jenis` ENUM('hutang','piutang') NOT NULL DEFAULT 'hutang' COMMENT 'hutang=kita berhutang, piutang=orang lain berhutang ke kita',
  `jumlah` DECIMAL(15,2) NOT NULL,
  `sisa` DECIMAL(15,2) NOT NULL COMMENT 'Sisa hutang yang belum dibayar',
  `keterangan` TEXT NULL,
  `tanggal_hutang` DATE NOT NULL,
  `jatuh_tempo` DATE NULL,
  `status` ENUM('belum_lunas','lunas','macet') NOT NULL DEFAULT 'belum_lunas',
  `bukti_gambar` LONGTEXT NULL COMMENT 'Base64 encoded image bukti hutang',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`debitur_id`) REFERENCES `debitur`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabel Pembayaran / Cicilan
-- ----------------------------
CREATE TABLE `pembayaran` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hutang_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL COMMENT 'User yang mencatat pembayaran',
  `jumlah_bayar` DECIMAL(15,2) NOT NULL,
  `tanggal_bayar` DATE NOT NULL,
  `metode` ENUM('tunai','transfer','qris','lainnya') NOT NULL DEFAULT 'tunai',
  `keterangan` TEXT NULL,
  `bukti_gambar` LONGTEXT NULL COMMENT 'Base64 encoded bukti transfer/pembayaran',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`hutang_id`) REFERENCES `hutang`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Tabel Log Aktivitas
-- ----------------------------
CREATE TABLE `log_aktivitas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `aksi` VARCHAR(100) NOT NULL,
  `keterangan` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Data Default: Admin & User
-- ----------------------------
INSERT INTO `users` (`nama`, `username`, `email`, `password`, `role`) VALUES
('Administrator', 'admin', 'admin@hutangapp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Doe', 'john', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
-- Password default: password

-- ----------------------------
-- Contoh Data Debitur
-- ----------------------------
INSERT INTO `debitur` (`user_id`, `nama`, `telepon`, `alamat`, `catatan`) VALUES
(1, 'Budi Santoso', '08123456789', 'Jl. Merdeka No. 10, Jakarta', 'Teman lama'),
(1, 'Siti Rahayu', '08987654321', 'Jl. Sudirman No. 5, Bandung', 'Rekan bisnis'),
(2, 'Ahmad Fauzi', '08111223344', 'Jl. Gatot Subroto No. 20, Surabaya', 'Keluarga');

-- ----------------------------
-- Contoh Data Hutang
-- ----------------------------
INSERT INTO `hutang` (`kode`, `user_id`, `debitur_id`, `jenis`, `jumlah`, `sisa`, `keterangan`, `tanggal_hutang`, `jatuh_tempo`, `status`) VALUES
('HT-20240001', 1, 1, 'piutang', 5000000.00, 3000000.00, 'Pinjaman untuk modal usaha', '2024-01-15', '2024-07-15', 'belum_lunas'),
('HT-20240002', 1, 2, 'hutang', 2500000.00, 2500000.00, 'Hutang beli bahan bangunan', '2024-02-01', '2024-05-01', 'belum_lunas'),
('HT-20240003', 2, 3, 'piutang', 1500000.00, 0.00, 'Pinjaman darurat', '2024-01-20', '2024-04-20', 'lunas');

-- ----------------------------
-- Contoh Data Pembayaran
-- ----------------------------
INSERT INTO `pembayaran` (`hutang_id`, `user_id`, `jumlah_bayar`, `tanggal_bayar`, `metode`, `keterangan`) VALUES
(1, 1, 1000000.00, '2024-03-01', 'transfer', 'Cicilan pertama'),
(1, 1, 1000000.00, '2024-04-15', 'tunai', 'Cicilan kedua'),
(3, 2, 1500000.00, '2024-04-20', 'transfer', 'Pelunasan');
