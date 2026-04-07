<?php
// admin/edit_staf.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();
if (!isAdmin()) { header('Location: dashboard.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$staf = $koneksi->query("SELECT * FROM staf WHERE id=$id")->fetch_assoc();
if (!$staf) {
    $_SESSION['msg'] = 'Staf tidak ditemukan.';
    header('Location: staf.php');
    exit;
}

$page_title = 'Edit Staf';
$active_menu = 'staf';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = in_array($_POST['role'] ?? '', ['admin','editor','reporter']) ? $_POST['role'] : 'reporter';
    $status   = in_array($_POST['status'] ?? '', ['aktif','nonaktif']) ? $_POST['status'] : 'aktif';
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    // Validasi
    if (!$nama)     $errors[] = 'Nama wajib diisi.';
    if (!$username) $errors[] = 'Username wajib diisi.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';

    // Cek duplikat (kecuali milik sendiri)
    $uname_esc = $koneksi->real_escape_string($username);
    $email_esc = $koneksi->real_escape_string($email);
    if ($koneksi->query("SELECT id FROM staf WHERE username='$uname_esc' AND id!=$id")->num_rows > 0)
        $errors[] = 'Username sudah digunakan oleh staf lain.';
    if ($koneksi->query("SELECT id FROM staf WHERE email='$email_esc' AND id!=$id")->num_rows > 0)
        $errors[] = 'Email sudah digunakan oleh staf lain.';

    // Password opsional — hanya diubah kalau diisi
    $hash = $staf['password'];
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        } elseif ($password !== $konfirmasi) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
        }
    }

    // Tidak boleh nonaktifkan / downgrade diri sendiri
    if ($id === (int)$_SESSION['staf_id']) {
        if ($status === 'nonaktif') $errors[] = 'Anda tidak dapat menonaktifkan akun Anda sendiri.';
        if ($role !== 'admin')      $errors[] = 'Anda tidak dapat mengubah role akun Anda sendiri.';
    }

    if (empty($errors)) {
        $stmt = $koneksi->prepare("UPDATE staf SET nama=?, username=?, email=?, password=?, role=?, status=? WHERE id=?");
        $stmt->bind_param('ssssssi', $nama, $username, $email, $hash, $role, $status, $id);
        if ($stmt->execute()) {
            // Update session jika edit diri sendiri
            if ($id === (int)$_SESSION['staf_id']) {
                $_SESSION['staf_nama']     = $nama;
                $_SESSION['staf_username'] = $username;
                $_SESSION['staf_role']     = $role;
            }
            $_SESSION['msg'] = 'Data staf "' . htmlspecialchars($nama) . '" berhasil diperbarui!';
            header('Location: staf.php');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan perubahan ke database.';
        }
    }

    // Repopulate untuk tampil ulang form
    $staf = array_merge($staf, compact('nama','username','email','role','status'));
}

require_once 'includes/admin_layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Edit Staf</h5>
        <small class="text-muted">Ubah data akun staf redaksi</small>
    </div>
    <a href="staf.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Staf
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Ada kesalahan:</strong>
    <ul class="mb-0 mt-2">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- FORM UTAMA -->
    <div class="col-lg-8">
        <form method="POST" id="editStafForm">

            <!-- Info Pribadi -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle text-danger"></i>
                    <span class="fw-semibold">Informasi Pribadi</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama" class="form-control"
                                   value="<?= htmlspecialchars($staf['nama']) ?>"
                                   placeholder="Nama lengkap" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Username <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">@</span>
                                <input type="text" name="username" class="form-control"
                                       value="<?= htmlspecialchars($staf['username']) ?>"
                                       placeholder="username" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($staf['email']) ?>"
                                   placeholder="email@contoh.com" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role & Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-danger"></i>
                    <span class="fw-semibold">Role & Status Akun</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" class="form-select" <?= $id===$_SESSION['staf_id']?'disabled':'' ?>>
                                <option value="reporter" <?= $staf['role']==='reporter'?'selected':'' ?>>
                                    🖊️ Reporter
                                </option>
                                <option value="editor" <?= $staf['role']==='editor'?'selected':'' ?>>
                                    ✏️ Editor
                                </option>
                                <option value="admin" <?= $staf['role']==='admin'?'selected':'' ?>>
                                    👑 Admin
                                </option>
                            </select>
                            <?php if ($id===$_SESSION['staf_id']): ?>
                                <!-- kirim nilai asli saat disabled -->
                                <input type="hidden" name="role" value="<?= htmlspecialchars($staf['role']) ?>">
                                <small class="text-muted">Tidak dapat mengubah role akun sendiri.</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Akun</label>
                            <select name="status" class="form-select" <?= $id===$_SESSION['staf_id']?'disabled':'' ?>>
                                <option value="aktif"    <?= $staf['status']==='aktif'   ?'selected':'' ?>>✅ Aktif</option>
                                <option value="nonaktif" <?= $staf['status']==='nonaktif'?'selected':'' ?>>🚫 Nonaktif</option>
                            </select>
                            <?php if ($id===$_SESSION['staf_id']): ?>
                                <input type="hidden" name="status" value="aktif">
                                <small class="text-muted">Tidak dapat menonaktifkan akun sendiri.</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Penjelasan role -->
                    <div class="mt-3 p-3 rounded" style="background:#f8f9fa;border-left:3px solid var(--merah);">
                        <small class="text-muted">
                            <strong>Reporter</strong> — hanya bisa menulis & mengelola artikel milik sendiri.<br>
                            <strong>Editor</strong> — bisa mengelola semua artikel.<br>
                            <strong>Admin</strong> — akses penuh termasuk manajemen staf.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Ganti Password -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-lock text-danger"></i>
                    <span class="fw-semibold">Ganti Password</span>
                    <span class="badge bg-secondary ms-1" style="font-size:0.7rem;">Opsional</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Kosongkan jika tidak ingin mengubah password.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="pwd1" class="form-control"
                                       placeholder="Min. 6 karakter" autocomplete="new-password">
                                <button type="button" class="btn btn-light border"
                                        onclick="togglePwd('pwd1','eye1')">
                                    <i class="bi bi-eye" id="eye1"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" name="konfirmasi" id="pwd2" class="form-control"
                                       placeholder="Ulangi password baru" autocomplete="new-password">
                                <button type="button" class="btn btn-light border"
                                        onclick="togglePwd('pwd2','eye2')">
                                    <i class="bi bi-eye" id="eye2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Password strength indicator -->
                    <div class="mt-2" id="pwdStrengthWrap" style="display:none;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:5px;">
                                <div class="progress-bar" id="pwdBar" style="width:0%;transition:width 0.3s;"></div>
                            </div>
                            <small id="pwdLabel" class="text-muted" style="width:60px;text-align:right;font-size:0.75rem;"></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-merah px-4">
                    <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                </button>
                <a href="staf.php" class="btn btn-outline-secondary px-4">Batal</a>
                <?php if ($id !== (int)$_SESSION['staf_id']): ?>
                <a href="hapus_staf.php?id=<?= $id ?>" class="btn btn-outline-danger ms-auto px-4"
                   onclick="return confirm('Yakin hapus staf <?= htmlspecialchars(addslashes($staf['nama'])) ?>?\nSemua artikelnya akan ikut terhapus!')">
                    <i class="bi bi-trash me-2"></i>Hapus Staf
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- SIDEBAR INFO -->
    <div class="col-lg-4">
        <!-- Kartu Profil -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-4">
                <div class="mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;background:var(--merah);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.8rem;font-weight:700;font-family:'Playfair Display',serif;">
                    <?= strtoupper(substr($staf['nama'], 0, 1)) ?>
                </div>
                <h6 class="fw-bold mb-0" id="previewNama"><?= htmlspecialchars($staf['nama']) ?></h6>
                <small class="text-muted" id="previewUsername">@<?= htmlspecialchars($staf['username']) ?></small>
                <div class="mt-2">
                    <?php
                    $role_colors = ['admin'=>'danger','editor'=>'primary','reporter'=>'secondary'];
                    ?>
                    <span class="badge bg-<?= $role_colors[$staf['role']] ?>" id="previewRole">
                        <?= ucfirst($staf['role']) ?>
                    </span>
                    <span class="badge <?= $staf['status']==='aktif'?'bg-success':'bg-secondary' ?>" id="previewStatus">
                        <?= ucfirst($staf['status']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistik Staf -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-semibold">
                <i class="bi bi-bar-chart me-2 text-danger"></i>Statistik
            </div>
            <div class="card-body p-0">
                <?php
                $jml_total   = $koneksi->query("SELECT COUNT(*) FROM artikel WHERE id_staf=$id")->fetch_row()[0];
                $jml_publish = $koneksi->query("SELECT COUNT(*) FROM artikel WHERE id_staf=$id AND status='publish'")->fetch_row()[0];
                $jml_draft   = $koneksi->query("SELECT COUNT(*) FROM artikel WHERE id_staf=$id AND status='draft'")->fetch_row()[0];
                $total_views = $koneksi->query("SELECT COALESCE(SUM(views),0) FROM artikel WHERE id_staf=$id AND status='publish'")->fetch_row()[0];
                ?>
                <div class="d-flex justify-content-between px-3 py-2 border-bottom">
                    <span class="text-muted small">Total Artikel</span>
                    <span class="fw-bold"><?= $jml_total ?></span>
                </div>
                <div class="d-flex justify-content-between px-3 py-2 border-bottom">
                    <span class="text-muted small">Dipublikasi</span>
                    <span class="fw-bold text-success"><?= $jml_publish ?></span>
                </div>
                <div class="d-flex justify-content-between px-3 py-2 border-bottom">
                    <span class="text-muted small">Draft</span>
                    <span class="fw-bold text-warning"><?= $jml_draft ?></span>
                </div>
                <div class="d-flex justify-content-between px-3 py-2">
                    <span class="text-muted small">Total Tayangan</span>
                    <span class="fw-bold text-danger"><?= formatViews($total_views) ?></span>
                </div>
            </div>
        </div>

        <!-- Info Akun -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 fw-semibold">
                <i class="bi bi-clock-history me-2 text-danger"></i>Info Akun
            </div>
            <div class="card-body p-0">
                <div class="px-3 py-2 border-bottom">
                    <small class="text-muted d-block">Bergabung sejak</small>
                    <span class="small fw-semibold"><?= date('d F Y', strtotime($staf['created_at'])) ?></span>
                </div>
                <div class="px-3 py-2">
                    <small class="text-muted d-block">ID Staf</small>
                    <code class="small">#<?= $staf['id'] ?></code>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle show/hide password
function togglePwd(id, iconId) {
    const p = document.getElementById(id);
    const i = document.getElementById(iconId);
    p.type = p.type === 'password' ? 'text' : 'password';
    i.className = p.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Live preview nama & username di kartu profil
document.querySelector('[name="nama"]').addEventListener('input', function () {
    document.getElementById('previewNama').textContent = this.value || '—';
});
document.querySelector('[name="username"]').addEventListener('input', function () {
    document.getElementById('previewUsername').textContent = '@' + (this.value || '...');
});

// Password strength meter
const pwd1 = document.getElementById('pwd1');
const pwdBar = document.getElementById('pwdBar');
const pwdLabel = document.getElementById('pwdLabel');
const pwdWrap = document.getElementById('pwdStrengthWrap');

pwd1.addEventListener('input', function () {
    const v = this.value;
    pwdWrap.style.display = v ? 'block' : 'none';
    let score = 0;
    if (v.length >= 6)  score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    const levels = [
        { pct: 20,  color: '#dc3545', label: 'Lemah'   },
        { pct: 40,  color: '#fd7e14', label: 'Kurang'  },
        { pct: 60,  color: '#ffc107', label: 'Sedang'  },
        { pct: 80,  color: '#20c997', label: 'Kuat'    },
        { pct: 100, color: '#198754', label: 'Sangat Kuat' },
    ];
    const lvl = levels[Math.min(score - 1, 4)] || levels[0];
    pwdBar.style.width = lvl.pct + '%';
    pwdBar.style.background = lvl.color;
    pwdLabel.textContent = lvl.label;
    pwdLabel.style.color = lvl.color;
});

// Konfirmasi match indicator
document.getElementById('pwd2').addEventListener('input', function () {
    const match = this.value === pwd1.value;
    this.style.borderColor = this.value ? (match ? '#198754' : '#dc3545') : '';
});

// Client-side confirm sebelum submit
document.getElementById('editStafForm').addEventListener('submit', function (e) {
    const nama = document.querySelector('[name="nama"]').value.trim();
    if (!nama) { e.preventDefault(); alert('Nama tidak boleh kosong.'); }
});
</script>

<?php require_once 'includes/admin_layout_end.php'; ?>
