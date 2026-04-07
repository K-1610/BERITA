<?php
// index.php - Halaman Utama Portal Berita
$page_title = 'Beranda';
$meta_desc = 'Portal berita terpercaya - informasi terkini dan terlengkap';
require_once 'includes/header.php';

// Artikel unggulan (featured)
$featured = $koneksi->query("SELECT a.*, k.nama AS kat_nama, k.slug AS kat_slug, s.nama AS penulis 
    FROM artikel a JOIN kategori k ON a.id_kategori = k.id JOIN staf s ON a.id_staf = s.id
    WHERE a.status='publish' AND a.featured=1 ORDER BY a.created_at DESC LIMIT 4");
$featured_arr = [];
while ($r = $featured->fetch_assoc()) $featured_arr[] = $r;

// Berita terbaru
$terbaru = $koneksi->query("SELECT a.*, k.nama AS kat_nama, k.slug AS kat_slug, s.nama AS penulis 
    FROM artikel a JOIN kategori k ON a.id_kategori = k.id JOIN staf s ON a.id_staf = s.id
    WHERE a.status='publish' ORDER BY a.created_at DESC LIMIT 9");

// Trending (views tertinggi)
$trending = $koneksi->query("SELECT a.*, k.nama AS kat_nama, k.slug AS kat_slug
    FROM artikel a JOIN kategori k ON a.id_kategori = k.id
    WHERE a.status='publish' ORDER BY a.views DESC LIMIT 5");
?>

<main class="container my-4">

    <!-- HERO: ARTIKEL FEATURED -->
    <?php if (!empty($featured_arr)): $utama = $featured_arr[0]; ?>
    <div class="row g-3 mb-4">
        <!-- Artikel Utama -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" style="border-radius:8px;">
                <div style="height:360px; background: linear-gradient(135deg,#2c2c2c,#555); position:relative;">
                    <?php if ($utama['gambar'] && file_exists('assets/img/' . $utama['gambar'])): ?>
                        <img src="assets/img/<?= $utama['gambar'] ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-newspaper" style="font-size:5rem;color:rgba(255,255,255,0.1);"></i>
                        </div>
                    <?php endif; ?>
                    <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,0.9));padding:30px 20px 20px;">
                        <span class="badge mb-2" style="background:var(--merah);"><?= htmlspecialchars($utama['kat_nama']) ?></span>
                        <h2 class="font-judul text-white mb-2 fw-bold" style="font-size:1.5rem;line-height:1.3;">
                            <a href="artikel.php?slug=<?= $utama['slug'] ?>" class="text-white text-decoration-none stretched-link">
                                <?= htmlspecialchars($utama['judul']) ?>
                            </a>
                        </h2>
                        <small class="text-white-50"><i class="bi bi-person me-1"></i><?= htmlspecialchars($utama['penulis']) ?> &bull; <i class="bi bi-clock me-1"></i><?= timeAgo($utama['created_at']) ?> &bull; <i class="bi bi-eye me-1"></i><?= formatViews($utama['views']) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Artikel Unggulan Sampingan -->
        <div class="col-lg-5">
            <div class="d-flex flex-column gap-3 h-100">
                <?php foreach (array_slice($featured_arr, 1, 3) as $f): ?>
                <div class="card border-0 shadow-sm flex-fill position-relative overflow-hidden" style="border-radius:8px;">
                    <div style="height:105px; background: linear-gradient(135deg,#333,#666); position:relative;">
                        <?php if ($f['gambar'] && file_exists('assets/img/' . $f['gambar'])): ?>
                            <img src="assets/img/<?= $f['gambar'] ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                        <?php endif; ?>
                        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);padding:12px 14px;display:flex;flex-direction:column;justify-content:flex-end;">
                            <span class="badge mb-1" style="background:var(--merah);font-size:0.7rem;width:fit-content;"><?= htmlspecialchars($f['kat_nama']) ?></span>
                            <h6 class="text-white mb-0 fw-bold" style="font-size:0.88rem;line-height:1.3;">
                                <a href="artikel.php?slug=<?= $f['slug'] ?>" class="text-white text-decoration-none stretched-link">
                                    <?= htmlspecialchars(mb_substr($f['judul'], 0, 70)) ?>
                                </a>
                            </h6>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- KOLOM UTAMA -->
        <div class="col-lg-8">

            <!-- BERITA TERBARU -->
            <div class="d-flex align-items-center mb-3">
                <div style="width:4px;height:24px;background:var(--merah);border-radius:2px;margin-right:10px;"></div>
                <h5 class="mb-0 fw-bold font-judul">Berita Terbaru</h5>
            </div>

            <div class="row g-3">
                <?php while ($art = $terbaru->fetch_assoc()): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:8px;overflow:hidden;">
                        <div style="height:160px; background: linear-gradient(135deg,#eee,#ddd); position:relative;">
                            <?php if ($art['gambar'] && file_exists('assets/img/' . $art['gambar'])): ?>
                                <img src="assets/img/<?= $art['gambar'] ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-image" style="font-size:2.5rem;color:#bbb;"></i>
                                </div>
                            <?php endif; ?>
                            <span class="badge position-absolute" style="top:10px;left:10px;background:var(--merah);"><?= htmlspecialchars($art['kat_nama']) ?></span>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title fw-bold" style="font-size:0.92rem;line-height:1.4;">
                                <a href="artikel.php?slug=<?= $art['slug'] ?>" class="text-dark text-decoration-none">
                                    <?= htmlspecialchars(mb_substr($art['judul'], 0, 80)) ?>
                                </a>
                            </h6>
                            <p class="card-text text-muted small mb-2"><?= htmlspecialchars(mb_substr($art['ringkasan'] ?? strip_tags($art['konten']), 0, 90)) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?= timeAgo($art['created_at']) ?></small>
                                <small class="text-muted"><i class="bi bi-eye me-1"></i><?= formatViews($art['views']) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div class="col-lg-4">
            <!-- TRENDING -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:8px;">
                <div class="card-header border-0 fw-bold d-flex align-items-center" style="background:#fff;border-bottom:2px solid var(--merah) !important;padding-bottom:12px;">
                    <i class="bi bi-fire text-danger me-2"></i>Trending
                </div>
                <div class="card-body p-0">
                    <?php $rank = 1; while ($tr = $trending->fetch_assoc()): ?>
                    <div class="d-flex align-items-start p-3 border-bottom">
                        <div class="fw-bold me-3 flex-shrink-0" style="font-size:1.6rem;color:<?= $rank == 1 ? '#C0392B' : '#ddd' ?>;font-family:'Playfair Display',serif;width:30px;"><?= $rank ?></div>
                        <div>
                            <a href="artikel.php?slug=<?= $tr['slug'] ?>" class="text-dark text-decoration-none fw-semibold" style="font-size:0.88rem;line-height:1.4;">
                                <?= htmlspecialchars(mb_substr($tr['judul'], 0, 70)) ?>
                            </a>
                            <div class="mt-1">
                                <span class="badge" style="background:var(--merah);font-size:0.7rem;"><?= htmlspecialchars($tr['kat_nama']) ?></span>
                                <small class="text-muted ms-1"><i class="bi bi-eye"></i> <?= formatViews($tr['views']) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php $rank++; endwhile; ?>
                </div>
            </div>

            <!-- KATEGORI -->
            <div class="card border-0 shadow-sm" style="border-radius:8px;">
                <div class="card-header border-0 fw-bold d-flex align-items-center" style="background:#fff;border-bottom:2px solid var(--merah) !important;padding-bottom:12px;">
                    <i class="bi bi-grid-3x3-gap me-2 text-danger"></i>Kategori
                </div>
                <div class="card-body">
                    <?php foreach ($kategori_list as $kat):
                        $cnt = $koneksi->query("SELECT COUNT(*) as c FROM artikel WHERE id_kategori={$kat['id']} AND status='publish'")->fetch_assoc()['c'];
                    ?>
                    <a href="kategori.php?slug=<?= $kat['slug'] ?>" class="d-flex justify-content-between align-items-center text-decoration-none text-dark py-2 border-bottom">
                        <span><i class="bi bi-chevron-right me-1 text-danger" style="font-size:0.75rem;"></i><?= htmlspecialchars($kat['nama']) ?></span>
                        <span class="badge rounded-pill" style="background:var(--merah);"><?= $cnt ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
