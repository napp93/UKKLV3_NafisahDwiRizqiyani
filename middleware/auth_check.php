<?php
// Proteksi Sesi & Hak Akses (3 Role)
// Pemakaian:
//   $base = '';            // '' untuk root, '../' untuk subfolder
//   require 'middleware/auth_check.php';
//   require_login();                          // semua yang sudah login
//   require_role(['super_admin','admin']);    // batasi role tertentu

require_once __DIR__ . '/../config/kuis_db.php';
require_once __DIR__ . '/../includes/helpers.php';

function base_path(): string
{
    global $base;
    return $base ?? '';
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Silakan login terlebih dahulu.');
        header('Location: ' . base_path() . 'auth/login.php');
        exit;
    }
}

function require_role(array $roles): void
{
    require_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        http_response_code(403);
        flash('error', 'Akses ditolak: kamu tidak punya hak untuk halaman itu.');
        header('Location: ' . base_path() . 'index.php');
        exit;
    }
}

function is_staff(): bool
{
    return is_logged_in() && in_array($_SESSION['user']['role'], ['super_admin', 'admin'], true);
}

function is_super_admin(): bool
{
    return is_logged_in() && $_SESSION['user']['role'] === 'super_admin';
}

// Boleh edit/hapus karya? Pemilik, Admin, atau Super Admin.
function can_manage_project(array $project): bool
{
    return is_logged_in()
        && ((int) $project['user_id'] === (int) $_SESSION['user']['id'] || is_staff());
}
