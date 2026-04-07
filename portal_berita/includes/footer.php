<?php
// includes/footer.php
?>
<footer class="mt-5" style="background: #1a1a1a; color: #ccc;">
    <div class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div style="font-family:'Playfair Display',serif; font-size:1.8rem; font-weight:900; color:#C0392B;">
                        <i class="bi bi-newspaper me-1"></i><?= SITE_NAME ?>
                    </div>
                    <p class="mt-3 small">Portal berita terpercaya yang menyajikan informasi terkini, akurat, dan berimbang dari seluruh penjuru Indonesia.</p>
                    <div class="mt-3">
                        <a href="#" class="text-white me-3 fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3 fs-5"><i class="bi bi-twitter-x"></i></a>
                        <a href="https://www.instagram.com/key_sixteen_?igsh=eHU0Z215ajhrd2F6" class="text-white me-3 fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white me-3 fs-5"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white fw-bold mb-3 text-uppercase" style="font-size:0.85rem;letter-spacing:1px;">Kategori</h6>
                    <ul class="list-unstyled small">
                        <?php
                        global $koneksi;
                        $kat_footer = $koneksi->query("SELECT nama, slug FROM kategori ORDER BY nama LIMIT 7");
                        while ($k = $kat_footer->fetch_assoc()):
                        ?>
                        <li class="mb-1"><a href="<?= SITE_URL ?>/kategori.php?slug=<?= $k['slug'] ?>" style="color:#bbb;text-decoration:none;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#bbb'">
                            <i class="bi bi-chevron-right me-1" style="font-size:0.7rem;color:#C0392B;"></i><?= htmlspecialchars($k['nama']) ?>
                        </a></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white fw-bold mb-3 text-uppercase" style="font-size:0.85rem;letter-spacing:1px;">Berita Terbaru</h6>
                    <?php
                    $terbaru_footer = $koneksi->query("SELECT judul, slug, created_at FROM artikel WHERE status='publish' ORDER BY created_at DESC LIMIT 4");
                    while ($tf = $terbaru_footer->fetch_assoc()):
                    ?>
                    <div class="mb-2 pb-2 border-bottom border-secondary">
                        <a href="<?= SITE_URL ?>/artikel.php?slug=<?= $tf['slug'] ?>" style="color:#ccc;text-decoration:none;font-size:0.85rem;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#ccc'">
                            <?= htmlspecialchars(mb_substr($tf['judul'], 0, 60)) ?>...
                        </a>
                        <div style="font-size:0.75rem;color:#888;" class="mt-1"><i class="bi bi-clock me-1"></i><?= timeAgo($tf['created_at']) ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white fw-bold mb-3 text-uppercase" style="font-size:0.85rem;letter-spacing:1px;">Kontak</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><i class="bi bi-geo-alt-fill me-2 text-danger"></i>Jl. Suropati, Tegalgubug, Arjawinangun, Cirebon</li>
                        <li class="mb-2"><i class="bi bi-telephone-fill me-2 text-danger"></i>(021) 123-4567</li>
                        <li class="mb-2"><i class="bi bi-envelope-fill me-2 text-danger"></i>redaksi@portalberita.com</li>
                        <li class="mb-2"><i class="bi bi-clock-fill me-2 text-danger"></i>Senin - Jumat: 08.00 - 17.00</li>
                    </ul>
                    <div class="mt-3">
                        <span class="badge bg-danger me-1">Tentang Kami</span>
                        <span class="badge bg-secondary me-1">Kebijakan Privasi</span>
                        <span class="badge bg-secondary">Kontak</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div style="background:#111; padding: 14px 0;" class="text-center small">
        <div class="container">
            &copy; <?= date('Y') ?> <strong style="color:#C0392B;"><?= SITE_NAME ?></strong>. Semua hak dilindungi. &nbsp;|&nbsp;
            Dibuat dengan <i class="bi bi-heart-fill text-danger"></i> untuk Indonesia
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto close alerts
document.querySelectorAll('.alert').forEach(function(alert) {
    setTimeout(function() {
        alert.classList.add('fade');
        setTimeout(function() { alert.remove(); }, 300);
    }, 4000);
});
</script>
</body>
</html>
