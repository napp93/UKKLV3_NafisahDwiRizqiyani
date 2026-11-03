<?php
// Form & Proses Login Multi-Role
$base = '../';
require_once __DIR__ . '/../middleware/auth_check.php';

if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nama, email, password, role, foto FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'    => (int) $u['id'],
                'nama'  => $u['nama'],
                'email' => $u['email'],
                'role'  => $u['role'],
            ];
            flash('success', 'Selamat datang kembali, ' . $u['nama'] . '!');
            header('Location: ../index.php');
            exit;
        }
        $error = 'Email atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Etalase Digital Stella</title>
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
        <span class="chip">✦ Karya Digital Siswa</span>
        <h1>Etalase<br><em>Digital Stella</em></h1>
        <p>Tempat semua karya digital siswa dipamerkan. Masuk untuk melihat, menambah, dan mengelola karyamu.</p>
        <div class="showcase-dots"><i></i><i></i><i></i></div>
    </section>

    <section class="auth-card">
        <h2>Selamat datang 👋</h2>
        <p class="sub">Masuk ke akunmu untuk melanjutkan</p>

        <?= flash_show() ?>
        <?php if ($error): ?>
            <div class="alert alert-error shake"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="on">
            <?= csrf_field() ?>
            <div class="field">
                <input type="email" name="email" id="email" placeholder=" " value="<?= e($email) ?>" required>
                <label for="email">Email</label>
            </div>
            <div class="field">
                <input type="password" name="password" id="password" placeholder=" " required>
                <label for="password">Password</label>
                <button type="button" class="toggle-pass" onclick="togglePass(this)" aria-label="Tampilkan password">👁</button>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>

        <p class="switch">Belum punya akun? <a href="register.php">Daftar sebagai siswa</a></p>
    </section>
</main>

<script>
function togglePass(btn) {
    const input = document.getElementById('password');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.style.opacity = show ? '1' : '.6';
}
</script>
</body>
</html>
