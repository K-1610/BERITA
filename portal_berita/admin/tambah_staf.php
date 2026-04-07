<?php
// admin/tambah_staf.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();
if (!isAdmin()) { header('Location: dashboard.php'); exit; }

$page_title = 'Tambah Staf';
$active_menu = 'tambah_staf';
$errors = [];
$data = ['nama'=>'','username'=>'','email'=>'','role'=>'reporter'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama']     = trim($_POST['nama'] ?? '');
    $data['username'] = trim($_POST['username'] ?? '');
    $data['email']    = trim($_POST['email'] ?? '');
    $data['role']     = in_array($_POST['role']??'',['admin','editor','reporter']) ? $_POST['role'] : 'reporter';
    $password         = $_POST['password'] ?? '';
    $konfirmasi       = $_POST['konfirmasi'] ?? '';

    if (!$data['nama'])     $errors[] = 'Nama wajib diisi.';
    if (!$data['username']) $errors[] = 'Username wajib diisi.';
    if (!$data['email'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $konfirmasi) $errors[] = 'Konfirmasi password tidak cocok.';

    // Cek duplikat
    $uname = $koneksi->real_escape_string($data['username']);
    $email = $koneksi->real_escape_string($data['email']);
    if ($koneksi->query("SELECT id FROM staf WHERE username='$uname'")->num_rows > 0)
        $errors[] = 'Username sudah digunakan.';
    if ($koneksi->query("SELECT id FROM staf WHERE email='$email'")->num_rows > 0)
        $errors[] = 'Email sudah terdaftar.';

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $koneksi->prepare("INSERT INTO staf (nama, username, password, email, role, status) VALUES (?,?,?,?,?,'aktif')");
        $stmt->bind_param('sssss', $data['nama'], $data['username'], $hash, $data['email'], $data['role']);
        if ($stmt->execute()) {
            $_SESSION['msg'] = 'Staf baru berhasil ditambahkan!';
            header('Location: staf.php');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan data staf.';
        }
    }
}

require_once 'includes/admin_layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Tambah Staf Baru</h5>
    <a href="staf.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <strong><i class="bi bi-exclamation-triangle me-2"></i>Ada kesalahan:</strong>
    <ul class="mb-0 mt-1"><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold py-3">Informasi Staf</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" placeholder="Nama lengkap staf" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">@</span>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data['username']) ?>" placeholder="username_unik" required>
                        </div>
                        <small class="text-muted">Hanya huruf kecil, angka, dan underscore.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" placeholder="email@contoh.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select">
                            <option value="reporter" <?= $data['role']=='reporter'?'selected':'' ?>>🖊️ Reporter — Tulis & kelola artikel sendiri</option>
                            <option value="editor"   <?= $data['role']=='editor'?'selected':'' ?>>✏️ Editor — Kelola semua artikel</option>
                            <option value="admin"    <?= $data['role']=='admin'?'selected':'' ?>>👑 Admin — Akses penuh</option>
                        </select>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="pwd1" class="form-control" placeholder="Min. 6 karakter" required>
                            <button type="button" class="btn btn-light border" onclick="togglePwd('pwd1','eye1')"><i class="bi bi-eye" id="eye1"></i></button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="konfirmasi" id="pwd2" class="form-control" placeholder="Ulangi password" required>
                            <button type="button" class="btn btn-light border" onclick="togglePwd('pwd2','eye2')"><i class="bi bi-eye" id="eye2"></i></button>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-merah"><i class="bi bi-person-check me-2"></i>Simpan Staf</button>
                        <a href="staf.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePwd(id, iconId) {
    const p = document.getElementById(id);
    const i = document.getElementById(iconId);
    p.type = p.type === 'password' ? 'text' : 'password';
    i.className = p.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>

<?php require_once 'includes/admin_layout_end.php'; ?>
