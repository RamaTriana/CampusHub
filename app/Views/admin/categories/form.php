<?php

$pageTitle = $heading . ' | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'forms.css', 'buttons.css', 'alerts.css'];
$categoryId = is_array($category) ? (int) ($category['id_kategori'] ?? 0) : null;
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
?>

        <section class="ch-content ch-admin-page">
            <div class="page-header">
                <div>
                    <h1><?= \CampusHub\Core\View::escape($heading) ?></h1>
                    <p>Nama kategori maksimal 100 karakter dan deskripsi maksimal 2.000 karakter.</p>
                </div>
            </div>

            <?php if (is_array($flash ?? null)) : ?>
                <div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>">
                    <?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="<?= \CampusHub\Core\View::escape($formAction) ?>" method="POST">
                    <?= \CampusHub\Core\View::csrfField() ?>
                    <?php if ($categoryId !== null) : ?>
                        <input type="hidden" name="id" value="<?= $categoryId ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label" for="nama_kategori">Nama Kategori</label>
                        <input
                            class="form-control"
                            type="text"
                            id="nama_kategori"
                            name="nama_kategori"
                            value="<?= \CampusHub\Core\View::escape(is_array($category) ? ($category['nama_kategori'] ?? '') : '') ?>"
                            maxlength="100"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="deskripsi">Deskripsi</label>
                        <textarea
                            class="form-control"
                            id="deskripsi"
                            name="deskripsi"
                            maxlength="2000"
                            rows="4"><?= \CampusHub\Core\View::escape(is_array($category) ? ($category['deskripsi'] ?? '') : '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Simpan</button>
                        <a class="btn btn-outline" href="/admin/kategori/">Batal</a>
                    </div>
                </form>
            </div>
        </section>

<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
