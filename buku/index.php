<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }

require_once '../config/db.php';

$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe']  ?? '';

// Kategori untuk dropdown filter
$kategoriList = mysqli_query($conn, "SELECT DISTINCT kategori FROM buku WHERE kategori != '' ORDER BY kategori");

// Query semua buku (DataTable handle di client-side)
$bukuAll = mysqli_query($conn, "SELECT * FROM buku ORDER BY created_at DESC");

$pageTitle = 'Data Buku';
$depth = 1;
require_once '../includes/header.php';
?>

<style>
/* ===== PRINT STYLE ===== */
@media print {
  #sidebar, #topbar, .table-toolbar, .table-pagination,
  .btn, .modal-overlay, #toastBox { display: none !important; }
  #mainContent { margin-left: 0 !important; }
  .page-content { padding: 0 !important; }
  table { font-size: 11px; }
  thead th { background: #eee !important; }
}

/* Sort icon aktif */
th.sort-asc  .sort-ico::after { content: ' ▲'; font-size:.65rem; color:var(--gold); }
th.sort-desc .sort-ico::after { content: ' ▼'; font-size:.65rem; color:var(--gold); }

/* Highlight hasil search */
mark { background: rgba(201,168,76,0.35); border-radius: 3px; padding: 0 2px; color: inherit; }

/* Entries select */
.entries-select {
  border: 1.5px solid var(--border); background: var(--cream);
  border-radius: var(--radius); padding: .38rem .7rem;
  font-size: .85rem; font-family: var(--ff-sans); color: var(--ink);
  outline: none; cursor: pointer;
}
.entries-select:focus { border-color: var(--gold); }
</style>

<div class="page-content">

<?php if ($pesan): ?>
<div class="alert alert-<?= $tipe==='sukses'?'success':'danger' ?>">
  <i class="fa fa-<?= $tipe==='sukses'?'circle-check':'circle-exclamation' ?>"></i>
  <?= htmlspecialchars($pesan) ?>
</div>
<?php endif; ?>

<div class="page-header page-header-row">
  <div>
    <h2>Data Buku</h2>
    <p>Kelola seluruh koleksi buku perpustakaan</p>
  </div>
  <button class="btn btn-gold" onclick="bukaModal('modalTambah')">
    <i class="fa fa-plus"></i> Tambah Buku
  </button>
</div>

<div class="card">

  <!-- ===== TOOLBAR ===== -->
  <div class="table-toolbar" style="flex-wrap:wrap;gap:.6rem">

    <!-- Kiri: show entries + filter -->
    <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;color:var(--muted)">
        Tampilkan
        <select class="entries-select" id="perHalamanSel" onchange="gantiEntries(this.value)">
          <option value="5">5</option>
          <option value="8" selected>8</option>
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="999999">Semua</option>
        </select>
        entri
      </div>

      <!-- Filter kategori -->
      <select class="entries-select" id="filterKategori" onchange="renderTabel()">
        <option value="">Semua Kategori</option>
        <?php while ($k = mysqli_fetch_assoc($kategoriList)): ?>
        <option value="<?= htmlspecialchars($k['kategori']) ?>">
          <?= htmlspecialchars($k['kategori']) ?>
        </option>
        <?php endwhile; ?>
      </select>

      <!-- Filter stok -->
      <select class="entries-select" id="filterStok" onchange="renderTabel()">
        <option value="">Semua Stok</option>
        <option value="ada">Tersedia</option>
        <option value="habis">Habis</option>
      </select>
    </div>

    <!-- Kanan: search + export -->
    <div style="display:flex;align-items:center;gap:.5rem;margin-left:auto;flex-wrap:wrap">
      <div class="search-bar">
        <i class="fa fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Cari judul, pengarang, ISBN…"
          oninput="halamanAktif=1;renderTabel()"/>
      </div>
      <button class="btn btn-outline btn-sm" onclick="exportCSV()" title="Export CSV">
        <i class="fa fa-file-csv"></i> CSV
      </button>
      <button class="btn btn-outline btn-sm" onclick="exportExcel()" title="Export Excel">
        <i class="fa fa-file-excel"></i> Excel
      </button>
      <button class="btn btn-outline btn-sm" onclick="window.print()" title="Print">
        <i class="fa fa-print"></i> Print
      </button>
    </div>
  </div>

  <!-- ===== TABEL ===== -->
  <div class="table-wrap">
    <table id="tabelBuku">
      <thead>
        <tr>
          <th style="width:45px">No</th>
          <th class="sortable" data-col="1">Judul <span class="sort-ico"></span></th>
          <th class="sortable" data-col="2">Pengarang <span class="sort-ico"></span></th>
          <th class="sortable" data-col="3">Kategori <span class="sort-ico"></span></th>
          <th class="sortable" data-col="4" style="width:70px">Tahun <span class="sort-ico"></span></th>
          <th class="sortable" data-col="5" style="width:70px">Stok <span class="sort-ico"></span></th>
          <th style="width:60px">File</th>
          <th style="width:120px">Aksi</th>
        </tr>
      </thead>
      <tbody id="tabelBody">
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($bukuAll)):
          $files = $row['file_lampiran'] ? json_decode($row['file_lampiran'], true) : [];
          $dataJson = htmlspecialchars(json_encode($row), ENT_QUOTES);
        ?>
        <tr data-kategori="<?= htmlspecialchars($row['kategori']) ?>"
            data-stok="<?= $row['stok'] > 0 ? 'ada' : 'habis' ?>">
          <td class="td-no"><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($row['judul']) ?></strong>
            <?php if($row['deskripsi']): ?>
            <div style="font-size:.75rem;color:var(--muted);margin-top:2px">
              <?= htmlspecialchars(mb_substr($row['deskripsi'],0,55)) ?>…
            </div>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['pengarang']) ?></td>
          <td><span class="badge badge-gold"><?= htmlspecialchars($row['kategori']) ?></span></td>
          <td><?= $row['tahun'] ?></td>
          <td>
            <?php if ($row['stok'] > 0): ?>
              <span class="badge badge-green"><?= $row['stok'] ?></span>
            <?php else: ?>
              <span class="badge badge-red">Habis</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($files)): ?>
              <span class="badge badge-muted" title="<?= count($files) ?> file">
                <i class="fa fa-paperclip"></i> <?= count($files) ?>
              </span>
            <?php else: ?>
              <span style="color:var(--muted);font-size:.8rem">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:.3rem">
              <button class="btn btn-outline btn-sm" title="Detail"
                onclick='bukaModalDetail(<?= $dataJson ?>)'>
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-outline btn-sm" title="Edit"
                onclick='bukaModalEdit(<?= $dataJson ?>)'>
                <i class="fa fa-pen"></i>
              </button>
              <button class="btn btn-danger btn-sm" title="Hapus"
                onclick="bukaModalHapus(<?= $row['id'] ?>,'<?= htmlspecialchars(addslashes($row['judul'])) ?>')">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== PAGINATION ===== -->
  <div class="table-pagination">
    <span id="infoData" style="font-size:.82rem;color:var(--muted)"></span>
    <div class="pg-btns" id="paginasiBtn"></div>
  </div>

</div><!-- end card -->
</div><!-- end page-content -->

<!-- ==================== MODAL TAMBAH ==================== -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fa fa-book-medical" style="color:var(--gold)"></i> Tambah Buku</h3>
      <button class="modal-close" onclick="tutupModal('modalTambah')"><i class="fa fa-xmark"></i></button>
    </div>
    <form method="POST" action="tambah.php" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label>Judul <span style="color:var(--rust)">*</span></label>
            <input type="text" name="judul" placeholder="Judul buku" required/>
          </div>
          <div class="form-group">
            <label>Pengarang</label>
            <input type="text" name="pengarang" placeholder="Nama penulis"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn" placeholder="978-xxx-xxx"/>
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select name="kategori">
              <option value="">-- Pilih --</option>
              <?php foreach(['Fiksi','Sains','Teknologi','Sejarah','Pendidikan','Agama','Kesehatan','Lainnya'] as $c): ?>
              <option value="<?= $c ?>"><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Tahun</label>
            <input type="number" name="tahun" value="<?= date('Y') ?>" min="1900" max="2099"/>
          </div>
          <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stok" value="0" min="0"/>
          </div>
        </div>
        <div class="form-group">
          <label>Deskripsi</label>
          <textarea name="deskripsi" rows="2" placeholder="Sinopsis singkat…"></textarea>
        </div>
        <div class="form-group">
          <label>Upload File <span style="color:var(--muted);text-transform:none;font-weight:400;font-size:.78rem">(PDF/DOC/Gambar, maks 5MB/file)</span></label>
          <div class="upload-zone" onclick="document.getElementById('fileT').click()">
            <i class="fa fa-cloud-arrow-up"></i>
            <p><strong>Klik</strong> atau seret file ke sini</p>
            <p style="font-size:.78rem;margin-top:.25rem">PDF · DOC · JPG · PNG</p>
          </div>
          <input type="file" id="fileT" name="lampiran[]" multiple
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display:none"
            onchange="tampilFile(this,'listT')"/>
          <div class="file-list" id="listT"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="tutupModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-gold"><i class="fa fa-floppy-disk"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== MODAL EDIT ==================== -->
<div class="modal-overlay" id="modalEdit">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fa fa-pen-to-square" style="color:var(--gold)"></i> Edit Buku</h3>
      <button class="modal-close" onclick="tutupModal('modalEdit')"><i class="fa fa-xmark"></i></button>
    </div>
    <form method="POST" action="proses_edit.php" enctype="multipart/form-data">
      <input type="hidden" name="id" id="editId"/>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label>Judul <span style="color:var(--rust)">*</span></label>
            <input type="text" name="judul" id="editJudul" required/>
          </div>
          <div class="form-group">
            <label>Pengarang</label>
            <input type="text" name="pengarang" id="editPengarang"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>ISBN</label>
            <input type="text" name="isbn" id="editIsbn"/>
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select name="kategori" id="editKategori">
              <option value="">-- Pilih --</option>
              <?php foreach(['Fiksi','Sains','Teknologi','Sejarah','Pendidikan','Agama','Kesehatan','Lainnya'] as $c): ?>
              <option value="<?= $c ?>"><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Tahun</label>
            <input type="number" name="tahun" id="editTahun" min="1900" max="2099"/>
          </div>
          <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stok" id="editStok" min="0"/>
          </div>
        </div>
        <div class="form-group">
          <label>Deskripsi</label>
          <textarea name="deskripsi" id="editDeskripsi" rows="2"></textarea>
        </div>
        <div class="form-group">
          <label>Tambah File Baru</label>
          <div class="upload-zone" onclick="document.getElementById('fileE').click()">
            <i class="fa fa-cloud-arrow-up"></i>
            <p><strong>Klik</strong> atau seret file ke sini</p>
          </div>
          <input type="file" id="fileE" name="lampiran[]" multiple
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display:none"
            onchange="tampilFile(this,'listE')"/>
          <div class="file-list" id="listE"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="tutupModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-gold"><i class="fa fa-floppy-disk"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== MODAL DETAIL ==================== -->
<div class="modal-overlay" id="modalDetail">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fa fa-book-open" style="color:var(--gold)"></i> Detail Buku</h3>
      <button class="modal-close" onclick="tutupModal('modalDetail')"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="isiDetail"></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="tutupModal('modalDetail')">Tutup</button>
    </div>
  </div>
</div>

<!-- ==================== MODAL HAPUS ==================== -->
<div class="modal-overlay" id="modalHapus">
  <div class="modal" style="max-width:420px">
    <div class="modal-header">
      <h3>Konfirmasi Hapus</h3>
      <button class="modal-close" onclick="tutupModal('modalHapus')"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p style="color:var(--muted);line-height:1.8">
        Yakin ingin menghapus buku:<br>
        <strong id="judulHapus" style="color:var(--ink)"></strong>?<br>
        <span style="font-size:.82rem">File lampiran akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.</span>
      </p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="tutupModal('modalHapus')">Batal</button>
      <a id="linkHapus" href="#" class="btn btn-danger"><i class="fa fa-trash"></i> Ya, Hapus</a>
    </div>
  </div>
</div>

<div id="toastBox"></div>
</div></div>

<script>
// ============================================================
// MODAL
// ============================================================
function bukaModal(id) { document.getElementById(id).classList.add('open'); }
function tutupModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

function bukaModalEdit(data) {
  document.getElementById('editId').value        = data.id;
  document.getElementById('editJudul').value     = data.judul        || '';
  document.getElementById('editPengarang').value = data.pengarang    || '';
  document.getElementById('editIsbn').value      = data.isbn         || '';
  document.getElementById('editTahun').value     = data.tahun        || '';
  document.getElementById('editStok').value      = data.stok         || 0;
  document.getElementById('editDeskripsi').value = data.deskripsi    || '';
  const sel = document.getElementById('editKategori');
  [...sel.options].forEach(o => o.selected = (o.value === data.kategori));
  document.getElementById('listE').innerHTML = '';
  bukaModal('modalEdit');
}

function bukaModalDetail(data) {
  const files = data.file_lampiran ? JSON.parse(data.file_lampiran) : [];
  const fileHtml = files.length
    ? files.map(f => `<div class="file-item" style="margin-top:.3rem"><i class="fa fa-paperclip"></i> ${f.nama}</div>`).join('')
    : '<span style="color:var(--muted)">Tidak ada file</span>';
  document.getElementById('isiDetail').innerHTML = `
    <table style="width:100%;font-size:.88rem;border-collapse:collapse">
      <tr><td style="padding:.45rem 0;color:var(--muted);width:110px">Judul</td><td><strong>${data.judul}</strong></td></tr>
      <tr><td style="padding:.45rem 0;color:var(--muted)">Pengarang</td><td>${data.pengarang||'—'}</td></tr>
      <tr><td style="padding:.45rem 0;color:var(--muted)">ISBN</td><td>${data.isbn||'—'}</td></tr>
      <tr><td style="padding:.45rem 0;color:var(--muted)">Kategori</td><td><span class="badge badge-gold">${data.kategori||'—'}</span></td></tr>
      <tr><td style="padding:.45rem 0;color:var(--muted)">Tahun</td><td>${data.tahun||'—'}</td></tr>
      <tr><td style="padding:.45rem 0;color:var(--muted)">Stok</td><td>${parseInt(data.stok)>0?'<span class="badge badge-green">'+data.stok+' tersedia</span>':'<span class="badge badge-red">Habis</span>'}</td></tr>
      <tr><td style="padding:.45rem 0;color:var(--muted);vertical-align:top">Deskripsi</td><td style="line-height:1.6">${data.deskripsi||'—'}</td></tr>
      <tr><td style="padding:.45rem 0;color:var(--muted);vertical-align:top">File</td><td>${fileHtml}</td></tr>
    </table>`;
  bukaModal('modalDetail');
}

function bukaModalHapus(id, judul) {
  document.getElementById('judulHapus').textContent = judul;
  document.getElementById('linkHapus').href = 'hapus.php?id=' + id;
  bukaModal('modalHapus');
}

// ============================================================
// UPLOAD PREVIEW
// ============================================================
function tampilFile(input, listId) {
  const list = document.getElementById(listId);
  list.innerHTML = '';
  [...input.files].forEach((f, i) => {
    const ext  = f.name.split('.').pop().toUpperCase();
    const size = f.size < 1048576 ? (f.size/1024).toFixed(1)+' KB' : (f.size/1048576).toFixed(1)+' MB';
    const ico  = {PDF:'fa-file-pdf',DOC:'fa-file-word',DOCX:'fa-file-word',
                  JPG:'fa-file-image',JPEG:'fa-file-image',PNG:'fa-file-image'}[ext] || 'fa-file';
    const d = document.createElement('div');
    d.className = 'file-item';
    d.innerHTML = `<i class="fa ${ico}"></i><span>${f.name}</span><span class="file-size">${size}</span>`;
    list.appendChild(d);
  });
}

// Drag & drop
document.querySelectorAll('.upload-zone').forEach(z => {
  z.addEventListener('dragover',  e => { e.preventDefault(); z.classList.add('dragover'); });
  z.addEventListener('dragleave', () => z.classList.remove('dragover'));
  z.addEventListener('drop',      e => { e.preventDefault(); z.classList.remove('dragover'); });
});

// ============================================================
// DATATABLE — Search · Sort · Filter · Pagination · Export
// ============================================================
const tbody      = document.getElementById('tabelBody');
const semuaRow   = [...tbody.querySelectorAll('tr')];
let halamanAktif = 1;
let perHalaman   = 8;
let sortKolom    = -1;
let sortAsc      = true;

// --- Sort ---
document.querySelectorAll('th.sortable').forEach(th => {
  th.style.cursor = 'pointer';
  th.addEventListener('click', () => {
    const col = parseInt(th.dataset.col);
    if (sortKolom === col) sortAsc = !sortAsc;
    else { sortKolom = col; sortAsc = true; }

    // Update class visual
    document.querySelectorAll('th.sortable').forEach(t => t.classList.remove('sort-asc','sort-desc'));
    th.classList.add(sortAsc ? 'sort-asc' : 'sort-desc');

    semuaRow.sort((a, b) => {
      const ta = a.cells[col]?.textContent.trim() || '';
      const tb = b.cells[col]?.textContent.trim() || '';
      const cmp = ta.localeCompare(tb, 'id', {numeric: true});
      return sortAsc ? cmp : -cmp;
    });
    semuaRow.forEach(r => tbody.appendChild(r));
    halamanAktif = 1;
    renderTabel();
  });
});

// --- Filter & Render ---
function renderTabel() {
  const keyword  = document.getElementById('searchInput').value.toLowerCase().trim();
  const katFilter = document.getElementById('filterKategori').value;
  const stokFilter = document.getElementById('filterStok').value;

  // Filter rows
  const filtered = semuaRow.filter(r => {
    const teks = r.textContent.toLowerCase();
    const kat  = r.dataset.kategori || '';
    const stok = r.dataset.stok     || '';
    return (
      (!keyword  || teks.includes(keyword)) &&
      (!katFilter || kat === katFilter) &&
      (!stokFilter || stok === stokFilter)
    );
  });

  const total    = filtered.length;
  const totalHal = Math.max(1, Math.ceil(total / perHalaman));
  if (halamanAktif > totalHal) halamanAktif = 1;
  const mulai = (halamanAktif - 1) * perHalaman;
  const akhir = mulai + perHalaman;

  // Sembunyikan semua, tampilkan yang sesuai halaman
  semuaRow.forEach(r => r.style.display = 'none');
  filtered.slice(mulai, akhir).forEach((r, idx) => {
    r.style.display = '';
    r.querySelector('.td-no').textContent = mulai + idx + 1;

    // Highlight keyword
    if (keyword) highlightRow(r, keyword);
    else clearHighlight(r);
  });

  // Info
  const infoEl = document.getElementById('infoData');
  if (total === 0) {
    infoEl.textContent = 'Tidak ada data yang cocok';
  } else {
    infoEl.textContent = `Menampilkan ${mulai+1}–${Math.min(akhir,total)} dari ${total} entri`;
  }

  // Pagination
  const pgBox = document.getElementById('paginasiBtn');
  pgBox.innerHTML = '';
  if (totalHal <= 1) return;

  buatPgBtn(pgBox, '«', 1,            halamanAktif === 1);
  buatPgBtn(pgBox, '‹', halamanAktif-1, halamanAktif === 1);
  // Window halaman ±2
  let start = Math.max(1, halamanAktif-2);
  let end   = Math.min(totalHal, halamanAktif+2);
  if (start > 1) { buatPgBtn(pgBox, '1', 1); if (start > 2) pgBox.insertAdjacentHTML('beforeend','<span style="padding:0 .3rem;color:var(--muted)">…</span>'); }
  for (let i = start; i <= end; i++) buatPgBtn(pgBox, i, i, false, i === halamanAktif);
  if (end < totalHal) { if (end < totalHal-1) pgBox.insertAdjacentHTML('beforeend','<span style="padding:0 .3rem;color:var(--muted)">…</span>'); buatPgBtn(pgBox, totalHal, totalHal); }
  buatPgBtn(pgBox, '›', halamanAktif+1, halamanAktif === totalHal);
  buatPgBtn(pgBox, '»', totalHal,       halamanAktif === totalHal);
}

function buatPgBtn(container, label, page, disabled=false, active=false) {
  const b = document.createElement('button');
  b.className = 'pg-btn' + (active ? ' active' : '');
  b.textContent = label;
  b.disabled = disabled;
  b.onclick = () => { halamanAktif = page; renderTabel(); };
  container.appendChild(b);
}

function gantiEntries(val) {
  perHalaman = parseInt(val);
  halamanAktif = 1;
  renderTabel();
}

// --- Highlight ---
function highlightRow(row, keyword) {
  ['td:nth-child(2)','td:nth-child(3)','td:nth-child(4)'].forEach(sel => {
    const td = row.querySelector(sel);
    if (!td) return;
    const strong = td.querySelector('strong');
    const target = strong || td;
    const ori = target.textContent;
    const re  = new RegExp(`(${keyword.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
    target.innerHTML = ori.replace(re, '<mark>$1</mark>');
  });
}

function clearHighlight(row) {
  row.querySelectorAll('mark').forEach(m => {
    m.replaceWith(document.createTextNode(m.textContent));
  });
}

// ============================================================
// EXPORT CSV
// ============================================================
function getDataExport() {
  const keyword   = document.getElementById('searchInput').value.toLowerCase().trim();
  const katFilter  = document.getElementById('filterKategori').value;
  const stokFilter = document.getElementById('filterStok').value;
  return semuaRow.filter(r => {
    const teks = r.textContent.toLowerCase();
    return (!keyword || teks.includes(keyword)) &&
           (!katFilter || r.dataset.kategori === katFilter) &&
           (!stokFilter || r.dataset.stok === stokFilter);
  });
}

function exportCSV() {
  const rows = [['No','Judul','Pengarang','Kategori','Tahun','Stok']];
  getDataExport().forEach((r, i) => {
    const c = r.cells;
    rows.push([
      i+1,
      c[1]?.querySelector('strong')?.textContent.trim() || c[1]?.textContent.trim(),
      c[2]?.textContent.trim(),
      c[3]?.textContent.trim(),
      c[4]?.textContent.trim(),
      c[5]?.textContent.trim()
    ]);
  });
  const csv  = rows.map(r => r.map(v=>`"${(v||'').replace(/"/g,'""')}"`).join(',')).join('\n');
  unduhFile('\uFEFF'+csv, 'data-buku.csv', 'text/csv;charset=utf-8');
  showToast('CSV berhasil diexport!');
}

function exportExcel() {
  // Export sebagai HTML table yang bisa dibuka Excel
  const rows = getDataExport();
  let html = `<html xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:x="urn:schemas-microsoft-com:office:excel">
    <head><meta charset="UTF-8"><style>td,th{border:1px solid #ccc;padding:6px}th{background:#f5f0e8;font-weight:bold}</style></head>
    <body><table><thead><tr>
      <th>No</th><th>Judul</th><th>Pengarang</th><th>Kategori</th><th>Tahun</th><th>Stok</th>
    </tr></thead><tbody>`;
  rows.forEach((r, i) => {
    const c = r.cells;
    html += `<tr>
      <td>${i+1}</td>
      <td>${c[1]?.querySelector('strong')?.textContent.trim()||c[1]?.textContent.trim()}</td>
      <td>${c[2]?.textContent.trim()}</td>
      <td>${c[3]?.textContent.trim()}</td>
      <td>${c[4]?.textContent.trim()}</td>
      <td>${c[5]?.textContent.trim()}</td>
    </tr>`;
  });
  html += '</tbody></table></body></html>';
  unduhFile(html, 'data-buku.xls', 'application/vnd.ms-excel');
  showToast('Excel berhasil diexport!');
}

function unduhFile(content, filename, type) {
  const blob = new Blob([content], {type});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = filename;
  a.click();
  URL.revokeObjectURL(a.href);
}

// ============================================================
// TOAST
// ============================================================
function showToast(msg, icon='circle-check') {
  const b = document.getElementById('toastBox');
  const t = document.createElement('div');
  t.className = 'toast';
  t.innerHTML = `<i class="fa fa-${icon}"></i> ${msg}`;
  b.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}
<?php if ($pesan): ?>
showToast('<?= htmlspecialchars(addslashes($pesan)) ?>','<?= $tipe==="sukses"?"circle-check":"circle-exclamation" ?>');
<?php endif; ?>

// Sidebar mobile
if (window.innerWidth <= 900) document.getElementById('toggler').style.display = 'block';
window.addEventListener('resize', () => {
  document.getElementById('toggler').style.display = window.innerWidth <= 900 ? 'block' : 'none';
});

// Init
renderTabel();
</script>
</body></html>
