<?php
// Kelola User (Super Admin Only): tambah, edit, hapus, lihat profil
$base = '../';
require_once __DIR__ . '/../middleware/auth_check.php';
require_role(['super_admin']);

$me     = current_user();
$errors = [];
$editId = (int) ($_GET['edit'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $nama  = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $role  = $_POST['role'] ?? 'admin';

        if (mb_strlen($nama) < 3)                       $errors[] = 'Nama minimal 3 karakter.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
        if (strlen($pass) < 6)                          $errors[] = 'Password minimal 6 karakter.';
        if (!in_array($role, ['admin', 'siswa'], true)) $errors[] = 'Role tidak valid.';
        if (!$errors) {
            $cek = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $cek->execute([$email]);
            if ($cek->fetch()) $errors[] = 'Email sudah terdaftar.';
        }
        if (!$errors) {
            $pdo->prepare('INSERT INTO users (nama, email, password, role) VALUES (?,?,?,?)')
                ->execute([$nama, $email, password_hash($pass, PASSWORD_DEFAULT), $role]);
            flash('success', 'User ' . $nama . ' berhasil ditambahkan.');
            header('Location: users.php');
            exit;
        }
    }

    if ($aksi === 'edit') {
        $id    = (int) ($_POST['id'] ?? 0);
        $nama  = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = $_POST['role'] ?? '';
        $pass  = $_POST['password'] ?? '';
        $editId = $id;

        if (mb_strlen($nama) < 3)                       $errors[] = 'Nama minimal 3 karakter.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
        if ($pass !== '' && strlen($pass) < 6)          $errors[] = 'Password baru minimal 6 karakter.';
        if ($id !== (int) $me['id'] && !in_array($role, ['admin', 'siswa'], true)) {
            $errors[] = 'Role tidak valid.';
        }
        if (!$errors) {
            $cek = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
            $cek->execute([$email, $id]);
            if ($cek->fetch()) $errors[] = 'Email sudah dipakai user lain.';
        }
        if (!$errors) {
            $target = $pdo->prepare('SELECT role FROM users WHERE id = ?');
            $target->execute([$id]);
            $t = $target->fetch();
            if (!$t) {
                $errors[] = 'User tidak ditemukan.';
            } else {
                // super admin tidak boleh diubah rolenya lewat form ini
                $roleFinal = ($t['role'] === 'super_admin') ? 'super_admin' : $role;
                if ($pass !== '') {
                    $pdo->prepare('UPDATE users SET nama=?, email=?, role=?, password=? WHERE id=?')
                        ->execute([$nama, $email, $roleFinal, password_hash($pass, PASSWORD_DEFAULT), $id]);
                } else {
                    $pdo->prepare('UPDATE users SET nama=?, email=?, role=? WHERE id=?')
                        ->execute([$nama, $email, $roleFinal, $id]);
                }
                if ($id === (int) $me['id']) {
                    $_SESSION['user']['nama']  = $nama;
                    $_SESSION['user']['email'] = $email;
                }
                flash('success', 'Data user berhasil diperbarui.');
                header('Location: users.php');
                exit;
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) $me['id']) {
            flash('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        } else {
            $t = $pdo->prepare('SELECT role, foto FROM users WHERE id = ?');
            $t->execute([$id]);
            $row = $t->fetch();
            if (!$row) {
                flash('error', 'User tidak ditemukan.');
            } elseif ($row['role'] === 'super_admin') {
                flash('error', 'Akun Super Admin tidak dapat dihapus.');
            } else {
                // bersihkan file gambar karya & foto profil
                $g = $pdo->prepare('SELECT gambar FROM projects WHERE user_id = ?');
                $g->execute([$id]);
                foreach ($g->fetchAll() as $r) hapus_file('', $r['gambar']);
                hapus_file('profil/', $row['foto']);
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]); // projects ikut terhapus (CASCADE)
                flash('success', 'User berhasil dihapus.');
            }
        }
        header('Location: users.php');
        exit;
    }
}

$users = $pdo->query(
    'SELECT u.id, u.nama, u.email, u.role, u.created_at,
            (SELECT COUNT(*) FROM projects p WHERE p.user_id = u.id) AS total_karya
     FROM users u ORDER BY FIELD(u.role, "super_admin", "admin", "siswa"), u.nama'
)->fetchAll();

$editUser = null;
if ($editId) {
    foreach ($users as $x) {
        if ((int) $x['id'] === $editId) { $editUser = $x; break; }
    }
}

$judul    = 'Kelola User';
$page_css = ['admin.css'];
include __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
    <h1>Kelola <em>User</em></h1>
    <p class="sub">Tambah, ubah, hapus akun, atau lihat profil admin dan siswa.</p>
</section>

<?php if ($errors): ?>
    <div class="alert alert-error shake">
        <?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<section class="panel">
    <div class="panel-head"><h2><?= $editUser ? 'Edit user' : 'Tambah user baru' ?></h2></div>

    <form method="post" class="user-form">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="<?= $editUser ? 'edit' : 'tambah' ?>">
        <?php if ($editUser): ?><input type="hidden" name="id" value="<?= (int) $editUser['id'] ?>"><?php endif; ?>

        <div class="form-row">
            <div>
                <label class="lbl">Nama</label>
                <input class="input" type="text" name="nama" value="<?= e($editUser['nama'] ?? '') ?>" required>
            </div>
            <div>
                <label class="lbl">Email</label>
                <input class="input" type="email" name="email" value="<?= e($editUser['email'] ?? '') ?>" required>
            </div>
            <div>
                <label class="lbl">Password <?= $editUser ? '<small>(kosongkan jika tidak diubah)</small>' : '' ?></label>
                <input class="input" type="password" name="password" <?= $editUser ? '' : 'required' ?> autocomplete="new-password">
            </div>
            <div>
                <label class="lbl">Role</label>
                <?php if ($editUser && $editUser['role'] === 'super_admin'): ?>
                    <input class="input" type="text" value="Super Admin" disabled>
                <?php else: ?>
                    <select class="input" name="role">
                        <?php $r = $editUser['role'] ?? 'admin'; ?>
                        <option value="admin"  <?= $r === 'admin'  ? 'selected' : '' ?>>Admin</option>
                        <option value="siswa"  <?= $r === 'siswa'  ? 'selected' : '' ?>>Siswa</option>
                    </select>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions left">
            <button class="btn btn-primary" type="submit"><?= $editUser ? 'Simpan perubahan' : 'Tambah user' ?></button>
            <?php if ($editUser): ?><a class="btn btn-ghost" href="users.php">Batal</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-head"><h2>Daftar user <small>(<?= count($users) ?>)</small></h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Karya</th><th class="right">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= e($u['nama']) ?></strong></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="role-badge role-<?= e($u['role']) ?>"><?= e(role_label($u['role'])) ?></span></td>
                    <td><?= (int) $u['total_karya'] ?></td>
                    <td class="right actions">
                        <a class="btn btn-ghost btn-sm" href="../profil.php?id=<?= (int) $u['id'] ?>">Lihat profil</a>
                        <a class="btn btn-soft btn-sm" href="users.php?edit=<?= (int) $u['id'] ?>">Edit</a>
                        <?php if ($u['role'] !== 'super_admin' && (int) $u['id'] !== (int) $me['id']): ?>
                            <form method="post" onsubmit="return confirm('Hapus user ini beserta seluruh karyanya?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
