-- ==========================================================
-- Skema Database: MyBudgetGue
-- Kompatibel dengan MySQL / MariaDB (Laragon & XAMPP)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `mybudgetgue` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mybudgetgue`;

-- Tabel Pengguna (Users)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `unlocked_badges` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Pengaturan (Settings)
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `batas_harian` DECIMAL(15,2) DEFAULT 0,
  `batas_bulanan` DECIMAL(15,2) DEFAULT 0,
  `target_tabungan` DECIMAL(15,2) DEFAULT 0,
  `tabungan_saat_ini` DECIMAL(15,2) DEFAULT 0,
  `notifikasi_aktif` TINYINT(1) DEFAULT 1,
  `waktu_pengingat` VARCHAR(10) DEFAULT '20:00',
  `push_notification` TINYINT(1) DEFAULT 1,
  `email_notification` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_settings_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
