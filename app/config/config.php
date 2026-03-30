<?php

// Deteksi BASE_URL secara otomatis agar fleksibel di localhost maupun domain
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . $domainName . $scriptName;

// Hapus trailing slash jika ada
$baseUrl = rtrim($baseUrl, '/');

define('BASE_URL', $baseUrl);

?>
