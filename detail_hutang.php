<?php
require_once 'config/helpers.php';
requireLogin();

$uid   = (int)$_SESSION['user_id'];
$isAdm = isAdmin();
$hid   = (int)($_GET['id'] ?? 0);

$whereOwn = $isAdm ? "h.id = $hid" : "h.id = $hid AND h.user_id = $uid";
$hutang = db()->query("SELECT h.*, d.nama as debitur_nama, d.telepon, d.alamat, u.nama as user_nama FROM hutang h JOIN debitur d ON h.debitur_id=d.id JOIN users u ON h.user_id=u.id WHERE $whereOwn")->fetch_assoc();
if (!$hutang) { setFlash('danger', 'Data tidak ditemukan.'); redirect('hutang.php'); }

$pageTitle  = 'Detail Hutang';
$breadcrumb = ['Catatan Hutang' => 'hutang.php', $hutang['kode'] => null];

// ─── Riwayat Pembayaran ──────────────────────────────
$riwayat = db()->query("SELECT p.*, u.nama as petugas FROM pembayaran p JOIN users u ON p.user_id=u.id WHERE p.hutang_id = $hid ORDER BY p.tanggal_bayar DESC");

// ─── Update Status ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if ($_POST['action'] === 'update_status') {
        $ns = in_array($_POST['status'], ['belum_lunas','lunas','macet']) ? $_POST['status'] : 'belum_lunas';
        db()->query("UPDATE hutang SET status='$ns' WHERE id=$hid");
        logActivity('UPDATE_STATUS_HUTANG', "ID:$hid Status:$ns");
        setFlash('success', 'Status berhasil diperbarui.');
        redirect("detail_hutang.php?id=$hid");
    }
}

require_once 'includes/header.php';
?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

  <!-- Kiri: Info + Riwayat -->
  <div>
    <!-- Info Card -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-header">
        <div>
          <span class="card-title"><?= h($hutang['kode']) ?></span>
          <?php
          $st = ['belum_lunas'=>['warning','Belum Lunas'],'lunas'=>['success','Lunas'],'macet'=>['danger','Macet']];
          $s  = $st[$hutang['status']] ?? ['muted','–'];
          ?>
          <span class="badge badge-<?= $s[0] ?>" style="margin-left:8px"><?= $s[1] ?></span>
        </div>
        <div style="display:flex;gap:6px">
          <a href="pembayaran.php?hutang_id=<?= $hid ?>" class="btn btn-success btn-sm"><i class="fa-solid fa-money-bill-wave"></i>Tambah Bayar</a>
        </div>
      </div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div>
            <div style="font-size:.74rem;color:var(--text-muted);margin-bottom:3px">Debitur</div>
            <div style="font-weight:700"><?= h($hutang['debitur_nama']) ?></div>
            <?php if ($hutang['telepon']): ?><div style="font-size:.8rem;color:var(--text-muted)"><?= h($hutang['telepon']) ?></div><?php endif; ?>
          </div>
          <div>
            <div style="font-size:.74rem;color:var(--text-muted);margin-bottom:3px">Jenis</div>
            <?php if ($hutang['jenis']==='piutang'): ?>
              <span class="badge badge-success"><i class="fa-solid fa-arrow-down"></i>Piutang</span>
            <?php else: ?>
              <span class="badge badge-danger"><i class="fa-solid fa-arrow-up"></i>Hutang</span>
            <?php endif; ?>
          </div>
          <div>
            <div style="font-size:.74rem;color:var(--text-muted);margin-bottom:3px">Jumlah Awal</div>
            <div style="font-family:var(--mono);font-weight:700;font-size:1rem"><?= formatRupiah($hutang['jumlah']) ?></div>
          </div>
          <div>
            <div style="font-size:.74rem;color:var(--text-muted);margin-bottom:3px">Sisa Hutang</div>
            <div style="font-family:var(--mono);font-weight:800;font-size:1rem;color:<?= $hutang['sisa']>0?'var(--danger)':'var(--success)' ?>"><?= formatRupiah($hutang['sisa']) ?></div>
          </div>
          <div>
            <div style="font-size:.74rem;color:var(--text-muted);margin-bottom:3px">Tgl Hutang</div>
            <div><?= formatDate($hutang['tanggal_hutang']) ?></div>
          </div>
          <div>
            <div style="font-size:.74rem;color:var(--text-muted);margin-bottom:3px">Jatuh Tempo</div>
            <div><?= $hutang['jatuh_tempo'] ? formatDate($hutang['jatuh_tempo']) : '—' ?></div>
          </div>
          <?php if ($isAdm): ?>
          <div>
            <div style="font-size:.74rem;color:var(--text-muted);margin-bottom:3px">Dicatat oleh</div>
            <div><?= h($hutang['user_nama']) ?></div>
          </div>
          <?php endif; ?>
          <div style="grid-column:span 2">
            <div style="font-size:.74rem;color:var(--text-muted);margin-bottom:3px">Keterangan</div>
            <div style="font-size:.875rem"><?= $hutang['keterangan'] ? h($hutang['keterangan']) : '—' ?></div>
          </div>
        </div>

        <!-- Progress -->
        <?php $pct = $hutang['jumlah'] > 0 ? (1 - $hutang['sisa']/$hutang['jumlah'])*100 : 100; ?>
        <div style="margin-top:16px">
          <div style="display:flex;justify-content:space-between;font-size:.78rem;color:var(--text-muted);margin-bottom:6px">
            <span>Progres Pelunasan</span><span><?= number_format($pct,1) ?>%</span>
          </div>
          <div class="progress" style="height:8px"><div class="progress-bar" style="width:<?= $pct ?>%;background:linear-gradient(90deg,var(--accent),var(--success))"></div></div>
          <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--text-muted);margin-top:4px">
            <span>Terbayar: <?= formatRupiah($hutang['jumlah'] - $hutang['sisa']) ?></span>
            <span>Sisa: <?= formatRupiah($hutang['sisa']) ?></span>
          </div>
        </div>
      </div>

      <?php if ($hutang['bukti_gambar']): ?>
      <div style="padding:0 20px 20px">
        <div style="font-size:.8rem;font-weight:600;margin-bottom:8px;color:var(--text-muted)">BUKTI DOKUMEN</div>
        <img src="<?= $hutang['bukti_gambar'] ?>" class="img-preview" onclick="openLightbox(this.src)" style="width:120px;height:90px">
      </div>
      <?php endif; ?>
    </div>

    <!-- Riwayat Pembayaran -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--accent);margin-right:8px"></i>Riwayat Pembayaran</span>
        <span style="font-size:.78rem;color:var(--text-muted)"><?= $riwayat->num_rows ?> transaksi</span>
      </div>
      <?php if ($riwayat->num_rows === 0): ?>
        <div class="empty-state"><i class="fa-solid fa-receipt"></i><h3>Belum ada pembayaran</h3><p>Belum ada riwayat pembayaran untuk hutang ini.</p></div>
      <?php else: ?>
      <div class="table-responsive">
        <table>
          <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>Keterangan</th><th>Petugas</th><th>Bukti</th></tr></thead>
          <tbody>
          <?php while ($p = $riwayat->fetch_assoc()): ?>
            <tr>
              <td style="font-size:.83rem"><?= formatDate($p['tanggal_bayar']) ?><br><span style="font-size:.72rem;color:var(--text-muted)"><?= timeAgo($p['created_at']) ?></span></td>
              <td style="font-family:var(--mono);font-weight:700;color:var(--success)"><?= formatRupiah($p['jumlah_bayar']) ?></td>
              <td><span class="badge badge-info"><?= ucfirst($p['metode']) ?></span></td>
              <td style="font-size:.82rem"><?= $p['keterangan'] ? h($p['keterangan']) : '—' ?></td>
              <td style="font-size:.8rem"><?= h($p['petugas']) ?></td>
              <td>
                <?php if ($p['bukti_gambar']): ?>
                  <img src="<?= $p['bukti_gambar'] ?>" class="img-preview" onclick="openLightbox(this.src)" style="width:50px;height:40px">
                <?php else: ?><span style="color:var(--text-muted);font-size:.75rem">—</span><?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Kanan: Aksi -->
  <div class="card">
    <div class="card-header"><span class="card-title">Ubah Status</span></div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
          <label>Status Hutang</label>
          <select name="status" class="form-control">
            <option value="belum_lunas" <?= $hutang['status']==='belum_lunas'?'selected':'' ?>>Belum Lunas</option>
            <option value="lunas"       <?= $hutang['status']==='lunas'      ?'selected':'' ?>>Lunas</option>
            <option value="macet"       <?= $hutang['status']==='macet'      ?'selected':'' ?>>Macet</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%"><i class="fa-solid fa-save"></i>Simpan Status</button>
      </form>
    </div>
    <div style="padding:0 16px 16px">
      <a href="pembayaran.php?hutang_id=<?= $hid ?>" class="btn btn-success" style="width:100%;justify-content:center;margin-bottom:8px">
        <i class="fa-solid fa-money-bill-wave"></i>Catat Pembayaran
      </a>
      <a href="hutang.php" class="btn btn-outline" style="width:100%;justify-content:center"><i class="fa-solid fa-arrow-left"></i>Kembali</a>
    </div>
  </div>

</div>

<?php require_once 'includes/footer.php'; ?>
