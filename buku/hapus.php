<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

require_once '../config/db.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: index.php?pesan=ID+tidak+valid&tipe=error");
    exit;
}

// Ambil data buku dulu (untuk hapus file lampirannya)
$stmt = mysqli_prepare($conn, "SELECT judul, file_lampiran FROM buku WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$buku   = mysqli_fetch_assoc($result);

if (!$buku) {
    header("Location: index.php?pesan=Buku+tidak+ditemukan&tipe=error");
    exit;
}

// Hapus file lampiran dari folder uploads
if ($buku['file_lampiran']) {
    $files = json_decode($buku['file_lampiran'], true);
    if (is_array($files)) {
        foreach ($files as $f) {
            $filePath = '../uploads/' . $f['path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}

// Hapus dari database
$stmtHapus = mysqli_prepare($conn, "DELETE FROM buku WHERE id = ?");
mysqli_stmt_bind_param($stmtHapus, 'i', $id);

if (mysqli_stmt_execute($stmtHapus)) {
    $judul = urlencode("Buku \"{$buku['judul']}\" berhasil dihapus.");
    header("Location: index.php?pesan=$judul&tipe=sukses");
} else {
    header("Location: index.php?pesan=Gagal+menghapus+buku&tipe=error");
}
exit;
?>
