<?php

$pageTitle = 'Profil Panitia | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/committee/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Profil Panitia</h1><p>Data akun sendiri. Role dan organisasi ditetapkan Admin.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <div class="card ch-admin-settings-card">
        <form action="/committee/profile" method="post">
            <?= \CampusHub\Core\View::csrfField() ?>
            <div class="form-group"><label class="form-label" for="nama">Nama</label><input class="form-control" id="nama" name="nama" maxlength="100" value="<?= \CampusHub\Core\View::escape($user['nama']) ?>" required></div>
            <div class="form-group"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="100" value="<?= \CampusHub\Core\View::escape($user['email']) ?>" required></div>
            <div class="form-group"><label class="form-label" for="nim">NIM</label><input class="form-control" id="nim" value="<?= \CampusHub\Core\View::escape($user['nim'] ?? '') ?>" readonly></div>
            <div class="form-group"><label class="form-label" for="username">Username</label><input class="form-control" id="username" value="<?= \CampusHub\Core\View::escape($user['username']) ?>" readonly></div>
            <div class="form-group"><label class="form-label" for="organisasi">Organisasi</label><input class="form-control" id="organisasi" value="<?= \CampusHub\Core\View::escape($user['nama_organisasi'] ?? 'Belum ditetapkan') ?>" readonly></div>
            <div class="form-group"><label class="form-label" for="role">Role</label><input class="form-control" id="role" value="Panitia" readonly></div>
            <hr>
            <div class="form-group"><label class="form-label" for="current_password">Password saat ini</label><input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required></div>
            <div class="form-group"><label class="form-label" for="new_password">Password baru <small>(opsional)</small></label><input class="form-control" id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="8"></div>
            <div class="form-group"><label class="form-label" for="confirm_password">Konfirmasi password baru</label><input class="form-control" id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" minlength="8"></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan profil</button></div>
        </form>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/includes/committee/layout-end.php'; ?>
