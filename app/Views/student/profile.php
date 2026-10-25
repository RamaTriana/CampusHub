<?php

$pageTitle = 'Profil Mahasiswa | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/student/layout-start.php';
$photoFile = basename((string) ($user['foto'] ?? ''));
$photoPath = dirname(__DIR__, 3) . '/assets/uploads/profile/' . $photoFile;
$photoUrl = preg_match('/\A[a-zA-Z0-9._-]+\.(?:jpg|jpeg|png)\z/i', $photoFile) === 1 && is_file($photoPath)
    ? '/assets/uploads/profile/' . rawurlencode($photoFile)
    : '/public/assets/images/image.png';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Profil Mahasiswa</h1><p>Kelola informasi akun dan keamanan profil Anda.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <div class="card ch-admin-settings-card">
        <div class="ch-student-profile-heading">
            <img class="ch-profile-photo" src="<?= \CampusHub\Core\View::escape($photoUrl) ?>" alt="Foto profil <?= \CampusHub\Core\View::escape($user['nama']) ?>" width="72" height="72">
            <div><strong><?= \CampusHub\Core\View::escape($user['nama']) ?></strong><span>Mahasiswa<?= !empty($user['nim']) ? ' · ' . \CampusHub\Core\View::escape($user['nim']) : '' ?></span></div>
        </div>
        <form action="/student/profile" method="post" enctype="multipart/form-data">
            <?= \CampusHub\Core\View::csrfField() ?>
            <div class="form-group"><label class="form-label" for="nama">Nama</label><input class="form-control" id="nama" name="nama" maxlength="100" value="<?= \CampusHub\Core\View::escape($user['nama']) ?>" required></div>
            <div class="form-group"><label class="form-label" for="nim">NIM</label><input class="form-control" id="nim" name="nim" maxlength="20" pattern="[A-Za-z0-9./-]*" value="<?= \CampusHub\Core\View::escape($user['nim'] ?? '') ?>" autocomplete="off"></div>
            <div class="form-group"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="100" value="<?= \CampusHub\Core\View::escape($user['email']) ?>" required></div>
            <div class="form-group"><label class="form-label" for="username">Username</label><input class="form-control" id="username" value="<?= \CampusHub\Core\View::escape($user['username']) ?>" readonly></div>
            <div class="form-group"><label class="form-label" for="foto">Foto profil <small>(JPG/PNG, maks. 2 MB)</small></label><input class="form-control" id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png"></div>
            <hr>
            <div class="form-group"><label class="form-label" for="current_password">Password saat ini</label><input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required></div>
            <div class="form-group"><label class="form-label" for="new_password">Password baru <small>(kosongkan jika tidak ingin mengubah)</small></label><input class="form-control" id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="8"></div>
            <div class="form-group"><label class="form-label" for="confirm_password">Konfirmasi password baru</label><input class="form-control" id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" minlength="8"></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan profil</button></div>
        </form>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/includes/student/layout-end.php'; ?>
