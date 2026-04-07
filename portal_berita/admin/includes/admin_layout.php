<?php
// admin/includes/admin_layout.php
// Sertakan di atas setiap halaman admin setelah requireLogin()
// Variabel yang harus di-set sebelum include: $page_title, $active_menu
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin' ?> | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --merah: #C0392B; }
        body { font-family: 'Source Sans 3', sans-serif; background: #f4f6f9; }
        .sidebar { width: 250px; min-height: 100vh; background: #1a1a1a; position: fixed; top: 0; left: 0; z-index: 100; }
        .sidebar-brand { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand .logo { font-family: 'Playfair Display',serif; font-size: 1.3rem; font-weight: 900; color: #C0392B; }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 10px 20px; border-radius: 6px; margin: 2px 10px; font-size: 0.88rem; transition: 0.2s; display: flex; align-items: center; gap: 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(192,57,43,0.3); }
        .sidebar .nav-link i { font-size: 1rem; width: 20px; }
        .sidebar-section { padding: 14px 20px 6px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.3); font-weight: 600; }
        .main-content { margin-left: 250px; min-height: 100vh; }
        .top-bar-admin { background: #fff; padding: 14px 24px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; justify-content: space-between; }
        .content-area { padding: 24px; }
        .form-control:focus, .form-select:focus { border-color: var(--merah); box-shadow: 0 0 0 0.2rem rgba(192,57,43,0.15); }
        .btn-merah { background: var(--merah); color: #fff; border: none; }
        .btn-merah:hover { background: #96281B; color: #fff; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><i class="bi bi-newspaper me-2"></i><?= SITE_NAME ?></div>
        <div style="font-size:0.72rem;color:rgba(255,255,255,0.4);margin-top:2px;">Panel Administrasi</div>
    </div>
    <nav class="pt-2">
        <div class="sidebar-section">Menu Utama</div>
        <a class="nav-link <?= ($active_menu??'')==='dashboard'?'active':'' ?>" href="dashboard.php"><i class="bi bi-speedometer2"></i>Dashboard</a>
        <a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-globe"></i>Lihat Website</a>

        <div class="sidebar-section">Konten</div>
        <a class="nav-link <?= ($active_menu??'')==='artikel_masuk'?'active':'' ?>" href="artikel_masuk.php"><i class="bi bi-newspaper"></i>Daftar Artikel</a>
        <a class="nav-link <?= ($active_menu??'')==='tambah_artikel'?'active':'' ?>" href="tambah_artikel.php"><i class="bi bi-plus-circle"></i>Tambah Artikel</a>

        <?php if (isAdmin()): ?>
        <div class="sidebar-section">Manajemen</div>
        <a class="nav-link <?= ($active_menu??'')==='staf'?'active':'' ?>" href="staf.php"><i class="bi bi-people"></i>Daftar Staf</a>
        <a class="nav-link <?= ($active_menu??'')==='tambah_staf'?'active':'' ?>" href="tambah_staf.php"><i class="bi bi-person-plus"></i>Tambah Staf</a>
        <?php endif; ?>

        <div class="sidebar-section">Akun</div>
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i>Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="top-bar-admin">
        <h6 class="mb-0 fw-bold"><?= $page_title ?? 'Admin' ?></h6>
        <div class="d-flex align-items-center gap-3">
            <span class="badge rounded-pill" style="background:var(--merah);"><?= ucfirst($_SESSION['staf_role']) ?></span>
            <div class="d-flex align-items-center gap-2">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--merah);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:0.85rem;">
                    <?= strtoupper(substr($_SESSION['staf_nama'], 0, 1)) ?>
                </div>
                <span class="small fw-semibold"><?= htmlspecialchars($_SESSION['staf_nama']) ?></span>
            </div>
            <a href="logout.php" class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
    <div class="content-area">
