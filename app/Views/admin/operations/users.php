<?php

$pageTitle = 'Pengguna | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css', 'alerts.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
$queryString = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== ''));
?>
<section class="ch-content ch-admin-page">
    <div class="page-header">
        <div>
            <h1>Pengguna</h1>
            <p><?= (int) $total ?> akun terdaftar. Password tidak ditampilkan pada halaman ini.</p>
        </div>
    </div>
    <?php if (is_array($flash ?? null)) : ?>
        <div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>">
            <?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?>
        </div>
    <?php endif; ?>
    <form class="ch-admin-filters" method="get">
        <label>Cari pengguna
            <input class="form-control" type="search" name="q" value="<?= \CampusHub\Core\View::escape($filters['q']) ?>" placeholder="Nama, NIM, email, username">
        </label>
        <label>Role
            <select class="form-control" name="role">
                <option value="">Semua role</option>
                <?php foreach ([ROLE_ADMIN, ROLE_COMMITTEE, ROLE_STUDENT] as $roleOption) : ?>
                    <option value="<?= \CampusHub\Core\View::escape($roleOption) ?>" <?= $filters['role'] === $roleOption ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($roleOption) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select class="form-control" name="status">
                <option value="">Semua status</option>
                <?php foreach ([STATUS_ACTIVE, STATUS_INACTIVE] as $statusOption) : ?>
                    <option value="<?= \CampusHub\Core\View::escape($statusOption) ?>" <?= $filters['status'] === $statusOption ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($statusOption) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Organisasi
            <select class="form-control" name="organization">
                <option value="">Semua organisasi</option>
                <?php foreach ($organizations as $organization) : ?>
                    <option value="<?= (int) $organization['id_organisasi'] ?>" <?= $filters['organization'] === (string) $organization['id_organisasi'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($organization['nama_organisasi']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="btn btn-primary" type="submit">Terapkan</button>
        <a class="btn btn-outline" href="/admin/users">Reset</a>
    </form>
    <div class="table-container">
        <table class="table">
            <thead><tr><th>Nama / NIM</th><th>Email / Username</th><th>Organisasi</th><th>Pengaturan akses</th><th>Detail</th></tr></thead>
            <tbody>
            <?php if ($users === []) : ?>
                <tr><td colspan="5">Tidak ada pengguna yang cocok dengan filter.</td></tr>
            <?php else : foreach ($users as $user) : ?>
                <tr>
                    <td><?= \CampusHub\Core\View::escape($user['nama']) ?><br><small><?= \CampusHub\Core\View::escape($user['nim'] ?: 'NIM tidak tersedia') ?></small></td>
                    <td><?= \CampusHub\Core\View::escape($user['email']) ?><br><small><?= \CampusHub\Core\View::escape($user['username']) ?></small></td>
                    <td><?= \CampusHub\Core\View::escape($user['nama_organisasi'] ?? '—') ?></td>
                    <td>
                        <form class="ch-admin-inline-form" action="/admin/users/perbarui" method="post">
                            <?= \CampusHub\Core\View::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $user['id_user'] ?>">
                            <select name="role" aria-label="Role <?= \CampusHub\Core\View::escape($user['nama']) ?>">
                                <?php foreach ([ROLE_ADMIN, ROLE_COMMITTEE, ROLE_STUDENT] as $roleOption) : ?>
                                    <option value="<?= \CampusHub\Core\View::escape($roleOption) ?>" <?= $user['role'] === $roleOption ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($roleOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="status" aria-label="Status <?= \CampusHub\Core\View::escape($user['nama']) ?>">
                                <?php foreach ([STATUS_ACTIVE, STATUS_INACTIVE] as $statusOption) : ?>
                                    <option value="<?= \CampusHub\Core\View::escape($statusOption) ?>" <?= $user['status'] === $statusOption ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($statusOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="id_organisasi" aria-label="Organisasi <?= \CampusHub\Core\View::escape($user['nama']) ?>">
                                <option value="">Tanpa organisasi</option>
                                <?php foreach ($organizations as $organization) : ?>
                                    <option value="<?= (int) $organization['id_organisasi'] ?>" <?= (int) ($user['id_organisasi'] ?? 0) === (int) $organization['id_organisasi'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($organization['nama_organisasi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
                        </form>
                    </td>
                    <td><a class="btn btn-outline btn-sm" href="/admin/users/detail?id=<?= (int) $user['id_user'] ?>">Lihat</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1) : ?>
        <nav class="ch-admin-pagination" aria-label="Halaman pengguna">
            <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($queryString) ?>&amp;page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($queryString) ?>&amp;page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
