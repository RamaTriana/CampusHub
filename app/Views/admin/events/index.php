<?php

$pageTitle = 'Event | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css', 'alerts.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
$filters = $filters ?? ['q' => '', 'status' => '', 'category' => '', 'organization' => '', 'from' => '', 'to' => '', 'sort' => ''];
$references = $references ?? ['categories' => [], 'organizations' => []];
$total = $total ?? count($events);
$page = $page ?? 1;
$pages = $pages ?? 1;
$queryString = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== ''));
?>

        <section class="ch-content ch-admin-page">
            <div class="page-header">
                <div>
                    <h1>Event</h1>
                    <p><?= (int) $total ?> event terdaftar. Cari, filter, dan urutkan event CampusHub.</p>
                </div>
                <a class="btn btn-primary" href="/admin/event/tambah">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    Tambah Event
                </a>
            </div>

            <?php if (is_array($flash ?? null)) : ?>
                <div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>">
                    <?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?>
                </div>
            <?php endif; ?>

            <form class="ch-admin-filters" method="get">
                <label>Cari event
                    <input class="form-control" type="search" name="q" value="<?= \CampusHub\Core\View::escape($filters['q']) ?>" placeholder="Judul, lokasi, organisasi, panitia">
                </label>
                <label>Status
                    <select class="form-control" name="status">
                        <option value="">Semua status</option>
                        <?php foreach (['Draft', 'Publish', 'Selesai'] as $status) : ?>
                            <option value="<?= $status ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Kategori
                    <select class="form-control" name="category">
                        <option value="">Semua kategori</option>
                        <?php foreach ($references['categories'] as $category) : ?>
                            <option value="<?= (int) $category['id_kategori'] ?>" <?= $filters['category'] === (string) $category['id_kategori'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($category['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Organisasi
                    <select class="form-control" name="organization">
                        <option value="">Semua organisasi</option>
                        <?php foreach ($references['organizations'] as $organization) : ?>
                            <option value="<?= (int) $organization['id_organisasi'] ?>" <?= $filters['organization'] === (string) $organization['id_organisasi'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($organization['nama_organisasi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Dari tanggal<input class="form-control" type="date" name="from" value="<?= \CampusHub\Core\View::escape($filters['from']) ?>"></label>
                <label>Sampai tanggal<input class="form-control" type="date" name="to" value="<?= \CampusHub\Core\View::escape($filters['to']) ?>"></label>
                <label>Urutkan
                    <select class="form-control" name="sort">
                        <option value="">Terbaru dibuat</option>
                        <option value="title" <?= $filters['sort'] === 'title' ? 'selected' : '' ?>>Judul</option>
                        <option value="date_asc" <?= $filters['sort'] === 'date_asc' ? 'selected' : '' ?>>Tanggal terdekat</option>
                        <option value="date_desc" <?= $filters['sort'] === 'date_desc' ? 'selected' : '' ?>>Tanggal terjauh</option>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Terapkan</button>
                <a class="btn btn-outline" href="/admin/event/">Reset</a>
            </form>

            <div class="table-container">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Poster</th>
                        <th scope="col">Judul</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Organisasi</th>
                        <th scope="col">Panitia</th>
                        <th scope="col">Tanggal &amp; Jam</th>
                        <th scope="col">Lokasi</th>
                        <th scope="col">Kuota</th>
                        <th scope="col">Pendaftar</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($events === []) : ?>
                        <tr>
                            <td colspan="12">Belum ada event yang cocok dengan filter.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($events as $index => $event) : ?>
                            <?php
                            $posterFilename = is_string($event['poster'] ?? null)
                                ? basename($event['poster'])
                                : '';
                            if (in_array($posterFilename, ['.', '..'], true)) {
                                $posterFilename = '';
                            }
                            $posterUrl = $posterFilename === ''
                                ? null
                                : '/assets/uploads/poster/' . rawurlencode($posterFilename);
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <?php if ($posterUrl !== null) : ?>
                                        <img
                                            src="<?= \CampusHub\Core\View::escape($posterUrl) ?>"
                                            alt="Poster <?= \CampusHub\Core\View::escape($event['judul'] ?? '') ?>"
                                            width="72"
                                            height="72"
                                            loading="lazy">
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?= \CampusHub\Core\View::escape($event['judul'] ?? '') ?></td>
                                <td><?= \CampusHub\Core\View::escape($event['nama_kategori'] ?? '') ?></td>
                                <td><?= \CampusHub\Core\View::escape($event['nama_organisasi'] ?? '') ?></td>
                                <td><?= \CampusHub\Core\View::escape($event['nama_panitia'] ?? '') ?></td>
                                <td>
                                    <?= \CampusHub\Core\View::escape($event['tanggal'] ?? '') ?><br>
                                    <?= \CampusHub\Core\View::escape(substr((string) ($event['jam'] ?? ''), 0, 5)) ?>
                                </td>
                                <td><?= \CampusHub\Core\View::escape($event['lokasi'] ?? '') ?></td>
                                <td><?= (int) ($event['kuota'] ?? 0) ?></td>
                                <td><?= (int) ($event['jumlah_pendaftar'] ?? 0) ?></td>
                                <td><?= \CampusHub\Core\View::escape($event['status'] ?? '') ?></td>
                                <td>
                                    <a
                                        class="btn btn-outline btn-sm"
                                        href="/admin/event/detail?id=<?= (int) ($event['id_event'] ?? 0) ?>">
                                        Detail
                                    </a>
                                    <a
                                        class="btn btn-warning btn-sm"
                                        href="/admin/event/edit?id=<?= (int) ($event['id_event'] ?? 0) ?>">
                                        Edit
                                    </a>
                                    <?php if ((int) ($event['jumlah_pendaftar'] ?? 0) === 0) : ?>
                                        <form class="ch-admin-inline-form" method="post" action="/admin/event/hapus">
                                            <?= \CampusHub\Core\View::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= (int) ($event['id_event'] ?? 0) ?>">
                                            <button class="btn btn-outline btn-sm" type="submit">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pages > 1) : ?>
                <nav class="ch-admin-pagination" aria-label="Halaman event">
                    <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($queryString) ?>&amp;page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
                    <span>Halaman <?= $page ?> dari <?= $pages ?></span>
                    <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($queryString) ?>&amp;page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
                </nav>
            <?php endif; ?>
        </section>

<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
