-- =============================================
-- LGN E-Learning - Email Verification System
-- Migration Script for Railway MySQL
-- =============================================
-- Copy paste script ini ke MySQL Workbench, phpMyAdmin, 
-- atau Railway MySQL Console
-- =============================================

-- USE `railway`; -- Ganti dengan nama database Anda jika beda

-- =============================================
-- 1. Tabel pending_registrations
-- =============================================
CREATE TABLE IF NOT EXISTS `pending_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','tutor') DEFAULT 'student',
  `verification_code` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. Tabel password_resets
-- =============================================
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `code` varchar(255) NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `password_resets_ibfk_1` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. Tambah kolom email_verified_at ke tabel users
-- =============================================
-- Check apakah kolom sudah ada, jika belum baru tambahkan
SET @col_exists = (
  SELECT COUNT(*) 
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'email_verified_at'
);

SET @sql = IF(
  @col_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `email_verified_at` timestamp NULL DEFAULT NULL AFTER `is_active`',
  'SELECT "Column email_verified_at already exists" AS Result'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =============================================
-- 4. Verifikasi tabel berhasil dibuat
-- =============================================
SELECT 
  'pending_registrations' AS table_name,
  CASE 
    WHEN COUNT(*) > 0 THEN '✅ EXISTS'
    ELSE '❌ NOT FOUND'
  END AS status
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'pending_registrations'

UNION ALL

SELECT 
  'password_resets' AS table_name,
  CASE 
    WHEN COUNT(*) > 0 THEN '✅ EXISTS'
    ELSE '❌ NOT FOUND'
  END AS status
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'password_resets'

UNION ALL

SELECT 
  'email_verified_at column' AS table_name,
  CASE 
    WHEN COUNT(*) > 0 THEN '✅ EXISTS'
    ELSE '❌ NOT FOUND'
  END AS status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'email_verified_at';

-- =============================================
-- 5. (Opsional) Lihat struktur tabel
-- =============================================
-- DESCRIBE pending_registrations;
-- DESCRIBE password_resets;
-- SHOW CREATE TABLE pending_registrations;
-- SHOW CREATE TABLE password_resets;

-- =============================================
-- SELESAI! 🎉
-- =============================================
-- Tabel sudah siap digunakan untuk:
-- - Email verification saat registrasi
-- - Password reset dengan kode verifikasi
-- =============================================
