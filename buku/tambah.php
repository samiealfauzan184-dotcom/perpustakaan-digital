<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

require_once '../config/db.php';

$error  = '';
$sukses = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul'] ?? '');
    $pengarang = trim($_POST['pengarang'] ?? '');
    $isbn      = trim($_POST['isbn'] ?? '');
    $kategori  = trim($_POST['kategori'] ?? '');
    $tahun     = (int)($_POST['tahun'] ?? 0);
    $stok      = (int)($_POST['stok'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($judul)) {
        $error = 'Judul buku wajib diisi.';
    } else {
        // Proses upload multiple file
        $fileTersimpan = [];
        $uploadDir     = '../uploads/';

        if (!empty($_FILES['lampiran']['name'][0])) {
            $allowed    = ['pdf','doc','docx','jpg','jpeg','png','gif'];
            $maxSize    = 5 * 1024 * 1024; // 5MB
            $adaError   = false;

            foreach ($_FILES['lampiran']['name'] as $i => $namaFile) {
                if ($_FILES['lampiran']['error'][$i] !== UPLOAD_ERR_OK) continue;

                $ext  = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
                $size = $_FILES['lampiran']['size'][$i];

                if (!in_array($ext, $allowed)) {
                    $error = "File \"$namaFile\" tidak diizinkan. Format: PDF, DOC, DOCX, JPG, PNG.";
                    $adaError = true; break;
                }
                if ($size > $maxSize) {
                    $error = "File \"$namaFile\" terlalu besar. Maksimal 5MB.";
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
            "INSERT INTO buku (judul, pengarang, isbn, kategori, tahun, stok, deskripsi, file_lampiran)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'ssssiiss',
            $judul, $pengarang, $isbn, $kategori, $tahun, $stok, $deskripsi, $fileJson
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?pesan=" . urlencode("Buku \"$judul\" berhasil ditambahkan.") . "&tipe=sukses");
            exit;
        } else {
            $error = 'Gagal menyimpan data: ' . mysqli_error($conn);
        }
    }
    selesai:;
}

$pageTitle = 'Tambah Buku';
$depth = 1;
require_once '../includes/header.php';
?>

<div class="page-content">

  <div class="page-header page-header-row">
    <div>
      <h2>Tambah Buku</h2>
      <p>Isi form di bawah untuk menambahkan koleksi baru</p>
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
      <h3><i class="fa fa-book-medical" style="color:var(--gold)"></i> Form Data Buku</h3>
    </div>
    <div class="card-body">
      <form method="POST" action="tambah.php" enctype="multipart/form-data">

        <div class="form-row">
          <div class="form-group">
            <label>Judul Buku <span style="color:var(--rust)">*</span></label>
            <input type="text" name="judul" placeholder="Contoh: Laskar Pelangi"
              value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>" required/>
          </div>
          <div class="form-group">
            <label>Pengarang</label>
            <input type="text" name="pengarang" placeholder="Nama penulis"
              value="<?= htmlspecialchars($_POST['pengarang'] ?? '') ?>"/>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn" placeholder="978-xxx-xxx-xx-x"
              value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select name="kategori">
              <option value="">-- Pilih Kategori --</option>
              <?php
              $cats = ['Fiksi','Sains','Teknologi','Sejarah','Pendidikan','Agama','Kesehatan','Lainnya'];
              foreach ($cats as $c):
                $sel = (($_POST['kategori'] ?? '') === $c) ? 'selected' : '';
              ?>
              <option value="<?= $c ?>" <?= $sel ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Tahun Terbit</label>
            <input type="number" name="tahun" placeholder="2024" min="1900" max="2099"
              value="<?= htmlspecialchars($_POST['tahun'] ?? date('Y')) ?>"/>
          </div>
          <div class="form-group">
            <label>Jumlah Stok</label>
            <input type="number" name="stok" placeholder="0" min="0"
              value="<?= htmlspecialchars($_POST['stok'] ?? '0') ?>"/>
          </div>
        </div>

        <div class="form-group">
          <label>Deskripsi Singkat</label>
          <textarea name="deskripsi" rows="3" placeholder="Ringkasan atau sinopsis buku…"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
        </div>

        <!-- Upload Multiple File -->
        <div class="form-group">
          <label>Upload File Lampiran <span style="color:var(--muted);font-weight:400;text-transform:none">(PDF, DOC, Gambar — maks. 5MB/file)</span></label>

          <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
            <i class="fa fa-cloud-arrow-up"></i>
            <p><strong>Klik untuk pilih file</strong> atau seret & lepas ke sini</p>
            <p style="margin-top:0.3rem;font-size:0.8rem">PDF · DOC · DOCX · JPG · PNG · GIF</p>
          </div>
          <input type="file" id="fileInput" name="lampiran[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
            style="display:none" onchange="tampilkanFile(this.files)"/>
          <div class="file-list" id="fileList"></div>
        </div>

        <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:0.5rem">
          <a href="index.php" class="btn btn-outline">Batal</a>
          <button type="submit" class="btn btn-gold">
            <i class="fa fa-floppy-disk"></i> Simpan Buku
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<div id="toastBox"></div>
</div></div>

<script>
// Drag & drop
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
  e.preventDefault();
  zone.classList.remove('dragover');
  const inp = document.getElementById('fileInput');
  // Gabungkan file yang sudah ada + yang baru di-drop
  const dt = new DataTransfer();
  [...(inp.files || []), ...e.dataTransfer.files].forEach(f => dt.items.add(f));
  inp.files = dt.files;
  tampilkanFile(inp.files);
});

function tampilkanFile(files) {
  const list = document.getElementById('fileList');
  list.innerHTML = '';
  if (!files || files.length === 0) return;

  [...files].forEach((f, i) => {
    const ext  = f.name.split('.').pop().toUpperCase();
    const size = f.size < 1024*1024
      ? (f.size/1024).toFixed(1) + ' KB'
      : (f.size/(1024*1024)).toFixed(1) + ' MB';

    const icons = { PDF:'fa-file-pdf', DOC:'fa-file-word', DOCX:'fa-file-word',
                    JPG:'fa-file-image', JPEG:'fa-file-image', PNG:'fa-file-image', GIF:'fa-file-image' };
    const ico = icons[ext] || 'fa-file';

    const div = document.createElement('div');
    div.className = 'file-item';
    div.innerHTML = `
      <i class="fa ${ico}"></i>
      <span>${f.name}</span>
      <span class="file-size">${size}</span>
      <button type="button" class="rm" onclick="hapusFile(${i})" title="Hapus">
        <i class="fa fa-xmark"></i>
      </button>`;
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

// Sidebar toggle mobile
if (window.innerWidth <= 900) document.getElementById('toggler').style.display = 'block';
window.addEventListener('resize', () => {
  document.getElementById('toggler').style.display = window.innerWidth <= 900 ? 'block' : 'none';
});
</script>
</body>
</html>
