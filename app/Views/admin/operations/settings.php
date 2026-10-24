<?php

$pageTitle = 'Settings | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'forms.css', 'buttons.css', 'alerts.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Pengaturan akun</h1><p>Perbarui profil dan password akun administrator yang sedang aktif.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <div class="card ch-admin-settings-card">
        <form action="/admin/settings" method="post">
            <?= \CampusHub\Core\View::csrfField() ?>
            <div class="form-group"><label class="form-label" for="nama">Nama</label><input class="form-control" id="nama" name="nama" maxlength="100" value="<?= \CampusHub\Core\View::escape($user['nama']) ?>" required></div>
            <div class="form-group"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="100" value="<?= \CampusHub\Core\View::escape($user['email']) ?>" required></div>
            <hr>
            <div class="form-group"><label class="form-label" for="current_password">Password saat ini</label><input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required></div>
            <div class="form-group"><label class="form-label" for="new_password">Password baru <small>(kosongkan jika tidak ingin mengubah)</small></label><input class="form-control" id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="8"></div>
            <div class="form-group"><label class="form-label" for="confirm_password">Konfirmasi password baru</label><input class="form-control" id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" minlength="8"></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan pengaturan</button></div>
        </form>
    </div>
</section>
<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
