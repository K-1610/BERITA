-- ============================================
-- DATABASE: portal_berita
-- ============================================

CREATE DATABASE IF NOT EXISTS portal_berita CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_berita;

-- Tabel kategori
CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel staf/admin
CREATE TABLE IF NOT EXISTS staf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin','editor','reporter') DEFAULT 'reporter',
    foto VARCHAR(255) DEFAULT NULL,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel artikel
CREATE TABLE IF NOT EXISTS artikel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    konten LONGTEXT NOT NULL,
    ringkasan TEXT,
    gambar VARCHAR(255) DEFAULT NULL,
    id_kategori INT NOT NULL,
    id_staf INT NOT NULL,
    status ENUM('draft','publish','arsip') DEFAULT 'draft',
    views INT DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id) ON DELETE CASCADE,
    FOREIGN KEY (id_staf) REFERENCES staf(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- DATA AWAL
-- ============================================

-- Kategori
INSERT INTO kategori (nama, slug) VALUES
('Nasional', 'nasional'),
('Internasional', 'internasional'),
('Ekonomi', 'ekonomi'),
('Teknologi', 'teknologi'),
('Olahraga', 'olahraga'),
('Hiburan', 'hiburan'),
('Kesehatan', 'kesehatan');

-- Admin default (password: Admin@123)
INSERT INTO staf (nama, username, password, email, role, status) VALUES
('Super Admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@portalberita.com', 'admin', 'aktif'),
('Editor Utama', 'editor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor@portalberita.com', 'editor', 'aktif');

-- Artikel contoh
INSERT INTO artikel (judul, slug, konten, ringkasan, id_kategori, id_staf, status, views, featured) VALUES
('Pemerintah Luncurkan Program Ekonomi Baru untuk Pemulihan Nasional', 'pemerintah-luncurkan-program-ekonomi-baru', '<p>Pemerintah Indonesia resmi meluncurkan program ekonomi baru yang bertujuan untuk mempercepat pemulihan ekonomi nasional pasca pandemi. Program ini mencakup berbagai sektor mulai dari UMKM hingga industri besar.</p><p>Menteri Keuangan dalam konferensi pers menyatakan bahwa program ini akan diimplementasikan secara bertahap mulai kuartal pertama tahun ini dengan anggaran yang telah disiapkan sebesar triliunan rupiah.</p><p>Program tersebut meliputi subsidi bunga pinjaman, kemudahan izin usaha, serta pelatihan tenaga kerja yang akan menyentuh jutaan masyarakat Indonesia di seluruh pelosok negeri.</p>', 'Pemerintah Indonesia resmi meluncurkan program ekonomi baru untuk mempercepat pemulihan ekonomi nasional.', 3, 1, 'publish', 1250, 1),
('Inovasi Teknologi AI Semakin Mengubah Dunia Kerja di Era Modern', 'inovasi-teknologi-ai-mengubah-dunia-kerja', '<p>Kecerdasan buatan (AI) kini semakin merambah berbagai sektor pekerjaan di seluruh dunia. Para ahli memprediksi bahwa dalam dekade mendatang, lebih dari separuh pekerjaan yang ada saat ini akan berubah secara signifikan.</p><p>Namun di sisi lain, AI juga menciptakan lapangan kerja baru yang sebelumnya tidak pernah ada. Profesi seperti AI trainer, prompt engineer, dan data scientist semakin diminati perusahaan-perusahaan global.</p><p>Indonesia sendiri tidak ketinggalan dalam merespons tren ini. Berbagai startup teknologi lokal mulai mengintegrasikan AI dalam layanan mereka untuk meningkatkan efisiensi dan pengalaman pengguna.</p>', 'Kecerdasan buatan semakin mengubah dunia kerja. Temukan bagaimana AI memengaruhi berbagai sektor industri.', 4, 2, 'publish', 980, 1),
('Timnas Indonesia Raih Kemenangan Gemilang di Kualifikasi Piala Dunia', 'timnas-indonesia-raih-kemenangan-kualifikasi', '<p>Timnas Indonesia berhasil meraih kemenangan penting dalam laga kualifikasi Piala Dunia yang berlangsung di Stadion Gelora Bung Karno, Jakarta. Pertandingan berlangsung sengit hingga menit-menit terakhir.</p><p>Gol kemenangan dicetak oleh striker andalan di babak kedua setelah memanfaatkan kemelut di kotak penalti lawan. Suporter yang memadati stadion menyambut gol tersebut dengan antusias luar biasa.</p><p>Pelatih kepala menyatakan kepuasannya atas performa tim dan berjanji akan terus meningkatkan kualitas permainan menjelang laga-laga berikutnya yang tidak kalah krusial.</p>', 'Timnas Indonesia raih kemenangan penting di kualifikasi Piala Dunia. Simak laporan lengkapnya.', 5, 1, 'publish', 2100, 1),
('Tips Menjaga Kesehatan Mental di Tengah Kesibukan Sehari-hari', 'tips-menjaga-kesehatan-mental', '<p>Kesehatan mental merupakan aspek penting yang sering diabaikan di tengah kesibukan modern. Para psikolog merekomendasikan beberapa cara sederhana namun efektif untuk menjaga keseimbangan mental.</p><p>Salah satu kuncinya adalah manajemen waktu yang baik, termasuk menyisihkan waktu untuk diri sendiri dan hobi yang menyenangkan. Istirahat yang cukup juga menjadi faktor krusial dalam menjaga kesehatan mental.</p>', 'Simak tips menjaga kesehatan mental di tengah kesibukan sehari-hari dari para ahli psikologi.', 7, 2, 'publish', 750, 0),
('Festival Budaya Nusantara Hadirkan Ribuan Pengunjung dari Seluruh Indonesia', 'festival-budaya-nusantara-2024', '<p>Festival Budaya Nusantara kembali digelar dan berhasil menarik ribuan pengunjung dari berbagai penjuru Indonesia. Event tahunan ini menjadi ajang pelestarian dan promosi kebudayaan lokal yang kaya.</p><p>Berbagai pertunjukan seni tradisional, kuliner khas daerah, dan pameran kerajinan tangan memenuhi area festival selama tiga hari penyelenggaraan. Antusiasme masyarakat sangat tinggi dalam merespons acara budaya ini.</p>', 'Festival Budaya Nusantara sukses digelar dan menarik ribuan pengunjung dari seluruh Indonesia.', 6, 1, 'publish', 540, 0);
