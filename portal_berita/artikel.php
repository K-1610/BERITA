<?php
// artikel.php
require_once 'includes/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$slug = isset($_GET['slug']) ? $koneksi->real_escape_string($_GET['slug']) : '';
if (!$slug) { header('Location: index.php'); exit; }

$art = $koneksi->query("SELECT a.*, k.nama AS kat_nama, k.slug AS kat_slug, s.nama AS penulis, s.role AS penulis_role 
    FROM artikel a JOIN kategori k ON a.id_kategori=k.id JOIN staf s ON a.id_staf=s.id
    WHERE a.slug='$slug' AND a.status='publish'")->fetch_assoc();

if (!$art) { header('Location: index.php'); exit; }

// Tambah views
$koneksi->query("UPDATE artikel SET views=views+1 WHERE id={$art['id']}");

$page_title = $art['judul'];
$meta_desc = mb_substr(strip_tags($art['ringkasan'] ?? $art['konten']), 0, 150);

// Artikel terkait
$terkait = $koneksi->query("SELECT * FROM artikel WHERE id_kategori={$art['id_kategori']} AND id!={$art['id']} AND status='publish' ORDER BY RAND() LIMIT 4");

// Trending
$trending_side = $koneksi->query("SELECT a.*, k.nama AS kat_nama FROM artikel a JOIN kategori k ON a.id_kategori=k.id WHERE a.status='publish' ORDER BY a.views DESC LIMIT 5");

require_once 'includes/header.php';
?>

<main class="container my-4">
    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php" class="text-danger">Beranda</a></li>
            <li class="breadcrumb-item"><a href="kategori.php?slug=<?= $art['kat_slug'] ?>" class="text-danger"><?= htmlspecialchars($art['kat_nama']) ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars(mb_substr($art['judul'], 0, 40)) ?>...</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <article>
                <span class="badge mb-2 px-3 py-2" style="background:var(--merah);"><?= htmlspecialchars($art['kat_nama']) ?></span>
                <h1 class="font-judul fw-bold mb-3" style="line-height:1.3;"><?= htmlspecialchars($art['judul']) ?></h1>

                <div class="d-flex flex-wrap gap-3 align-items-center mb-3 pb-3 border-bottom">
                    <span class="text-muted small"><i class="bi bi-person-circle me-1 text-danger"></i><?= htmlspecialchars($art['penulis']) ?></span>
                    <span class="text-muted small"><i class="bi bi-calendar3 me-1 text-danger"></i><?= date('d F Y', strtotime($art['created_at'])) ?></span>
                    <span class="text-muted small"><i class="bi bi-eye me-1 text-danger"></i><?= number_format($art['views']) ?> tayang</span>
                    <div class="ms-auto d-flex gap-2">
                        <a href="https://www.facebook.com/sharer.php?u=<?= urlencode(SITE_URL.'/artikel.php?slug='.$art['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-facebook"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL.'/artikel.php?slug='.$art['slug']) ?>&text=<?= urlencode($art['judul']) ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-twitter-x"></i></a>
                        <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>alert('Link disalin!'))" class="btn btn-sm btn-outline-secondary"><i class="bi bi-link-45deg"></i></button>
                    </div>
                </div>

                <?php if ($art['gambar'] && file_exists('assets/img/' . $art['gambar'])): ?>
                <figure class="mb-4">
                    <img src="assets/img/<?= $art['gambar'] ?>" alt="<?= htmlspecialchars($art['judul']) ?>" class="img-fluid rounded w-100" style="max-height:420px;object-fit:cover;">
                </figure>
                <?php endif; ?>

                <?php if ($art['ringkasan']): ?>
                <div class="alert" style="background:#FDF8F3;border-left:4px solid var(--merah);border-radius:4px;" class="mb-4">
                    <strong><?= htmlspecialchars($art['ringkasan']) ?></strong>
                </div>
                <?php endif; ?>

                <div class="article-body" style="font-size:1.05rem;line-height:1.9;color:#2c2c2c;">
                    <?= $art['konten'] ?>
                </div>

                <div class="mt-4 pt-3 border-top d-flex gap-2 flex-wrap">
                    <small class="text-muted me-2">Bagikan:</small>
                    <a href="https://www.facebook.com/sharer.php?u=<?= urlencode(SITE_URL.'/artikel.php?slug='.$art['slug']) ?>" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-facebook me-1"></i>Facebook</a>
                    <a href="https://wa.me/?text=<?= urlencode($art['judul'].' - '.SITE_URL.'/artikel.php?slug='.$art['slug']) ?>" target="_blank" class="btn btn-sm btn-success"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL.'/artikel.php?slug='.$art['slug']) ?>" target="_blank" class="btn btn-sm btn-dark"><i class="bi bi-twitter-x me-1"></i>Twitter</a>
                </div>
            </article>

            <!-- ARTIKEL TERKAIT -->
            <div class="mt-5">
                <div class="d-flex align-items-center mb-3">
                    <div style="width:4px;height:24px;background:var(--merah);border-radius:2px;margin-right:10px;"></div>
                    <h5 class="mb-0 fw-bold">Artikel Terkait</h5>
                </div>
                <div class="row g-3">
                    <?php while ($tk = $terkait->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:8px;overflow:hidden;">
                            <div style="height:130px;background:#eee;display:flex;align-items:center;justify-content:center;">
                                <?php if ($tk['gambar'] && file_exists('assets/img/'.$tk['gambar'])): ?>
                                <img src="assets/img/<?= $tk['gambar'] ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                <?php else: ?>
                                <i class="bi bi-image text-muted fs-3"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="fw-bold" style="font-size:0.88rem;line-height:1.4;">
                                    <a href="artikel.php?slug=<?= $tk['slug'] ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars(mb_substr($tk['judul'], 0, 70)) ?>
                                    </a>
                                </h6>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?= timeAgo($tk['created_at']) ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:8px;position:sticky;top:20px;">
                <div class="card-header border-0 fw-bold d-flex align-items-center" style="background:#fff;border-bottom:2px solid var(--merah) !important;padding-bottom:12px;">
                    <i class="bi bi-fire text-danger me-2"></i>Trending
                </div>
                <div class="card-body p-0">
                    <?php $rank=1; while ($tr = $trending_side->fetch_assoc()): ?>
                    <div class="d-flex align-items-start p-3 border-bottom">
                        <div class="fw-bold me-3 flex-shrink-0" style="font-size:1.5rem;color:<?=$rank==1?'#C0392B':'#ddd'?>;font-family:'Playfair Display',serif;width:28px;"><?=$rank?></div>
                        <div>
                            <a href="artikel.php?slug=<?= $tr['slug'] ?>" class="text-dark text-decoration-none fw-semibold" style="font-size:0.85rem;line-height:1.4;">
                                <?= htmlspecialchars(mb_substr($tr['judul'],0,65)) ?>
                            </a>
                            <div class="mt-1"><small class="text-muted"><i class="bi bi-eye"></i> <?= formatViews($tr['views']) ?></small></div>
                        </div>
                    </div>
                    <?php $rank++; endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
