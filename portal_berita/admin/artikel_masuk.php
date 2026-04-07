<?php
// admin/artikel_masuk.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();

$page_title = 'Daftar Artikel';
$active_menu = 'artikel_masuk';

$msg = '';
if (isset($_SESSION['msg'])) { $msg = $_SESSION['msg']; unset($_SESSION['msg']); }

// Filter
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$kat_filter = isset($_GET['kat']) ? (int)$_GET['kat'] : 0;
$search = isset($_GET['q']) ? $koneksi->real_escape_string(trim($_GET['q'])) : '';

$where = '1=1';
if ($status_filter) $where .= " AND a.status='$status_filter'";
if ($kat_filter) $where .= " AND a.id_kategori=$kat_filter";
if ($search) $where .= " AND a.judul LIKE '%$search%'";
// Reporter hanya lihat artikelnya sendiri
if ($_SESSION['staf_role'] === 'reporter') $where .= " AND a.id_staf={$_SESSION['staf_id']}";

$page = isset($_GET['p']) ? max(1,(int)$_GET['p']) : 1;
$per_page = 10;
$offset = ($page-1)*$per_page;

$total = $koneksi->query("SELECT COUNT(*) FROM artikel a WHERE $where")->fetch_row()[0];
$total_pages = ceil($total/$per_page);

$artikel = $koneksi->query("SELECT a.*, k.nama AS kat_nama, s.nama AS penulis 
    FROM artikel a JOIN kategori k ON a.id_kategori=k.id JOIN staf s ON a.id_staf=s.id
    WHERE $where ORDER BY a.created_at DESC LIMIT $per_page OFFSET $offset");

$kategori_all = $koneksi->query("SELECT * FROM kategori ORDER BY nama");

require_once 'includes/admin_layout.php';
?>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Daftar Artikel</h5>
        <small class="text-muted">Total: <?= number_format($total) ?> artikel</small>
    </div>
    <a href="tambah_artikel.php" class="btn btn-merah"><i class="bi bi-plus-circle me-1"></i>Tambah Artikel</a>
</div>

<!-- FILTER -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Cari judul..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="publish" <?= $status_filter=='publish'?'selected':'' ?>>Publish</option>
                    <option value="draft" <?= $status_filter=='draft'?'selected':'' ?>>Draft</option>
                    <option value="arsip" <?= $status_filter=='arsip'?'selected':'' ?>>Arsip</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="kat" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php while ($k = $kategori_all->fetch_assoc()): ?>
                    <option value="<?= $k['id'] ?>" <?= $kat_filter==$k['id']?'selected':'' ?>><?= htmlspecialchars($k['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-merah w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-3">#</th>
                        <th>Judul Artikel</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total == 0): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">Tidak ada artikel ditemukan.</td></tr>
                    <?php else: $no = $offset+1; while ($a = $artikel->fetch_assoc()): ?>
                    <tr>
                        <td class="px-3 text-muted small"><?= $no++ ?></td>
                        <td style="max-width:250px;">
                            <div class="fw-semibold" style="font-size:0.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= htmlspecialchars($a['judul']) ?>
                            </div>
                            <?php if ($a['featured']): ?>
                            <span class="badge bg-warning text-dark" style="font-size:0.65rem;"><i class="bi bi-star-fill me-1"></i>Unggulan</span>
                            <?php endif; ?>
                        </td>
                        <td><small><?= htmlspecialchars($a['kat_nama']) ?></small></td>
                        <td><small><?= htmlspecialchars($a['penulis']) ?></small></td>
                        <td>
                            <?php
                            $badges = ['publish'=>'success','draft'=>'warning','arsip'=>'secondary'];
                            $labels = ['publish'=>'Publish','draft'=>'Draft','arsip'=>'Arsip'];
                            ?>
                            <span class="badge bg-<?= $badges[$a['status']] ?>"><?= $labels[$a['status']] ?></span>
                        </td>
                        <td><small><?= formatViews($a['views']) ?></small></td>
                        <td><small><?= date('d/m/Y', strtotime($a['created_at'])) ?></small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="../artikel.php?slug=<?= $a['slug'] ?>" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2" title="Lihat"><i class="bi bi-eye"></i></a>
                                <a href="edit_artikel.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-warning py-0 px-2" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="hapus_artikel.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus" onclick="return confirm('Yakin hapus artikel ini?')"><i class="bi bi-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PAGINATION -->
<?php if ($total_pages > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i=1;$i<=$total_pages;$i++): ?>
        <li class="page-item <?= $i==$page?'active':'' ?>">
            <a class="page-link" href="?q=<?= urlencode($search) ?>&status=<?= $status_filter ?>&kat=<?= $kat_filter ?>&p=<?= $i ?>"
               style="<?= $i==$page?'background:var(--merah);border-color:var(--merah);':'' ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once 'includes/admin_layout_end.php'; ?>
