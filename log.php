<?php
// log.php
require_once 'config/helpers.php';
requireAdmin();
$pageTitle = 'Log Aktivitas';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;
$total   = (int)db()->query("SELECT COUNT(*) as c FROM log_aktivitas")->fetch_assoc()['c'];
$pages   = (int)ceil($total / $perPage);
$offset  = ($page - 1) * $perPage;
$rows = db()->query("SELECT l.*, u.nama, u.username FROM log_aktivitas l LEFT JOIN users u ON l.user_id=u.id ORDER BY l.created_at DESC LIMIT $perPage OFFSET $offset");
require_once 'includes/header.php';
?>
<div class="card">
  <div class="card-header"><span class="card-title"><i class="fa-solid fa-scroll" style="color:var(--accent);margin-right:8px"></i>Log Aktivitas Sistem</span><span style="font-size:.78rem;color:var(--text-muted)"><?= $total ?> entri</span></div>
  <div class="table-responsive">
    <table>
      <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Keterangan</th><th>IP</th></tr></thead>
      <tbody>
      <?php if ($rows->num_rows === 0): ?>
        <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-scroll"></i><p>Belum ada log</p></div></td></tr>
      <?php else: while ($r = $rows->fetch_assoc()): ?>
        <tr>
          <td style="font-size:.8rem;white-space:nowrap"><?= formatDateTime($r['created_at']) ?></td>
          <td style="font-size:.83rem"><?= $r['nama'] ? h($r['nama']).'<br><span style="font-size:.72rem;color:var(--text-muted)">@'.h($r['username']).'</span>' : '<span style="color:var(--text-muted)">—</span>' ?></td>
          <td><span class="badge badge-purple" style="font-size:.72rem"><?= h($r['aksi']) ?></span></td>
          <td style="font-size:.82rem"><?= h($r['keterangan'] ?? '') ?></td>
          <td style="font-size:.75rem;color:var(--text-muted);font-family:var(--mono)"><?= h($r['ip_address'] ?? '') ?></td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pages > 1): ?>
  <div style="padding:14px 16px;display:flex;justify-content:flex-end">
    <div class="pagination">
      <?php for ($p=1; $p<=$pages; $p++): ?>
        <a href="?page=<?= $p ?>" class="page-btn <?= $p===$page?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
