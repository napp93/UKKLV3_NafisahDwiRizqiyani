<?php
// Proses Logout Sesi
require_once __DIR__ . '/../config/kuis_db.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

session_start();
$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Kamu sudah logout. Sampai jumpa!'];
header('Location: login.php');
exit;
