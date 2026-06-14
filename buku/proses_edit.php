<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: index.php"); exit; }

$id        = (int)($_POST['id'] ?? 0);
$judul     = trim($_POST['judul'] ?? '');
$pengarang = trim($_POST['pengarang'] ?? '');
$isbn      = trim($_POST['isbn'] ?? '');
$kategori  = trim($_POST['kategori'] ?? '');
$tahun     = (int)($_POST['tahun'] ?? 0);
$stok      = (int)($_POST['stok'] ?? 0);
$deskripsi = trim($_POST['deskripsi'] ?? '');

if (!$id || empty($judul)) {
    header("Location: index.php?pesan=Data+tidak+valid&tipe=error");
    exit;
}

// Ambil file lama
$stmtLama = mysqli_prepare($conn, "SELECT file_lampiran FROM buku WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmtLama, 'i', $id);
mysqli_stmt_execute($stmtLama);
$res      = mysqli_stmt_get_result($stmtLama);
$buku     = mysqli_fetch_assoc($res);
$fileLama = $buku['file_lampiran'] ? json_decode($buku['file_lampiran'], true) : [];

// Upload file baru (jika ada)
$fileTersimpan = $fileLama;
if (!empty($_FILES['lampiran']['name'][0])) {
    $allowed   = ['pdf','doc','docx','jpg','jpeg','png','gif'];
    $uploadDir = '../uploads/';
    foreach ($_FILES['lampiran']['name'] as $i => $namaFile) {
        if ($_FILES['lampiran']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $ext  = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        $size = $_FILES['lampiran']['size'][$i];
        if (!in_array($ext, $allowed) || $size > 5*1024*1024) continue;
        $namaSimp = time().'_'.$i.'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',$namaFile);
        if (move_uploaded_file($_FILES['lampiran']['tmp_name'][$i], $uploadDir.$namaSimp)) {
            $fileTersimpan[] = ['nama'=>$namaFile,'path'=>$namaSimp,'size'=>$size];
        }
    }
}

$fileJson = !empty($fileTersimpan) ? json_encode($fileTersimpan) : null;

$stmt = mysqli_prepare($conn,
    "UPDATE buku SET judul=?,pengarang=?,isbn=?,kategori=?,tahun=?,stok=?,deskripsi=?,file_lampiran=? WHERE id=?"
);
mysqli_stmt_bind_param($stmt,'ssssiissi',$judul,$pengarang,$isbn,$kategori,$tahun,$stok,$deskripsi,$fileJson,$id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: index.php?pesan=".urlencode("Buku \"$judul\" berhasil diperbarui.")."&tipe=sukses");
} else {
    header("Location: index.php?pesan=Gagal+memperbarui+data&tipe=error");
}
exit;
?>
