<?php

$pageTitle = $heading . ' | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'forms.css', 'buttons.css', 'alerts.css'];
$organizationId = is_array($organization)
    ? (int) ($organization['id_organisasi'] ?? 0)
    : null;
$logoFilename = is_array($organization) && is_string($organization['logo'] ?? null)
    ? basename($organization['logo'])
    : '';
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
?>

        <section class="ch-content ch-admin-page">
            <div class="page-header">
                <div>
                    <h1><?= \CampusHub\Core\View::escape($heading) ?></h1>
                    <p>Logo JPG atau PNG, maksimal 2 MB. Kolom deskripsi menggunakan tipe TEXT.</p>
                </div>
            </div>

            <?php if (is_array($flash ?? null)) : ?>
                <div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>">
                    <?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="<?= \CampusHub\Core\View::escape($formAction) ?>" method="POST" enctype="multipart/form-data">
                    <?= \CampusHub\Core\View::csrfField() ?>
                    <?php if ($organizationId !== null) : ?>
                        <input type="hidden" name="id" value="<?= $organizationId ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label" for="nama_organisasi">Nama Organisasi</label>
                        <input
                            class="form-control"
                            type="text"
                            id="nama_organisasi"
                            name="nama_organisasi"
                            value="<?= \CampusHub\Core\View::escape(is_array($organization) ? ($organization['nama_organisasi'] ?? '') : '') ?>"
                            maxlength="100"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="deskripsi">Deskripsi</label>
                        <textarea
                            class="form-control"
                            id="deskripsi"
                            name="deskripsi"
                            maxlength="65535"
                            rows="6"><?= \CampusHub\Core\View::escape(is_array($organization) ? ($organization['deskripsi'] ?? '') : '') ?></textarea>
                    </div>

                    <?php if ($logoFilename !== '') : ?>
                        <div class="form-group">
                            <p class="form-label">Logo saat ini</p>
                            <img
                                src="/assets/uploads/organisasi/<?= rawurlencode($logoFilename) ?>"
                                alt="Logo <?= \CampusHub\Core\View::escape($organization['nama_organisasi'] ?? '') ?>"
                                width="120"
                                height="120">
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label" for="logo">
                            <?= $logoFilename === '' ? 'Logo Organisasi' : 'Ganti Logo Organisasi' ?>
                        </label>
                        <input
                            class="form-control"
                            type="file"
                            id="logo"
                            name="logo"
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                        <small class="form-help">Kosongkan jika tidak ingin mengganti logo saat ini.</small>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Simpan</button>
                        <a class="btn btn-outline" href="/admin/organisasi/">Batal</a>
                    </div>
                </form>
            </div>
        </section>

<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
