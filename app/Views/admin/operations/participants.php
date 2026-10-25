<?php

$pageTitle = 'Pendaftaran & Peserta | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css', 'alerts.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
$exportQuery = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== ''));
?>
<section class="ch-content ch-admin-page">
    <div class="page-header">
        <div><h1>Pendaftaran &amp; Peserta</h1><p><?= (int) $total ?> pendaftaran tercatat lintas event.</p></div>
        <a class="btn btn-primary" href="/admin/participants/export<?= $exportQuery !== '' ? '?' . \CampusHub\Core\View::escape($exportQuery) : '' ?>">Export CSV</a>
    </div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <form class="ch-admin-filters" method="get">
        <label>Cari peserta / event<input class="form-control" type="search" name="q" value="<?= \CampusHub\Core\View::escape($filters['q']) ?>" placeholder="Nama, NIM, email, event"></label>
        <label>Event<select class="form-control" name="event"><option value="">Semua event</option><?php foreach ($events as $event) : ?><option value="<?= (int) $event['id_event'] ?>" <?= (string) $event['id_event'] === $filters['event'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($event['judul']) ?></option><?php endforeach; ?></select></label>
        <label>Status<select class="form-control" name="status"><option value="">Semua status</option><?php foreach ([STATUS_PENDING, STATUS_APPROVED, STATUS_REJECTED] as $option) : ?><option value="<?= \CampusHub\Core\View::escape($option) ?>" <?= $filters['status'] === $option ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($option) ?></option><?php endforeach; ?></select></label>
        <label>Dari tanggal<input class="form-control" type="date" name="from" value="<?= \CampusHub\Core\View::escape($filters['from']) ?>"></label>
        <label>Sampai tanggal<input class="form-control" type="date" name="to" value="<?= \CampusHub\Core\View::escape($filters['to']) ?>"></label>
        <button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline" href="/admin/participants">Reset</a>
    </form>
    <div class="table-container"><table class="table">
        <thead><tr><th>Peserta</th><th>Event</th><th>Waktu daftar</th><th>Status</th><th>Kehadiran</th></tr></thead>
        <tbody>
        <?php if ($registrations === []) : ?><tr><td colspan="5">Belum ada pendaftaran yang cocok. Pendaftaran baru akan muncul di sini.</td></tr>
        <?php else : foreach ($registrations as $registration) : ?><tr>
            <td><?= \CampusHub\Core\View::escape($registration['nama']) ?><br><small><?= \CampusHub\Core\View::escape($registration['nim'] ?: $registration['email']) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($registration['judul']) ?><br><small>Event <?= \CampusHub\Core\View::escape($registration['tanggal_event']) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($registration['tanggal_daftar']) ?></td>
            <td><?= \CampusHub\Core\View::escape($registration['status']) ?></td>
            <td><?= \CampusHub\Core\View::escape($registration['status_hadir']) ?><?= $registration['waktu_checkin'] ? '<br><small>' . \CampusHub\Core\View::escape($registration['waktu_checkin']) . '</small>' : '' ?></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : ?><nav class="ch-admin-pagination" aria-label="Halaman pendaftaran">
        <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($exportQuery) ?>&amp;page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?><span>Halaman <?= $page ?> dari <?= $pages ?></span><?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($exportQuery) ?>&amp;page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
    </nav><?php endif; ?>
</section>
<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
