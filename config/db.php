<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_perpustakaan');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:2rem;color:#b94c2c'>
        <b>Koneksi Database Gagal</b><br>
        " . mysqli_connect_error() . "<br><br>
        Pastikan MySQL aktif dan database <code>db_perpustakaan</code> sudah dibuat.<br>
        Jalankan file <code>database.sql</code> di phpMyAdmin.
    </div>");
}

mysqli_set_charset($conn, 'utf8mb4');
?>
