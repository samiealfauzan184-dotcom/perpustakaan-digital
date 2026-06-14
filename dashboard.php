<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/db.php';

// Ambil statistik dari database
$totalBuku     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM buku"))[0] ?? 0;
$totalStok     = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(stok) FROM buku"))[0] ?? 0;
$bukuTerbaru   = mysqli_query($conn, "SELECT judul, pengarang, kategori, stok, created_at FROM buku ORDER BY created_at DESC LIMIT 5");

$pageTitle = 'Dashboard';
$depth = 0;
require_once 'includes/header.php';
?>

<div class="page-content">

  <!-- Sambutan -->
  <div class="page-header">
    <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</h2>
    <p>Kelola koleksi perpustakaan digital Anda dari sini.</p>
  </div>

  <!-- Stat Cards -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(201,168,76,0.15)">
        <i class="fa fa-book" style="color:var(--gold)"></i>
      </div>
      <div class="stat-val"><?= $totalBuku ?></div>
      <div class="stat-label">Total Judul Buku</div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(74,103,65,0.15)">
        <i class="fa fa-layer-group" style="color:var(--sage)"></i>
      </div>
      <div class="stat-val"><?= $totalStok ?></div>
      <div class="stat-label">Total Stok Buku</div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(185,76,44,0.15)">
        <i class="fa fa-user-shield" style="color:var(--rust)"></i>
      </div>
      <div class="stat-val"><?= ucfirst($_SESSION['role']) ?></div>
      <div class="stat-label">Level Akses</div>
    </div>
  </div>

  <!-- Tabel buku terbaru -->
  <div class="card">
    <div class="card-header">
      <h3><i class="fa fa-clock-rotate-left" style="color:var(--gold)"></i> Buku Terbaru Ditambahkan</h3>
      <a href="buku/index.php" class="btn btn-outline btn-sm">
        <i class="fa fa-arrow-right"></i> Lihat Semua
      </a>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Judul</th>
            <th>Pengarang</th>
            <th>Kategori</th>
            <th>Stok</th>
            <th>Ditambahkan</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          if (mysqli_num_rows($bukuTerbaru) > 0):
            while ($row = mysqli_fetch_assoc($bukuTerbaru)):
          ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><strong><?= htmlspecialchars($row['judul']) ?></strong></td>
            <td><?= htmlspecialchars($row['pengarang']) ?></td>
            <td><span class="badge badge-gold"><?= htmlspecialchars($row['kategori']) ?></span></td>
            <td>
              <?php if ($row['stok'] > 0): ?>
                <span class="badge badge-green"><?= $row['stok'] ?> tersedia</span>
              <?php else: ?>
                <span class="badge badge-red">Habis</span>
              <?php endif; ?>
            </td>
            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
          </tr>
          <?php
            endwhile;
          else:
          ?>
          <tr>
            <td colspan="6" class="tbl-empty">
              <i class="fa fa-book-open" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem"></i>
              Belum ada buku. <a href="buku/index.php" style="color:var(--gold)">Tambah sekarang →</a>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- end page-content -->

<!-- Toast container -->
<div id="toastBox"></div>

</div><!-- end mainContent -->
</div><!-- end flex wrapper -->

<script>
// Sidebar toggle mobile
const toggler = document.getElementById('toggler');
if (window.innerWidth <= 900) toggler.style.display = 'block';
window.addEventListener('resize', () => {
  toggler.style.display = window.innerWidth <= 900 ? 'block' : 'none';
});
</script>
</body>
</html>
