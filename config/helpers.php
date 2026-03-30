<?php
// config/helpers.php

require_once __DIR__ . '/database.php';

// ─── Session ───────────────────────────────────────────────
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params(SESSION_LIFETIME);
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        redirect('dashboard.php');
    }
}

function currentUser(): array {
    if (!isLoggedIn()) return [];
    $id = (int)$_SESSION['user_id'];
    $stmt = db()->prepare("SELECT id, nama, username, email, role, avatar FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?? [];
}

// ─── Redirect & Flash ──────────────────────────────────────
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function setFlash(string $type, string $message): void {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    startSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ─── Format ────────────────────────────────────────────────
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate(string $date): string {
    if (!$date || $date === '0000-00-00') return '-';
    return date('d M Y', strtotime($date));
}

function formatDateTime(string $dt): string {
    if (!$dt) return '-';
    return date('d M Y H:i', strtotime($dt));
}

function timeAgo(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff/60) . ' menit lalu';
    if ($diff < 86400) return floor($diff/3600) . ' jam lalu';
    return floor($diff/86400) . ' hari lalu';
}

// ─── CSRF ──────────────────────────────────────────────────
function csrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    startSession();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        setFlash('danger', 'Token keamanan tidak valid. Silakan coba lagi.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'dashboard.php'));
        exit;
    }
}

// ─── Image Base64 ─────────────────────────────────────────
function processImageUpload(array $file): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_FILE_SIZE) {
        setFlash('warning', 'Ukuran gambar maksimal 2MB.');
        return null;
    }
    $type = mime_content_type($file['tmp_name']);
    if (!in_array($type, ALLOWED_TYPES)) {
        setFlash('warning', 'Format gambar tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.');
        return null;
    }
    $data = file_get_contents($file['tmp_name']);
    return 'data:' . $type . ';base64,' . base64_encode($data);
}

function generateKode(string $prefix = 'HT'): string {
    $year = date('Y');
    $result = db()->query("SELECT COUNT(*) as total FROM hutang WHERE YEAR(created_at) = $year");
    $count = ($result->fetch_assoc()['total'] ?? 0) + 1;
    return $prefix . '-' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
}

// ─── Log ──────────────────────────────────────────────────
function logActivity(string $aksi, string $keterangan = ''): void {
    startSession();
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = db()->prepare("INSERT INTO log_aktivitas (user_id, aksi, keterangan, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $uid, $aksi, $keterangan, $ip);
    $stmt->execute();
}

// ─── Sanitize ─────────────────────────────────────────────
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function clean(string $str): string {
    return trim(strip_tags($str));
}

// ─── Pagination ──────────────────────────────────────────
function paginate(string $table, string $where, int $page, int $perPage = 15): array {
    $offset = ($page - 1) * $perPage;
    $total  = db()->query("SELECT COUNT(*) as c FROM $table WHERE $where")->fetch_assoc()['c'];
    $pages  = (int)ceil($total / $perPage);
    return ['total' => $total, 'pages' => $pages, 'offset' => $offset, 'perPage' => $perPage];
}
