<?php
// admin/login.php
session_start();
require_once '../includes/koneksi.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha_input = trim($_POST['captcha'] ?? '');

    // Validasi captcha
    if ((int)$captcha_input !== (int)($_SESSION['captcha_result'] ?? -999)) {
        $error = 'Jawaban captcha salah. Silakan coba lagi.';
    } else {
        $stmt = $koneksi->prepare("SELECT * FROM staf WHERE username=? AND status='aktif' LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $staf = $stmt->get_result()->fetch_assoc();

        if ($staf && password_verify($password, $staf['password'])) {
            $_SESSION['staf_id'] = $staf['id'];
            $_SESSION['staf_nama'] = $staf['nama'];
            $_SESSION['staf_username'] = $staf['username'];
            $_SESSION['staf_role'] = $staf['role'];
            session_regenerate_id(true);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
    // Refresh captcha setelah attempt
    unset($_SESSION['captcha_result']);
}

// Generate captcha baru
$captcha_soal = generateCaptcha();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Source Sans 3', sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .login-brand {
            background: linear-gradient(135deg, #C0392B, #96281B);
            padding: 40px 30px;
            color: #fff;
            text-align: center;
        }
        .login-form { padding: 40px 35px; }
        .form-control:focus { border-color: #C0392B; box-shadow: 0 0 0 0.2rem rgba(192,57,43,0.15); }
        .btn-login { background: #C0392B; border: none; color: #fff; font-weight: 600; padding: 12px; border-radius: 8px; transition: all 0.2s; }
        .btn-login:hover { background: #96281B; color: #fff; transform: translateY(-1px); }
        .captcha-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 4px;
            text-align: center;
            color: #333;
            font-family: 'Courier New', monospace;
            user-select: none;
        }
        .bg-noise {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E");
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7 col-xl-6">
            <div class="login-card">
                <div class="login-brand">
                    <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;margin-bottom:6px;">
                        <i class="bi bi-newspaper me-2"></i><?= SITE_NAME ?>
                    </div>
                    <p class="mb-0 opacity-75">Panel Administrasi</p>
                    <div class="mt-3">
                        <span class="badge bg-white text-danger">Admin</span>
                        <span class="badge bg-white text-danger ms-1">Editor</span>
                        <span class="badge bg-white text-danger ms-1">Reporter</span>
                    </div>
                </div>
                <div class="login-form">
                    <h5 class="fw-bold mb-1">Selamat Datang</h5>
                    <p class="text-muted small mb-4">Masuk ke panel admin untuk mengelola konten</p>

                    <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center py-2">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-danger"></i></span>
                                <input type="text" name="username" class="form-control border-start-0"
                                       placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-danger"></i></span>
                                <input type="password" name="password" id="pwd" class="form-control border-start-0" placeholder="Masukkan password" required>
                                <button type="button" class="btn btn-light border" onclick="togglePwd()"><i class="bi bi-eye" id="eyeIcon"></i></button>
                            </div>
                        </div>

                        <!-- CAPTCHA -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Verifikasi Keamanan</label>
                            <p class="text-muted small mb-2">Selesaikan perhitungan berikut:</p>
                            <div class="captcha-box mb-2"><?= $captcha_soal ?> = ?</div>
                            <input type="number" name="captcha" class="form-control" placeholder="Jawaban kamu..." required min="0">
                        </div>

                        <button type="submit" class="btn btn-login w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Dashboard
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="../index.php" class="text-danger text-decoration-none small">
                            <i class="bi bi-arrow-left me-1"></i>Kembali ke Portal Berita
                        </a>
                    </div>

                    <hr class="my-3">
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd() {
    const p = document.getElementById('pwd');
    const i = document.getElementById('eyeIcon');
    p.type = p.type === 'password' ? 'text' : 'password';
    i.className = p.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
