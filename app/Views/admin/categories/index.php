<?php

$pageTitle = 'Kategori Event | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css', 'alerts.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
?>

        <section class="ch-content ch-admin-page">
            <div class="page-header">
                <div>
                    <h1>Kategori Event</h1>
                    <p>Kelola kategori yang digunakan pada event CampusHub.</p>
                </div>
                <a class="btn btn-primary" href="/admin/kategori/tambah">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    Tambah Kategori
                </a>
            </div>

            <?php if (is_array($flash ?? null)) : ?>
                <div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>">
                    <?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama Kategori</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col">Event</th>
                        <th scope="col">Dibuat</th>
                        <th scope="col">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($categories === []) : ?>
                        <tr>
                            <td colspan="6">Belum ada kategori.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($categories as $index => $category) : ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= \CampusHub\Core\View::escape($category['nama_kategori'] ?? '') ?></td>
                                <td><?= \CampusHub\Core\View::escape($category['deskripsi'] ?? '—') ?></td>
                                <td><?= (int) ($category['jumlah_event'] ?? 0) ?></td>
                                <td><?= \CampusHub\Core\View::escape($category['created_at'] ?? '') ?></td>
                                <td class="ch-admin-inline-form">
                                    <a
                                        class="btn btn-warning btn-sm"
                                        href="/admin/kategori/edit?id=<?= (int) ($category['id_kategori'] ?? 0) ?>">
                                        Edit
                                    </a>
                                    <?php if ((int) ($category['jumlah_event'] ?? 0) === 0) : ?>
                                        <form method="post" action="/admin/kategori/hapus">
                                            <?= \CampusHub\Core\View::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= (int) ($category['id_kategori'] ?? 0) ?>">
                                            <button class="btn btn-outline btn-sm" type="submit">Hapus</button>
                                        </form>
                                    <?php else : ?>
                                        <span>Digunakan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
