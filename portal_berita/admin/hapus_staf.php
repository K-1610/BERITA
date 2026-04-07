<?php
// admin/hapus_staf.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();
if (!isAdmin()) { header('Location: dashboard.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Tidak boleh hapus diri sendiri
if ($id === (int)$_SESSION['staf_id']) {
    $_SESSION['msg'] = 'Anda tidak dapat menghapus akun Anda sendiri.';
    header('Location: staf.php');
    exit;
}

$staf = $koneksi->query("SELECT * FROM staf WHERE id=$id")->fetch_assoc();
if (!$staf) {
    $_SESSION['msg'] = 'Staf tidak ditemukan.';
    header('Location: staf.php');
    exit;
}

// Hapus foto staf jika ada
if ($staf['foto'] && file_exists('../assets/img/' . $staf['foto'])) {
    unlink('../assets/img/' . $staf['foto']);
}

// Artikel staf akan ikut terhapus karena ON DELETE CASCADE
$koneksi->query("DELETE FROM staf WHERE id=$id");

$_SESSION['msg'] = 'Staf "' . htmlspecialchars($staf['nama']) . '" berhasil dihapus.';
header('Location: staf.php');
exit;
?>
