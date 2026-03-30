<?php
require_once 'config/helpers.php';
startSession();

if (isLoggedIn()) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = db()->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            startSession();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            session_regenerate_id(true);
            logActivity('LOGIN', 'User login berhasil');
            redirect('dashboard.php');
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — CatatHutang</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --bg: #f4f6fb; --card: #ffffff; --border: #e2e8f0;
  --text: #0f172a; --muted: #64748b;
  --accent: #6366f1; --accent2: #8b5cf6;
  --danger: #ef4444; --font: 'Plus Jakarta Sans', sans-serif;
}
[data-theme="dark"] { --bg: #0d1117; --card: #161b22; --border: #30363d; --text: #e6edf3; --muted: #8b949e; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: var(--font); background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; transition: background .2s, color .2s; }
.login-wrap { width: 100%; max-width: 420px; }
.login-brand { text-align: center; margin-bottom: 28px; }
.brand-logo { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, var(--accent), var(--accent2)); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 10px; }
.brand-logo i { color: #fff; font-size: 22px; }
.brand-logo-name { font-size: 1.4rem; font-weight: 800; letter-spacing: -.5px; }
.brand-sub { font-size: .82rem; color: var(--muted); }
.login-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 32px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
.login-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 4px; }
.login-subtitle { font-size: .83rem; color: var(--muted); margin-bottom: 24px; }
.form-group { margin-bottom: 16px; }
label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: 6px; }
.input-wrap { position: relative; }
.input-wrap i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; }
.input-wrap input { width: 100%; padding: 11px 14px 11px 38px; border: 1px solid var(--border); border-radius: 9px; background: var(--bg); color: var(--text); font-family: var(--font); font-size: .875rem; outline: none; transition: border-color .2s, box-shadow .2s; }
.input-wrap input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
.eye-btn { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); font-size: 14px; }
.error-msg { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.25); color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: .83rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
[data-theme="dark"] .error-msg { color: #fca5a5; }
.btn-login { width: 100%; padding: 12px; background: linear-gradient(135deg, var(--accent), var(--accent2)); color: #fff; border: none; border-radius: 9px; font-family: var(--font); font-size: .9rem; font-weight: 700; cursor: pointer; transition: opacity .2s, transform .1s; margin-top: 4px; }
.btn-login:hover { opacity: .92; transform: translateY(-1px); }
.demo-info { margin-top: 20px; padding: 12px; background: rgba(99,102,241,.07); border-radius: 8px; font-size: .78rem; color: var(--muted); text-align: center; border: 1px solid rgba(99,102,241,.15); }
.demo-info strong { color: var(--accent); font-family: monospace; }
.theme-toggle { position: fixed; top: 16px; right: 16px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; width: 36px; height: 36px; display: grid; place-items: center; cursor: pointer; color: var(--muted); }
</style>
</head>
<body>
<button class="theme-toggle" onclick="toggleTheme()"><i class="fa-solid fa-moon" id="themeIcon"></i></button>

<div class="login-wrap">
  <div class="login-brand">
    <div class="brand-logo"><i class="fa-solid fa-book-open-cover"></i></div>
    <div class="brand-logo-name">CatatHutang</div>
    <div class="brand-sub">Manajemen Hutang & Piutang</div>
  </div>

  <div class="login-card">
    <h1 class="login-title">Selamat datang 👋</h1>
    <p class="login-subtitle">Masuk ke akun Anda untuk melanjutkan.</p>

    <?php if ($error): ?>
    <div class="error-msg"><i class="fa-solid fa-triangle-exclamation"></i><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username / Email</label>
        <div class="input-wrap">
          <i class="fa-solid fa-user"></i>
          <input type="text" name="username" placeholder="Masukkan username" value="<?= h($_POST['username'] ?? '') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock"></i>
          <input type="password" name="password" id="passwordInput" placeholder="Masukkan password" required>
          <button type="button" class="eye-btn" onclick="togglePwd()"><i class="fa-solid fa-eye" id="eyeIcon"></i></button>
        </div>
      </div>
      <button type="submit" class="btn-login"><i class="fa-solid fa-right-to-bracket" style="margin-right:7px"></i>Masuk</button>
    </form>

    <div class="demo-info">
      Demo: <strong>admin</strong> / <strong>password</strong> &nbsp;|&nbsp; <strong>john</strong> / <strong>password</strong>
    </div>
  </div>
</div>

<script>
(function() {
  const t = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', t);
  document.getElementById('themeIcon').className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
})();
function toggleTheme() {
  const c = document.documentElement.getAttribute('data-theme');
  const n = c === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', n);
  localStorage.setItem('theme', n);
  document.getElementById('themeIcon').className = n === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}
function togglePwd() {
  const i = document.getElementById('passwordInput');
  const e = document.getElementById('eyeIcon');
  i.type = i.type === 'password' ? 'text' : 'password';
  e.className = i.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}
</script>
</body>
</html>
