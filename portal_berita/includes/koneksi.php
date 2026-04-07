<?php
// includes/koneksi.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'portal_berita');
define('SITE_NAME', 'KeyNews');
define('SITE_URL', 'http://localhost/portal_berita');

$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($koneksi->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;">
        <h2 style="color:#dc3545;">⚠️ Koneksi Database Gagal</h2>
        <p>Pastikan MySQL berjalan dan konfigurasi database benar.</p>
        <code>' . $koneksi->connect_error . '</code>
    </div>');
}

$koneksi->set_charset('utf8mb4');

// Helper functions
function sanitize($data) {
    global $koneksi;
    return htmlspecialchars(strip_tags(trim($koneksi->real_escape_string($data))));
}

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return $diff . ' detik lalu';
    if ($diff < 3600) return floor($diff/60) . ' menit lalu';
    if ($diff < 86400) return floor($diff/3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff/86400) . ' hari lalu';
    return date('d M Y', $time);
}

function formatViews($n) {
    if ($n >= 1000) return round($n/1000, 1) . 'K';
    return $n;
}

function isLoggedIn() {
    return isset($_SESSION['staf_id']) && !empty($_SESSION['staf_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['staf_role']) && $_SESSION['staf_role'] === 'admin';
}

function generateCaptcha() {
    $num1 = rand(1, 15);
    $num2 = rand(1, 10);
    $_SESSION['captcha_result'] = $num1 + $num2;
    return "$num1 + $num2";
}
?>
