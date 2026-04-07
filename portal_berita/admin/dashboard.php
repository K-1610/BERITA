<?php
// admin/dashboard.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();

$page_title = 'Dashboard Admin';

// Statistik
$total_artikel = $koneksi->query("SELECT COUNT(*) FROM artikel")->fetch_row()[0];
$total_publish = $koneksi->query("SELECT COUNT(*) FROM artikel WHERE status='publish'")->fetch_row()[0];
$total_draft = $koneksi->query("SELECT COUNT(*) FROM artikel WHERE status='draft'")->fetch_row()[0];
$total_staf = $koneksi->query("SELECT COUNT(*) FROM staf WHERE status='aktif'")->fetch_row()[0];
$total_views = $koneksi->query("SELECT SUM(views) FROM artikel WHERE status='publish'")->fetch_row()[0] ?? 0;
$total_kategori = $koneksi->query("SELECT COUNT(*) FROM kategori")->fetch_row()[0];

// Artikel terbaru
$artikel_terbaru = $koneksi->query("SELECT a.*, k.nama AS kat_nama, s.nama AS penulis
    FROM artikel a JOIN kategori k ON a.id_kategori=k.id JOIN staf s ON a.id_staf=s.id
    ORDER BY a.created_at DESC LIMIT 8");

// Trending
$trending = $koneksi->query("SELECT a.judul, a.views, k.nama AS kat_nama FROM artikel a JOIN kategori k ON a.id_kategori=k.id WHERE a.status='publish' ORDER BY a.views DESC LIMIT 5");

// Views per kategori
$kat_stats = $koneksi->query("SELECT k.nama, COUNT(a.id) AS jumlah, COALESCE(SUM(a.views),0) AS total_views 
    FROM kategori k LEFT JOIN artikel a ON k.id=a.id_kategori AND a.status='publish'
    GROUP BY k.id ORDER BY jumlah DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --merah: #C0392B; --merah-gelap: #96281B; }
        body { font-family: 'Source Sans 3', sans-serif; background: #f4f6f9; }
        .sidebar { width: 250px; min-height: 100vh; background: #1a1a1a; position: fixed; top: 0; left: 0; z-index: 100; transition: 0.3s; }
        .sidebar-brand { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand .logo { font-family: 'Playfair Display',serif; font-size: 1.3rem; font-weight: 900; color: #C0392B; }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 10px 20px; border-radius: 6px; margin: 2px 10px; font-size: 0.88rem; transition: 0.2s; display: flex; align-items: center; gap: 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(192,57,43,0.3); }
        .sidebar .nav-link i { font-size: 1rem; width: 20px; }
        .sidebar-section { padding: 14px 20px 6px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.3); font-weight: 600; }
        .main-content { margin-left: 250px; min-height: 100vh; }
        .top-bar-admin { background: #fff; padding: 14px 24px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; justify-content: space-between; }
        .content-area { padding: 24px; }
        .stat-card { border-radius: 12px; border: none; }
        .stat-card .icon-box { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .table th { font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; color: #666; font-weight: 600; border-top: none; }
        .badge-status-publish { background: #d4edda; color: #155724; }
        .badge-status-draft { background: #fff3cd; color: #856404; }
        .badge-status-arsip { background: #f8d7da; color: #721c24; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><i class="bi bi-newspaper me-2"></i><?= SITE_NAME ?></div>
        <div style="font-size:0.72rem;color:rgba(255,255,255,0.4);margin-top:2px;">Panel Administrasi</div>
    </div>
    <nav class="pt-2">
        <div class="sidebar-section">Menu Utama</div>
        <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i>Dashboard</a>
        <a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-globe"></i>Lihat Website</a>

        <div class="sidebar-section">Konten</div>
        <a class="nav-link" href="artikel_masuk.php"><i class="bi bi-newspaper"></i>Daftar Artikel</a>
        <a class="nav-link" href="tambah_artikel.php"><i class="bi bi-plus-circle"></i>Tambah Artikel</a>

        <?php if (isAdmin()): ?>
        <div class="sidebar-section">Manajemen</div>
        <a class="nav-link" href="staf.php"><i class="bi bi-people"></i>Daftar Staf</a>
        <a class="nav-link" href="tambah_staf.php"><i class="bi bi-person-plus"></i>Tambah Staf</a>
        <?php endif; ?>

        <div class="sidebar-section">Akun</div>
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i>Logout</a>
    </nav>
</div>

<!-- MAIN -->
<div class="main-content">
    <div class="top-bar-admin">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').style.transform='translateX(0)'">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="mb-0 fw-bold">Dashboard</h6>
        </div>
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
        <h5 class="fw-bold mb-1">Selamat datang, <?= htmlspecialchars($_SESSION['staf_nama']) ?>! 👋</h5>
        <p class="text-muted small mb-4"><?= date('l, d F Y') ?> — Ini ringkasan aktivitas portal berita Anda.</p>

        <!-- STAT CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-box" style="background:#ffe8e6;color:#C0392B;"><i class="bi bi-newspaper"></i></div>
                        <div>
                            <div class="fw-bold fs-4"><?= number_format($total_artikel) ?></div>
                            <div class="text-muted small">Total Artikel</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-box" style="background:#d4edda;color:#28a745;"><i class="bi bi-check-circle"></i></div>
                        <div>
                            <div class="fw-bold fs-4"><?= number_format($total_publish) ?></div>
                            <div class="text-muted small">Dipublikasi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-box" style="background:#fff3cd;color:#856404;"><i class="bi bi-eye"></i></div>
                        <div>
                            <div class="fw-bold fs-4"><?= formatViews($total_views) ?></div>
                            <div class="text-muted small">Total Tayangan</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-box" style="background:#cce5ff;color:#004085;"><i class="bi bi-people"></i></div>
                        <div>
                            <div class="fw-bold fs-4"><?= $total_staf ?></div>
                            <div class="text-muted small">Staf Aktif</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Artikel Terbaru -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <span class="fw-bold">Artikel Terbaru</span>
                        <a href="artikel_masuk.php" class="btn btn-sm btn-outline-danger">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-3">Judul</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($a = $artikel_terbaru->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-3" style="max-width:220px;">
                                            <div class="fw-semibold" style="font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                <?= htmlspecialchars($a['judul']) ?>
                                            </div>
                                            <small class="text-muted"><?= timeAgo($a['created_at']) ?></small>
                                        </td>
                                        <td><small><?= htmlspecialchars($a['kat_nama']) ?></small></td>
                                        <td>
                                            <span class="badge rounded-pill badge-status-<?= $a['status'] ?>">
                                                <?= ucfirst($a['status']) ?>
                                            </span>
                                        </td>
                                        <td><small><?= formatViews($a['views']) ?></small></td>
                                        <td>
                                            <a href="edit_artikel.php?id=<?= $a['id'] ?>" class="btn btn-xs btn-outline-warning py-0 px-2" style="font-size:0.75rem;"><i class="bi bi-pencil"></i></a>
                                            <a href="hapus_artikel.php?id=<?= $a['id'] ?>" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:0.75rem;" onclick="return confirm('Hapus artikel ini?')"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Stats -->
            <div class="col-lg-4">
                <!-- Trending -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom py-3 fw-bold"><i class="bi bi-fire text-danger me-2"></i>Artikel Trending</div>
                    <div class="card-body p-0">
                        <?php $rk=1; while ($tr = $trending->fetch_assoc()): ?>
                        <div class="d-flex align-items-start px-3 py-2 border-bottom">
                            <span class="fw-bold me-2 flex-shrink-0" style="color:<?=$rk==1?'#C0392B':'#ccc'?>;font-size:1.1rem;width:20px;"><?=$rk?></span>
                            <div>
                                <div style="font-size:0.82rem;font-weight:600;line-height:1.3;"><?= htmlspecialchars(mb_substr($tr['judul'],0,55)) ?>...</div>
                                <small class="text-muted"><i class="bi bi-eye me-1"></i><?= formatViews($tr['views']) ?></small>
                            </div>
                        </div>
                        <?php $rk++; endwhile; ?>
                    </div>
                </div>

                <!-- Statistik per Kategori -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 fw-bold"><i class="bi bi-bar-chart me-2 text-danger"></i>Artikel per Kategori</div>
                    <div class="card-body p-3">
                        <?php while ($ks = $kat_stats->fetch_assoc()): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-semibold"><?= htmlspecialchars($ks['nama']) ?></small>
                                <small class="text-muted"><?= $ks['jumlah'] ?> artikel</small>
                            </div>
                            <div class="progress" style="height:5px;">
                                <div class="progress-bar" style="width:<?= min(100, ($ks['jumlah']/$total_artikel)*100) ?>%;background:var(--merah);"></div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
