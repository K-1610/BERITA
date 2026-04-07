<?php
// admin/tambah_artikel.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();

$page_title = 'Tambah Artikel';
$active_menu = 'tambah_artikel';

$errors = [];
$data = ['judul'=>'','ringkasan'=>'','konten'=>'','id_kategori'=>'','status'=>'draft','featured'=>0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['judul'] = trim($_POST['judul'] ?? '');
    $data['ringkasan'] = trim($_POST['ringkasan'] ?? '');
    $data['konten'] = trim($_POST['konten'] ?? '');
    $data['id_kategori'] = (int)($_POST['id_kategori'] ?? 0);
    $data['status'] = in_array($_POST['status']??'',['draft','publish','arsip']) ? $_POST['status'] : 'draft';
    $data['featured'] = isset($_POST['featured']) ? 1 : 0;

    if (!$data['judul']) $errors[] = 'Judul wajib diisi.';
    if (!$data['konten']) $errors[] = 'Konten wajib diisi.';
    if (!$data['id_kategori']) $errors[] = 'Kategori wajib dipilih.';

    // Upload gambar
    $gambar = '';
    if (!empty($_FILES['gambar']['name'])) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Format gambar harus JPG, PNG, atau WEBP.';
        } elseif ($_FILES['gambar']['size'] > 3*1024*1024) {
            $errors[] = 'Ukuran gambar maksimal 3MB.';
        } else {
            $gambar = uniqid('img_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/img/' . $gambar)) {
                $errors[] = 'Gagal upload gambar. Pastikan folder assets/img/ dapat ditulis.';
                $gambar = '';
            }
        }
    }

    if (empty($errors)) {
        $slug = slugify($data['judul']);
        // Pastikan slug unik
        $orig = $slug;
        $i = 1;
        while ($koneksi->query("SELECT id FROM artikel WHERE slug='$slug'")->num_rows > 0) {
            $slug = $orig . '-' . $i++;
        }

        $stmt = $koneksi->prepare("INSERT INTO artikel (judul, slug, konten, ringkasan, gambar, id_kategori, id_staf, status, featured) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssiisi', $data['judul'], $slug, $data['konten'], $data['ringkasan'], $gambar, $data['id_kategori'], $_SESSION['staf_id'], $data['status'], $data['featured']);

        if ($stmt->execute()) {
            $_SESSION['msg'] = 'Artikel berhasil ditambahkan!';
            header('Location: artikel_masuk.php');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan artikel.';
        }
    }
}

$kategori_all = $koneksi->query("SELECT * FROM kategori ORDER BY nama");
require_once 'includes/admin_layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Tambah Artikel Baru</h5>
    <a href="artikel_masuk.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <strong><i class="bi bi-exclamation-triangle me-2"></i>Ada kesalahan:</strong>
    <ul class="mb-0 mt-1"><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold py-3">Konten Artikel</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Artikel <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control" placeholder="Tulis judul artikel yang menarik..." value="<?= htmlspecialchars($data['judul']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ringkasan / Lead</label>
                        <textarea name="ringkasan" class="form-control" rows="3" placeholder="Ringkasan singkat artikel (tampil di halaman utama dan meta deskripsi)..."><?= htmlspecialchars($data['ringkasan']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konten Artikel <span class="text-danger">*</span></label>
                        <textarea name="konten" id="konten" class="form-control" rows="18" placeholder="Tulis konten artikel di sini... (HTML diperbolehkan)"><?= htmlspecialchars($data['konten']) ?></textarea>
                        <small class="text-muted">Anda bisa menggunakan tag HTML seperti &lt;p&gt;, &lt;h2&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;</small>
                    </div>
                </div>
            </div>

            <!-- Gambar -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold py-3">Gambar Artikel</div>
                <div class="card-body">
                    <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <small class="text-muted">Format: JPG, PNG, WEBP. Maks 3MB. Disarankan ukuran 1200x630px.</small>
                    <div id="preview" class="mt-3" style="display:none;">
                        <img id="previewImg" src="" alt="" class="img-fluid rounded" style="max-height:200px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Publikasi -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold py-3">Publikasi</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= $data['status']=='draft'?'selected':'' ?>>📝 Draft</option>
                            <option value="publish" <?= $data['status']=='publish'?'selected':'' ?>>✅ Publish</option>
                            <option value="arsip" <?= $data['status']=='arsip'?'selected':'' ?>>📦 Arsip</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1" <?= $data['featured']?'checked':'' ?>>
                        <label class="form-check-label fw-semibold" for="featured">⭐ Tampilkan di Unggulan</label>
                        <div class="form-text">Artikel akan ditampilkan di halaman utama.</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-merah"><i class="bi bi-send me-2"></i>Simpan Artikel</button>
                        <a href="artikel_masuk.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </div>
            </div>

            <!-- Kategori -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold py-3">Kategori <span class="text-danger">*</span></div>
                <div class="card-body">
                    <?php while ($k = $kategori_all->fetch_assoc()): ?>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="id_kategori" id="kat_<?= $k['id'] ?>" value="<?= $k['id'] ?>" <?= $data['id_kategori']==$k['id']?'checked':'' ?>>
                        <label class="form-check-label" for="kat_<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></label>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('preview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'includes/admin_layout_end.php'; ?>
