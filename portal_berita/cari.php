<?php
// cari.php
require_once 'includes/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$q = isset($_GET['q']) ? $koneksi->real_escape_string(trim($_GET['q'])) : '';
$page_title = 'Cari: ' . htmlspecialchars($q);

$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

if ($q) {
    $total = $koneksi->query("SELECT COUNT(*) FROM artikel a WHERE (a.judul LIKE '%$q%' OR a.konten LIKE '%$q%') AND a.status='publish'")->fetch_row()[0];
    $hasil = $koneksi->query("SELECT a.*, k.nama AS kat_nama, k.slug AS kat_slug, s.nama AS penulis
        FROM artikel a JOIN kategori k ON a.id_kategori=k.id JOIN staf s ON a.id_staf=s.id
        WHERE (a.judul LIKE '%$q%' OR a.konten LIKE '%$q%') AND a.status='publish'
        ORDER BY a.created_at DESC LIMIT $per_page OFFSET $offset");
    $total_pages = ceil($total / $per_page);
}

require_once 'includes/header.php';
?>

<main class="container my-4">
    <div class="d-flex align-items-center mb-4">
        <div style="width:4px;height:28px;background:var(--merah);border-radius:2px;margin-right:12px;"></div>
        <div>
            <h4 class="mb-0 font-judul fw-bold">Hasil Pencarian: "<?= htmlspecialchars($q) ?>"</h4>
            <?php if ($q): ?><small class="text-muted"><?= $total ?> hasil ditemukan</small><?php endif; ?>
        </div>
    </div>

    <?php if (!$q): ?>
    <div class="alert alert-info">Masukkan kata kunci pencarian di atas.</div>
    <?php elseif ($total == 0): ?>
    <div class="text-center py-5">
        <i class="bi bi-search text-muted" style="font-size:4rem;"></i>
        <h5 class="mt-3 text-muted">Tidak ada hasil untuk "<?= htmlspecialchars($q) ?>"</h5>
        <p class="text-muted">Coba kata kunci yang berbeda.</p>
        <a href="index.php" class="btn btn-danger">Kembali ke Beranda</a>
    </div>
    <?php else: ?>
    <?php while ($art = $hasil->fetch_assoc()): ?>
    <div class="card border-0 shadow-sm mb-3" style="border-radius:8px;">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-2">
                    <div style="height:80px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        <?php if ($art['gambar'] && file_exists('assets/img/'.$art['gambar'])): ?>
                        <img src="assets/img/<?= $art['gambar'] ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                        <i class="bi bi-image text-muted"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-10">
                    <span class="badge mb-1" style="background:var(--merah);"><?= htmlspecialchars($art['kat_nama']) ?></span>
                    <h6 class="fw-bold mb-1">
                        <a href="artikel.php?slug=<?= $art['slug'] ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($art['judul']) ?>
                        </a>
                    </h6>
                    <p class="text-muted small mb-1"><?= htmlspecialchars(mb_substr($art['ringkasan'] ?? strip_tags($art['konten']), 0, 150)) ?>...</p>
                    <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($art['penulis']) ?> &bull; <i class="bi bi-clock me-1"></i><?= timeAgo($art['created_at']) ?></small>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i=1; $i<=$total_pages; $i++): ?>
            <li class="page-item <?= $i==$page?'active':'' ?>">
                <a class="page-link" href="?q=<?= urlencode($q) ?>&p=<?= $i ?>"
                   style="<?= $i==$page?'background:var(--merah);border-color:var(--merah);':'' ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
