<?php

$pageTitle = 'Peserta Event | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/committee/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Peserta</h1><p><?= (int) $total ?> pendaftaran pada event yang ditugaskan kepada Anda.</p></div><a class="btn btn-primary" href="/committee/participants/export<?= $exportQuery !== '' ? '?' . \CampusHub\Core\View::escape($exportQuery) : '' ?>">Export CSV</a></div>
    <form class="ch-admin-filters" method="get">
        <label>Cari peserta<input class="form-control" type="search" name="q" value="<?= \CampusHub\Core\View::escape($filters['q']) ?>" placeholder="Nama, NIM, email"></label>
        <label>Event<select class="form-control" name="event"><option value="">Semua event saya</option><?php foreach ($events as $event) : ?><option value="<?= (int) $event['id_event'] ?>" <?= (string) $event['id_event'] === $filters['event'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($event['judul']) ?></option><?php endforeach; ?></select></label>
        <label>Status pendaftaran<select class="form-control" name="status"><option value="">Semua status</option><?php foreach ([STATUS_PENDING, STATUS_APPROVED, STATUS_REJECTED] as $status) : ?><option value="<?= \CampusHub\Core\View::escape($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($status) ?></option><?php endforeach; ?></select></label>
        <button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline" href="/committee/participants">Reset</a>
    </form>
    <div class="table-container"><table class="table">
        <thead><tr><th>Peserta</th><th>Event</th><th>Tanggal daftar</th><th>Status pendaftaran</th><th>Kehadiran</th></tr></thead>
        <tbody>
        <?php if ($participants === []) : ?><tr><td colspan="5">Belum ada peserta yang cocok dengan filter.</td></tr>
        <?php else : foreach ($participants as $participant) : ?><tr>
            <td><?= \CampusHub\Core\View::escape($participant['nama']) ?><br><small><?= \CampusHub\Core\View::escape($participant['nim'] ?: $participant['email']) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($participant['judul']) ?><br><small><?= \CampusHub\Core\View::escape($participant['tanggal_event']) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($participant['tanggal_daftar']) ?></td>
            <td><?= \CampusHub\Core\View::escape($participant['status']) ?></td>
            <td><?= \CampusHub\Core\View::escape($participant['status_hadir']) ?></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : $query = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== '')); ?>
        <nav class="ch-admin-pagination" aria-label="Halaman peserta">
            <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__, 3) . '/includes/committee/layout-end.php'; ?>
