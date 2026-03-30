<?php
// laporan.php
require_once 'config/helpers.php';
requireAdmin();
$pageTitle = 'Laporan';

$bulan = (int)($_GET['bulan'] ?? date('m'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));

// Summary
$r1 = db()->query("SELECT COALESCE(SUM(sisa),0) as t FROM hutang WHERE jenis='piutang' AND status='belum_lunas'");
$r2 = db()->query("SELECT COALESCE(SUM(sisa),0) as t FROM hutang WHERE jenis='hutang' AND status='belum_lunas'");
$r3 = db()->query("SELECT COALESCE(SUM(jumlah_bayar),0) as t FROM pembayaran WHERE MONTH(tanggal_bayar)=$bulan AND YEAR(tanggal_bayar)=$tahun");
$r4 = db()->query("SELECT COUNT(*) as c FROM hutang WHERE status='lunas' AND MONTH(updated_at)=$bulan AND YEAR(updated_at)=$tahun");

$totalPiutang = $r1->fetch_assoc()['t'];
$totalHutang  = $r2->fetch_assoc()['t'];
$totalBayar   = $r3->fetch_assoc()['t'];
$totalLunas   = $r4->fetch_assoc()['c'];

// Top debitur
$topRows = db()->query("SELECT d.nama, COUNT(h.id) as jml, COALESCE(SUM(h.sisa),0) as sisa FROM hutang h JOIN debitur d ON h.debitur_id=d.id WHERE h.status='belum_lunas' GROUP BY d.id ORDER BY sisa DESC LIMIT 10");

require_once 'includes/header.php';
?>
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
  <form method="GET" style="display:flex;gap:8px">
    <select name="bulan" class="form-control" style="width:140px">
      <?php for ($m=1; $m<=12; $m++): ?><option value="<?= $m ?>" <?= $m===$bulan?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option><?php endfor; ?>
    </select>
    <select name="tahun" class="form-control" style="width:100px">
      <?php for ($y=date('Y')-2; $y<=date('Y')+1; $y++): ?><option value="<?= $y ?>" <?= $y===$tahun?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
    </select>
    <button class="btn btn-outline btn-sm"><i class="fa-solid fa-filter"></i>Tampilkan</button>
  </form>
  <button class="btn btn-outline btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i>Cetak</button>
</div>

<div class="stat-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-hand-holding-dollar"></i></div><div class="stat-info"><div class="stat-label">Total Piutang Aktif</div><div class="stat-value" style="color:var(--success)"><?= formatRupiah($totalPiutang) ?></div></div></div>
  <div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-money-check-dollar"></i></div><div class="stat-info"><div class="stat-label">Total Hutang Aktif</div><div class="stat-value" style="color:var(--danger)"><?= formatRupiah($totalHutang) ?></div></div></div>
  <div class="stat-card"><div class="stat-icon blue"><i class="fa-solid fa-wallet"></i></div><div class="stat-info"><div class="stat-label">Pembayaran <?= date('M Y', mktime(0,0,0,$bulan,1,$tahun)) ?></div><div class="stat-value"><?= formatRupiah($totalBayar) ?></div></div></div>
  <div class="stat-card"><div class="stat-icon purple"><i class="fa-solid fa-circle-check"></i></div><div class="stat-info"><div class="stat-label">Dilunasi <?= date('M Y', mktime(0,0,0,$bulan,1,$tahun)) ?></div><div class="stat-value"><?= $totalLunas ?> transaksi</div></div></div>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Top Debitur Belum Lunas</span></div>
  <div class="table-responsive">
    <table>
      <thead><tr><th>#</th><th>Nama Debitur</th><th>Jumlah Transaksi</th><th>Total Sisa</th><th>Proporsi</th></tr></thead>
      <tbody>
      <?php $no=1; $max=(float)($topRows->num_rows>0?db()->query("SELECT MAX(sisa) as m FROM hutang WHERE status='belum_lunas'")->fetch_assoc()['m']:1); $topRows->data_seek(0); while ($r=$topRows->fetch_assoc()): $pct=$max>0?($r['sisa']/$max)*100:0; ?>
        <tr>
          <td style="color:var(--text-muted)"><?= $no++ ?></td>
          <td><strong><?= h($r['nama']) ?></strong></td>
          <td><span class="badge badge-muted"><?= $r['jml'] ?></span></td>
          <td style="font-family:var(--mono);font-weight:700;color:var(--danger)"><?= formatRupiah($r['sisa']) ?></td>
          <td style="width:200px"><div class="progress"><div class="progress-bar" style="width:<?= $pct ?>%;background:var(--danger)"></div></div></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
