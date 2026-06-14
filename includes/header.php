<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . str_repeat('../', $depth ?? 0) . "login.php");
    exit;
}

$root   = str_repeat('../', $depth ?? 0);
$script = $_SERVER['PHP_SELF'];

function isActive(string $keyword): string {
    global $script;
    return str_contains($script, $keyword) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle ?? 'BiblioTek') ?> — BiblioTek</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="<?= $root ?>assets/css/style.css"/>
</head>
<body>
<div style="display:flex">

<!-- SIDEBAR -->
<aside id="sidebar">
  <div class="sidebar-logo">
    <h1>📚 BiblioTek</h1>
    <span>Perpustakaan Digital</span>
  </div>

  <nav class="sidebar-nav">

    <div class="nav-label">Menu Utama</div>
    <a class="nav-item <?= isActive('dashboard') ?>" href="<?= $root ?>dashboard.php">
      <i class="fa fa-house"></i> Dashboard
    </a>

    <div class="nav-label">Koleksi</div>
    <a class="nav-item <?= isActive('/buku/') ?>" href="<?= $root ?>buku/index.php">
      <i class="fa fa-book"></i> Data Buku
    </a>

    <div class="nav-label">Fitur</div>
    <a class="nav-item <?= isActive('ttd') ?>" href="<?= $root ?>ttd.php">
      <i class="fa fa-pen-nib"></i> TTD Digital
    </a>
    <a class="nav-item <?= isActive('media') ?>" href="<?= $root ?>media.php">
      <i class="fa fa-play-circle"></i> Video &amp; Animasi
    </a>

  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div style="font-weight:600;color:var(--cream);font-size:0.85rem">
          <?= htmlspecialchars($_SESSION['nama'] ?? '') ?>
        </div>
        <div style="font-size:0.72rem;color:var(--muted)">
          <?= ucfirst($_SESSION['role'] ?? 'admin') ?>
        </div>
      </div>
    </div>
    <a href="<?= $root ?>logout.php"
       class="btn btn-outline btn-full btn-sm"
       style="color:rgba(245,240,232,0.7);border-color:rgba(255,255,255,0.15)">
      <i class="fa fa-right-from-bracket"></i> Logout
    </a>
  </div>
</aside>

<!-- MAIN CONTENT -->
<div id="mainContent">

  <!-- TOPBAR -->
  <div id="topbar">
    <button id="toggler"
      onclick="document.getElementById('sidebar').classList.toggle('open')"
      style="display:none;background:none;border:none;cursor:pointer;font-size:1.2rem;padding:0.25rem">
      <i class="fa fa-bars"></i>
    </button>
    <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></span>
    <div class="topbar-right">
      <form class="search-bar" method="GET" action="<?= $root ?>buku/index.php">
        <i class="fa fa-magnifying-glass"></i>
        <input type="search" name="q" placeholder="Cari buku…"
          value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"/>
      </form>
    </div>
  </div>
