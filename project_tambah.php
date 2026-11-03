<?php
// Form Tambah Project + JS Preview
$base = '';
require_once __DIR__ . '/middleware/auth_check.php';
require_login();

$errors = [];
$nama = $deskripsi = $link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nama      = trim($_POST['nama_project'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $link      = trim($_POST['link_project'] ?? '');

    if (mb_strlen($nama) < 3)  $errors[] = 'Nama project minimal 3 karakter.';
    if ($deskripsi === '')     $errors[] = 'Deskripsi wajib diisi.';
    if (!valid_url($link))     $errors[] = 'Link project harus diawali http:// atau https://';

    [$gambar, $errUpload] = upload_gambar($_FILES['gambar'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
    if ($errUpload) $errors[] = $errUpload;
    if (!$gambar && !$errUpload) $errors[] = 'Gambar project wajib diunggah.';

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO projects (user_id, nama_project, deskripsi, gambar, link_project) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([current_user()['id'], $nama, $deskripsi, $gambar, $link ?: null]);
        flash('success', 'Karya berhasil ditambahkan!');
        header('Location: index.php');
        exit;
    }
    // jika gagal setelah upload, bersihkan file
    hapus_file('', $gambar ?? null);
}

$judul    = 'Tambah Karya';
$page_css = ['form.css'];
include __DIR__ . '/includes/header.php';
?>

<div class="form-page">
    <div class="form-card">
        <h1>Tambah <em>Karya</em></h1>
        <p class="sub">Bagikan karya digitalmu ke etalase.</p>

        <?php if ($errors): ?>
            <div class="alert alert-error shake">
                <?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <label class="lbl" for="nama_project">Nama project</label>
            <input class="input" type="text" id="nama_project" name="nama_project" value="<?= e($nama) ?>" required>

            <label class="lbl" for="deskripsi">Deskripsi</label>
            <textarea class="input" id="deskripsi" name="deskripsi" rows="5" required><?= e($deskripsi) ?></textarea>

            <label class="lbl" for="link_project">Link project <small>(opsional)</small></label>
            <input class="input" type="url" id="link_project" name="link_project" placeholder="https://" value="<?= e($link) ?>">

            <label class="lbl">Gambar project <small>(JPG/PNG/WEBP/GIF, maks 2 MB)</small></label>
            <label class="dropzone" id="dropzone" for="gambar">
                <img id="preview" alt="" hidden>
                <span id="dz-text">📷 Klik untuk memilih gambar</span>
            </label>
            <input type="file" id="gambar" name="gambar" accept="image/*" hidden required>

            <div class="form-actions">
                <a class="btn btn-ghost" href="index.php">Batal</a>
                <button class="btn btn-primary" type="submit">Simpan karya</button>
            </div>
        </form>
    </div>
</div>

<script>
const input = document.getElementById('gambar');
const preview = document.getElementById('preview');
const text = document.getElementById('dz-text');
input.addEventListener('change', () => {
    const f = input.files[0];
    if (!f) return;
    if (f.size > 2 * 1024 * 1024) {
        alert('Ukuran gambar maksimal 2 MB.');
        input.value = '';
        return;
    }
    preview.src = URL.createObjectURL(f);
    preview.hidden = false;
    text.textContent = 'Ganti gambar';
    document.getElementById('dropzone').classList.add('has-img');
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
