<?php
session_start();

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, username, password, nama_lengkap, role FROM user WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama_lengkap'];
            $_SESSION['role']     = $user['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — BiblioTek</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>

<div class="login-page">
  <div class="login-wrap">

    <!-- Sisi kiri dekoratif -->
    <div class="login-art">
      <div class="login-art-brand">
        <h1>📚 BiblioTek</h1>
        <p>Sistem Perpustakaan Digital</p>
      </div>
      <div class="login-art-quote">
        "Membaca adalah jendela dunia.<br>
        Setiap buku adalah pintu menuju<br>
        petualangan baru yang tak terbatas."
      </div>
    </div>

    <!-- Sisi kanan form login -->
    <div class="login-form-side">
      <h2>Selamat Datang</h2>
      <p>Masuk untuk mengelola perpustakaan digital</p>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="fa fa-circle-exclamation"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label>Username</label>
          <input
            type="text"
            name="username"
            placeholder="Masukkan username"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            required
            autofocus
          />
        </div>

        <div class="form-group">
          <label>Password</label>
          <div style="position:relative">
            <input
              type="password"
              name="password"
              id="passwordInput"
              placeholder="Masukkan password"
              required
              style="padding-right:2.8rem"
            />
            <button type="button" onclick="togglePass()"
              style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:0.9rem">
              <i class="fa fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem">
          <i class="fa fa-right-to-bracket"></i> Masuk
        </button>
      </form>

      <p style="margin-top:1.5rem;font-size:0.8rem;color:var(--muted);text-align:center">
        Default login: <strong>admin</strong> / <strong>admin123</strong>
      </p>
    </div>

  </div>
</div>

<script>
function togglePass() {
  const inp = document.getElementById('passwordInput');
  const ico = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    ico.className = 'fa fa-eye-slash';
  } else {
    inp.type = 'password';
    ico.className = 'fa fa-eye';
  }
}
</script>
</body>
</html>
