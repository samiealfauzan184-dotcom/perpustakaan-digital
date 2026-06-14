<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

require_once 'config/db.php';

$pesan = '';
$tipe  = '';

// Simpan TTD ke database anggota
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ttd_data'])) {
    $nama     = trim($_POST['nama'] ?? '');
    $nim      = trim($_POST['nim'] ?? '');
    $tgl      = $_POST['tanggal'] ?? date('Y-m-d');
    $ttdData  = $_POST['ttd_data'] ?? '';

    if (empty($nama) || empty($nim)) {
        $pesan = 'Nama dan NIM wajib diisi.';
        $tipe  = 'error';
    } elseif (empty($ttdData) || $ttdData === 'data:,') {
        $pesan = 'Tanda tangan tidak boleh kosong. Silakan buat tanda tangan dulu.';
        $tipe  = 'error';
    } else {
        // Cek apakah NIM sudah ada
        $cek = mysqli_prepare($conn, "SELECT id FROM anggota WHERE nim=? LIMIT 1");
        mysqli_stmt_bind_param($cek, 's', $nim);
        mysqli_stmt_execute($cek);
        $ada = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));

        if ($ada) {
            // Update TTD
            $upd = mysqli_prepare($conn, "UPDATE anggota SET ttd_digital=?,nama=? WHERE nim=?");
            mysqli_stmt_bind_param($upd, 'sss', $ttdData, $nama, $nim);
            mysqli_stmt_execute($upd);
        } else {
            // Insert anggota baru
            $ins = mysqli_prepare($conn, "INSERT INTO anggota (nim,nama,ttd_digital) VALUES (?,?,?)");
            mysqli_stmt_bind_param($ins, 'sss', $nim, $nama, $ttdData);
            mysqli_stmt_execute($ins);
        }
        $pesan = "TTD berhasil disimpan untuk $nama ($nim)!";
        $tipe  = 'sukses';
    }
}

// Ambil riwayat TTD tersimpan
$riwayat = mysqli_query($conn, "SELECT nim, nama, created_at FROM anggota WHERE ttd_digital IS NOT NULL ORDER BY created_at DESC LIMIT 10");

$pageTitle = 'TTD Digital';
$depth = 0;
require_once 'includes/header.php';
?>

<div class="page-content">
  <div class="page-header">
    <h2>Tanda Tangan Digital</h2>
    <p>Buat dan simpan tanda tangan digital anggota perpustakaan</p>
  </div>

  <?php if ($pesan): ?>
  <div class="alert alert-<?= $tipe==='sukses'?'success':'danger' ?>">
    <i class="fa fa-<?= $tipe==='sukses'?'circle-check':'circle-exclamation' ?>"></i>
    <?= htmlspecialchars($pesan) ?>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 320px;gap:1.25rem;align-items:start">

    <!-- CANVAS -->
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-signature" style="color:var(--gold)"></i> Kanvas Tanda Tangan</h3>
        <span class="badge badge-muted">Canvas HTML5</span>
      </div>
      <div class="card-body">
        <!-- Toolbar -->
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;padding:.75rem 1rem;background:var(--cream);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:1rem">
          <div style="display:flex;align-items:center;gap:.5rem">
            <label style="font-size:.8rem;color:var(--muted)">Warna:</label>
            <input type="color" id="penColor" value="#1a1410"
              style="width:32px;height:32px;border-radius:6px;border:1px solid var(--border);cursor:pointer;padding:0"/>
          </div>
          <div style="display:flex;align-items:center;gap:.5rem">
            <label style="font-size:.8rem;color:var(--muted)">Ketebalan:</label>
            <input type="range" id="penSize" min="1" max="14" value="3" style="width:90px;cursor:pointer"/>
            <span id="penSizeVal" style="font-size:.8rem;color:var(--muted);min-width:25px">3px</span>
          </div>
          <div style="margin-left:auto;display:flex;gap:.5rem">
            <button class="btn btn-outline btn-sm" onclick="clearCanvas()"><i class="fa fa-eraser"></i> Hapus</button>
            <button class="btn btn-outline btn-sm" onclick="undoCanvas()"><i class="fa fa-rotate-left"></i> Undo</button>
            <button class="btn btn-gold btn-sm" onclick="downloadTTD()"><i class="fa fa-download"></i> Download</button>
          </div>
        </div>

        <!-- Canvas -->
        <div style="border:2px solid var(--border);border-radius:var(--radius);overflow:hidden;background:#fff;position:relative">
          <canvas id="signatureCanvas" style="display:block;cursor:crosshair;touch-action:none;width:100%"></canvas>
          <div id="canvasPlaceholder" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none">
            <span style="color:var(--border);font-size:.9rem;font-style:italic">Tanda tangan di sini…</span>
          </div>
        </div>
        <p style="font-size:.77rem;color:var(--muted);margin-top:.5rem;text-align:center">
          <i class="fa fa-info-circle"></i> Gunakan mouse atau jari (layar sentuh)
        </p>

        <!-- Preview -->
        <div id="previewBox" style="display:none;margin-top:1rem;padding:1rem;background:var(--cream);border:1px solid var(--border);border-radius:var(--radius)">
          <p style="font-size:.8rem;color:var(--muted);margin-bottom:.5rem">Preview TTD:</p>
          <img id="previewImg" style="max-width:100%;border:1px solid var(--border);border-radius:7px"/>
        </div>
      </div>
    </div>

    <!-- FORM SIMPAN -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <div class="card">
        <div class="card-header"><h3>Dokumen Persetujuan</h3></div>
        <div class="card-body">
          <form method="POST" action="ttd.php" id="formTTD">
            <input type="hidden" name="ttd_data" id="ttdInput"/>
            <div class="form-group">
              <label>Nama Lengkap <span style="color:var(--rust)">*</span></label>
              <input type="text" name="nama" placeholder="Nama anggota" required
                value="<?= htmlspecialchars($_POST['nama']??'') ?>"/>
            </div>
            <div class="form-group">
              <label>NIM / No. Anggota <span style="color:var(--rust)">*</span></label>
              <input type="text" name="nim" placeholder="Contoh: 22310001" required
                value="<?= htmlspecialchars($_POST['nim']??'') ?>"/>
            </div>
            <div class="form-group">
              <label>Tanggal</label>
              <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>"/>
            </div>
            <p style="font-size:.8rem;color:var(--muted);margin-bottom:1rem;line-height:1.6">
              Dengan menandatangani, saya bertanggung jawab atas buku yang dipinjam dan akan mengembalikannya tepat waktu.
            </p>
            <button type="button" class="btn btn-primary btn-full" onclick="submitTTD()">
              <i class="fa fa-file-signature"></i> Simpan TTD
            </button>
          </form>
        </div>
      </div>

      <!-- Riwayat -->
      <div class="card">
        <div class="card-header"><h3>Riwayat TTD Tersimpan</h3></div>
        <div class="card-body" style="padding:0">
          <?php if (mysqli_num_rows($riwayat) > 0): ?>
          <table style="width:100%;font-size:.83rem;border-collapse:collapse">
            <thead>
              <tr>
                <th style="padding:.6rem .9rem;background:var(--cream);text-align:left;color:var(--muted);font-size:.75rem;text-transform:uppercase;border-bottom:1px solid var(--border)">NIM</th>
                <th style="padding:.6rem .9rem;background:var(--cream);text-align:left;color:var(--muted);font-size:.75rem;text-transform:uppercase;border-bottom:1px solid var(--border)">Nama</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($r = mysqli_fetch_assoc($riwayat)): ?>
              <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:.55rem .9rem"><code style="font-size:.78rem"><?= htmlspecialchars($r['nim']) ?></code></td>
                <td style="padding:.55rem .9rem"><?= htmlspecialchars($r['nama']) ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
          <?php else: ?>
          <div style="padding:1.5rem;text-align:center;color:var(--muted);font-size:.85rem">
            Belum ada TTD tersimpan
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="toastBox"></div>
</div></div>

<script>
// ===== CANVAS SETUP =====
const canvas  = document.getElementById('signatureCanvas');
const ctx     = canvas.getContext('2d');
const ph      = document.getElementById('canvasPlaceholder');
let drawing   = false;
let history   = [];

function resizeCanvas() {
  const w = canvas.parentElement.clientWidth;
  const h = Math.round(w * 0.38);
  const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
  canvas.width  = w;
  canvas.height = h;
  ctx.putImageData(img, 0, 0);
  ctx.lineJoin = 'round';
  ctx.lineCap  = 'round';
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

function getPos(e) {
  const rect = canvas.getBoundingClientRect();
  const scaleX = canvas.width  / rect.width;
  const scaleY = canvas.height / rect.height;
  if (e.touches) {
    return {
      x: (e.touches[0].clientX - rect.left) * scaleX,
      y: (e.touches[0].clientY - rect.top)  * scaleY
    };
  }
  return {
    x: (e.clientX - rect.left) * scaleX,
    y: (e.clientY - rect.top)  * scaleY
  };
}

function startDraw(e) {
  e.preventDefault();
  drawing = true;
  history.push(ctx.getImageData(0,0,canvas.width,canvas.height));
  ph.style.display = 'none';
  const p = getPos(e);
  ctx.beginPath();
  ctx.moveTo(p.x, p.y);
}
function draw(e) {
  if (!drawing) return;
  e.preventDefault();
  ctx.lineWidth   = document.getElementById('penSize').value;
  ctx.strokeStyle = document.getElementById('penColor').value;
  const p = getPos(e);
  ctx.lineTo(p.x, p.y);
  ctx.stroke();
}
function endDraw() {
  drawing = false;
  updatePreview();
}

canvas.addEventListener('mousedown',  startDraw);
canvas.addEventListener('mousemove',  draw);
canvas.addEventListener('mouseup',    endDraw);
canvas.addEventListener('mouseleave', endDraw);
canvas.addEventListener('touchstart', startDraw, {passive:false});
canvas.addEventListener('touchmove',  draw,      {passive:false});
canvas.addEventListener('touchend',   endDraw);

function clearCanvas() {
  history = [];
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ph.style.display = 'flex';
  document.getElementById('previewBox').style.display = 'none';
}

function undoCanvas() {
  if (history.length > 0) {
    ctx.putImageData(history.pop(), 0, 0);
    updatePreview();
  }
}

function updatePreview() {
  const dataUrl = canvas.toDataURL('image/png');
  document.getElementById('previewImg').src = dataUrl;
  document.getElementById('previewBox').style.display = 'block';
}

function downloadTTD() {
  const a = document.createElement('a');
  a.href     = canvas.toDataURL('image/png');
  a.download = 'ttd-digital.png';
  a.click();
  showToast('TTD berhasil didownload!', 'circle-check');
}

function submitTTD() {
  const dataUrl = canvas.toDataURL('image/png');
  if (dataUrl === 'data:,') {
    showToast('Buat tanda tangan dulu!', 'circle-exclamation');
    return;
  }
  document.getElementById('ttdInput').value = dataUrl;
  document.getElementById('formTTD').submit();
}

// Pen size label
document.getElementById('penSize').addEventListener('input', function() {
  document.getElementById('penSizeVal').textContent = this.value + 'px';
});

// Toast
function showToast(msg, icon='circle-check') {
  const b = document.getElementById('toastBox');
  const t = document.createElement('div');
  t.className = 'toast';
  t.innerHTML = `<i class="fa fa-${icon}"></i> ${msg}`;
  b.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

// Sidebar mobile
if (window.innerWidth<=900) document.getElementById('toggler').style.display='block';
window.addEventListener('resize',()=>{
  document.getElementById('toggler').style.display=window.innerWidth<=900?'block':'none';
});

<?php if ($pesan): ?>
showToast('<?= htmlspecialchars(addslashes($pesan)) ?>','<?= $tipe==="sukses"?"circle-check":"circle-exclamation" ?>');
<?php endif; ?>
</script>
</body>
</html>
