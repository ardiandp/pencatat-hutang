<?php
// config/config.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hutang_db');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'CatatHutang');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/hutang_app');

// Session config
define('SESSION_NAME', 'hutang_session');
define('SESSION_LIFETIME', 7200); // 2 jam

// Upload config (base64 max size ~2MB)
define('MAX_FILE_SIZE', 2 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (ubah ke 0 di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
