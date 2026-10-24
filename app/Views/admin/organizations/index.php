<?php

$pageTitle = 'Organisasi | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css', 'alerts.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
?>

<section class="ch-content ch-admin-page">
    <div class="page-header">
        <div>
            <h1>Organisasi</h1>
            <p>Kelola organisasi kampus yang terdaftar di CampusHub.</p>
        </div>
        <a class="btn btn-primary" href="/admin/organisasi/tambah">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            Tambah Organisasi
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
                    <th scope="col">Logo</th>
                    <th scope="col">Nama Organisasi</th>
                    <th scope="col">Deskripsi</th>
                    <th scope="col">Event</th>
                    <th scope="col">Anggota</th>
                    <th scope="col">Dibuat</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($organizations === []) : ?>
                    <tr>
                        <td colspan="8">Belum ada organisasi.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($organizations as $index => $organization) : ?>
                        <?php
                        $logoFilename = is_string($organization['logo'] ?? null)
                            ? basename($organization['logo'])
                            : '';
                        $logoUrl = $logoFilename === ''
                            ? null
                            : '/assets/uploads/organisasi/' . rawurlencode($logoFilename);
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <?php if ($logoUrl !== null) : ?>
                                    <img
                                        src="<?= \CampusHub\Core\View::escape($logoUrl) ?>"
                                        alt="Logo <?= \CampusHub\Core\View::escape($organization['nama_organisasi'] ?? '') ?>"
                                        width="56"
                                        height="56"
                                        loading="lazy">
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= \CampusHub\Core\View::escape($organization['nama_organisasi'] ?? '') ?></td>
                            <td><?= nl2br(\CampusHub\Core\View::escape($organization['deskripsi'] ?? '')) ?></td>
                            <td><a href="/admin/event/?organization=<?= (int) ($organization['id_organisasi'] ?? 0) ?>"><?= (int) ($organization['jumlah_event'] ?? 0) ?> event</a></td>
                            <td><a href="/admin/users?organization=<?= (int) ($organization['id_organisasi'] ?? 0) ?>"><?= (int) ($organization['jumlah_anggota'] ?? 0) ?> anggota</a></td>
                            <td><?= \CampusHub\Core\View::escape($organization['created_at'] ?? '') ?></td>
                            <td class="ch-admin-inline-form">
                                <a
                                    class="btn btn-warning btn-sm"
                                    href="/admin/organisasi/edit?id=<?= (int) ($organization['id_organisasi'] ?? 0) ?>">
                                    Edit
                                </a>
                                <?php if ((int) ($organization['jumlah_event'] ?? 0) === 0 && (int) ($organization['jumlah_anggota'] ?? 0) === 0) : ?>
                                    <form method="post" action="/admin/organisasi/hapus">
                                        <?= \CampusHub\Core\View::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= (int) ($organization['id_organisasi'] ?? 0) ?>">
                                        <button class="btn btn-outline btn-sm" type="submit">Hapus</button>
                                    </form>
                                <?php else : ?>
                                    <span>Memiliki relasi</span>
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