<?php
require_once 'config/helpers.php';
requireLogin();

$pageTitle = 'Catatan Hutang';
$uid = (int)$_SESSION['user_id'];
$isAdm = isAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $debitorId = (int)$_POST['debitur_id'];
        $jenis     = in_array($_POST['jenis'], ['hutang','piutang']) ? $_POST['jenis'] : 'hutang';
        $jumlah    = (float)str_replace(['.','Rp ',' '], '', $_POST['jumlah']);
        $ket       = clean($_POST['keterangan'] ?? '');
        $tgl       = clean($_POST['tanggal_hutang']);
        $tempo     = !empty($_POST['jatuh_tempo']) ? clean($_POST['jatuh_tempo']) : null;
        $kode      = generateKode();
        $bukti     = null;
        if (!empty($_FILES['bukti']['name'])) $bukti = processImageUpload($_FILES['bukti']);

        if ($jumlah <= 0) {
            setFlash('danger', 'Jumlah hutang harus lebih dari 0.');
        } else {
            $stmt = db()->prepare(
                "INSERT INTO hutang (kode,user_id,debitur_id,jenis,jumlah,sisa,keterangan,tanggal_hutang,jatuh_tempo,bukti_gambar)
                 VALUES (?,?,?,?,?,?,?,?,?,?)"
            );
            $stmt->bind_param('siissdssss', $kode, $uid, $debitorId, $jenis, $jumlah, $jumlah, $ket, $tgl, $tempo, $bukti);
            if ($stmt->execute()) {
                logActivity('TAMBAH_HUTANG', "Kode: $kode");
                setFlash('success', "Catatan hutang $kode berhasil ditambahkan.");
            } else {
                setFlash('danger', 'Gagal menyimpan: ' . $stmt->error);
            }
        }
        redirect('hutang.php');
    }

    if ($action === 'hapus') {
        $hid = (int)$_POST['hutang_id'];
        $whereOwn = $isAdm ? "id = $hid" : "id = $hid AND user_id = $uid";
        db()->query("DELETE FROM hutang WHERE $whereOwn");
        logActivity('HAPUS_HUTANG', "ID: $hid");
        setFlash('success', 'Catatan hutang berhasil dihapus.');
        redirect('hutang.php');
    }
}

$search       = clean($_GET['q'] ?? '');
$filterJenis  = clean($_GET['jenis'] ?? '');
$filterStatus = clean($_GET['status'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where = $isAdm ? '1=1' : "h.user_id = $uid";
if ($search)       $where .= " AND (d.nama LIKE '%".db()->real_escape_string($search)."%' OR h.kode LIKE '%".db()->real_escape_string($search)."%')";
if ($filterJenis)  $where .= " AND h.jenis = '".db()->real_escape_string($filterJenis)."'";
if ($filterStatus) $where .= " AND h.status = '".db()->real_escape_string($filterStatus)."'";

$countR = db()->query("SELECT COUNT(*) as c FROM hutang h JOIN debitur d ON h.debitur_id=d.id WHERE $where");
$total  = (int)$countR->fetch_assoc()['c'];
$pages  = (int)ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

$rows = db()->query(
    "SELECT h.*, d.nama as debitur_nama, u.nama as user_nama
     FROM hutang h
     JOIN debitur d ON h.debitur_id=d.id
     JOIN users u ON h.user_id=u.id
     WHERE $where
     ORDER BY h.created_at DESC
     LIMIT $perPage OFFSET $offset"
);

$whereDebitur = $isAdm ? '1=1' : "user_id = $uid";
$debiturList  = db()->query("SELECT id, nama FROM debitur WHERE $whereDebitur ORDER BY nama");

require_once 'includes/header.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px">
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap">
    <div class="search-box">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input class="form-control" name="q" placeholder="Cari debitur / kode..." value="<?= h($search) ?>" style="width:220px">
    </div>
    <select class="form-control" name="jenis" style="width:140px" onchange="this.form.submit()">
      <option value="">Semua Jenis</option>
      <option value="piutang" <?= $filterJenis==='piutang'?'selected':'' ?>>Piutang</option>
      <option value="hutang"  <?= $filterJenis==='hutang' ?'selected':'' ?>>Hutang</option>
    </select>
    <select class="form-control" name="status" style="width:160px" onchange="this.form.submit()">
      <option value="">Semua Status</option>
      <option value="belum_lunas" <?= $filterStatus==='belum_lunas'?'selected':'' ?>>Belum Lunas</option>
      <option value="lunas"       <?= $filterStatus==='lunas'      ?'selected':'' ?>>Lunas</option>
      <option value="macet"       <?= $filterStatus==='macet'      ?'selected':'' ?>>Macet</option>
    </select>
    <button class="btn btn-outline btn-sm"><i class="fa-solid fa-filter"></i>Filter</button>
    <?php if ($search || $filterJenis || $filterStatus): ?>
      <a href="hutang.php" class="btn btn-outline btn-sm">Reset</a>
    <?php endif; ?>
  </form>
  <button class="btn btn-primary" onclick="openModal('modalTambah')">
    <i class="fa-solid fa-plus"></i>Tambah Catatan
  </button>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">Daftar Catatan Hutang</span>
    <span style="font-size:.78rem;color:var(--text-muted)"><?= $total ?> data</span>
  </div>
  <div class="table-responsive">
    <table>
      <thead><tr>
        <th>Kode</th><th>Debitur</th>
        <?php if ($isAdm): ?><th>User</th><?php endif; ?>
        <th>Jenis</th><th>Jumlah</th><th>Sisa</th><th>Tgl Hutang</th><th>Jatuh Tempo</th><th>Status</th><th>Bukti</th><th>Aksi</th>
      </tr></thead>
      <tbody>
      <?php if ($rows->num_rows === 0): ?>
        <tr><td colspan="<?= $isAdm?12:11 ?>">
          <div class="empty-state"><i class="fa-solid fa-inbox"></i><h3>Belum ada data</h3><p>Tambahkan catatan hutang pertama Anda.</p></div>
        </td></tr>
      <?php else: while ($row = $rows->fetch_assoc()):
        $pct = $row['jumlah'] > 0 ? (1 - $row['sisa'] / $row['jumlah']) * 100 : 100;
        $statusMap = ['belum_lunas'=>['warning','Belum Lunas'],'lunas'=>['success','Lunas'],'macet'=>['danger','Macet']];
        $st = $statusMap[$row['status']] ?? ['muted','–'];
      ?>
        <tr>
          <td><span style="font-family:var(--mono);font-size:.78rem;font-weight:600;color:var(--accent)"><?= h($row['kode']) ?></span></td>
          <td><strong><?= h($row['debitur_nama']) ?></strong></td>
          <?php if ($isAdm): ?><td style="font-size:.8rem;color:var(--text-muted)"><?= h($row['user_nama']) ?></td><?php endif; ?>
          <td>
            <?php if ($row['jenis']==='piutang'): ?>
              <span class="badge badge-success"><i class="fa-solid fa-arrow-down-to-line"></i> Piutang</span>
            <?php else: ?>
              <span class="badge badge-danger"><i class="fa-solid fa-arrow-up-from-line"></i> Hutang</span>
            <?php endif; ?>
          </td>
          <td style="font-family:var(--mono);font-size:.82rem"><?= formatRupiah($row['jumlah']) ?></td>
          <td>
            <div style="font-family:var(--mono);font-size:.82rem;font-weight:700;color:<?= $row['sisa']>0?'var(--danger)':'var(--success)' ?>">
              <?= formatRupiah($row['sisa']) ?>
            </div>
            <div class="progress" style="margin-top:4px;width:90px">
              <div class="progress-bar" style="width:<?= $pct ?>%;background:var(--success)"></div>
            </div>
          </td>
          <td style="font-size:.82rem"><?= formatDate($row['tanggal_hutang']) ?></td>
          <td style="font-size:.82rem">
            <?php if ($row['jatuh_tempo']): ?>
              <?php $dLeft = (int)floor((strtotime($row['jatuh_tempo'])-time())/86400); ?>
              <div><?= formatDate($row['jatuh_tempo']) ?></div>
              <?php if ($dLeft >= 0 && $row['status']==='belum_lunas'): ?>
                <span class="badge badge-<?= $dLeft<=3?'danger':'warning' ?>" style="font-size:.68rem"><?= $dLeft ?>h lagi</span>
              <?php endif; ?>
            <?php else: ?><span style="color:var(--text-muted)">—</span><?php endif; ?>
          </td>
          <td><span class="badge badge-<?= $st[0] ?>"><?= $st[1] ?></span></td>
         <td>
  <?php if ($row['bukti_gambar']): ?>
    <button type="button" class="btn btn-outline btn-xs" onclick="openLightbox('<?= h($row['bukti_gambar']) ?>')">
      <i class="fa-solid fa-image"></i> Lihat
    </button>
  <?php else: ?>
    <span style="font-size:.75rem;color:var(--text-muted)">—</span>
  <?php endif; ?>
</td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="detail_hutang.php?id=<?= $row['id'] ?>" class="btn btn-outline btn-xs" title="Detail"><i class="fa-solid fa-eye"></i></a>
              <a href="pembayaran.php?hutang_id=<?= $row['id'] ?>" class="btn btn-success btn-xs" title="Catat Bayar"><i class="fa-solid fa-money-bill-wave"></i></a>
              <form method="POST" onsubmit="confirmDelete(this);return false;" style="display:inline">
                <input type="hidden" name="action" value="hapus">
                <input type="hidden" name="hutang_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <button type="submit" class="btn btn-danger btn-xs" title="Hapus"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pages > 1): ?>
  <div style="padding:14px 16px;display:flex;justify-content:flex-end">
    <div class="pagination">
      <?php for ($p=1; $p<=$pages; $p++):
        $q = http_build_query(array_merge($_GET, ['page'=>$p])); ?>
        <a href="?<?= $q ?>" class="page-btn <?= $p===$page?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title"><i class="fa-solid fa-plus" style="color:var(--accent);margin-right:8px"></i>Tambah Catatan Hutang</span>
      <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="action" value="tambah">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-row">
          <div class="form-group">
            <label>Debitur <span style="color:var(--danger)">*</span></label>
            <select name="debitur_id" class="form-control" required>
              <option value="">-- Pilih Debitur --</option>
              <?php $debiturList->data_seek(0); while ($d = $debiturList->fetch_assoc()): ?>
                <option value="<?= $d['id'] ?>"><?= h($d['nama']) ?></option>
              <?php endwhile; ?>
            </select>
            <div class="form-hint">Belum ada? <a href="debitur.php">Tambah debitur dulu</a></div>
          </div>
          <div class="form-group">
            <label>Jenis <span style="color:var(--danger)">*</span></label>
            <select name="jenis" class="form-control" required>
              <option value="piutang">Piutang (orang berhutang ke saya)</option>
              <option value="hutang">Hutang (saya berhutang ke orang lain)</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Jumlah <span style="color:var(--danger)">*</span></label>
            <div style="position:relative">
              <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.85rem">Rp</span>
              <input type="number" name="jumlah" class="form-control" style="padding-left:36px" placeholder="0" min="1" step="any" required>
            </div>
          </div>
          <div class="form-group">
            <label>Tanggal Hutang <span style="color:var(--danger)">*</span></label>
            <input type="date" name="tanggal_hutang" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label>Jatuh Tempo</label>
            <input type="date" name="jatuh_tempo" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label>Keterangan</label>
          <textarea name="keterangan" class="form-control" placeholder="Keterangan hutang (opsional)..."></textarea>
        </div>
        <div class="form-group">
          <label>Bukti / Dokumentasi <span style="color:var(--text-muted);font-weight:400">(maks 2MB, JPG/PNG)</span></label>
          <input type="file" name="bukti" class="form-control" accept="image/*" onchange="previewUpload(this)">
          <img id="buktiPreview" style="display:none;margin-top:8px;max-height:120px;border-radius:8px;border:2px solid var(--border)">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i>Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function previewUpload(input) {
  const preview = document.getElementById('buktiPreview');
  if (input.files && input.files[0]) {
    if (input.files[0].size > 2*1024*1024) { alert('Ukuran maks 2MB!'); input.value=''; return; }
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display='block'; };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php require_once 'includes/footer.php'; ?>
