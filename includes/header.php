<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle ?? 'Dashboard') . ' — ' . APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ── CSS Variables ──────────────────────────────── */
:root {
  --bg-primary:   #f4f6fb;
  --bg-card:      #ffffff;
  --bg-sidebar:   #0f172a;
  --bg-hover:     #f1f5f9;
  --border:       #e2e8f0;
  --text-primary: #0f172a;
  --text-muted:   #64748b;
  --text-sidebar: #94a3b8;
  --text-sidebar-active: #ffffff;
  --accent:       #6366f1;
  --accent-2:     #8b5cf6;
  --success:      #10b981;
  --warning:      #f59e0b;
  --danger:       #ef4444;
  --info:         #3b82f6;
  --shadow:       0 1px 3px rgba(0,0,0,.07), 0 4px 12px rgba(0,0,0,.05);
  --shadow-lg:    0 10px 40px rgba(0,0,0,.12);
  --radius:       12px;
  --radius-sm:    8px;
  --transition:   .2s ease;
  --sidebar-w:    260px;
  --topbar-h:     64px;
  --font:         'Plus Jakarta Sans', sans-serif;
  --mono:         'JetBrains Mono', monospace;
}
[data-theme="dark"] {
  --bg-primary:   #0d1117;
  --bg-card:      #161b22;
  --bg-sidebar:   #0d1117;
  --bg-hover:     #21262d;
  --border:       #30363d;
  --text-primary: #e6edf3;
  --text-muted:   #8b949e;
  --text-sidebar: #8b949e;
  --shadow:       0 1px 3px rgba(0,0,0,.3), 0 4px 12px rgba(0,0,0,.2);
}

/* ── Reset ──────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 15px; }
body { font-family: var(--font); background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; transition: background var(--transition), color var(--transition); }
a { text-decoration: none; color: var(--accent); }
img { max-width: 100%; }

/* ── Layout ─────────────────────────────────────── */
.app-wrapper { display: flex; min-height: 100vh; }

/* ── Sidebar ────────────────────────────────────── */
.sidebar {
  width: var(--sidebar-w); background: var(--bg-sidebar);
  position: fixed; top: 0; left: 0; height: 100vh;
  display: flex; flex-direction: column; z-index: 100;
  transition: transform var(--transition);
  border-right: 1px solid rgba(255,255,255,.05);
}
.sidebar-brand {
  padding: 24px 20px 16px;
  display: flex; align-items: center; gap: 12px;
  border-bottom: 1px solid rgba(255,255,255,.07);
}
.brand-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, var(--accent), var(--accent-2));
  display: grid; place-items: center; flex-shrink: 0;
}
.brand-icon i { color: #fff; font-size: 16px; }
.brand-name { font-size: 1.1rem; font-weight: 800; color: #fff; letter-spacing: -.3px; }
.brand-tagline { font-size: .7rem; color: var(--text-sidebar); }

.sidebar-menu { flex: 1; overflow-y: auto; padding: 12px 0; }
.menu-section { padding: 6px 20px 4px; font-size: .65rem; font-weight: 700; color: #475569; letter-spacing: 1px; text-transform: uppercase; }
.sidebar-link {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 20px; margin: 1px 8px; border-radius: 8px;
  color: var(--text-sidebar); font-size: .875rem; font-weight: 500;
  transition: all var(--transition); cursor: pointer;
}
.sidebar-link:hover { background: rgba(255,255,255,.06); color: #cbd5e1; }
.sidebar-link.active { background: linear-gradient(135deg, rgba(99,102,241,.25), rgba(139,92,246,.15)); color: var(--text-sidebar-active); }
.sidebar-link .icon { width: 18px; text-align: center; font-size: 14px; }
.sidebar-link .badge { margin-left: auto; background: var(--danger); color: #fff; font-size: .65rem; padding: 2px 6px; border-radius: 20px; font-weight: 700; }

.sidebar-footer { padding: 12px 8px; border-top: 1px solid rgba(255,255,255,.07); }
.user-card {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px; border-radius: 8px;
  transition: background var(--transition); cursor: pointer;
}
.user-card:hover { background: rgba(255,255,255,.06); }
.user-avatar {
  width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, var(--accent), var(--accent-2));
  display: grid; place-items: center; font-size: 13px; font-weight: 700; color: #fff;
  overflow: hidden;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.user-info { flex: 1; min-width: 0; }
.user-name { font-size: .82rem; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { font-size: .7rem; color: var(--text-sidebar); }

/* ── Main Content ────────────────────────────────── */
.main-content { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

/* ── Topbar ──────────────────────────────────────── */
.topbar {
  height: var(--topbar-h); background: var(--bg-card);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 24px; position: sticky; top: 0; z-index: 90;
  box-shadow: 0 1px 0 var(--border);
}
.topbar-left { display: flex; align-items: center; gap: 16px; }
.menu-toggle { display: none; background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px; padding: 4px; }
.page-title { font-size: 1rem; font-weight: 700; }
.breadcrumb { font-size: .78rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }
.breadcrumb span { color: var(--text-muted); }

.topbar-right { display: flex; align-items: center; gap: 8px; }
.topbar-btn {
  width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border);
  background: var(--bg-card); cursor: pointer; display: grid; place-items: center;
  color: var(--text-muted); transition: all var(--transition);
}
.topbar-btn:hover { background: var(--bg-hover); color: var(--text-primary); }

/* ── Page Body ───────────────────────────────────── */
.page-body { padding: 24px; flex: 1; }

/* ── Cards ───────────────────────────────────────── */
.card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: var(--radius); box-shadow: var(--shadow);
  overflow: hidden;
}
.card-header {
  padding: 16px 20px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}
.card-title { font-size: .9rem; font-weight: 700; }
.card-body { padding: 20px; }

/* ── Stat Cards ──────────────────────────────────── */
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow);
  display: flex; align-items: center; gap: 16px; position: relative; overflow: hidden;
  transition: transform var(--transition), box-shadow var(--transition);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
.stat-icon {
  width: 48px; height: 48px; border-radius: 12px;
  display: grid; place-items: center; font-size: 20px; flex-shrink: 0;
}
.stat-icon.purple { background: rgba(99,102,241,.12); color: var(--accent); }
.stat-icon.green  { background: rgba(16,185,129,.12); color: var(--success); }
.stat-icon.amber  { background: rgba(245,158,11,.12);  color: var(--warning); }
.stat-icon.red    { background: rgba(239,68,68,.12);   color: var(--danger); }
.stat-icon.blue   { background: rgba(59,130,246,.12);  color: var(--info); }
.stat-info { flex: 1; }
.stat-label { font-size: .75rem; color: var(--text-muted); font-weight: 500; margin-bottom: 4px; }
.stat-value { font-size: 1.4rem; font-weight: 800; font-family: var(--mono); letter-spacing: -1px; }
.stat-sub { font-size: .72rem; color: var(--text-muted); margin-top: 2px; }

/* ── Tables ──────────────────────────────────────── */
.table-responsive { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: .85rem; }
thead th { padding: 11px 16px; text-align: left; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); background: var(--bg-hover); border-bottom: 1px solid var(--border); }
tbody td { padding: 13px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: var(--bg-hover); }

/* ── Badges ──────────────────────────────────────── */
.badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600;
}
.badge-success { background: rgba(16,185,129,.12); color: #059669; }
.badge-warning { background: rgba(245,158,11,.12);  color: #d97706; }
.badge-danger  { background: rgba(239,68,68,.12);   color: #dc2626; }
.badge-info    { background: rgba(59,130,246,.12);  color: #2563eb; }
.badge-purple  { background: rgba(99,102,241,.12);  color: #4f46e5; }
.badge-muted   { background: var(--bg-hover);       color: var(--text-muted); }

/* ── Buttons ─────────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 16px; border-radius: var(--radius-sm); border: none;
  font-family: var(--font); font-size: .84rem; font-weight: 600;
  cursor: pointer; transition: all var(--transition); white-space: nowrap;
}
.btn-sm { padding: 6px 12px; font-size: .78rem; }
.btn-xs { padding: 4px 9px; font-size: .72rem; border-radius: 6px; }
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { background: #4f46e5; transform: translateY(-1px); }
.btn-success { background: var(--success); color: #fff; }
.btn-success:hover { background: #059669; }
.btn-danger  { background: var(--danger); color: #fff; }
.btn-danger:hover { background: #dc2626; }
.btn-warning { background: var(--warning); color: #fff; }
.btn-outline {
  background: transparent; border: 1px solid var(--border);
  color: var(--text-primary);
}
.btn-outline:hover { background: var(--bg-hover); }

/* ── Forms ───────────────────────────────────────── */
.form-group { margin-bottom: 16px; }
label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: 6px; color: var(--text-primary); }
.form-control {
  width: 100%; padding: 10px 14px; border: 1px solid var(--border);
  border-radius: var(--radius-sm); background: var(--bg-card);
  color: var(--text-primary); font-family: var(--font); font-size: .875rem;
  transition: border-color var(--transition), box-shadow var(--transition);
  outline: none;
}
.form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
textarea.form-control { resize: vertical; min-height: 80px; }
select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
.form-hint { font-size: .74rem; color: var(--text-muted); margin-top: 4px; }

/* ── Alerts ──────────────────────────────────────── */
.alert {
  padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 16px;
  display: flex; align-items: center; gap: 10px; font-size: .875rem;
}
.alert-success { background: rgba(16,185,129,.1);  border: 1px solid rgba(16,185,129,.25); color: #065f46; }
.alert-danger  { background: rgba(239,68,68,.1);   border: 1px solid rgba(239,68,68,.25);  color: #991b1b; }
.alert-warning { background: rgba(245,158,11,.1);  border: 1px solid rgba(245,158,11,.25); color: #92400e; }
.alert-info    { background: rgba(59,130,246,.1);  border: 1px solid rgba(59,130,246,.25); color: #1e40af; }
[data-theme="dark"] .alert-success { color: #6ee7b7; }
[data-theme="dark"] .alert-danger  { color: #fca5a5; }
[data-theme="dark"] .alert-warning { color: #fcd34d; }
[data-theme="dark"] .alert-info    { color: #93c5fd; }

/* ── Modal ───────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.5); backdrop-filter: blur(4px);
  z-index: 200; display: none; align-items: center; justify-content: center; padding: 16px;
}
.modal-overlay.open { display: flex; animation: fadeIn .15s ease; }
.modal {
  background: var(--bg-card); border-radius: var(--radius);
  box-shadow: var(--shadow-lg); width: 100%; max-width: 600px;
  max-height: 90vh; overflow-y: auto;
  animation: slideUp .2s ease;
}
.modal-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.modal-title { font-size: .95rem; font-weight: 700; }
.modal-close { background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 18px; width: 30px; height: 30px; display: grid; place-items: center; border-radius: 6px; transition: background var(--transition); }
.modal-close:hover { background: var(--bg-hover); color: var(--text-primary); }
.modal-body { padding: 20px; }
.modal-footer { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; gap: 8px; justify-content: flex-end; }
.modal-lg { max-width: 850px; }

/* ── Image Preview ───────────────────────────────── */
.img-preview-wrap { position: relative; display: inline-block; }
.img-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid var(--border); cursor: pointer; transition: transform var(--transition); }
.img-preview:hover { transform: scale(1.05); }
.img-lightbox-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.88); z-index: 300;
  display: none; align-items: center; justify-content: center;
}
.img-lightbox-overlay.open { display: flex; }
.img-lightbox-overlay img { max-width: 90vw; max-height: 90vh; border-radius: 8px; }
.lightbox-close { position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,.15); border: none; color: #fff; width: 40px; height: 40px; border-radius: 50%; font-size: 18px; cursor: pointer; display: grid; place-items: center; }

/* ── Pagination ──────────────────────────────────── */
.pagination { display: flex; gap: 4px; align-items: center; }
.page-btn {
  width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border);
  background: var(--bg-card); color: var(--text-muted); font-size: .8rem; font-weight: 600;
  cursor: pointer; display: grid; place-items: center; transition: all var(--transition);
}
.page-btn:hover { background: var(--bg-hover); color: var(--text-primary); }
.page-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }

/* ── Empty State ─────────────────────────────────── */
.empty-state { text-align: center; padding: 48px 20px; }
.empty-state i { font-size: 3rem; color: var(--border); margin-bottom: 16px; display: block; }
.empty-state h3 { font-size: .95rem; margin-bottom: 6px; }
.empty-state p { font-size: .82rem; color: var(--text-muted); }

/* ── Progress Bar ────────────────────────────────── */
.progress { height: 6px; background: var(--border); border-radius: 99px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 99px; transition: width .5s ease; }

/* ── Overlay / Sidebar mobile ────────────────────── */
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 99; }

/* ── Search ──────────────────────────────────────── */
.search-box { position: relative; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px; }
.search-box input { padding-left: 34px; width: 220px; }

/* ── Animations ──────────────────────────────────── */
@keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); }
  .sidebar.open { transform: translateX(0); }
  .sidebar-overlay.open { display: block; }
  .main-content { margin-left: 0; }
  .menu-toggle { display: grid; place-items: center; }
  .page-body { padding: 16px; }
  .stat-grid { grid-template-columns: 1fr 1fr; }
  .search-box input { width: 140px; }
  .form-row { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  .stat-grid { grid-template-columns: 1fr; }
  .stat-value { font-size: 1.15rem; }
}

/* ── Print ───────────────────────────────────────── */
@media print {
  .sidebar, .topbar, .btn, .pagination { display: none !important; }
  .main-content { margin-left: 0 !important; }
}
</style>
</head>
<body>

<?php $flash = getFlash(); ?>

<div class="app-wrapper">

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fa-solid fa-book-open-cover"></i></div>
    <div>
      <div class="brand-name"><?= APP_NAME ?></div>
      <div class="brand-tagline">Catatan Hutang & Piutang</div>
    </div>
  </div>

  <nav class="sidebar-menu">
    <div class="menu-section">Utama</div>
    <a href="dashboard.php" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : '' ?>">
      <span class="icon"><i class="fa-solid fa-chart-pie"></i></span> Dashboard
    </a>
    <a href="hutang.php" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'hutang.php') ? 'active' : '' ?>">
      <span class="icon"><i class="fa-solid fa-file-invoice-dollar"></i></span> Catatan Hutang
    </a>
    <a href="pembayaran.php" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'pembayaran.php') ? 'active' : '' ?>">
      <span class="icon"><i class="fa-solid fa-money-bill-transfer"></i></span> Pembayaran
    </a>
    <a href="debitur.php" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'debitur.php') ? 'active' : '' ?>">
      <span class="icon"><i class="fa-solid fa-users"></i></span> Debitur
    </a>

    <?php if (isAdmin()): ?>
    <div class="menu-section">Admin</div>
    <a href="users.php" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'users.php') ? 'active' : '' ?>">
      <span class="icon"><i class="fa-solid fa-user-gear"></i></span> Kelola User
    </a>
    <a href="laporan.php" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'laporan.php') ? 'active' : '' ?>">
      <span class="icon"><i class="fa-solid fa-chart-bar"></i></span> Laporan
    </a>
    <a href="log.php" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'log.php') ? 'active' : '' ?>">
      <span class="icon"><i class="fa-solid fa-scroll"></i></span> Log Aktivitas
    </a>
    <?php endif; ?>

    <div class="menu-section">Akun</div>
    <a href="profil.php" class="sidebar-link <?= (basename($_SERVER['PHP_SELF']) === 'profil.php') ? 'active' : '' ?>">
      <span class="icon"><i class="fa-solid fa-circle-user"></i></span> Profil Saya
    </a>
    <a href="logout.php" class="sidebar-link" onclick="return confirm('Yakin ingin keluar?')">
      <span class="icon"><i class="fa-solid fa-right-from-bracket"></i></span> Keluar
    </a>
  </nav>

  <?php $me = currentUser(); ?>
  <div class="sidebar-footer">
    <a href="profil.php" class="user-card" style="text-decoration:none">
      <div class="user-avatar">
        <?php if (!empty($me['avatar'])): ?>
          <img src="<?= $me['avatar'] ?>" alt="">
        <?php else: ?>
          <?= strtoupper(substr($me['nama'] ?? 'U', 0, 1)) ?>
        <?php endif; ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= h($me['nama'] ?? '') ?></div>
        <div class="user-role"><?= ucfirst($me['role'] ?? '') ?></div>
      </div>
    </a>
  </div>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Main -->
<div class="main-content">
  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
      <div>
        <div class="page-title"><?= h($pageTitle ?? 'Dashboard') ?></div>
        <?php if (!empty($breadcrumb)): ?>
        <div class="breadcrumb">
          <a href="dashboard.php">Home</a>
          <?php foreach ($breadcrumb as $label => $link): ?>
            <span>/</span>
            <?php if ($link): ?><a href="<?= $link ?>"><?= h($label) ?></a>
            <?php else: ?><span><?= h($label) ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="topbar-right">
      <button class="topbar-btn" onclick="toggleTheme()" id="themeBtn" title="Toggle tema">
        <i class="fa-solid fa-moon" id="themeIcon"></i>
      </button>
    </div>
  </header>

  <!-- Flash Message -->
  <div style="padding: 0 24px;">
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>" style="margin-top:16px">
      <i class="fa-solid fa-<?= $flash['type'] === 'success' ? 'circle-check' : ($flash['type'] === 'danger' ? 'triangle-exclamation' : 'circle-info') ?>"></i>
      <?= h($flash['message']) ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Page Content -->
  <main class="page-body">
