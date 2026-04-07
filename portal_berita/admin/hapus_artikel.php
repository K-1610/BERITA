<?php
// admin/hapus_artikel.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$art = $koneksi->query("SELECT * FROM artikel WHERE id=$id")->fetch_assoc();

if (!$art) {
    $_SESSION['msg'] = 'Artikel tidak ditemukan.';
    header('Location: artikel_masuk.php');
    exit;
}

// Hak akses
if ($_SESSION['staf_role'] === 'reporter' && $art['id_staf'] != $_SESSION['staf_id']) {
    $_SESSION['msg'] = 'Anda tidak memiliki izin untuk menghapus artikel ini.';
    header('Location: artikel_masuk.php');
    exit;
}

// Hapus gambar jika ada
if ($art['gambar'] && file_exists('../assets/img/' . $art['gambar'])) {
    unlink('../assets/img/' . $art['gambar']);
}

$koneksi->query("DELETE FROM artikel WHERE id=$id");
$_SESSION['msg'] = 'Artikel berhasil dihapus.';
header('Location: artikel_masuk.php');
exit;
?>
