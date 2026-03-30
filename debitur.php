<?php
require_once 'config/helpers.php';
requireLogin();

$pageTitle = 'Debitur';
$uid   = (int)$_SESSION['user_id'];
$isAdm = isAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $nama  = clean($_POST['nama']);
        $telp  = clean($_POST['telepon'] ?? '');
        $alamat= clean($_POST['alamat'] ?? '');
        $cat   = clean($_POST['catatan'] ?? '');
        if (empty($nama)) { setFlash('danger','Nama wajib diisi.'); redirect('debitur.php'); }
        $stmt = db()->prepare("INSERT INTO debitur (user_id,nama,telepon,alamat,catatan) VALUES (?,?,?,?,?)");
        $stmt->bind_param('issss', $uid, $nama, $telp, $alamat, $cat);
        $stmt->execute();
        logActivity('TAMBAH_DEBITUR', $nama);
        setFlash('success',"Debitur $nama berhasil ditambahkan.");
        redirect('debitur.php');
    }
    if ($action === 'hapus') {
        $did = (int)$_POST['debitur_id'];
        $w = $isAdm ? "id=$did" : "id=$did AND user_id=$uid";
        db()->query("DELETE FROM debitur WHERE $w");
        setFlash('success','Debitur berhasil dihapus.');
        redirect('debitur.php');
    }
}

$search = clean($_GET['q'] ?? '');
$where  = $isAdm ? '1=1' : "d.user_id=$uid";
if ($search) $where .= " AND d.nama LIKE '%".db()->real_escape_string($search)."%'";
$rows = db()->query("SELECT d.*, (SELECT COUNT(*) FROM hutang WHERE debitur_id=d.id) as jml_hutang, (SELECT COALESCE(SUM(sisa),0) FROM hutang WHERE debitur_id=d.id AND status='belum_lunas') as total_sisa FROM debitur d WHERE $where ORDER BY d.nama");

require_once 'includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <form method="GET" style="display:flex;gap:8px">
    <div class="search-box"><i class="fa-solid fa-magnifying-glass"></i><input class="form-control" name="q" placeholder="Cari debitur..." value="<?= h($search) ?>"></div>
    <button class="btn btn-outline btn-sm"><i class="fa-solid fa-filter"></i>Cari</button>
  </form>
  <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fa-solid fa-plus"></i>Tambah Debitur</button>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Daftar Debitur</span></div>
  <div class="table-responsive">
    <table>
      <thead><tr><th>#</th><th>Nama</th><th>Telepon</th><th>Alamat</th><th>Total Hutang</th><th>Sisa Belum Bayar</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if ($rows->num_rows === 0): ?>
        <tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-users"></i><h3>Belum ada debitur</h3></div></td></tr>
      <?php else: $no=1; while ($r = $rows->fetch_assoc()): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:.8rem"><?= $no++ ?></td>
          <td><strong><?= h($r['nama']) ?></strong><?= $r['catatan'] ? '<div style="font-size:.73rem;color:var(--text-muted)">'.h($r['catatan']).'</div>' : '' ?></td>
          <td style="font-size:.83rem"><?= $r['telepon'] ? '<a href="tel:'.$r['telepon'].'">'.h($r['telepon']).'</a>' : '—' ?></td>
          <td style="font-size:.82rem"><?= $r['alamat'] ? h(substr($r['alamat'],0,50)).(strlen($r['alamat'])>50?'…':'') : '—' ?></td>
          <td><span class="badge badge-muted"><?= $r['jml_hutang'] ?> transaksi</span></td>
          <td style="font-family:var(--mono);font-size:.85rem;font-weight:700;color:<?= $r['total_sisa']>0?'var(--danger)':'var(--success)' ?>"><?= formatRupiah($r['total_sisa']) ?></td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="hutang.php" class="btn btn-outline btn-xs"><i class="fa-solid fa-eye"></i></a>
              <form method="POST" onsubmit="confirmDelete(this);return false">
                <input type="hidden" name="action" value="hapus">
                <input type="hidden" name="debitur_id" value="<?= $r['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Tambah Debitur</span>
      <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="tambah">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group">
          <label>Nama <span style="color:var(--danger)">*</span></label>
          <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Telepon</label>
            <input type="text" name="telepon" class="form-control" placeholder="08xx">
          </div>
        </div>
        <div class="form-group">
          <label>Alamat</label>
          <textarea name="alamat" class="form-control" placeholder="Alamat..."></textarea>
        </div>
        <div class="form-group">
          <label>Catatan</label>
          <input type="text" name="catatan" class="form-control" placeholder="Misal: Teman, Rekan bisnis...">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i>Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
