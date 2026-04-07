<?php
// kategori.php
require_once 'includes/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$slug = isset($_GET['slug']) ? $koneksi->real_escape_string($_GET['slug']) : '';
$kat = $koneksi->query("SELECT * FROM kategori WHERE slug='$slug'")->fetch_assoc();
if (!$kat) { header('Location: index.php'); exit; }

$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

$total = $koneksi->query("SELECT COUNT(*) FROM artikel WHERE id_kategori={$kat['id']} AND status='publish'")->fetch_row()[0];
$total_pages = ceil($total / $per_page);

$artikel = $koneksi->query("SELECT a.*, k.nama AS kat_nama, s.nama AS penulis
    FROM artikel a JOIN kategori k ON a.id_kategori=k.id JOIN staf s ON a.id_staf=s.id
    WHERE a.id_kategori={$kat['id']} AND a.status='publish'
    ORDER BY a.created_at DESC LIMIT $per_page OFFSET $offset");

$page_title = $kat['nama'];
require_once 'includes/header.php';
?>

<main class="container my-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <div style="width:4px;height:28px;background:var(--merah);border-radius:2px;margin-right:12px;"></div>
                <div>
                    <h4 class="mb-0 font-judul fw-bold"><?= htmlspecialchars($kat['nama']) ?></h4>
                    <small class="text-muted"><?= $total ?> artikel ditemukan</small>
                </div>
            </div>

            <div class="row g-3">
                <?php while ($art = $artikel->fetch_assoc()): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:8px;overflow:hidden;">
                        <div style="height:170px;background:#eee;display:flex;align-items:center;justify-content:center;position:relative;">
                            <?php if ($art['gambar'] && file_exists('assets/img/'.$art['gambar'])): ?>
                            <img src="assets/img/<?= $art['gambar'] ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                            <i class="bi bi-image text-muted" style="font-size:2rem;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="fw-bold" style="font-size:0.92rem;line-height:1.4;">
                                <a href="artikel.php?slug=<?= $art['slug'] ?>" class="text-dark text-decoration-none">
                                    <?= htmlspecialchars(mb_substr($art['judul'], 0, 80)) ?>
                                </a>
                            </h6>
                            <p class="text-muted small mb-2"><?= htmlspecialchars(mb_substr($art['ringkasan'] ?? strip_tags($art['konten']), 0, 90)) ?>...</p>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?= timeAgo($art['created_at']) ?></small>
                                <small class="text-muted"><i class="bi bi-eye me-1"></i><?= formatViews($art['views']) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- PAGINATION -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?slug=<?= $slug ?>&p=<?= $page-1 ?>">‹</a>
                    </li>
                    <?php for ($i=1; $i<=$total_pages; $i++): ?>
                    <li class="page-item <?= $i==$page ? 'active' : '' ?>">
                        <a class="page-link" href="?slug=<?= $slug ?>&p=<?= $i ?>" 
                           style="<?= $i==$page ? 'background:var(--merah);border-color:var(--merah);' : '' ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?slug=<?= $slug ?>&p=<?= $page+1 ?>">›</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>

        <!-- SIDEBAR -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:8px;">
                <div class="card-header border-0 fw-bold" style="background:#fff;border-bottom:2px solid var(--merah) !important;">
                    <i class="bi bi-grid-3x3-gap me-2 text-danger"></i>Semua Kategori
                </div>
                <div class="card-body p-0">
                    <?php foreach ($kategori_list as $k):
                        $cnt = $koneksi->query("SELECT COUNT(*) FROM artikel WHERE id_kategori={$k['id']} AND status='publish'")->fetch_row()[0];
                    ?>
                    <a href="kategori.php?slug=<?= $k['slug'] ?>" class="d-flex justify-content-between align-items-center text-decoration-none text-dark px-3 py-2 border-bottom <?= $k['id']==$kat['id']?'bg-danger text-white':'' ?>">
                        <span style="<?= $k['id']==$kat['id']?'color:#fff':''; ?>"><?= htmlspecialchars($k['nama']) ?></span>
                        <span class="badge rounded-pill <?= $k['id']==$kat['id']?'bg-white text-danger':'bg-danger' ?>"><?= $cnt ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
