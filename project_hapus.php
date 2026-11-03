<?php
// Proses Hapus Project + Gambar (POST + CSRF; pemilik / admin / super admin)
$base = '';
require_once __DIR__ . '/middleware/auth_check.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    flash('error', 'Karya tidak ditemukan.');
} elseif (!can_manage_project($p)) {
    http_response_code(403);
    flash('error', 'Kamu tidak berhak menghapus karya ini.');
} else {
    $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
    hapus_file('', $p['gambar']);
    flash('success', 'Karya berhasil dihapus.');
}

header('Location: index.php');
exit;
