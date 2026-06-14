<?php
/**
 * BiblioTek — Setup Password
 * Jalankan file ini SEKALI setelah import database.sql
 * Akses: http://localhost/perpustakaan-digital/setup.php
 * Setelah selesai, HAPUS file ini dari server!
 */

require_once 'config/db.php';

$pesan  = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hash_admin   = password_hash('admin123', PASSWORD_DEFAULT);
    $hash_petugas = password_hash('petugas123', PASSWORD_DEFAULT);

    // Hapus user lama jika ada, lalu insert ulang
    mysqli_query($conn, "DELETE FROM user WHERE username IN ('admin','petugas')");

    $stmt = mysqli_prepare($conn,
        "INSERT INTO user (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)"
    );

    // Insert admin
    $u = 'admin'; $n = 'Administrator'; $r = 'admin';
    mysqli_stmt_bind_param($stmt, 'ssss', $u, $hash_admin, $n, $r);
    mysqli_stmt_execute($stmt);

    // Insert petugas
    $u = 'petugas'; $n = 'Petugas Perpustakaan'; $r = 'petugas';
    mysqli_stmt_bind_param($stmt, 'ssss', $u, $hash_petugas, $n, $r);
    mysqli_stmt_execute($stmt);

    $pesan  = 'Password berhasil di-set! Sekarang kamu bisa login.';
    $status = 'sukses';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Setup BiblioTek</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'DM Sans',sans-serif;background:#1a1410;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
    .box{background:#fdfaf4;border-radius:16px;padding:2.5rem;width:min(460px,100%);box-shadow:0 32px 80px rgba(0,0,0,0.4)}
    h1{font-family:'Playfair Display',serif;color:#c9a84c;font-size:1.8rem;margin-bottom:0.3rem}
    p.sub{color:#8a7968;font-size:0.88rem;margin-bottom:1.75rem}
    .info{background:#f5f0e8;border:1px solid #d8cebc;border-radius:10px;padding:1.2rem;margin-bottom:1.5rem;font-size:0.88rem;line-height:1.8}
    .info strong{color:#1a1410}
    .info code{background:#d8cebc;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.85rem}
    .alert-sukses{background:rgba(74,103,65,0.12);border:1px solid rgba(74,103,65,0.3);color:#4a6741;border-radius:10px;padding:1rem;margin-bottom:1.2rem;font-size:0.9rem}
    .btn{display:block;width:100%;padding:0.75rem;background:#c9a84c;color:#1a1410;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background 0.2s}
    .btn:hover{background:#b8922e}
    .btn-link{display:block;text-align:center;margin-top:1rem;color:#8a7968;font-size:0.85rem;text-decoration:none}
    .btn-link:hover{color:#c9a84c}
    .warn{background:rgba(185,76,44,0.1);border:1px solid rgba(185,76,44,0.25);color:#b94c2c;border-radius:8px;padding:0.75rem;font-size:0.82rem;margin-top:1.2rem;text-align:center}
  </style>
</head>
<body>
<div class="box">
  <h1>📚 BiblioTek</h1>
  <p class="sub">Setup Awal — Generate Password</p>

  <?php if ($status === 'sukses'): ?>
    <div class="alert-sukses">
      ✅ <?= $pesan ?>
    </div>
    <div class="info">
      <strong>Akun yang sudah dibuat:</strong><br>
      Username: <code>admin</code> — Password: <code>admin123</code><br>
      Username: <code>petugas</code> — Password: <code>petugas123</code>
    </div>
    <a href="login.php" class="btn">🚀 Pergi ke Halaman Login</a>
    <div class="warn">⚠️ Segera hapus file <code>setup.php</code> setelah login berhasil!</div>

  <?php else: ?>
    <div class="info">
      File ini akan membuat akun login dengan password yang di-hash oleh PHP server kamu sendiri.<br><br>
      <strong>Akun yang akan dibuat:</strong><br>
      Username: <code>admin</code> — Password: <code>admin123</code><br>
      Username: <code>petugas</code> — Password: <code>petugas123</code>
    </div>

    <form method="POST">
      <button type="submit" class="btn">⚙️ Jalankan Setup Sekarang</button>
    </form>
    <a href="login.php" class="btn-link">← Sudah setup? Langsung login</a>
  <?php endif; ?>
</div>
</body>
</html>
