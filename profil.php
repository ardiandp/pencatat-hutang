<?php
// profil.php
require_once 'config/helpers.php';
requireLogin();
$pageTitle = 'Profil Saya';
$uid = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $nama  = clean($_POST['nama']);
        $email = clean($_POST['email']);
        $avatar = null;
        if (!empty($_FILES['avatar']['name'])) $avatar = processImageUpload($_FILES['avatar']);
        $stmt = db()->prepare("UPDATE users SET nama=?, email=?" . ($avatar ? ", avatar=?" : "") . " WHERE id=?");
        if ($avatar) $stmt->bind_param('sssi', $nama, $email, $avatar, $uid);
        else         $stmt->bind_param('ssi', $nama, $email, $uid);
        $stmt->execute();
        $_SESSION['nama'] = $nama;
        setFlash('success','Profil berhasil diperbarui.');
        redirect('profil.php');
    }
    if ($action === 'change_pass') {
        $old = $_POST['old_pass']; $new = $_POST['new_pass'];
        $user = db()->query("SELECT password FROM users WHERE id=$uid")->fetch_assoc();
        if (!password_verify($old, $user['password'])) { setFlash('danger','Password lama salah.'); redirect('profil.php'); }
        if (strlen($new) < 6) { setFlash('danger','Password baru minimal 6 karakter.'); redirect('profil.php'); }
        $hash = password_hash($new, PASSWORD_BCRYPT);
        db()->query("UPDATE users SET password='$hash' WHERE id=$uid");
        setFlash('success','Password berhasil diubah.');
        redirect('profil.php');
    }
}

$me = db()->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
require_once 'includes/header.php';
?>
<div style="max-width:600px;margin:0 auto">
  <div class="card" style="margin-bottom:20px">
    <div class="card-header"><span class="card-title">Informasi Profil</span></div>
    <form method="POST" enctype="multipart/form-data">
      <div class="card-body">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
          <div class="user-avatar" style="width:64px;height:64px;font-size:22px" id="avatarWrap">
            <?php if ($me['avatar']): ?><img src="<?= $me['avatar'] ?>" id="avatarPreview"><?php else: echo strtoupper(substr($me['nama'],0,1)); endif; ?>
          </div>
          <div>
            <label class="btn btn-outline btn-sm" style="cursor:pointer;display:inline-flex;gap:6px;align-items:center"><i class="fa-solid fa-camera"></i>Ganti Foto<input type="file" name="avatar" style="display:none" accept="image/*" onchange="previewAvatar(this)"></label>
            <div class="form-hint">JPG/PNG maks 2MB</div>
          </div>
        </div>
        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" class="form-control" value="<?= h($me['nama']) ?>" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= h($me['email']) ?>" required></div>
        <div class="form-group"><label>Username</label><input type="text" class="form-control" value="<?= h($me['username']) ?>" disabled></div>
        <div class="form-group"><label>Role</label><input type="text" class="form-control" value="<?= ucfirst($me['role']) ?>" disabled></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i>Simpan Perubahan</button></div>
    </form>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Ubah Password</span></div>
    <form method="POST">
      <div class="card-body">
        <input type="hidden" name="action" value="change_pass">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group"><label>Password Lama</label><input type="password" name="old_pass" class="form-control" required></div>
        <div class="form-group"><label>Password Baru (min 6 karakter)</label><input type="password" name="new_pass" class="form-control" minlength="6" required></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-warning"><i class="fa-solid fa-lock"></i>Ubah Password</button></div>
    </form>
  </div>
</div>
<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const r = new FileReader();
    r.onload = e => {
      const wrap = document.getElementById('avatarWrap');
      wrap.innerHTML = '<img src="'+e.target.result+'" style="width:100%;height:100%;object-fit:cover;border-radius:50%">';
    };
    r.readAsDataURL(input.files[0]);
  }
}
</script>
<?php require_once 'includes/footer.php'; ?>
