<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

require_once 'config/db.php';

$pageTitle = 'Media';
$depth = 0;
require_once 'includes/header.php';
?>

<style>
/* Animasi CSS */
@keyframes floatBook {
  0%,100% { transform: translateY(0) rotate(-3deg); }
  50%      { transform: translateY(-18px) rotate(3deg); }
}
@keyframes pulseRing {
  0%   { transform: scale(0.8); opacity:1; }
  100% { transform: scale(2.2); opacity:0; }
}
@keyframes slideIn {
  from { transform: translateX(60px); opacity:0; }
  to   { transform: translateX(0);    opacity:1; }
}
@keyframes fadeInUp {
  from { transform: translateY(30px); opacity:0; }
  to   { transform: translateY(0);    opacity:1; }
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
@keyframes colorShift {
  0%   { color: var(--gold); }
  33%  { color: var(--sage); }
  66%  { color: var(--rust); }
  100% { color: var(--gold); }
}
@keyframes bounceIn {
  0%   { transform:scale(0); opacity:0; }
  60%  { transform:scale(1.15); }
  100% { transform:scale(1); opacity:1; }
}

.anim-stage {
  background: var(--ink);
  border-radius: var(--radius);
  padding: 2.5rem;
  text-align: center;
  min-height: 220px;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: 1rem; position: relative; overflow: hidden;
}
.pulse-ring {
  width: 70px; height: 70px;
  border: 3px solid var(--gold);
  border-radius: 50%;
  position: absolute;
  animation: pulseRing 1.8s ease-out infinite;
}
.floating-book {
  font-size: 3.5rem;
  animation: floatBook 3s ease-in-out infinite;
  position: relative; z-index: 1;
}
.slide-items span {
  display: inline-block;
  animation: slideIn 0.6s ease both;
  font-size: .9rem; color: rgba(245,240,232,0.7);
  margin: 0 .4rem;
}
.slide-items span:nth-child(2) { animation-delay:.1s; }
.slide-items span:nth-child(3) { animation-delay:.2s; }
.slide-items span:nth-child(4) { animation-delay:.3s; }

.loading-dots span {
  display: inline-block; width: 10px; height: 10px;
  border-radius: 50%; background: var(--gold);
  animation: pulseRing 1.2s ease-in-out infinite;
  margin: 0 4px;
}
.loading-dots span:nth-child(2) { animation-delay:.2s; }
.loading-dots span:nth-child(3) { animation-delay:.4s; }

.media-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 1.25rem; margin-bottom: 1.25rem;
}
.media-card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: var(--radius); overflow: hidden;
}
.media-label {
  display: flex; align-items: center; gap: .5rem;
  padding: .8rem 1.1rem; border-bottom: 1px solid var(--border);
  font-size: .82rem; font-weight: 600; color: var(--muted);
  background: var(--cream);
}
.media-body { padding: 1.1rem; }
.audio-player-custom {
  background: var(--ink); border-radius: var(--radius);
  padding: 1.5rem; color: var(--cream);
}
.audio-player-custom h4 { font-family: var(--ff-serif); color: var(--gold); margin-bottom: .3rem; }
.audio-player-custom p  { font-size: .82rem; color: rgba(245,240,232,.55); margin-bottom: 1rem; }
.audio-player-custom audio { width: 100%; margin-top: .5rem; }

/* Slideshow buku -->
.slideshow {
  position: relative; overflow: hidden;
  border-radius: var(--radius); min-height: 150px;
  background: var(--ink); display: flex; align-items: center; justify-content: center;
}
.slide { display: none; text-align: center; padding: 1.5rem; animation: fadeInUp .5s ease; }
.slide.active { display: block; }
.slide .emoji { font-size: 3rem; margin-bottom: .5rem; }
.slide p { color: rgba(245,240,232,.7); font-size: .88rem; }
.slide strong { color: var(--gold); font-family: var(--ff-serif); display: block; margin-bottom: .3rem; }
.slideshow-dots { display: flex; justify-content: center; gap: .4rem; margin-top: .75rem; }
.sdot { width: 8px; height: 8px; border-radius: 50%; background: var(--border); cursor: pointer; transition: background .2s; }
.sdot.active { background: var(--gold); }

/* Counter animasi */
.counter { font-size: 2.8rem; font-weight: 700; color: var(--gold); font-family: var(--ff-serif); }
</style>

<div class="page-content">
  <div class="page-header">
    <h2>Media & Animasi</h2>
    <p>Video tutorial, animasi CSS, dan audio suasana perpustakaan</p>
  </div>

  <div class="media-grid">

    <!-- VIDEO -->
    <div class="media-card">
      <div class="media-label"><i class="fa fa-play-circle" style="color:var(--gold)"></i> Video Tutorial</div>
      <div class="media-body">
        <video controls style="width:100%;border-radius:8px;background:var(--ink);max-height:220px"
          poster="">
          <source src="assets/video/tutorial.mp4" type="video/mp4">
          <!-- Ganti src dengan path video kamu -->
          <p style="color:var(--muted);text-align:center;padding:2rem;font-size:.85rem">
            <i class="fa fa-video" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3"></i>
            Letakkan file video di <code>assets/video/tutorial.mp4</code>
          </p>
        </video>
        <div style="margin-top:.85rem">
          <strong style="font-size:.95rem">Tutorial Penggunaan BiblioTek</strong>
          <p style="font-size:.8rem;color:var(--muted);margin-top:.25rem">Panduan lengkap penggunaan sistem perpustakaan digital</p>
          <div style="display:flex;gap:.5rem;margin-top:.6rem">
            <span class="badge badge-green">Video</span>
            <span class="badge badge-muted">MP4</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ANIMASI CSS -->
    <div class="media-card">
      <div class="media-label"><i class="fa fa-wand-magic-sparkles" style="color:var(--gold)"></i> Animasi CSS3</div>
      <div class="media-body" style="padding:0">
        <div class="anim-stage">
          <div class="pulse-ring"></div>
          <div class="pulse-ring" style="animation-delay:.6s"></div>
          <div class="floating-book">📚</div>
          <h3 style="color:var(--gold);font-size:1.1rem;position:relative;z-index:1">Selamat Membaca!</h3>
          <div class="slide-items" style="position:relative;z-index:1">
            <span>📖 Fiksi</span>
            <span>🔬 Sains</span>
            <span>💻 Teknologi</span>
            <span>📜 Sejarah</span>
          </div>
          <div class="loading-dots" style="position:relative;z-index:1">
            <span></span><span></span><span></span>
          </div>
        </div>
        <div style="padding:.85rem 1.1rem;font-size:.8rem;color:var(--muted)">
          Animasi: <code>floatBook</code> · <code>pulseRing</code> · <code>slideIn</code> — tanpa library
        </div>
      </div>
    </div>

    <!-- AUDIO -->
    <div class="media-card">
      <div class="media-label"><i class="fa fa-music" style="color:var(--gold)"></i> Audio Perpustakaan</div>
      <div class="media-body">
        <div class="audio-player-custom">
          <h4>Musik Suasana Perpustakaan</h4>
          <p>Lo-fi study beats untuk konsentrasi belajar</p>
          <audio controls style="width:100%">
            <source src="assets/audio/lofi.mp3" type="audio/mpeg">
            <!-- Ganti src dengan path audio kamu -->
          </audio>
        </div>
        <div style="margin-top:.85rem">
          <p style="font-size:.8rem;color:var(--muted)">
            <i class="fa fa-info-circle"></i>
            Letakkan file audio di <code>assets/audio/lofi.mp3</code>
          </p>
          <div style="display:flex;gap:.5rem;margin-top:.6rem">
            <span class="badge badge-muted">Audio</span>
            <span class="badge badge-muted">MP3</span>
          </div>
        </div>
      </div>
    </div>

    <!-- SLIDESHOW BUKU -->
    <div class="media-card">
      <div class="media-label"><i class="fa fa-images" style="color:var(--gold)"></i> Slideshow Koleksi</div>
      <div class="media-body">
        <div class="slideshow">
          <div class="slide active">
            <div class="emoji">📖</div>
            <strong>Laskar Pelangi</strong>
            <p>Andrea Hirata — Fiksi · 2005</p>
          </div>
          <div class="slide">
            <div class="emoji">🔬</div>
            <strong>Fisika Dasar Jilid 1</strong>
            <p>Halliday & Resnick — Sains · 2014</p>
          </div>
          <div class="slide">
            <div class="emoji">💻</div>
            <strong>Clean Code</strong>
            <p>Robert C. Martin — Teknologi · 2008</p>
          </div>
          <div class="slide">
            <div class="emoji">📜</div>
            <strong>Bumi Manusia</strong>
            <p>Pramoedya Ananta Toer — Fiksi · 1980</p>
          </div>
        </div>
        <div class="slideshow-dots" id="slideDots"></div>
        <div style="display:flex;justify-content:center;gap:.5rem;margin-top:.75rem">
          <button class="btn btn-outline btn-sm" onclick="prevSlide()"><i class="fa fa-chevron-left"></i></button>
          <button class="btn btn-outline btn-sm" onclick="nextSlide()"><i class="fa fa-chevron-right"></i></button>
        </div>
      </div>
    </div>

  </div>

  <!-- ROW BAWAH: Counter animasi + Efek CSS -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">

    <!-- COUNTER ANIMASI -->
    <div class="card">
      <div class="card-header"><h3><i class="fa fa-chart-bar" style="color:var(--gold)"></i> Counter Animasi</h3></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;text-align:center">
          <?php
          $totalBuku = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM buku"))[0]??0;
          $totalStok = mysqli_fetch_row(mysqli_query($conn,"SELECT COALESCE(SUM(stok),0) FROM buku"))[0]??0;
          $totalAngg = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM anggota"))[0]??0;
          ?>
          <div>
            <div class="counter" data-target="<?= $totalBuku ?>">0</div>
            <div style="font-size:.78rem;color:var(--muted);margin-top:.25rem">Judul Buku</div>
          </div>
          <div>
            <div class="counter" data-target="<?= $totalStok ?>">0</div>
            <div style="font-size:.78rem;color:var(--muted);margin-top:.25rem">Total Stok</div>
          </div>
          <div>
            <div class="counter" data-target="<?= $totalAngg ?>">0</div>
            <div style="font-size:.78rem;color:var(--muted);margin-top:.25rem">Anggota TTD</div>
          </div>
        </div>
        <p style="font-size:.78rem;color:var(--muted);text-align:center;margin-top:1rem">
          Data real-time dari database
        </p>
      </div>
    </div>

    <!-- EFEK TEKS CSS -->
    <div class="card">
      <div class="card-header"><h3><i class="fa fa-palette" style="color:var(--gold)"></i> Efek CSS Lainnya</h3></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:1rem">
        <div style="text-align:center;padding:1rem;background:var(--cream);border-radius:8px">
          <span style="font-family:var(--ff-serif);font-size:1.3rem;animation:colorShift 3s linear infinite;display:inline-block">
            BiblioTek Digital
          </span>
          <p style="font-size:.75rem;color:var(--muted);margin-top:.3rem">colorShift animation</p>
        </div>
        <div style="text-align:center;padding:1rem;background:var(--ink);border-radius:8px">
          <span style="font-size:2rem;display:inline-block;animation:spin 4s linear infinite">⚙️</span>
          <span style="font-size:2rem;display:inline-block;animation:bounceIn 1s ease both 0.5s">📚</span>
          <span style="font-size:2rem;display:inline-block;animation:floatBook 2s ease-in-out infinite">📖</span>
          <p style="font-size:.75rem;color:rgba(245,240,232,.4);margin-top:.3rem">spin · bounceIn · float</p>
        </div>
      </div>
    </div>

  </div>
</div>

<div id="toastBox"></div>
</div></div>

<script>
// ===== SLIDESHOW =====
const slides = document.querySelectorAll('.slide');
const dotsBox = document.getElementById('slideDots');
let aktifSlide = 0;
let intervalSlide;

slides.forEach((_,i) => {
  const d = document.createElement('div');
  d.className = 'sdot' + (i===0?' active':'');
  d.onclick = () => goSlide(i);
  dotsBox.appendChild(d);
});

function goSlide(n) {
  slides[aktifSlide].classList.remove('active');
  dotsBox.children[aktifSlide].classList.remove('active');
  aktifSlide = (n + slides.length) % slides.length;
  slides[aktifSlide].classList.add('active');
  dotsBox.children[aktifSlide].classList.add('active');
}
function nextSlide() { goSlide(aktifSlide + 1); resetInterval(); }
function prevSlide() { goSlide(aktifSlide - 1); resetInterval(); }
function resetInterval() { clearInterval(intervalSlide); intervalSlide = setInterval(() => goSlide(aktifSlide+1), 3500); }
intervalSlide = setInterval(() => goSlide(aktifSlide + 1), 3500);

// ===== COUNTER ANIMASI =====
function animateCounter(el) {
  const target = parseInt(el.dataset.target);
  const dur    = 1500;
  const step   = Math.ceil(target / (dur / 16));
  let current  = 0;
  const timer  = setInterval(() => {
    current += step;
    if (current >= target) { current = target; clearInterval(timer); }
    el.textContent = current;
  }, 16);
}

const observer = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      document.querySelectorAll('.counter').forEach(animateCounter);
      observer.disconnect();
    }
  });
}, { threshold: 0.3 });
const counterSection = document.querySelector('.counter');
if (counterSection) observer.observe(counterSection);

// Toast
function showToast(msg, icon='circle-check') {
  const b = document.getElementById('toastBox');
  const t = document.createElement('div');
  t.className='toast';
  t.innerHTML=`<i class="fa fa-${icon}"></i> ${msg}`;
  b.appendChild(t);
  setTimeout(()=>t.remove(),3500);
}

// Sidebar mobile
if (window.innerWidth<=900) document.getElementById('toggler').style.display='block';
window.addEventListener('resize',()=>{
  document.getElementById('toggler').style.display=window.innerWidth<=900?'block':'none';
});
</script>
</body>
</html>
