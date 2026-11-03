<?php
// Halaman Utama (Etalase Karya) - semua role yang sudah login
$base = '';
require_once __DIR__ . '/middleware/auth_check.php';
require_login();

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $pdo->prepare(
        'SELECT p.*, u.nama AS pembuat FROM projects p
         JOIN users u ON u.id = p.user_id
         WHERE p.nama_project LIKE ? OR u.nama LIKE ?
         ORDER BY p.created_at DESC'
    );
    $stmt->execute(["%$q%", "%$q%"]);
} else {
    $stmt = $pdo->query(
        'SELECT p.*, u.nama AS pembuat FROM projects p
         JOIN users u ON u.id = p.user_id
         ORDER BY p.created_at DESC'
    );
}
$projects = $stmt->fetchAll();

$judul     = 'Etalase';
$page_css  = ['main.css'];
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-text">
        <span class="chip">✦ Galeri Karya Siswa</span>
        <h1>Etalase <em>Digital</em> Stella</h1>
        <p>Jelajahi karya digital terbaik dari siswa. Klik sebuah karya untuk melihat detailnya.</p>
    </div>
    <a href="project_tambah.php" class="btn btn-primary btn-lg">＋ Tambah Karya</a>
</section>

<form class="searchbar" method="get">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari judul karya atau nama pembuat…">
    <button class="btn btn-soft" type="submit">Cari</button>
    <?php if ($q !== ''): ?><a class="btn btn-ghost" href="index.php">Reset</a><?php endif; ?>
</form>

<?php if (!$projects): ?>
    <div class="empty">
        <div class="empty-icon">🌸</div>
        <h3><?= $q !== '' ? 'Karya tidak ditemukan' : 'Belum ada karya' ?></h3>
        <p><?= $q !== '' ? 'Coba kata kunci lain.' : 'Jadilah yang pertama memamerkan karyamu!' ?></p>
    </div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($projects as $p): ?>
            <article class="card">
                <a class="card-link" href="project_detail.php?id=<?= (int) $p['id'] ?>" aria-label="Lihat <?= e($p['nama_project']) ?>">
                    <div class="card-thumb">
                        <?php if ($p['gambar']): ?>
                            <img src="uploads/<?= e($p['gambar']) ?>" alt="<?= e($p['nama_project']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="thumb-fallback"><?= e(mb_strtoupper(mb_substr($p['nama_project'], 0, 1))) ?></div>
                        <?php endif; ?>
                        <span class="thumb-overlay">Lihat detail →</span>
                    </div>
                    <div class="card-body">
                        <h3><?= e($p['nama_project']) ?></h3>
                        <p class="desc"><?= e(mb_strimwidth($p['deskripsi'], 0, 100, '…')) ?></p>
                        <div class="meta">
                            <span>👤 <?= e($p['pembuat']) ?></span>
                            <span><?= e(date('d M Y', strtotime($p['created_at']))) ?></span>
                        </div>
                    </div>
                </a>

                <?php if (can_manage_project($p)): ?>
                    <div class="card-actions">
                        <a class="btn btn-soft btn-sm" href="project_edit.php?id=<?= (int) $p['id'] ?>">✎ Edit</a>
                        <form method="post" action="project_hapus.php"
                              onsubmit="return confirm('Hapus karya ini? Tindakan tidak bisa dibatalkan.')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button class="btn btn-danger btn-sm" type="submit">🗑 Hapus</button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
