# 📚 BiblioTek — Sistem Perpustakaan Digital

> Proyek Web Pemrograman — Universitas Muhammadiyah Sukabumi

![Status](https://img.shields.io/badge/Progres-90%25-brightgreen?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-Native-blue?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange?style=flat-square&logo=mysql)

---

## 📋 Deskripsi Proyek

**BiblioTek** adalah sistem manajemen perpustakaan digital berbasis web menggunakan **PHP Native** dan **MySQL**. Desain klasik bernuansa **cream–gold–ink** dengan tipografi *Playfair Display*.

---

## ✅ Progres Fitur

| No | Fitur | Status |
|----|-------|:------:|
| 1 | Login & Autentikasi (PHP Session) | ✅ Selesai |
| 2 | CRUD Buku + Upload Multiple File | ✅ Selesai |
| 3 | Pencarian Data & DataTable | ✅ Selesai |
| 4 | Canvas TTD Digital | ✅ Selesai |
| 5 | Video / Animasi dan Audio | ✅ Selesai |
| 6 | Penggunaan Modal | ✅ Selesai |

---

## ✅ Detail Fitur

### 🔐 1. Login & Autentikasi
- Form login dua kolom (dekoratif + form)
- Autentikasi `password_verify()` bcrypt + `$_SESSION`
- Show/hide password, proteksi halaman, logout

### 📖 2. CRUD + Upload Multiple File
- Create, Read, Update, Delete — terhubung MySQL
- Upload multiple file: drag & drop, validasi tipe & ukuran (maks 5MB)
- File disimpan di `uploads/`, path disimpan sebagai JSON di DB
- Hapus buku → file lampiran ikut terhapus otomatis

### 🔍 3. Pencarian Data & DataTable
- **Live search** — tanpa reload, highlight keyword di hasil
- **Sort kolom** — klik header (Judul, Pengarang, Kategori, Tahun, Stok) dengan indikator ▲▼
- **Filter Kategori** — dropdown pilih kategori
- **Filter Stok** — Semua / Tersedia / Habis
- **Show entries** — pilih 5 / 8 / 10 / 25 / 50 / Semua entri per halaman
- **Pagination** — navigasi «‹ 1 2 3 ›» dengan window halaman
- **Export CSV** — download langsung dengan BOM UTF-8
- **Export Excel** — download file `.xls` yang bisa dibuka Excel
- **Print** — layout print-friendly (sidebar & toolbar tersembunyi)
- Info entri: "Menampilkan X–Y dari Z entri"

### ✍️ 4. Canvas TTD Digital
- Canvas HTML5: pilih warna & ketebalan, mouse + touch
- Undo per langkah, Bersihkan, Download PNG
- Simpan TTD ke database tabel `anggota` (base64)
- Tabel riwayat TTD tersimpan

### 🎬 5. Video / Animasi dan Audio
- Player Video & Audio HTML5 native
- Animasi CSS3: floatBook, pulseRing, slideIn, colorShift, bounceIn, spin
- Slideshow otomatis koleksi buku
- Counter animasi dari data real database

### 🪟 6. Modal
- Modal Tambah Buku (form + upload)
- Modal Edit Buku (data pre-filled otomatis)
- Modal Detail Buku (view-only)
- Modal Konfirmasi Hapus
- Animasi masuk, close overlay & tombol ✕

---

## 📁 Struktur Folder

```
perpustakaan-digital/
├── index.php           ← Redirect otomatis
├── login.php           ← Login + autentikasi session
├── logout.php
├── dashboard.php       ← Dashboard + statistik
├── ttd.php             ← Canvas TTD Digital + simpan DB
├── media.php           ← Video, Audio, Animasi CSS
├── setup.php           ← Setup password (hapus setelah pakai)
├── database.sql        ← Script setup database
├── config/db.php       ← Koneksi MySQL
├── buku/
│   ├── index.php       ← DataTable + 4 Modal + Export
│   ├── tambah.php      ← Proses tambah + upload
│   ├── proses_edit.php ← Proses edit dari modal
│   ├── edit.php        ← Form edit halaman terpisah
│   └── hapus.php       ← Hapus buku + file
├── includes/header.php ← Sidebar + Topbar
├── assets/css/style.css
├── assets/video/       ← Letakkan tutorial.mp4 di sini
├── assets/audio/       ← Letakkan lofi.mp3 di sini
└── uploads/            ← File yang diupload
```

---

## 🗄️ Database

**Nama:** `db_perpustakaan`

```sql
CREATE TABLE user (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, nama_lengkap VARCHAR(100), role ENUM('admin','petugas') DEFAULT 'admin', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE buku (id INT AUTO_INCREMENT PRIMARY KEY, judul VARCHAR(200) NOT NULL, pengarang VARCHAR(100), isbn VARCHAR(30), kategori VARCHAR(50), tahun INT, stok INT DEFAULT 0, deskripsi TEXT, file_lampiran TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE anggota (id INT AUTO_INCREMENT PRIMARY KEY, nim VARCHAR(20) UNIQUE, nama VARCHAR(100) NOT NULL, email VARCHAR(100), telepon VARCHAR(20), ttd_digital LONGTEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
```

---

## 🛠️ Teknologi

| Layer | Teknologi |
|-------|-----------|
| Frontend | HTML5, CSS3, JavaScript Vanilla |
| Backend | PHP Native (MySQLi Prepared Statement) |
| Database | MySQL |
| Server | Apache (Laragon) |
| Font | Playfair Display, DM Sans |
| Icon | Font Awesome 6.5 |

---

## 🚀 Cara Menjalankan

1. Extract ke `C:\laragon\www\perpustakaan-digital\`
2. Import `database.sql` di phpMyAdmin
3. Buka `http://localhost/perpustakaan-digital/setup.php` → klik Setup
4. Login: `http://localhost/perpustakaan-digital`

| Username | Password |
|----------|----------|
| `admin` | `admin123` |

> Hapus `setup.php` setelah berhasil login!

---

## 👤 Identitas

| | |
|---|---|
| **Nama** | Samie Al Fauzan |
| **Prodi** | Teknik Informatika |
| **Universitas** | Universitas Muhammadiyah Sukabumi |
| **Semester** | 4 |
| **Mata Kuliah** | Pemrograman Web |
