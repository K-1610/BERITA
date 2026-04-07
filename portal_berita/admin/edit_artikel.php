<?php
// admin/edit_artikel.php
session_start();
require_once '../includes/koneksi.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$art = $koneksi->query("SELECT * FROM artikel WHERE id=$id")->fetch_assoc();
if (!$art) { header('Location: artikel_masuk.php'); exit; }

// Reporter hanya bisa edit artikelnya sendiri
if ($_SESSION['staf_role'] === 'reporter' && $art['id_staf'] != $_SESSION['staf_id']) {
    $_SESSION['msg'] = 'Anda tidak memiliki akses untuk mengedit artikel ini.';
    header('Location: artikel_masuk.php');
    exit;
}

$page_title = 'Edit Artikel';
$active_menu = 'artikel_masuk';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $ringkasan = trim($_POST['ringkasan'] ?? '');
    $konten = trim($_POST['konten'] ?? '');
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $status = in_array($_POST['status']??'',['draft','publish','arsip']) ? $_POST['status'] : 'draft';
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (!$judul) $errors[] = 'Judul wajib diisi.';
    if (!$konten) $errors[] = 'Konten wajib diisi.';
    if (!$id_kategori) $errors[] = 'Kategori wajib dipilih.';

    $gambar = $art['gambar'];
    if (!empty($_FILES['gambar']['name'])) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Format gambar harus JPG, PNG, atau WEBP.';
        } else {
            $new_gambar = uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/img/' . $new_gambar)) {
                // Hapus gambar lama
                if ($gambar && file_exists('../assets/img/' . $gambar)) unlink('../assets/img/' . $gambar);
                $gambar = $new_gambar;
            }
        }
    }

    if (empty($errors)) {
        $slug = slugify($judul);
        $orig = $slug; $i = 1;
        while ($koneksi->query("SELECT id FROM artikel WHERE slug='$slug' AND id!=$id")->num_rows > 0) {
            $slug = $orig . '-' . $i++;
        }

        $stmt = $koneksi->prepare("UPDATE artikel SET judul=?, slug=?, konten=?, ringkasan=?, gambar=?, id_kategori=?, status=?, featured=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param('sssssisii', $judul, $slug, $konten, $ringkasan, $gambar, $id_kategori, $status, $featured, $id);

        if ($stmt->execute()) {
            $_SESSION['msg'] = 'Artikel berhasil diperbarui!';
            header('Location: artikel_masuk.php');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan perubahan.';
        }
    }
    // Refresh $art untuk repopulate form
    $art = array_merge($art, ['judul'=>$judul,'ringkasan'=>$ringkasan,'konten'=>$konten,'id_kategori'=>$id_kategori,'status'=>$status,'featured'=>$featured]);
}

$kategori_all = $koneksi->query("SELECT * FROM kategori ORDER BY nama");
require_once 'includes/admin_layout.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Edit Artikel</h5>
    <div class="d-flex gap-2">
        <a href="../artikel.php?slug=<?= $art['slug'] ?>" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-eye me-1"></i>Lihat</a>
        <a href="artikel_masuk.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
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
                        <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($art['judul']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ringkasan / Lead</label>
                        <textarea name="ringkasan" class="form-control" rows="3"><?= htmlspecialchars($art['ringkasan'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konten Artikel <span class="text-danger">*</span></label>
                        <textarea name="konten" class="form-control" rows="18"><?= htmlspecialchars($art['konten']) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold py-3">Gambar Artikel</div>
                <div class="card-body">
                    <?php if ($art['gambar'] && file_exists('../assets/img/'.$art['gambar'])): ?>
                    <div class="mb-3">
                        <p class="text-muted small mb-1">Gambar saat ini:</p>
                        <img src="../assets/img/<?= $art['gambar'] ?>" alt="" class="img-fluid rounded" style="max-height:150px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar.</small>
                    <div id="preview" class="mt-2" style="display:none;">
                        <img id="previewImg" src="" alt="" class="img-fluid rounded" style="max-height:150px;">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold py-3">Publikasi</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= $art['status']=='draft'?'selected':'' ?>>📝 Draft</option>
                            <option value="publish" <?= $art['status']=='publish'?'selected':'' ?>>✅ Publish</option>
                            <option value="arsip" <?= $art['status']=='arsip'?'selected':'' ?>>📦 Arsip</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1" <?= $art['featured']?'checked':'' ?>>
                        <label class="form-check-label fw-semibold" for="featured">⭐ Tampilkan di Unggulan</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-merah"><i class="bi bi-check-circle me-2"></i>Update Artikel</button>
                        <a href="hapus_artikel.php?id=<?= $id ?>" class="btn btn-outline-danger" onclick="return confirm('Yakin hapus artikel ini?')"><i class="bi bi-trash me-2"></i>Hapus Artikel</a>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold py-3">Kategori <span class="text-danger">*</span></div>
                <div class="card-body">
                    <?php while ($k = $kategori_all->fetch_assoc()): ?>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="id_kategori" id="kat_<?= $k['id'] ?>" value="<?= $k['id'] ?>" <?= $art['id_kategori']==$k['id']?'checked':'' ?>>
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
