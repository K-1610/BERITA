<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/koneksi.php';

// Ambil kategori untuk navbar
$res_kat = $koneksi->query("SELECT * FROM kategori ORDER BY nama ASC");
$kategori_list = [];
while ($row = $res_kat->fetch_assoc()) $kategori_list[] = $row;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | ' : '' ?><?= SITE_NAME ?></title>
    <meta name="description" content="<?= isset($meta_desc) ? $meta_desc : 'Portal berita terpercaya - informasi terkini dari seluruh Indonesia' ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --merah: #C0392B;
            --merah-gelap: #96281B;
            --hitam: #1a1a1a;
            --abu: #6c757d;
            --krem: #FDF8F3;
        }
        body { font-family: 'Source Sans 3', sans-serif; background: #fff; color: var(--hitam); }
        .font-judul { font-family: 'Playfair Display', serif; }

        /* TOP BAR */
        .top-bar { background: var(--hitam); color: #fff; font-size: 0.8rem; padding: 6px 0; }
        .top-bar a { color: #ccc; text-decoration: none; }
        .top-bar a:hover { color: #fff; }
        .ticker-wrap { overflow: hidden; }
        .ticker { display: flex; gap: 30px; animation: ticker 30s linear infinite; white-space: nowrap; }
        @keyframes ticker { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

        /* HEADER */
        .site-header { border-bottom: 3px solid var(--merah); padding: 12px 0; background: #fff; }
        .logo-text { font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 900; color: var(--merah); letter-spacing: -1px; line-height: 1; }
        .logo-sub { font-size: 0.7rem; letter-spacing: 4px; color: var(--abu); text-transform: uppercase; }

        /* NAVBAR */
        .main-nav { background: var(--hitam); }
        .main-nav .nav-link { color: rgba(255,255,255,0.85) !important; font-weight: 600; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 14px !important; transition: color 0.2s; }
        .main-nav .nav-link:hover, .main-nav .nav-link.active { color: #fff !important; }
        .main-nav .nav-link.active { border-bottom: 2px solid var(--merah); }
        .main-nav .dropdown-menu { border-radius: 0; border: none; border-top: 3px solid var(--merah); }
        .main-nav .dropdown-item { font-size: 0.85rem; }
        .search-form input { border-radius: 20px 0 0 20px; border: none; font-size: 0.85rem; }
        .search-form button { border-radius: 0 20px 20px 0; background: var(--merah); border-color: var(--merah); }

        /* BREAKING NEWS */
        .breaking-bar { background: var(--merah); color: #fff; font-size: 0.85rem; padding: 6px 0; }
        .breaking-label { background: #fff; color: var(--merah); font-weight: 700; padding: 2px 10px; border-radius: 3px; font-size: 0.8rem; margin-right: 12px; }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <i class="bi bi-calendar3 me-1"></i>
                <?= date('l, d F Y') ?> WIB
            </div>
            <div class="col-md-6 text-md-end">
                <a href="#"><i class="bi bi-facebook me-2"></i></a>
                <a href="#"><i class="bi bi-twitter-x me-2"></i></a>
                <a href="#"><i class="bi bi-instagram me-2"></i></a>
                <a href="#"><i class="bi bi-youtube"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- HEADER -->
<header class="site-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4">
                <a href="<?= SITE_URL ?>" style="text-decoration:none;">
                    <div class="logo-text"><i class="bi bi-newspaper me-1"></i><?= SITE_NAME ?></div>
                    <div class="logo-sub">Berita Terpercaya &bull; Terkini &bull; Berimbang</div>
                </a>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <form class="d-flex search-form" action="<?= SITE_URL ?>/cari.php" method="GET">
                    <input type="text" name="q" class="form-control" placeholder="Cari berita..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                    <button type="submit" class="btn btn-danger"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="col-md-3 text-end mt-3 mt-md-0">
                <?php if (isLoggedIn()): ?>
                    <a href="<?= SITE_URL ?>/admin/dashboard.php" class="btn btn-sm btn-outline-danger me-1"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                    <a href="<?= SITE_URL ?>/admin/logout.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/admin/login.php" class="btn btn-sm btn-danger"><i class="bi bi-person-circle me-1"></i>Login Admin</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- MAIN NAV -->
<nav class="main-nav navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>"><i class="bi bi-house-fill me-1"></i>Beranda</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Kategori</a>
                    <ul class="dropdown-menu">
                        <?php foreach ($kategori_list as $kat): ?>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/kategori.php?slug=<?= $kat['slug'] ?>">
                            <?= htmlspecialchars($kat['nama']) ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php foreach (array_slice($kategori_list, 0, 5) as $kat): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= SITE_URL ?>/kategori.php?slug=<?= $kat['slug'] ?>"><?= htmlspecialchars($kat['nama']) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <span class="text-muted small text-white-50">
                <i class="bi bi-circle-fill text-danger me-1" style="font-size:0.6rem;"></i>LIVE
            </span>
        </div>
    </div>
</nav>

<!-- BREAKING NEWS -->
<?php
$breaking = $koneksi->query("SELECT judul, slug FROM artikel WHERE status='publish' ORDER BY created_at DESC LIMIT 5");
$breaking_items = [];
while ($r = $breaking->fetch_assoc()) $breaking_items[] = $r;
if (!empty($breaking_items)):
?>
<div class="breaking-bar">
    <div class="container d-flex align-items-center overflow-hidden">
        <span class="breaking-label flex-shrink-0">TERKINI</span>
        <div class="ticker-wrap flex-grow-1">
            <div class="ticker">
                <?php foreach (array_merge($breaking_items, $breaking_items) as $b): ?>
                    <span><a href="<?= SITE_URL ?>/artikel.php?slug=<?= $b['slug'] ?>" style="color:#fff;text-decoration:none;">
                        <?= htmlspecialchars($b['judul']) ?>
                    </a></span>
                    <span class="text-danger px-2">•</span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
