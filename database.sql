-- ============================================================
-- BiblioTek — Setup Database
-- Jalankan file ini di phpMyAdmin atau MySQL CLI
-- Setelah import, akses: http://localhost/perpustakaan-digital/setup.php
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_perpustakaan
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE db_perpustakaan;

-- Tabel user
CREATE TABLE IF NOT EXISTS user (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  username     VARCHAR(50) NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  role         ENUM('admin','petugas') DEFAULT 'admin',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel buku
CREATE TABLE IF NOT EXISTS buku (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  judul         VARCHAR(200) NOT NULL,
  pengarang     VARCHAR(100),
  isbn          VARCHAR(30),
  kategori      VARCHAR(50),
  tahun         INT,
  stok          INT DEFAULT 0,
  deskripsi     TEXT,
  file_lampiran TEXT,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel anggota (untuk TTD Digital)
CREATE TABLE IF NOT EXISTS anggota (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nim         VARCHAR(20) UNIQUE,
  nama        VARCHAR(100) NOT NULL,
  email       VARCHAR(100),
  telepon     VARCHAR(20),
  ttd_digital LONGTEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Data buku contoh
-- ============================================================
INSERT INTO buku (judul, pengarang, isbn, kategori, tahun, stok, deskripsi) VALUES
('Laskar Pelangi', 'Andrea Hirata', '978-979-1234-01-2', 'Fiksi', 2005, 4, 'Novel inspiratif tentang perjuangan anak-anak Belitung meraih mimpi.'),
('Bumi Manusia', 'Pramoedya Ananta Toer', '978-979-1234-02-9', 'Fiksi', 1980, 3, 'Kisah Minke di masa kolonial Belanda, bagian pertama Tetralogi Buru.'),
('Fisika Dasar Jilid 1', 'Halliday & Resnick', '978-979-1234-03-6', 'Sains', 2014, 5, 'Buku teks fisika komprehensif untuk mahasiswa sains dan teknik.'),
('Clean Code', 'Robert C. Martin', '978-979-1234-04-3', 'Teknologi', 2008, 2, 'Panduan menulis kode bersih dan maintainable.'),
('Algoritma dan Pemrograman', 'Rinaldi Munir', '978-979-1234-05-0', 'Teknologi', 2016, 3, 'Buku teks algoritma untuk mahasiswa informatika.'),
('Sejarah Indonesia Modern', 'M.C. Ricklefs', '978-979-1234-06-7', 'Sejarah', 2008, 6, 'Sejarah komprehensif Indonesia dari abad ke-17 hingga masa kini.')
ON DUPLICATE KEY UPDATE id=id;
