# 📰 Portal Berita — Panduan Instalasi

## Teknologi
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: Bootstrap 5.3, Bootstrap Icons
- **Font**: Playfair Display + Source Sans 3 (Google Fonts)

---

## 🚀 Cara Install (XAMPP/Laragon)

### 1. Salin folder proyek
Salin folder `portal_berita` ke:
- **XAMPP**: `C:/xampp/htdocs/portal_berita`
- **Laragon**: `C:/laragon/www/portal_berita`

### 2. Import database
- Buka `phpMyAdmin` → buat database baru bernama **`portal_berita`**
- Klik menu **Import** → pilih file `database.sql`
- Klik **Go / Execute**

### 3. Konfigurasi koneksi
Edit file `includes/koneksi.php`:

```php
define('DB_HOST', 'localhost');   // host database
define('DB_USER', 'root');        // username MySQL
define('DB_PASS', '');            // password MySQL (kosong untuk XAMPP default)
define('DB_NAME', 'portal_berita');
define('SITE_URL', 'http://localhost/portal_berita'); // sesuaikan URL
```

### 4. Buat folder upload (pastikan writable)
```
portal_berita/
└── assets/
    └── img/       ← pastikan folder ini bisa ditulis (chmod 755 di Linux)
```

### 5. Buka browser
- **Website**: `http://localhost/portal_berita/`
- **Admin Panel**: `http://localhost/portal_berita/admin/login.php`

---

## 🔐 Login Default

| Username | Password  | Role     |
|----------|-----------|----------|
| admin    | password  | Admin    |
| editor   | password  | Editor   |

> ⚠️ **PENTING**: Ganti password setelah login pertama kali!

---

## 📁 Struktur File

```
portal_berita/
├── index.php              ← Halaman utama
├── artikel.php            ← Detail artikel
├── kategori.php           ← Halaman kategori
├── cari.php               ← Pencarian
├── database.sql           ← File database
│
├── includes/
│   ├── koneksi.php        ← Konfigurasi database & helper
│   ├── header.php         ← Header + navbar publik
│   └── footer.php         ← Footer publik
│
├── admin/
│   ├── login.php          ← Login dengan CAPTCHA
│   ├── logout.php         ← Logout
│   ├── dashboard.php      ← Dashboard admin
│   ├── artikel_masuk.php  ← Daftar artikel
│   ├── tambah_artikel.php ← Form tambah artikel
│   ├── edit_artikel.php   ← Form edit artikel
│   ├── hapus_artikel.php  ← Proses hapus artikel
│   ├── staf.php           ← Daftar staf
│   ├── tambah_staf.php    ← Form tambah staf
│   ├── hapus_staf.php     ← Proses hapus staf
│   ├── simpan_artikel.php ← Alias proses simpan
│   ├── simpan_staf.php    ← Alias proses simpan staf
│   ├── update_artikel.php ← Alias update artikel
│   ├── proses_login.php   ← Alias proses login
│   └── includes/
│       ├── admin_layout.php      ← Template admin (open)
│       └── admin_layout_end.php  ← Template admin (close)
│
└── assets/
    ├── css/style.css      ← CSS tambahan
    └── img/               ← Folder upload gambar artikel
```

---

## ✨ Fitur Lengkap

### 🌐 Website Publik
- [x] Halaman beranda dengan artikel unggulan (featured)
- [x] Breaking news ticker otomatis
- [x] Kategori berita (7 kategori default)
- [x] Halaman detail artikel dengan share ke sosmed
- [x] Halaman per-kategori dengan pagination
- [x] Pencarian artikel
- [x] Trending/populer berdasarkan views
- [x] Header responsif dengan navbar
- [x] Footer lengkap dengan kontak & berita terbaru

### 🔒 Admin Panel
- [x] Login dengan **CAPTCHA matematika**
- [x] Dashboard dengan statistik lengkap
- [x] Tambah/edit/hapus artikel
- [x] Upload gambar artikel
- [x] Filter artikel (status, kategori, pencarian)
- [x] Pagination daftar artikel
- [x] Manajemen staf (tambah/hapus)
- [x] Role-based access: Admin, Editor, Reporter
- [x] Logout aman

---

## 🔧 Kustomisasi

### Ganti nama portal
Edit `includes/koneksi.php`:
```php
define('SITE_NAME', 'Nama Portal Anda');
```

### Tambah kategori
Langsung melalui phpMyAdmin atau buat halaman admin kategori tambahan.

### Warna tema
Edit variabel CSS di `assets/css/style.css` dan `includes/header.php`:
```css
:root {
    --merah: #C0392B;  /* warna utama */
}
```

---

## 📝 Catatan

- Password di-hash menggunakan `bcrypt` (password_hash PHP)
- Semua input di-sanitasi untuk mencegah SQL Injection & XSS
- Session diregenerasi saat login untuk keamanan
- Reporter hanya bisa mengelola artikelnya sendiri
- Admin bisa mengelola semua konten dan staf
