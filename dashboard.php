<?php
require_once 'config/helpers.php';
requireLogin();

$pageTitle = 'Dashboard';
$uid = (int)$_SESSION['user_id'];
$isAdm = isAdmin();

// ─── Stats ───────────────────────────────────────────
$whereUser = $isAdm ? '1=1' : "h.user_id = $uid";

// Total piutang belum lunas (orang berhutang ke kita)
$r = db()->query("SELECT COALESCE(SUM(sisa),0) as total FROM hutang h WHERE $whereUser AND jenis='piutang' AND status='belum_lunas'");
$totalPiutang = $r->fetch_assoc()['total'];

// Total hutang kita yang belum lunas
$r = db()->query("SELECT COALESCE(SUM(sisa),0) as total FROM hutang h WHERE $whereUser AND jenis='hutang' AND status='belum_lunas'");
$totalHutang = $r->fetch_assoc()['total'];

// Jumlah transaksi
$r = db()->query("SELECT COUNT(*) as c FROM hutang h WHERE $whereUser");
$totalTrx = $r->fetch_assoc()['c'];

// Pembayaran bulan ini
$r = db()->query("SELECT COALESCE(SUM(p.jumlah_bayar),0) as total FROM pembayaran p JOIN hutang h ON p.hutang_id=h.id WHERE $whereUser AND MONTH(p.tanggal_bayar)=MONTH(NOW()) AND YEAR(p.tanggal_bayar)=YEAR(NOW())");
$bayarBulanIni = $r->fetch_assoc()['total'];

// Jatuh tempo minggu ini
$r = db()->query("SELECT COUNT(*) as c FROM hutang h WHERE $whereUser AND status='belum_lunas' AND jatuh_tempo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$jatuhTempo = $r->fetch_assoc()['c'];

// ─── Chart: Hutang per Bulan (6 bulan terakhir) ───────
$chartLabels = [];
$chartPiutang = [];
$chartHutang  = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $chartLabels[] = date('M Y', strtotime("-$i months"));
    $r1 = db()->query("SELECT COALESCE(SUM(sisa),0) as t FROM hutang h WHERE $whereUser AND jenis='piutang' AND DATE_FORMAT(created_at,'%Y-%m')='$m'");
    $r2 = db()->query("SELECT COALESCE(SUM(sisa),0) as t FROM hutang h WHERE $whereUser AND jenis='hutang' AND DATE_FORMAT(created_at,'%Y-%m')='$m'");
    $chartPiutang[] = (float)$r1->fetch_assoc()['t'];
    $chartHutang[]  = (float)$r2->fetch_assoc()['t'];
}

// ─── Chart: Status Donut ──────────────────────────────
$r = db()->query("SELECT status, COUNT(*) as c FROM hutang h WHERE $whereUser GROUP BY status");
$statusData = ['belum_lunas' => 0, 'lunas' => 0, 'macet' => 0];
while ($row = $r->fetch_assoc()) $statusData[$row['status']] = (int)$row['c'];

// ─── Transaksi Terbaru ────────────────────────────────
$recentSql = "SELECT h.*, d.nama as debitur_nama, u.nama as user_nama 
  FROM hutang h 
  JOIN debitur d ON h.debitur_id = d.id 
  JOIN users u ON h.user_id = u.id 
  WHERE $whereUser 
  ORDER BY h.created_at DESC LIMIT 6";
$recent = db()->query($recentSql);

// ─── Jatuh Tempo Segera ───────────────────────────────
$tempoSql = "SELECT h.*, d.nama as debitur_nama 
  FROM hutang h JOIN debitur d ON h.debitur_id = d.id 
  WHERE $whereUser AND status='belum_lunas' AND jatuh_tempo IS NOT NULL AND jatuh_tempo <= DATE_ADD(CURDATE(), INTERVAL 14 DAY) AND jatuh_tempo >= CURDATE()
  ORDER BY jatuh_tempo ASC LIMIT 5";
$tempoRows = db()->query($tempoSql);

require_once 'includes/header.php';
?>

<!-- Stat Cards -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-hand-holding-dollar"></i></div>
    <div class="stat-info">
      <div class="stat-label">Total Piutang (Belum Bayar)</div>
      <div class="stat-value" style="color:var(--success)"><?= formatRupiah($totalPiutang) ?></div>
      <div class="stat-sub">Orang berhutang ke Anda</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-money-check-dollar"></i></div>
    <div class="stat-info">
      <div class="stat-label">Total Hutang (Belum Bayar)</div>
      <div class="stat-value" style="color:var(--danger)"><?= formatRupiah($totalHutang) ?></div>
      <div class="stat-sub">Hutang Anda ke orang lain</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-receipt"></i></div>
    <div class="stat-info">
      <div class="stat-label">Total Transaksi</div>
      <div class="stat-value"><?= $totalTrx ?></div>
      <div class="stat-sub">Semua catatan</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fa-solid fa-wallet"></i></div>
    <div class="stat-info">
      <div class="stat-label">Pembayaran Bulan Ini</div>
      <div class="stat-value"><?= formatRupiah($bayarBulanIni) ?></div>
      <div class="stat-sub"><?= date('F Y') ?></div>
    </div>
  </div>
  <?php if ($jatuhTempo > 0): ?>
  <div class="stat-card" style="border-color:rgba(245,158,11,.3)">
    <div class="stat-icon amber"><i class="fa-solid fa-clock-rotate-left"></i></div>
    <div class="stat-info">
      <div class="stat-label">Jatuh Tempo 7 Hari</div>
      <div class="stat-value" style="color:var(--warning)"><?= $jatuhTempo ?></div>
      <div class="stat-sub">Transaksi perlu perhatian</div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Charts Row -->
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;margin-bottom:24px">
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-chart-line" style="color:var(--accent);margin-right:8px"></i>Tren Hutang & Piutang (6 Bulan)</span>
    </div>
    <div class="card-body">
      <canvas id="trendChart" height="100"></canvas>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-chart-pie" style="color:var(--accent);margin-right:8px"></i>Status Hutang</span>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;align-items:center">
      <canvas id="donutChart" style="max-width:180px;max-height:180px"></canvas>
      <div style="margin-top:16px;width:100%">
        <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:6px">
          <span><span style="display:inline-block;width:10px;height:10px;background:#f59e0b;border-radius:3px;margin-right:6px"></span>Belum Lunas</span>
          <strong><?= $statusData['belum_lunas'] ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:6px">
          <span><span style="display:inline-block;width:10px;height:10px;background:#10b981;border-radius:3px;margin-right:6px"></span>Lunas</span>
          <strong><?= $statusData['lunas'] ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.8rem">
          <span><span style="display:inline-block;width:10px;height:10px;background:#ef4444;border-radius:3px;margin-right:6px"></span>Macet</span>
          <strong><?= $statusData['macet'] ?></strong>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bottom Row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
  <!-- Transaksi Terbaru -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Transaksi Terbaru</span>
      <a href="hutang.php" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    <div class="table-responsive">
      <table>
        <thead><tr><th>Debitur</th><th>Jenis</th><th>Sisa</th><th>Status</th></tr></thead>
        <tbody>
        <?php if ($recent->num_rows === 0): ?>
          <tr><td colspan="4"><div class="empty-state" style="padding:24px"><i class="fa-solid fa-inbox"></i><p>Belum ada transaksi</p></div></td></tr>
        <?php else: while ($row = $recent->fetch_assoc()): ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.85rem"><?= h($row['debitur_nama']) ?></div>
              <div style="font-size:.72rem;color:var(--text-muted)"><?= h($row['kode']) ?></div>
            </td>
            <td>
              <?php if ($row['jenis'] === 'piutang'): ?>
                <span class="badge badge-success"><i class="fa-solid fa-arrow-down"></i>Piutang</span>
              <?php else: ?>
                <span class="badge badge-danger"><i class="fa-solid fa-arrow-up"></i>Hutang</span>
              <?php endif; ?>
            </td>
            <td style="font-family:var(--mono);font-size:.82rem;font-weight:700"><?= formatRupiah($row['sisa']) ?></td>
            <td>
              <?php
              $bs = ['belum_lunas'=>['warning','Belum Lunas'],'lunas'=>['success','Lunas'],'macet'=>['danger','Macet']];
              $st = $bs[$row['status']] ?? ['muted','–'];
              ?>
              <span class="badge badge-<?= $st[0] ?>"><?= $st[1] ?></span>
            </td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Jatuh Tempo -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-bell" style="color:var(--warning);margin-right:6px"></i>Jatuh Tempo Segera</span>
    </div>
    <div class="card-body" style="padding:0">
      <?php if ($tempoRows->num_rows === 0): ?>
        <div class="empty-state"><i class="fa-solid fa-circle-check" style="color:var(--success)"></i><p>Tidak ada yang jatuh tempo dalam 14 hari</p></div>
      <?php else: while ($row = $tempoRows->fetch_assoc()):
        $daysLeft = (int)floor((strtotime($row['jatuh_tempo']) - time()) / 86400);
        $urgency = $daysLeft <= 3 ? 'danger' : 'warning';
      ?>
        <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--border)">
          <div style="width:42px;height:42px;border-radius:10px;background:rgba(<?= $urgency==='danger'?'239,68,68':'245,158,11' ?>,.12);display:grid;place-items:center;flex-shrink:0">
            <i class="fa-solid fa-clock" style="color:var(--<?= $urgency ?>)"></i>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.85rem;font-weight:600"><?= h($row['debitur_nama']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted)"><?= formatRupiah($row['sisa']) ?> — <?= formatDate($row['jatuh_tempo']) ?></div>
          </div>
          <span class="badge badge-<?= $urgency ?>"><?= $daysLeft ?> hari</span>
        </div>
      <?php endwhile; endif; ?>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const gridColor = isDark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.06)';
const textColor = isDark ? '#8b949e' : '#64748b';

// Trend Chart
new Chart(document.getElementById('trendChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [
      { label: 'Piutang', data: <?= json_encode($chartPiutang) ?>, backgroundColor: 'rgba(16,185,129,.7)', borderRadius: 6 },
      { label: 'Hutang',  data: <?= json_encode($chartHutang) ?>,  backgroundColor: 'rgba(239,68,68,.7)',  borderRadius: 6 }
    ]
  },
  options: {
    responsive: true, plugins: { legend: { labels: { color: textColor } } },
    scales: {
      x: { grid: { color: gridColor }, ticks: { color: textColor } },
      y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt' } }
    }
  }
});

// Donut
new Chart(document.getElementById('donutChart'), {
  type: 'doughnut',
  data: {
    labels: ['Belum Lunas','Lunas','Macet'],
    datasets: [{ data: [<?= $statusData['belum_lunas'] ?>, <?= $statusData['lunas'] ?>, <?= $statusData['macet'] ?>], backgroundColor: ['#f59e0b','#10b981','#ef4444'], borderWidth: 0, hoverOffset: 6 }]
  },
  options: { cutout: '68%', plugins: { legend: { display: false } } }
});
</script>

<?php require_once 'includes/footer.php'; ?>
