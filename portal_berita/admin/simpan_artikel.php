<?php
// admin/simpan_artikel.php
// File ini adalah alias proses — redirect ke tambah_artikel.php
// Jika diakses langsung, arahkan ke form
session_start();
require_once '../includes/koneksi.php';
requireLogin();
header('Location: tambah_artikel.php');
exit;
?>
