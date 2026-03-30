<?php
require_once 'config/helpers.php';
requireLogin();

$pageTitle = 'Pembayaran';
$uid   = (int)$_SESSION['user_id'];
$isAdm = isAdmin();

// ─── Handle POST ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $hid     = (int)$_POST['hutang_id'];
    $bayar   = (float)str_replace(['.','Rp ',' '], '', $_POST['jumlah_bayar']);
    $tgl     = clean($_POST['tanggal_bayar']);
    $metode  = in_array($_POST['metode'], ['tunai','transfer','qris','lainnya']) ? $_POST['metode'] : 'tunai';
    $ket     = clean($_POST['keterangan'] ?? '');
    $bukti   = null;
    if (!empty($_FILES['bukti']['name'])) $bukti = processImageUpload($_FILES['bukti']);

    // Cek hutang
    $whereOwn = $isAdm ? "id = $hid" : "id = $hid AND user_id = $uid";
    $hutang = db()->query("SELECT * FROM hutang WHERE $whereOwn AND status != 'lunas'")->fetch_assoc();

    if (!$hutang) {
        setFlash('danger', 'Hutang tidak ditemukan atau sudah lunas.');
    } elseif ($bayar <= 0) {
        setFlash('danger', 'Jumlah bayar harus lebih dari 0.');
    } elseif ($bayar > $hutang['sisa']) {
        setFlash('danger', 'Jumlah bayar melebihi sisa hutang ('.formatRupiah($hutang['sisa']).').');
    } else {
        $sisa_baru = $hutang['sisa'] - $bayar;
        $status_baru = $sisa_baru <= 0 ? 'lunas' : 'belum_lunas';

        $stmt = db()->prepare("INSERT INTO pembayaran (hutang_id, user_id, jumlah_bayar, tanggal_bayar, metode, keterangan, bukti_gambar) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('iidsss' . ($bukti ? 's' : 's'), $hid, $uid, $bayar, $tgl, $metode, $ket, $bukti);

        // Simpler approach
        $b64 = $bukti ?? null;
        $stmt2 = db()->prepare("INSERT INTO pembayaran (hutang_id,user_id,jumlah_bayar,tanggal_bayar,metode,keterangan,bukti_gambar) VALUES (?,?,?,?,?,?,?)");
        $stmt2->bind_param('iidssss', $hid, $uid, $bayar, $tgl, $metode, $ket, $b64);
        $stmt2->execute();

        // Update sisa
        db()->query("UPDATE hutang SET sisa=$sisa_baru, status='$status_baru' WHERE id=$hid");
        logActivity('PEMBAYARAN', "Hutang#$hid Bayar:$bayar Status:$status_baru");
        setFlash('success', 'Pembayaran ' . formatRupiah($bayar) . ' berhasil dicatat.' . ($status_baru==='lunas' ? ' Hutang telah LUNAS! 🎉' : ''));
        $redir = isset($_GET['hutang_id']) ? "detail_hutang.php?id=$hid" : 'pembayaran.php';
        redirect($redir);
    }
}

// ─── Filter hutang for form ───────────────────────────
$whereH = $isAdm ? "status != 'lunas'" : "user_id=$uid AND status != 'lunas'";
$hutangList = db()->query("SELECT h.id, h.kode, h.sisa, d.nama FROM hutang h JOIN debitur d ON h.debitur_id=d.id WHERE $whereH ORDER BY d.nama");

// ─── List Pembayaran ──────────────────────────────────
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$whereP  = $isAdm ? '1=1' : "h.user_id = $uid";
$total   = (int)db()->query("SELECT COUNT(*) as c FROM pembayaran p JOIN hutang h ON p.hutang_id=h.id WHERE $whereP")->fetch_assoc()['c'];
$pages   = (int)ceil($total / $perPage);
$offset  = ($page - 1) * $perPage;

$rows = db()->query("SELECT p.*, h.kode, h.jenis, d.nama as debitur_nama, u.nama as petugas FROM pembayaran p JOIN hutang h ON p.hutang_id=h.id JOIN debitur d ON h.debitur_id=d.id JOIN users u ON p.user_id=u.id WHERE $whereP ORDER BY p.tanggal_bayar DESC, p.created_at DESC LIMIT $perPage OFFSET $offset");

// Pre-fill hutang_id from GET
$prefillHid = (int)($_GET['hutang_id'] ?? 0);

require_once 'includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div></div>
  <button class="btn btn-primary" onclick="openModal('modalBayar')"><i class="fa-solid fa-plus"></i>Catat Pembayaran</button>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-money-bill-transfer" style="color:var(--success);margin-right:8px"></i>Riwayat Pembayaran</span>
    <span style="font-size:.78rem;color:var(--text-muted)"><?= $total ?> data</span>
  </div>
  <div class="table-responsive">
    <table>
      <thead><tr>
        <th>Tanggal</th><th>Kode Hutang</th><th>Debitur</th><th>Jenis</th>
        <th>Jumlah Bayar</th><th>Metode</th><th>Keterangan</th>
        <?php if ($isAdm): ?><th>Petugas</th><?php endif; ?>
        <th>Bukti</th>
      </tr></thead>
      <tbody>
      <?php if ($rows->num_rows === 0): ?>
        <tr><td colspan="9"><div class="empty-state"><i class="fa-solid fa-receipt"></i><h3>Belum ada riwayat</h3><p>Belum ada pembayaran yang tercatat.</p></div></td></tr>
      <?php else: while ($row = $rows->fetch_assoc()): ?>
        <tr>
          <td>
            <div style="font-size:.83rem"><?= formatDate($row['tanggal_bayar']) ?></div>
            <div style="font-size:.72rem;color:var(--text-muted)"><?= timeAgo($row['created_at']) ?></div>
          </td>
          <td><a href="detail_hutang.php?id=<?= $row['hutang_id'] ?>" style="font-family:var(--mono);font-size:.78rem;font-weight:600;color:var(--accent)"><?= h($row['kode']) ?></a></td>
          <td><strong><?= h($row['debitur_nama']) ?></strong></td>
          <td><?= $row['jenis']==='piutang' ? '<span class="badge badge-success">Piutang</span>' : '<span class="badge badge-danger">Hutang</span>' ?></td>
          <td style="font-family:var(--mono);font-weight:700;color:var(--success);font-size:.88rem"><?= formatRupiah($row['jumlah_bayar']) ?></td>
          <td><span class="badge badge-info"><?= ucfirst($row['metode']) ?></span></td>
          <td style="font-size:.82rem"><?= $row['keterangan'] ? h($row['keterangan']) : '—' ?></td>
          <?php if ($isAdm): ?><td style="font-size:.8rem"><?= h($row['petugas']) ?></td><?php endif; ?>
          <td>
            <?php if ($row['bukti_gambar']): ?>
              <img src="<?= $row['bukti_gambar'] ?>" class="img-preview" onclick="openLightbox(this.src)" style="width:50px;height:40px">
            <?php else: ?><span style="color:var(--text-muted);font-size:.75rem">—</span><?php endif; ?>
          </td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pages > 1): ?>
  <div style="padding:14px 16px;display:flex;justify-content:flex-end">
    <div class="pagination">
      <?php for ($p=1; $p<=$pages; $p++): $q=http_build_query(array_merge($_GET,['page'=>$p])); ?>
        <a href="?<?= $q ?>" class="page-btn <?= $p===$page?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Bayar -->
<div class="modal-overlay <?= $prefillHid ? 'open' : '' ?>" id="modalBayar">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fa-solid fa-money-bill-wave" style="color:var(--success);margin-right:8px"></i>Catat Pembayaran</span>
      <button class="modal-close" onclick="closeModal('modalBayar')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
          <label>Pilih Hutang <span style="color:var(--danger)">*</span></label>
          <select name="hutang_id" class="form-control" required>
            <option value="">-- Pilih Hutang --</option>
            <?php $hutangList->data_seek(0); while ($h = $hutangList->fetch_assoc()): ?>
              <option value="<?= $h['id'] ?>" <?= $prefillHid===$h['id']?'selected':'' ?>>
                [<?= h($h['kode']) ?>] <?= h($h['nama']) ?> — Sisa: <?= formatRupiah($h['sisa']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Jumlah Bayar <span style="color:var(--danger)">*</span></label>
            <div style="position:relative"><span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.85rem">Rp</span>
            <input type="number" name="jumlah_bayar" class="form-control" style="padding-left:36px" placeholder="0" min="1" required></div>
          </div>
          <div class="form-group">
            <label>Tanggal Bayar <span style="color:var(--danger)">*</span></label>
            <input type="date" name="tanggal_bayar" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label>Metode Pembayaran</label>
          <select name="metode" class="form-control">
            <option value="tunai">💵 Tunai</option>
            <option value="transfer">🏦 Transfer Bank</option>
            <option value="qris">📱 QRIS</option>
            <option value="lainnya">Lainnya</option>
          </select>
        </div>
        <div class="form-group">
          <label>Keterangan</label>
          <textarea name="keterangan" class="form-control" placeholder="Keterangan pembayaran..."></textarea>
        </div>
        <div class="form-group">
          <label>Bukti Pembayaran (maks 2MB)</label>
          <input type="file" name="bukti" class="form-control" accept="image/*" onchange="previewBayar(this)">
          <img id="bayarPreview" style="display:none;margin-top:8px;max-height:120px;border-radius:8px;border:2px solid var(--border)">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalBayar')">Batal</button>
        <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i>Catat Pembayaran</button>
      </div>
    </form>
  </div>
</div>

<script>
function previewBayar(input) {
  const p = document.getElementById('bayarPreview');
  if (input.files && input.files[0]) {
    if (input.files[0].size > 2*1024*1024) { alert('Ukuran maks 2MB!'); input.value=''; return; }
    const r = new FileReader();
    r.onload = e => { p.src = e.target.result; p.style.display='block'; };
    r.readAsDataURL(input.files[0]);
  }
}
</script>

<?php require_once 'includes/footer.php'; ?>
