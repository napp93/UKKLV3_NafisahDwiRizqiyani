<?php
// Dashboard Admin / Super Admin
$base = '../';
require_once __DIR__ . '/../middleware/auth_check.php';
require_role(['super_admin', 'admin']);

$totalProject = (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$totalSiswa   = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'siswa'")->fetchColumn();
$totalAdmin   = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('admin','super_admin')")->fetchColumn();

$projects = $pdo->query(
    'SELECT p.id, p.nama_project, p.created_at, u.nama AS pembuat
     FROM projects p JOIN users u ON u.id = p.user_id
     ORDER BY p.created_at DESC'
)->fetchAll();

$judul    = 'Dashboard';
$page_css = ['admin.css'];
include __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
    <h1>Dashboard <em><?= is_super_admin() ? 'Super Admin' : 'Admin' ?></em></h1>
    <p class="sub">Kelola seluruh karya digital siswa.</p>
</section>

<div class="stats">
    <div class="stat"><span class="stat-num"><?= $totalProject ?></span><span>Total karya</span></div>
    <div class="stat"><span class="stat-num"><?= $totalSiswa ?></span><span>Siswa</span></div>
    <div class="stat"><span class="stat-num"><?= $totalAdmin ?></span><span>Admin</span></div>
</div>

<section class="panel">
    <div class="panel-head">
        <h2>Semua karya</h2>
        <a class="btn btn-primary btn-sm" href="../project_tambah.php">＋ Tambah</a>
    </div>

    <?php if (!$projects): ?>
        <p class="muted">Belum ada karya.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>#</th><th>Judul</th><th>Dibuat oleh</th><th>Waktu</th><th class="right">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($projects as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><a href="../project_detail.php?id=<?= (int) $p['id'] ?>"><?= e($p['nama_project']) ?></a></td>
                        <td><?= e($p['pembuat']) ?></td>
                        <td><?= e(date('d M Y H:i', strtotime($p['created_at']))) ?></td>
                        <td class="right actions">
                            <a class="btn btn-soft btn-sm" href="../project_edit.php?id=<?= (int) $p['id'] ?>">Edit</a>
                            <form method="post" action="../project_hapus.php"
                                  onsubmit="return confirm('Hapus karya ini?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
