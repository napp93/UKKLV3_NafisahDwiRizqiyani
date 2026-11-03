<?php
// Profil akun.
//  - Semua role: lihat & ubah profil SENDIRI (nama, bio, foto, password)
//  - Super Admin: boleh MELIHAT profil siswa & admin (read-only) lewat ?id=
$base = '';
require_once __DIR__ . '/middleware/auth_check.php';
require_login();

$me   = current_user();
$id   = (int) ($_GET['id'] ?? $me['id']);
$self = ($id === (int) $me['id']);

if (!$self && !is_super_admin()) {
    http_response_code(403);
    flash('error', 'Kamu hanya bisa melihat profilmu sendiri.');
    header('Location: profil.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nama, email, role, bio, foto, password, created_at FROM users WHERE id = ?');
$stmt->execute([$id]);
$u = $stmt->fetch();
if (!$u) {
    flash('error', 'Pengguna tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$errors = [];

if ($self && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nama = trim($_POST['nama'] ?? '');
    $bio  = trim($_POST['bio'] ?? '');
    $passLama = $_POST['password_lama'] ?? '';
    $passBaru = $_POST['password_baru'] ?? '';
    $passKonf = $_POST['password_konfirmasi'] ?? '';

    if (mb_strlen($nama) < 3)   $errors[] = 'Nama minimal 3 karakter.';
    if (mb_strlen($bio) > 300)  $errors[] = 'Bio maksimal 300 karakter.';

    $ubahPass = ($passBaru !== '' || $passLama !== '' || $passKonf !== '');
    if ($ubahPass) {
        if (!password_verify($passLama, $u['password'])) $errors[] = 'Password lama salah.';
        if (strlen($passBaru) < 6)                       $errors[] = 'Password baru minimal 6 karakter.';
        if ($passBaru !== $passKonf)                     $errors[] = 'Konfirmasi password baru tidak sama.';
    }

    [$fotoBaru, $errUpload] = upload_gambar($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE], 'profil/');
    if ($errUpload) $errors[] = $errUpload;

    if (!$errors) {
        $foto = $fotoBaru ?: $u['foto'];
        if ($ubahPass) {
            $upd = $pdo->prepare('UPDATE users SET nama = ?, bio = ?, foto = ?, password = ? WHERE id = ?');
            $upd->execute([$nama, $bio ?: null, $foto, password_hash($passBaru, PASSWORD_DEFAULT), $id]);
        } else {
            $upd = $pdo->prepare('UPDATE users SET nama = ?, bio = ?, foto = ? WHERE id = ?');
            $upd->execute([$nama, $bio ?: null, $foto, $id]);
        }
        if ($fotoBaru) {
            hapus_file('profil/', $u['foto']);
        }
        $_SESSION['user']['nama'] = $nama;
        flash('success', 'Profil berhasil diperbarui.');
        header('Location: profil.php');
        exit;
    }
    hapus_file('profil/', $fotoBaru ?? null);
    $u['nama'] = $nama;
    $u['bio']  = $bio;
}

// karya milik pengguna ini
$kp = $pdo->prepare('SELECT id, nama_project, gambar, created_at FROM projects WHERE user_id = ? ORDER BY created_at DESC');
$kp->execute([$id]);
$karya = $kp->fetchAll();

$judul    = $self ? 'Profil Saya' : 'Profil ' . $u['nama'];
$page_css = ['main.css', 'form.css'];
include __DIR__ . '/includes/header.php';
?>

<?php if (!$self): ?>
    <a class="back-link" href="admin/users.php">← Kembali ke kelola user</a>
<?php endif; ?>

<section class="profile-head">
    <div class="profile-photo">
        <?php if ($u['foto']): ?>
            <img src="uploads/profil/<?= e($u['foto']) ?>" alt="Foto <?= e($u['nama']) ?>" id="foto-preview">
        <?php else: ?>
            <span class="avatar-fallback xl" id="foto-fallback"><?= e(mb_strtoupper(mb_substr($u['nama'], 0, 1))) ?></span>
            <img src="" alt="" id="foto-preview" hidden>
        <?php endif; ?>
    </div>
    <div class="profile-meta">
        <span class="role-badge role-<?= e($u['role']) ?>"><?= e(role_label($u['role'])) ?></span>
        <h1><?= e($u['nama']) ?></h1>
        <p class="email">✉ <?= e($u['email']) ?></p>
        <p class="bio"><?= $u['bio'] ? nl2br(e($u['bio'])) : '<span class="muted">Belum ada bio.</span>' ?></p>
        <small class="muted">Bergabung sejak <?= e(date('d', strtotime($u['created_at'])) . ' ' . bulan_id((int) date('n', strtotime($u['created_at']))) . ' ' . date('Y', strtotime($u['created_at']))) ?></small>
    </div>
</section>

<?php if ($self): ?>
<div class="form-card profile-form">
    <h2>Ubah <em>profil</em></h2>

    <?php if ($errors): ?>
        <div class="alert alert-error shake">
            <?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <label class="lbl" for="foto">Foto profil <small>(maks 2 MB)</small></label>
        <input class="input" type="file" id="foto" name="foto" accept="image/*">

        <label class="lbl" for="nama">Nama</label>
        <input class="input" type="text" id="nama" name="nama" value="<?= e($u['nama']) ?>" required>

        <label class="lbl">Email <small>(tidak dapat diubah)</small></label>
        <input class="input" type="email" value="<?= e($u['email']) ?>" disabled>

        <label class="lbl" for="bio">Bio <small>(maks 300 karakter)</small></label>
        <textarea class="input" id="bio" name="bio" rows="3" maxlength="300"><?= e($u['bio'] ?? '') ?></textarea>

        <details class="pass-box" <?= $errors && ($_POST['password_lama'] ?? '') !== '' ? 'open' : '' ?>>
            <summary>🔒 Ganti password</summary>
            <label class="lbl" for="password_lama">Password lama</label>
            <input class="input" type="password" id="password_lama" name="password_lama" autocomplete="current-password">
            <label class="lbl" for="password_baru">Password baru</label>
            <input class="input" type="password" id="password_baru" name="password_baru" autocomplete="new-password">
            <label class="lbl" for="password_konfirmasi">Konfirmasi password baru</label>
            <input class="input" type="password" id="password_konfirmasi" name="password_konfirmasi" autocomplete="new-password">
        </details>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Simpan profil</button>
        </div>
    </form>
</div>

<script>
document.getElementById('foto').addEventListener('change', function () {
    const f = this.files[0];
    if (!f) return;
    if (f.size > 2 * 1024 * 1024) { alert('Ukuran gambar maksimal 2 MB.'); this.value = ''; return; }
    const img = document.getElementById('foto-preview');
    img.src = URL.createObjectURL(f);
    img.hidden = false;
    const fb = document.getElementById('foto-fallback');
    if (fb) fb.hidden = true;
});
</script>
<?php endif; ?>

<section class="section">
    <h2 class="section-title">Karya <?= $self ? 'saya' : 'oleh ' . e($u['nama']) ?> <small>(<?= count($karya) ?>)</small></h2>
    <?php if (!$karya): ?>
        <div class="empty small"><p>Belum ada karya yang diunggah.</p></div>
    <?php else: ?>
        <div class="grid mini">
            <?php foreach ($karya as $k): ?>
                <a class="card mini-card" href="project_detail.php?id=<?= (int) $k['id'] ?>">
                    <div class="card-thumb">
                        <?php if ($k['gambar']): ?>
                            <img src="uploads/<?= e($k['gambar']) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <div class="thumb-fallback"><?= e(mb_strtoupper(mb_substr($k['nama_project'], 0, 1))) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body"><h3><?= e($k['nama_project']) ?></h3></div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
