<?php

$pageTitle = 'Jelajahi Event | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/student/layout-start.php';
$exportQuery = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== ''));
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Jelajahi Event</h1><p><?= (int) $total ?> event yang dipublikasikan dan akan datang.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <form class="ch-admin-filters" method="get">
        <label>Cari judul event<input class="form-control" type="search" name="q" value="<?= \CampusHub\Core\View::escape($filters['q']) ?>" placeholder="Ketik nama event"></label>
        <label>Kategori<select class="form-control" name="category"><option value="">Semua kategori</option><?php foreach ($categories as $category) : ?><option value="<?= (int) $category['id_kategori'] ?>" <?= (string) $category['id_kategori'] === $filters['category'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($category['nama_kategori']) ?></option><?php endforeach; ?></select></label>
        <label>Organisasi<select class="form-control" name="organization"><option value="">Semua organisasi</option><?php foreach ($organizations as $organization) : ?><option value="<?= (int) $organization['id_organisasi'] ?>" <?= (string) $organization['id_organisasi'] === $filters['organization'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($organization['nama_organisasi']) ?></option><?php endforeach; ?></select></label>
        <label>Dari tanggal<input class="form-control" type="date" name="from" value="<?= \CampusHub\Core\View::escape($filters['from']) ?>"></label>
        <label>Sampai tanggal<input class="form-control" type="date" name="to" value="<?= \CampusHub\Core\View::escape($filters['to']) ?>"></label>
        <button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline" href="/student/events">Reset</a>
    </form>
    <div class="table-container"><table class="table ch-student-event-table">
        <thead><tr><th>Event</th><th>Jadwal</th><th>Organisasi</th><th>Kategori</th><th>Kuota tersedia</th><th>Pendaftaran</th><th></th></tr></thead>
        <tbody>
        <?php if ($events === []) : ?><tr><td colspan="7">Belum ada event yang sesuai. Coba ubah filter atau lihat kembali nanti.</td></tr>
        <?php else : foreach ($events as $event) : ?><tr>
            <td><div class="ch-student-event-cell">
                <?php if ($event['poster_url'] !== null) : ?><img src="<?= \CampusHub\Core\View::escape($event['poster_url']) ?>" alt="" loading="lazy"><?php else : ?><span class="ch-student-poster-placeholder" aria-hidden="true">CH</span><?php endif; ?>
                <a href="/student/events/detail?id=<?= (int) $event['id_event'] ?>"><?= \CampusHub\Core\View::escape($event['judul']) ?></a>
            </div></td>
            <td><?= \CampusHub\Core\View::escape($event['tanggal']) ?><br><small><?= \CampusHub\Core\View::escape(substr((string) $event['jam'], 0, 5)) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($event['nama_organisasi']) ?></td>
            <td><?= \CampusHub\Core\View::escape($event['nama_kategori']) ?></td>
            <td><?= (int) $event['remaining'] ?> / <?= (int) $event['kuota'] ?></td>
            <td><?= $event['id_pendaftaran'] === null ? 'Belum mendaftar' : \CampusHub\Core\View::escape($event['registration_status']) ?></td>
            <td><a class="btn btn-outline btn-sm" href="/student/events/detail?id=<?= (int) $event['id_event'] ?>">Lihat detail</a></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : ?>
        <nav class="ch-admin-pagination" aria-label="Halaman event">
            <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($exportQuery) ?>&amp;page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($exportQuery) ?>&amp;page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__, 3) . '/includes/student/layout-end.php'; ?>
