<?php
require_once 'config/helpers.php';
requireAdmin();

$pageTitle = 'Kelola User';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $nama  = clean($_POST['nama']);
        $uname = clean($_POST['username']);
        $email = clean($_POST['email']);
        $pass  = $_POST['password'];
        $role  = in_array($_POST['role'], ['admin','user']) ? $_POST['role'] : 'user';
        if (strlen($pass) < 6) { setFlash('danger','Password minimal 6 karakter.'); redirect('users.php'); }
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = db()->prepare("INSERT INTO users (nama,username,email,password,role) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss', $nama, $uname, $email, $hash, $role);
        if ($stmt->execute()) { logActivity('TAMBAH_USER',$uname); setFlash('success',"User $uname berhasil ditambahkan."); }
        else setFlash('danger', 'Username/email sudah digunakan.');
        redirect('users.php');
    }
    if ($action === 'toggle') {
        $id = (int)$_POST['user_id'];
        db()->query("UPDATE users SET is_active = 1 - is_active WHERE id=$id AND id != 1");
        setFlash('success','Status user diperbarui.');
        redirect('users.php');
    }
    if ($action === 'reset_pass') {
        $id   = (int)$_POST['user_id'];
        $pass = $_POST['new_password'];
        if (strlen($pass) < 6) { setFlash('danger','Password minimal 6 karakter.'); redirect('users.php'); }
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        db()->query("UPDATE users SET password='$hash' WHERE id=$id");
        setFlash('success','Password berhasil direset.');
        redirect('users.php');
    }
}

$rows = db()->query("SELECT u.*, (SELECT COUNT(*) FROM hutang WHERE user_id=u.id) as jml_hutang FROM users u ORDER BY u.created_at DESC");

require_once 'includes/header.php';
?>

<div style="display:flex;justify-content:flex-end;margin-bottom:20px">
  <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fa-solid fa-user-plus"></i>Tambah User</button>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Daftar User</span></div>
  <div class="table-responsive">
    <table>
      <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Hutang</th><th>Status</th><th>Bergabung</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php while ($u = $rows->fetch_assoc()): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="user-avatar" style="width:32px;height:32px;font-size:12px;flex-shrink:0">
                <?php if ($u['avatar']): ?><img src="<?= $u['avatar'] ?>"><?php else: echo strtoupper(substr($u['nama'],0,1)); endif; ?>
              </div>
              <div>
                <div style="font-weight:600;font-size:.875rem"><?= h($u['nama']) ?></div>
                <div style="font-size:.75rem;color:var(--text-muted)">@<?= h($u['username']) ?></div>
              </div>
            </div>
          </td>
          <td style="font-size:.83rem"><?= h($u['email']) ?></td>
          <td>
            <?php if ($u['role']==='admin'): ?>
              <span class="badge badge-purple"><i class="fa-solid fa-shield-halved"></i>Admin</span>
            <?php else: ?>
              <span class="badge badge-info"><i class="fa-solid fa-user"></i>User</span>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-muted"><?= $u['jml_hutang'] ?> catatan</span></td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <button type="submit" class="badge badge-<?= $u['is_active']?'success':'danger' ?>" style="border:none;cursor:pointer;font-size:.72rem;padding:4px 10px" <?= $u['id']==1?'disabled':'' ?>>
                <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
              </button>
            </form>
          </td>
          <td style="font-size:.8rem"><?= formatDate($u['created_at']) ?></td>
          <td>
            <button class="btn btn-outline btn-xs" onclick="showResetPass(<?= $u['id'] ?>, '<?= h($u['username']) ?>')"><i class="fa-solid fa-key"></i></button>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal">
    <div class="modal-header"><span class="modal-title">Tambah User</span><button class="modal-close" onclick="closeModal('modalTambah')"><i class="fa-solid fa-xmark"></i></button></div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="tambah">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-row">
          <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="nama" class="form-control" required></div>
          <div class="form-group"><label>Username *</label><input type="text" name="username" class="form-control" required></div>
        </div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
        <div class="form-row">
          <div class="form-group"><label>Password *</label><input type="password" name="password" class="form-control" minlength="6" required></div>
          <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-control"><option value="user">User</option><option value="admin">Admin</option></select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i>Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Reset Pass -->
<div class="modal-overlay" id="modalResetPass">
  <div class="modal" style="max-width:400px">
    <div class="modal-header"><span class="modal-title">Reset Password — <span id="rpUsername"></span></span><button class="modal-close" onclick="closeModal('modalResetPass')"><i class="fa-solid fa-xmark"></i></button></div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="reset_pass">
        <input type="hidden" name="user_id" id="rpUserId">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group"><label>Password Baru *</label><input type="password" name="new_password" class="form-control" minlength="6" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalResetPass')">Batal</button>
        <button type="submit" class="btn btn-warning"><i class="fa-solid fa-key"></i>Reset</button>
      </div>
    </form>
  </div>
</div>

<script>
function showResetPass(id, username) {
  document.getElementById('rpUserId').value = id;
  document.getElementById('rpUsername').textContent = username;
  openModal('modalResetPass');
}
</script>

<?php require_once 'includes/footer.php'; ?>
