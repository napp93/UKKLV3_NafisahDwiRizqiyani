<?php
// Fungsi bantu: escape, CSRF, flash message, upload gambar, format tanggal

function e($str): string
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        die('Token keamanan tidak valid. Silakan kembali dan coba lagi.');
    }
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_show(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
}

function role_label(string $role): string
{
    return [
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin',
        'siswa'       => 'Siswa',
    ][$role] ?? $role;
}

function bulan_id(int $m): string
{
    return ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
            'Agustus', 'September', 'Oktober', 'November', 'Desember'][$m];
}

function tanggal_id(string $datetime): string
{
    $t = strtotime($datetime);
    return date('j', $t) . ' ' . bulan_id((int) date('n', $t)) . ' ' . date('Y', $t) . ', ' . date('H:i', $t) . ' WIB';
}

/**
 * Upload gambar dengan validasi MIME asli + ukuran.
 * Return: [nama_file|null, pesan_error|null]
 */
function upload_gambar(array $file, string $subfolder = ''): array
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload gambar gagal (kode ' . $file['error'] . ').'];
    }
    if ($file['size'] > MAX_UPLOAD) {
        return [null, 'Ukuran gambar maksimal 2 MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        return [null, 'Format gambar harus JPG, PNG, WEBP, atau GIF.'];
    }

    $dir = UPLOAD_DIR . $subfolder;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return [null, 'Gagal menyimpan gambar di server.'];
    }
    return [$name, null];
}

function hapus_file(string $subfolder, ?string $name): void
{
    if (!$name) {
        return;
    }
    $path = UPLOAD_DIR . $subfolder . basename($name);
    if (is_file($path)) {
        unlink($path);
    }
}

function valid_url(string $url): bool
{
    return $url === '' || (filter_var($url, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $url));
}
