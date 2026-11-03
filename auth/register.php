<?php
// Registrasi Akun Siswa (role dikunci 'siswa' di sisi server)
$base = '../';
require_once __DIR__ . '/../middleware/auth_check.php';

if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
$nama = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi'] ?? '';

    if (mb_strlen($nama) < 3)                       $errors[] = 'Nama minimal 3 karakter.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($password) < 6)                      $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $konfirm)                     $errors[] = 'Konfirmasi password tidak sama.';

    if (!$errors) {
        $cek = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $errors[] = 'Email sudah terdaftar.';
        }
    }

    if (!$errors) {
        $ins = $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, "siswa")');
        $ins->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT)]);
        flash('success', 'Registrasi berhasil! Silakan login.');
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar | Etalase Digital Stella</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.php.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-body">

<div class="auth-scene" aria-hidden="true">
    <span class="orb o1"></span><span class="orb o2"></span><span class="orb o3"></span>
    <span class="orb o4"></span><span class="orb o5"></span>
    <span class="spark s1">✦</span><span class="spark s2">✦</span><span class="spark s3">✦</span>
    <span class="spark s4">✧</span><span class="spark s5">✧</span>
</div>

<main class="auth-wrap">
    <section class="auth-showcase">
        <span class="chip">✦ Gabung sekarang</span>
        <h1>Pamerkan<br><em>karyamu</em></h1>
        <p>Buat akun siswa, unggah proyek digitalmu, dan biarkan semua orang melihat hasil kerja kerasmu.</p>
        <div class="showcase-dots"><i></i><i></i><i></i></div>
    </section>

    <section class="auth-card">
        <h2>Buat akun siswa</h2>
        <p class="sub">Isi data di bawah untuk mendaftar</p>

        <?php if ($errors): ?>
            <div class="alert alert-error shake">
                <?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <div class="field">
                <input type="text" name="nama" id="nama" placeholder=" " value="<?= e($nama) ?>" required>
                <label for="nama">Nama lengkap</label>
            </div>
            <div class="field">
                <input type="email" name="email" id="email" placeholder=" " value="<?= e($email) ?>" required>
                <label for="email">Email</label>
            </div>
            <div class="field">
                <input type="password" name="password" id="password" placeholder=" " required>
                <label for="password">Password (min. 6 karakter)</label>
            </div>
            <div class="field">
                <input type="password" name="konfirmasi" id="konfirmasi" placeholder=" " required>
                <label for="konfirmasi">Konfirmasi password</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Daftar</button>
        </form>

        <p class="switch">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    </section>
</main>
</body>
</html>
