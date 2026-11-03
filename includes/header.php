<?php
// Header + Navbar bersama
// Variabel yang bisa di-set sebelum include:
//   $base       => '' atau '../'
//   $judul      => judul halaman
//   $page_css   => array nama file css tambahan, mis. ['main.css']
//   $body_class => class tambahan untuk <body>
$base       = $base ?? '';
$judul      = $judul ?? 'Etalase Digital Stella';
$page_css   = $page_css ?? [];
$body_class = $body_class ?? '';
$user       = current_user();
$halaman    = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> | Etalase Digital Stella</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.php.css">
    <?php foreach ($page_css as $css): ?>
        <link rel="stylesheet" href="<?= $base . 'assets/css/' . e($css) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= e($body_class) ?>">
<div class="bg-decor" aria-hidden="true">
    <span class="blob b1"></span>
    <span class="blob b2"></span>
    <span class="blob b3"></span>
</div>

<header class="navbar">
    <div class="nav-inner">
        <a class="brand" href="<?= $base ?>index.php">
            <img class="brand-mark" src="<?= $base ?>assets/img/logo%20telkom.jpeg" alt="Logo Telkom">
            <span>Etalase <em>Stella</em></span>
        </a>

        <nav class="nav-links">
            <a href="<?= $base ?>index.php" class="<?= $halaman === 'index.php' && $base === '' ? 'active' : '' ?>">Etalase</a>
            <?php if ($user): ?>
                <?php if (in_array($user['role'], ['super_admin', 'admin'], true)): ?>
                    <a href="<?= $base ?>admin/index.php" class="<?= $halaman === 'index.php' && $base !== '' ? 'active' : '' ?>">Dashboard</a>
                <?php endif; ?>
                <?php if ($user['role'] === 'super_admin'): ?>
                    <a href="<?= $base ?>admin/users.php" class="<?= $halaman === 'users.php' ? 'active' : '' ?>">Kelola User</a>
                <?php endif; ?>
                <a href="<?= $base ?>profil.php" class="<?= $halaman === 'profil.php' ? 'active' : '' ?>">Profil</a>
                <a href="<?= $base ?>auth/logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="<?= $base ?>auth/login.php">Login</a>
                <a href="<?= $base ?>auth/register.php" class="btn-pill">Daftar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
    <?= flash_show() ?>
