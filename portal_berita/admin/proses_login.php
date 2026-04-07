<?php
// admin/proses_login.php
// Proses login ditangani langsung di login.php
// File ini sebagai alias agar kompatibel dengan requirement
session_start();
require_once '../includes/koneksi.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>
