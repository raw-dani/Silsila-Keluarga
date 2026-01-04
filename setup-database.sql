-- ===================================================================
-- Database Setup Script for Aplikasi Silsila Keluarga
-- Jalankan di phpMyAdmin atau MySQL Command Line
-- ===================================================================

-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS silsila_keluarga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Buat user database
CREATE USER IF NOT EXISTS 'silsila_user'@'localhost' IDENTIFIED BY 'password123';

-- Berikan semua privileges ke user
GRANT ALL PRIVILEGES ON silsila_keluarga.* TO 'silsila_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Pilih database untuk digunakan
USE silsila_keluarga;

-- Tampilkan konfirmasi
SELECT 'Database silsila_keluarga dan user silsila_user berhasil dibuat!' AS Status;

-- ===================================================================
-- Cara menggunakan:
-- 1. Jalankan script ini di phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Atau via MySQL command line: mysql -u root -p < setup-database.sql
-- 3. Setelah itu jalankan: php artisan migrate --force
-- ===================================================================
