<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

require_once '../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit; }

// Ambil data buku
$stmt = mysqli_prepare($conn, "SELECT * FROM buku WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$buku   = mysqli_fetch_assoc($result);

if (!$buku) { header("Location: index.php?pesan=Buku+tidak+ditemukan&tipe=error"); exit; }

// File lampiran yang sudah ada
$fileLama = $buku['file_lampiran'] ? json_decode($buku['file_lampiran'], true) : [];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul'] ?? '');
    $pengarang = trim($_POST['pengarang'] ?? '');
    $isbn      = trim($_POST['isbn'] ?? '');
    $kategori  = trim($_POST['kategori'] ?? '');
    $tahun     = (int)($_POST['tahun'] ?? 0);
    $stok      = (int)($_POST['stok'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    // File lama yang tidak dihapus
    $pertahankan  = $_POST['pertahankan'] ?? [];
    $fileTersimpan = array_filter($fileLama, fn($f) => in_array($f['path'], $pertahankan));
    $fileTersimpan = array_values($fileTersimpan);

    if (empty($judul)) {
        $error = 'Judul buku wajib diisi.';
    } else {
        // Upload file baru
        if (!empty($_FILES['lampiran']['name'][0])) {
            $allowed  = ['pdf','doc','docx','jpg','jpeg','png','gif'];
            $maxSize  = 5 * 1024 * 1024;
            $uploadDir= '../uploads/';
            $adaError = false;

            foreach ($_FILES['lampiran']['name'] as $i => $namaFile) {
                if ($_FILES['lampiran']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $ext  = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
                $size = $_FILES['lampiran']['size'][$i];

                if (!in_array($ext, $allowed)) {
                    $error = "File \"$namaFile\" tidak diizinkan.";
                    $adaError = true; break;
                }
                if ($size > $maxSize) {
                    $error = "File \"$namaFile\" terlalu besar. Maks 5MB.";
                    $adaError = true; break;
                }

                $namaSimp = time() . '_' . $i . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $namaFile);
                if (move_uploaded_file($_FILES['lampiran']['tmp_name'][$i], $uploadDir . $namaSimp)) {
                    $fileTersimpan[] = ['nama' => $namaFile, 'path' => $namaSimp, 'size' => $size];
                }
            }
            if ($adaError) goto selesai;
        }

        $fileJson = !empty($fileTersimpan) ? json_encode($fileTersimpan) : null;

        $stmt = mysqli_prepare($conn,
            "UPDATE buku SET judul=?, pengarang=?, isbn=?, kategori=?, tahun=?, stok=?, deskripsi=?, file_lampiran=?
             WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, 'ssssiissi',
            $judul, $pengarang, $isbn, $kategori, $tahun, $stok, $deskripsi, $fileJson, $id
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?pesan=" . urlencode("Buku \"$judul\" berhasil diperbarui.") . "&tipe=sukses");
            exit;
        } else {
            $error = 'Gagal memperbarui data: ' . mysqli_error($conn);
        }
    }
    selesai:;
}

$pageTitle = 'Edit Buku';
$depth = 1;
require_once '../includes/header.php';
?>

<div class="page-content">

  <div class="page-header page-header-row">
    <div>
      <h2>Edit Buku</h2>
      <p>Perbarui data buku yang sudah ada</p>
    </div>
    <a href="index.php" class="btn btn-outline btn-sm">
      <i class="fa fa-arrow-left"></i> Kembali
    </a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger">
      <i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <h3><i class="fa fa-pen-to-square" style="color:var(--gold)"></i> Form Edit Buku</h3>
      <span class="badge badge-muted">ID: <?= $id ?></span>
    </div>
    <div class="card-body">
      <form method="POST" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data">

        <div class="form-row">
          <div class="form-group">
            <label>Judul Buku <span style="color:var(--rust)">*</span></label>
            <input type="text" name="judul" required
              value="<?= htmlspecialchars($buku['judul']) ?>"/>
          </div>
          <div class="form-group">
            <label>Pengarang</label>
            <input type="text" name="pengarang"
              value="<?= htmlspecialchars($buku['pengarang']) ?>"/>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn"
              value="<?= htmlspecialchars($buku['isbn']) ?>"/>
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select name="kategori">
              <option value="">-- Pilih Kategori --</option>
              <?php
              $cats = ['Fiksi','Sains','Teknologi','Sejarah','Pendidikan','Agama','Kesehatan','Lainnya'];
              foreach ($cats as $c):
                $sel = ($buku['kategori'] === $c) ? 'selected' : '';
              ?>
              <option value="<?= $c ?>" <?= $sel ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Tahun Terbit</label>
            <input type="number" name="tahun" min="1900" max="2099"
              value="<?= $buku['tahun'] ?>"/>
          </div>
          <div class="form-group">
            <label>Jumlah Stok</label>
            <input type="number" name="stok" min="0"
              value="<?= $buku['stok'] ?>"/>
          </div>
        </div>

        <div class="form-group">
          <label>Deskripsi Singkat</label>
          <textarea name="deskripsi" rows="3"><?= htmlspecialchars($buku['deskripsi']) ?></textarea>
        </div>

        <!-- File lampiran yang sudah ada -->
        <?php if (!empty($fileLama)): ?>
        <div class="form-group">
          <label>File Lampiran Saat Ini</label>
          <div class="file-list">
            <?php foreach ($fileLama as $f):
              $ext  = strtoupper(pathinfo($f['nama'], PATHINFO_EXTENSION));
              $icons= ['PDF'=>'fa-file-pdf','DOC'=>'fa-file-word','DOCX'=>'fa-file-word',
                       'JPG'=>'fa-file-image','JPEG'=>'fa-file-image','PNG'=>'fa-file-image'];
              $ico  = $icons[$ext] ?? 'fa-file';
              $size = isset($f['size'])
                ? ($f['size'] < 1048576 ? round($f['size']/1024,1).' KB' : round($f['size']/1048576,1).' MB')
                : '';
            ?>
            <div class="file-item">
              <input type="checkbox" name="pertahankan[]" value="<?= htmlspecialchars($f['path']) ?>" checked
                id="f_<?= htmlspecialchars($f['path']) ?>"/>
              <i class="fa <?= $ico ?>"></i>
              <label for="f_<?= htmlspecialchars($f['path']) ?>" style="cursor:pointer;flex:1">
                <?= htmlspecialchars($f['nama']) ?>
              </label>
              <span class="file-size"><?= $size ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <p style="font-size:0.78rem;color:var(--muted);margin-top:0.4rem">
            <i class="fa fa-info-circle"></i> Hilangkan centang untuk menghapus file tersebut.
          </p>
        </div>
        <?php endif; ?>

        <!-- Upload file baru -->
        <div class="form-group">
          <label>Tambah File Baru <span style="color:var(--muted);font-weight:400;text-transform:none">(opsional)</span></label>
          <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
            <i class="fa fa-cloud-arrow-up"></i>
            <p><strong>Klik untuk pilih file</strong> atau seret & lepas ke sini</p>
          </div>
          <input type="file" id="fileInput" name="lampiran[]" multiple
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
            style="display:none" onchange="tampilkanFile(this.files)"/>
          <div class="file-list" id="fileList"></div>
        </div>

        <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:0.5rem">
          <a href="index.php" class="btn btn-outline">Batal</a>
          <button type="submit" class="btn btn-gold">
            <i class="fa fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<div id="toastBox"></div>
</div></div>

<script>
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
  e.preventDefault(); zone.classList.remove('dragover');
  const inp = document.getElementById('fileInput');
  const dt  = new DataTransfer();
  [...(inp.files||[]), ...e.dataTransfer.files].forEach(f => dt.items.add(f));
  inp.files = dt.files;
  tampilkanFile(inp.files);
});

function tampilkanFile(files) {
  const list = document.getElementById('fileList');
  list.innerHTML = '';
  if (!files || !files.length) return;
  [...files].forEach((f, i) => {
    const ext  = f.name.split('.').pop().toUpperCase();
    const size = f.size < 1048576 ? (f.size/1024).toFixed(1)+' KB' : (f.size/1048576).toFixed(1)+' MB';
    const icons= {PDF:'fa-file-pdf',DOC:'fa-file-word',DOCX:'fa-file-word',
                  JPG:'fa-file-image',JPEG:'fa-file-image',PNG:'fa-file-image',GIF:'fa-file-image'};
    const ico  = icons[ext] || 'fa-file';
    const div  = document.createElement('div');
    div.className = 'file-item';
    div.innerHTML = `<i class="fa ${ico}"></i><span>${f.name}</span>
      <span class="file-size">${size}</span>
      <button type="button" class="rm" onclick="hapusFile(${i})"><i class="fa fa-xmark"></i></button>`;
    list.appendChild(div);
  });
}

function hapusFile(index) {
  const inp = document.getElementById('fileInput');
  const dt  = new DataTransfer();
  [...inp.files].forEach((f, i) => { if (i !== index) dt.items.add(f); });
  inp.files = dt.files;
  tampilkanFile(inp.files);
}

if (window.innerWidth <= 900) document.getElementById('toggler').style.display = 'block';
window.addEventListener('resize', () => {
  document.getElementById('toggler').style.display = window.innerWidth <= 900 ? 'block' : 'none';
});
</script>
</body>
</html>
