-- SQL Script to create app_settings table manually
-- Run this in your MySQL database 'silsila_keluarga'

CREATE TABLE IF NOT EXISTS `app_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default family name
INSERT INTO `app_settings` (`key`, `value`, `type`, `description`, `created_at`, `updated_at`)
VALUES ('family_name', 'Keluarga Besar', 'string', 'Nama keluarga besar yang ditampilkan di aplikasi', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = 'Keluarga Besar', `updated_at` = NOW();
