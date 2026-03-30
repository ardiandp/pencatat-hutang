  </main><!-- /.page-body -->
</div><!-- /.main-content -->
</div><!-- /.app-wrapper -->

<!-- Image Lightbox -->
<div class="img-lightbox-overlay" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()"><i class="fa-solid fa-xmark"></i></button>
  <img id="lightboxImg" src="" alt="">
</div>

<script>
// ─── Theme ────────────────────────────────────────
(function() {
  const stored = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', stored);
  updateThemeIcon(stored);
})();

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
  updateThemeIcon(next);
}
function updateThemeIcon(theme) {
  const icon = document.getElementById('themeIcon');
  if (!icon) return;
  icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
}

// ─── Sidebar Mobile ───────────────────────────────
function toggleSidebar() {
  const s = document.getElementById('sidebar');
  const o = document.getElementById('sidebarOverlay');
  s.classList.toggle('open');
  o.classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
}

// ─── Modal ────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
});

// ─── Lightbox ─────────────────────────────────────
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').classList.add('open');
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
}

// ─── Image Preview ────────────────────────────────
function previewImage(inputId, previewId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  if (!input || !preview) return;
  input.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { alert('Ukuran gambar maks 2MB!'); this.value = ''; return; }
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  });
}

// ─── Confirm Delete ───────────────────────────────
function confirmDelete(form) {
  if (confirm('Yakin ingin menghapus data ini? Tindakan tidak dapat dibatalkan.')) form.submit();
}

// ─── Format Rupiah Input ──────────────────────────
function formatRupiahInput(el) {
  let val = el.value.replace(/\D/g, '');
  el.value = val ? parseInt(val).toLocaleString('id-ID') : '';
}
</script>
</body>
</html>
