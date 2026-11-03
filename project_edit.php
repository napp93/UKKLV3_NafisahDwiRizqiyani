<?php
// Form Edit Project (pemilik / admin / super admin)
$base = '';
require_once __DIR__ . '/middleware/auth_check.php';
require_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    flash('error', 'Karya tidak ditemukan.');
    header('Location: index.php');
    exit;
}
if (!can_manage_project($p)) {
    http_response_code(403);
    flash('error', 'Kamu hanya bisa mengedit karya milikmu sendiri.');
    header('Location: index.php');
    exit;
}

$errors = [];
$nama      = $p['nama_project'];
$deskripsi = $p['deskripsi'];
$link      = $p['link_project'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $nama      = trim($_POST['nama_project'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $link      = trim($_POST['link_project'] ?? '');

    if (mb_strlen($nama) < 3)  $errors[] = 'Nama project minimal 3 karakter.';
    if ($deskripsi === '')     $errors[] = 'Deskripsi wajib diisi.';
    if (!valid_url($link))     $errors[] = 'Link project harus diawali http:// atau https://';

    [$gambarBaru, $errUpload] = upload_gambar($_FILES['gambar'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
    if ($errUpload) $errors[] = $errUpload;

    if (!$errors) {
        $gambar = $gambarBaru ?: $p['gambar'];
        $upd = $pdo->prepare(
            'UPDATE projects SET nama_project = ?, deskripsi = ?, gambar = ?, link_project = ? WHERE id = ?'
        );
        $upd->execute([$nama, $deskripsi, $gambar, $link ?: null, $id]);
        if ($gambarBaru) {
            hapus_file('', $p['gambar']); // hapus gambar lama
        }
        flash('success', 'Karya berhasil diperbarui.');
        header('Location: project_detail.php?id=' . $id);
        exit;
    }
    hapus_file('', $gambarBaru ?? null);
}

$judul    = 'Edit Karya';
$page_css = ['form.css'];
include __DIR__ . '/includes/header.php';
?>

<div class="form-page">
    <div class="form-card">
        <h1>Edit <em>Karya</em></h1>
        <p class="sub">Perbarui informasi karya digital.</p>

        <?php if ($errors): ?>
            <div class="alert alert-error shake">
                <?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $id ?>">

            <label class="lbl" for="nama_project">Nama project</label>
            <input class="input" type="text" id="nama_project" name="nama_project" value="<?= e($nama) ?>" required>

            <label class="lbl" for="deskripsi">Deskripsi</label>
            <textarea class="input" id="deskripsi" name="deskripsi" rows="5" required><?= e($deskripsi) ?></textarea>

            <label class="lbl" for="link_project">Link project <small>(opsional)</small></label>
            <input class="input" type="url" id="link_project" name="link_project" placeholder="https://" value="<?= e($link) ?>">

            <label class="lbl">Gambar project <small>(kosongkan jika tidak ingin mengganti)</small></label>
            <label class="dropzone has-img" id="dropzone" for="gambar">
                <?php if ($p['gambar']): ?>
                    <img id="preview" src="uploads/<?= e($p['gambar']) ?>" alt="">
                <?php else: ?>
                    <img id="preview" alt="" hidden>
                <?php endif; ?>
                <span id="dz-text">Ganti gambar</span>
            </label>
            <input type="file" id="gambar" name="gambar" accept="image/*" hidden>

            <div class="form-actions">
                <a class="btn btn-ghost" href="project_detail.php?id=<?= (int) $id ?>">Batal</a>
                <button class="btn btn-primary" type="submit">Simpan perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
const input = document.getElementById('gambar');
const preview = document.getElementById('preview');
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
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
