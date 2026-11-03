<?php
// Halaman detail karya: foto, judul, deskripsi, dibuat oleh, waktu
$base = '';
require_once __DIR__ . '/middleware/auth_check.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT p.*, u.nama AS pembuat, u.foto AS foto_pembuat, u.role AS role_pembuat
     FROM projects p JOIN users u ON u.id = p.user_id
     WHERE p.id = ?'
);
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    flash('error', 'Karya tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$judul    = $p['nama_project'];
$page_css = ['main.css'];
include __DIR__ . '/includes/header.php';
?>

<a class="back-link" href="index.php">← Kembali ke etalase</a>

<article class="detail">
    <div class="detail-media">
        <?php if ($p['gambar']): ?>
            <img src="uploads/<?= e($p['gambar']) ?>" alt="<?= e($p['nama_project']) ?>">
        <?php else: ?>
            <div class="thumb-fallback big"><?= e(mb_strtoupper(mb_substr($p['nama_project'], 0, 1))) ?></div>
        <?php endif; ?>
    </div>

    <div class="detail-info">
        <span class="chip">✦ Karya Digital</span>
        <h1><?= e($p['nama_project']) ?></h1>

        <div class="byline">
            <?php if ($p['foto_pembuat']): ?>
                <img class="avatar" src="uploads/profil/<?= e($p['foto_pembuat']) ?>" alt="">
            <?php else: ?>
                <span class="avatar avatar-fallback"><?= e(mb_strtoupper(mb_substr($p['pembuat'], 0, 1))) ?></span>
            <?php endif; ?>
            <div>
                <small>Dibuat oleh</small>
                <strong><?= e($p['pembuat']) ?></strong>
            </div>
        </div>

        <div class="detail-time">🕒 <?= e(tanggal_id($p['created_at'])) ?></div>

        <h4>Deskripsi</h4>
        <p class="detail-desc"><?= nl2br(e($p['deskripsi'])) ?></p>

        <div class="detail-actions">
            <?php if ($p['link_project']): ?>
                <a class="btn btn-primary" href="<?= e($p['link_project']) ?>" target="_blank" rel="noopener noreferrer">Buka project ↗</a>
            <?php endif; ?>

            <?php if (can_manage_project($p)): ?>
                <a class="btn btn-soft" href="project_edit.php?id=<?= (int) $p['id'] ?>">✎ Edit</a>
                <form method="post" action="project_hapus.php"
                      onsubmit="return confirm('Hapus karya ini? Tindakan tidak bisa dibatalkan.')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <button class="btn btn-danger" type="submit">🗑 Hapus</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</article>

<?php include __DIR__ . '/includes/footer.php'; ?>
