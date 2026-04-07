<?php
// admin/update_artikel.php
// Alias — redirect ke edit_artikel.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
header('Location: edit_artikel.php?id=' . $id);
exit;
?>
