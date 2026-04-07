<?php
// admin/simpan_staf.php
// Alias — redirect ke tambah_staf.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();
if (!isAdmin()) { header('Location: dashboard.php'); exit; }
header('Location: tambah_staf.php');
exit;
?>
