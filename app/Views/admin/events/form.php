<?php

$pageTitle = $heading . ' | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'forms.css', 'buttons.css', 'alerts.css'];
$eventId = is_array($event) ? (int) ($event['id_event'] ?? 0) : null;
$posterFilename = is_array($event) && is_string($event['poster'] ?? null)
    ? basename($event['poster'])
    : '';
if (in_array($posterFilename, ['.', '..'], true)) {
    $posterFilename = '';
}
$categories = $references['categories'] ?? [];
$organizations = $references['organizations'] ?? [];
$committees = $references['committees'] ?? [];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
?>

        <section class="ch-content ch-admin-page">
            <div class="page-header">
                <div>
                    <h1><?= \CampusHub\Core\View::escape($heading) ?></h1>
                    <p>Poster JPG atau PNG, maksimal 2 MB. Poster lama dipertahankan jika tidak diganti.</p>
                </div>
            </div>

            <?php if (is_array($flash ?? null)) : ?>
                <div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>">
                    <?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?>
                </div>
            <?php endif; ?>

            <?php if ($categories === [] || $organizations === [] || $committees === []) : ?>
                <div class="alert alert-warning">
                    Tambahkan kategori, organisasi, dan setidaknya satu akun Panitia aktif sebelum membuat event.
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="<?= \CampusHub\Core\View::escape($formAction) ?>" method="POST" enctype="multipart/form-data">
                    <?= \CampusHub\Core\View::csrfField() ?>
                    <?php if ($eventId !== null) : ?>
                        <input type="hidden" name="id" value="<?= $eventId ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label" for="judul">Judul Event</label>
                        <input
                            class="form-control"
                            type="text"
                            id="judul"
                            name="judul"
                            value="<?= \CampusHub\Core\View::escape(is_array($event) ? ($event['judul'] ?? '') : '') ?>"
                            maxlength="150"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="deskripsi">Deskripsi</label>
                        <textarea
                            class="form-control"
                            id="deskripsi"
                            name="deskripsi"
                            maxlength="65535"
                            rows="6"><?= \CampusHub\Core\View::escape(is_array($event) ? ($event['deskripsi'] ?? '') : '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="id_kategori">Kategori</label>
                        <select class="form-control" id="id_kategori" name="id_kategori" required>
                            <option value="">Pilih kategori</option>
                            <?php foreach ($categories as $category) : ?>
                                <option
                                    value="<?= (int) ($category['id_kategori'] ?? 0) ?>"
                                    <?= (int) ($event['id_kategori'] ?? 0) === (int) ($category['id_kategori'] ?? 0) ? 'selected' : '' ?>>
                                    <?= \CampusHub\Core\View::escape($category['nama_kategori'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="id_organisasi">Organisasi</label>
                        <select class="form-control" id="id_organisasi" name="id_organisasi" required>
                            <option value="">Pilih organisasi</option>
                            <?php foreach ($organizations as $organization) : ?>
                                <option
                                    value="<?= (int) ($organization['id_organisasi'] ?? 0) ?>"
                                    <?= (int) ($event['id_organisasi'] ?? 0) === (int) ($organization['id_organisasi'] ?? 0) ? 'selected' : '' ?>>
                                    <?= \CampusHub\Core\View::escape($organization['nama_organisasi'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="id_panitia">Panitia</label>
                        <select class="form-control" id="id_panitia" name="id_panitia" required>
                            <option value="">Pilih Panitia aktif</option>
                            <?php foreach ($committees as $committee) : ?>
                                <option
                                    value="<?= (int) ($committee['id_user'] ?? 0) ?>"
                                    <?= (int) ($event['id_panitia'] ?? 0) === (int) ($committee['id_user'] ?? 0) ? 'selected' : '' ?>>
                                    <?= \CampusHub\Core\View::escape($committee['nama'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tanggal">Tanggal</label>
                        <input
                            class="form-control"
                            type="date"
                            id="tanggal"
                            name="tanggal"
                            value="<?= \CampusHub\Core\View::escape(is_array($event) ? ($event['tanggal'] ?? '') : '') ?>"
                            min="1000-01-01"
                            max="9999-12-31"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="jam">Jam</label>
                        <input
                            class="form-control"
                            type="time"
                            id="jam"
                            name="jam"
                            value="<?= \CampusHub\Core\View::escape(is_array($event) ? substr((string) ($event['jam'] ?? ''), 0, 5) : '') ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lokasi">Lokasi</label>
                        <input
                            class="form-control"
                            type="text"
                            id="lokasi"
                            name="lokasi"
                            value="<?= \CampusHub\Core\View::escape(is_array($event) ? ($event['lokasi'] ?? '') : '') ?>"
                            maxlength="150"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="kuota">Kuota</label>
                        <input
                            class="form-control"
                            type="number"
                            id="kuota"
                            name="kuota"
                            value="<?= \CampusHub\Core\View::escape(is_array($event) ? ($event['kuota'] ?? 0) : 0) ?>"
                            min="0"
                            max="2147483647"
                            step="1"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <?php $eventStatus = is_array($event) ? (string) ($event['status'] ?? 'Draft') : 'Draft'; ?>
                        <select class="form-control" id="status" name="status" required>
                            <?php foreach (['Draft', 'Publish', 'Selesai'] as $status) : ?>
                                <option value="<?= \CampusHub\Core\View::escape($status) ?>" <?= $eventStatus === $status ? 'selected' : '' ?>>
                                    <?= \CampusHub\Core\View::escape($status) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($posterFilename !== '') : ?>
                        <div class="form-group">
                            <p class="form-label">Poster saat ini</p>
                            <img
                                src="/assets/uploads/poster/<?= rawurlencode($posterFilename) ?>"
                                alt="Poster <?= \CampusHub\Core\View::escape($event['judul'] ?? '') ?>"
                                width="180"
                                loading="lazy">
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label" for="poster">
                            <?= $posterFilename === '' ? 'Poster Event' : 'Ganti Poster Event' ?>
                        </label>
                        <input
                            class="form-control"
                            type="file"
                            id="poster"
                            name="poster"
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                        <small class="form-help">Kosongkan jika tidak ingin mengganti poster saat ini.</small>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Simpan</button>
                        <a class="btn btn-outline" href="/admin/event/">Batal</a>
                    </div>
                </form>
            </div>
        </section>

<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
