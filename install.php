<?php
// Jalankan SEKALI lewat browser: http://localhost/etalase-digital-stella/install.php
// Membuat database, tabel, dan 3 akun uji. HAPUS file ini setelah selesai.

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'etalase_stella';

$log = [];
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");
    $log[] = "Database <b>$db</b> siap.";

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('super_admin','admin','siswa') NOT NULL DEFAULT 'siswa',
        bio TEXT NULL,
        foto VARCHAR(255) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $log[] = "Tabel <b>users</b> siap.";

    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nama_project VARCHAR(150) NOT NULL,
        deskripsi TEXT NOT NULL,
        gambar VARCHAR(255) NULL,
        link_project VARCHAR(255) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_projects_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $log[] = "Tabel <b>projects</b> siap.";

    $akun = [
        ['Super Admin Stella', 'superadmin@stella.sch.id', 'super_admin'],
        ['Admin Stella',       'admin@stella.sch.id',      'admin'],
        ['Siswa Stella',       'siswa@stella.sch.id',      'siswa'],
    ];
    $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $ins = $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (?,?,?,?)');
    foreach ($akun as [$nama, $email, $role]) {
        $cek->execute([$email]);
        if (!$cek->fetchColumn()) {
            $ins->execute([$nama, $email, password_hash('password123', PASSWORD_DEFAULT), $role]);
            $log[] = "Akun <b>$email</b> dibuat.";
        } else {
            $log[] = "Akun <b>$email</b> sudah ada, dilewati.";
        }
    }
    $ok = true;
} catch (PDOException $e) {
    $ok = false;
    $log[] = 'Gagal: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Install</title>
<style>
body{font-family:system-ui,sans-serif;background:#f8efe4;color:#5a1f2b;display:grid;place-items:center;min-height:100vh;margin:0}
.box{background:#fffaf3;border:1px solid #e8c9c4;border-radius:18px;padding:32px;max-width:520px;box-shadow:0 20px 50px rgba(110,31,46,.15)}
h1{margin-top:0;font-size:1.4rem}li{margin:6px 0}code{background:#f3dcd8;padding:2px 6px;border-radius:6px}
a{color:#7a1f35;font-weight:700}
</style></head><body><div class="box">
<h1><?= $ok ? '✅ Instalasi selesai' : '❌ Instalasi gagal' ?></h1>
<ul><?php foreach ($log as $l) echo "<li>$l</li>"; ?></ul>
<?php if ($ok): ?>
<p>Password semua akun uji: <code>password123</code></p>
<p><b>Penting:</b> hapus file <code>install.php</code> setelah ini.</p>
<p><a href="auth/login.php">Ke halaman login →</a></p>
<?php endif; ?>
</div></body></html>
