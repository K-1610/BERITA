<?php
// admin/staf.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();
if (!isAdmin()) { header('Location: dashboard.php'); exit; }

$page_title = 'Manajemen Staf';
$active_menu = 'staf';

$msg = '';
if (isset($_SESSION['msg'])) { $msg = $_SESSION['msg']; unset($_SESSION['msg']); }

$staf_list = $koneksi->query("SELECT s.*, COUNT(a.id) AS jml_artikel FROM staf s LEFT JOIN artikel a ON s.id=a.id_staf GROUP BY s.id ORDER BY s.created_at DESC");

require_once 'includes/admin_layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Manajemen Staf</h5>
        <small class="text-muted">Kelola pengguna yang dapat mengakses panel admin</small>
    </div>
    <a href="tambah_staf.php" class="btn btn-merah"><i class="bi bi-person-plus me-1"></i>Tambah Staf</a>
</div>

<?php if ($msg): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?= $msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-3">#</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Artikel</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while ($s = $staf_list->fetch_assoc()): ?>
                    <tr>
                        <td class="px-3"><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:50%;background:var(--merah);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                                    <?= strtoupper(substr($s['nama'],0,1)) ?>
                                </div>
                                <span class="fw-semibold"><?= htmlspecialchars($s['nama']) ?></span>
                            </div>
                        </td>
                        <td><code><?= htmlspecialchars($s['username']) ?></code></td>
                        <td><small><?= htmlspecialchars($s['email']) ?></small></td>
                        <td>
                            <?php
                            $role_colors = ['admin'=>'danger','editor'=>'primary','reporter'=>'secondary'];
                            ?>
                            <span class="badge bg-<?= $role_colors[$s['role']] ?>"><?= ucfirst($s['role']) ?></span>
                        </td>
                        <td><span class="badge bg-light text-dark"><?= $s['jml_artikel'] ?> artikel</span></td>
                        <td>
                            <span class="badge <?= $s['status']=='aktif'?'bg-success':'bg-secondary' ?>">
                                <?= $s['status']=='aktif'?'Aktif':'Nonaktif' ?>
                            </span>
                        </td>
                        <td><small><?= date('d/m/Y', strtotime($s['created_at'])) ?></small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="edit_staf.php?id=<?= $s['id'] ?>"
                                   class="btn btn-sm btn-outline-warning py-0 px-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($s['id'] != $_SESSION['staf_id']): ?>
                                <a href="hapus_staf.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2"
                                   onclick="return confirm('Yakin hapus staf <?= htmlspecialchars(addslashes($s['nama'])) ?>?\nSemua artikelnya akan ikut terhapus!')"
                                   title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php else: ?>
                                <span class="badge bg-light text-muted ms-1">Anda</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_layout_end.php'; ?>
