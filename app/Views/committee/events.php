<?php

$pageTitle = 'Event Saya | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/committee/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Event Saya</h1><p><?= (int) $total ?> event yang ditugaskan kepada akun panitia ini. Keanggotaan organisasi saja tidak memberikan akses ke semua event.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <form class="ch-admin-filters" method="get">
        <label>Cari event<input class="form-control" type="search" name="q" value="<?= \CampusHub\Core\View::escape($filters['q']) ?>" placeholder="Judul, organisasi, lokasi"></label>
        <label>Status<select class="form-control" name="status"><option value="">Semua status</option><?php foreach (['Draft', 'Publish', 'Selesai'] as $status) : ?><option value="<?= $status ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select></label>
        <button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline" href="/committee/events">Reset</a>
    </form>
    <div class="table-container"><table class="table">
        <thead><tr><th>Event</th><th>Jadwal</th><th>Organisasi / kategori</th><th>Lokasi</th><th>Kuota</th><th>Total pendaftar</th><th>Disetujui</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if ($events === []) : ?><tr><td colspan="9">Belum ada event yang ditugaskan atau tidak ada yang cocok dengan filter.</td></tr>
        <?php else : foreach ($events as $event) : ?><tr>
            <td><strong><?= \CampusHub\Core\View::escape($event['judul']) ?></strong><br><small><?= \CampusHub\Core\View::escape(mb_strimwidth(trim(strip_tags((string) $event['deskripsi'])), 0, 100, '…', 'UTF-8')) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($event['tanggal']) ?><br><small><?= \CampusHub\Core\View::escape(substr((string) $event['jam'], 0, 5)) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($event['nama_organisasi']) ?><br><small><?= \CampusHub\Core\View::escape($event['nama_kategori']) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($event['lokasi']) ?></td>
            <td><?= (int) $event['approved_participants'] ?> / <?= (int) $event['kuota'] ?></td>
            <td><a href="/committee/participants?event=<?= (int) $event['id_event'] ?>"><?= (int) $event['total_registrations'] ?></a></td>
            <td><a href="/committee/participants?event=<?= (int) $event['id_event'] ?>&amp;status=Disetujui"><?= (int) $event['approved_participants'] ?></a></td>
            <td><?= \CampusHub\Core\View::escape($event['status']) ?></td>
            <td><a class="btn btn-outline btn-sm" href="/committee/events/detail?id=<?= (int) $event['id_event'] ?>">Detail</a></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : $query = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== '')); ?>
        <nav class="ch-admin-pagination" aria-label="Halaman event panitia">
            <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__, 3) . '/includes/committee/layout-end.php'; ?>
