# Etalase Digital Stella

Aplikasi etalase karya digital siswa dengan 3 role (Super Admin, Admin, Siswa). Dibuat dengan PHP Native + MySQL (PDO).

## Kebutuhan
- PHP 8.0 atau lebih baru (ekstensi `pdo_mysql` dan `fileinfo` aktif)
- MySQL / MariaDB
- XAMPP, Laragon, atau sejenisnya

## Instalasi

1. Salin folder `etalase-digital-stella` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Nyalakan Apache dan MySQL.
3. Siapkan database dengan **salah satu** cara:
   - **Otomatis:** buka `http://localhost/etalase-digital-stella/install.php`
   - **Manual:** buka phpMyAdmin, tab **Import**, pilih file `database.sql`
4. Cek `config/kuis_db.php`. Sesuaikan `DB_USER` dan `DB_PASS` bila MySQL kamu memakai password.
5. Buka `http://localhost/etalase-digital-stella/auth/login.php`
6. Hapus `install.php` setelah instalasi selesai.

## Akun uji

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@stella.sch.id | password123 |
| Admin | admin@stella.sch.id | password123 |
| Siswa | siswa@stella.sch.id | password123 |

Ganti password akun ini lewat halaman Profil sebelum dipakai sungguhan.

## Hak akses

| Fitur | Super Admin | Admin | Siswa |
|-------|:-:|:-:|:-:|
| Kelola user (tambah, edit, hapus) | ✅ | ❌ | ❌ |
| Lihat profil admin dan siswa | ✅ | ❌ | ❌ |
| Edit/hapus karya siapa pun | ✅ | ✅ | ❌ |
| Lihat semua karya | ✅ | ✅ | ✅ |
| Tambah karya, edit/hapus karya sendiri | ✅ | ✅ | ✅ |
| Ubah profil sendiri | ✅ | ✅ | ✅ |

## Struktur folder

```
etalase-digital-stella/
├── config/kuis_db.php        # Koneksi PDO
├── middleware/auth_check.php # Proteksi sesi dan role
├── includes/                 # header, footer, fungsi bantu
├── auth/                     # login, register, logout
├── admin/                    # dashboard dan kelola user
├── assets/css/               # CSS modular per halaman
├── uploads/                  # gambar karya (profil/ untuk foto profil)
├── index.php                 # Etalase
├── project_detail.php        # Detail karya
├── project_tambah.php / project_edit.php / project_hapus.php
├── profil.php                # Profil akun
├── database.sql              # Skema + akun uji
└── install.php               # Installer sekali jalan
```

## Skema database

Database `etalase_stella` berisi dua tabel:

- `users`: id, nama, email (unik), password (hash), role, bio, foto, created_at
- `projects`: id, user_id (FK ke users, hapus berantai), nama_project, deskripsi, gambar, link_project, created_at

## Troubleshooting

- **Koneksi database gagal:** pastikan MySQL menyala dan database sudah dibuat (langkah 3).
- **Gambar tidak bisa diunggah:** pastikan folder `uploads/` dan `uploads/profil/` bisa ditulis. Di Linux jalankan `chmod -R 775 uploads`.
- **Upload ditolak:** batas ukuran 2 MB, format JPG/PNG/WEBP/GIF. Naikkan `upload_max_filesize` di `php.ini` bila perlu.
