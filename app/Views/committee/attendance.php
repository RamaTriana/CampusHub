<?php

$pageTitle = 'Kehadiran | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/committee/layout-start.php';
$summaryTotal = ['approved' => 0, 'present' => 0, 'absent' => 0];
foreach ($summary as $eventSummary) {
    foreach ($summaryTotal as $key => $value) {
        $summaryTotal[$key] += (int) $eventSummary[$key];
    }
}
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Kehadiran</h1><p><?= (int) $total ?> peserta dengan pendaftaran disetujui pada event Anda.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <div class="ch-committee-attendance-summary">
        <article><span>Disetujui</span><strong><?= $summaryTotal['approved'] ?></strong></article>
        <article><span>Hadir</span><strong><?= $summaryTotal['present'] ?></strong></article>
        <article><span>Tidak hadir</span><strong><?= $summaryTotal['absent'] ?></strong></article>
    </div>
    <form class="ch-admin-filters" method="get">
        <label>Cari peserta<input class="form-control" type="search" name="q" value="<?= \CampusHub\Core\View::escape($filters['q']) ?>" placeholder="Nama atau NIM"></label>
        <label>Event<select class="form-control" name="event"><option value="">Semua event saya</option><?php foreach ($events as $event) : ?><option value="<?= (int) $event['id_event'] ?>" <?= (string) $event['id_event'] === $filters['event'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($event['judul']) ?></option><?php endforeach; ?></select></label>
        <label>Status<select class="form-control" name="status"><option value="">Semua status</option><?php foreach ([ATTENDANCE_PRESENT, ATTENDANCE_ABSENT, 'Belum diperiksa'] as $status) : ?><option value="<?= \CampusHub\Core\View::escape($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($status) ?></option><?php endforeach; ?></select></label>
        <button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline" href="/committee/attendance">Reset</a>
    </form>
    <div class="table-container"><table class="table">
        <thead><tr><th>Peserta</th><th>Event</th><th>Status hadir</th><th>Waktu dicatat</th><th>Catat kehadiran</th></tr></thead>
        <tbody>
        <?php if ($items === []) : ?><tr><td colspan="5">Belum ada pendaftaran disetujui yang cocok dengan filter.</td></tr>
        <?php else : foreach ($items as $item) : ?><tr>
            <td><?= \CampusHub\Core\View::escape($item['nama']) ?><br><small><?= \CampusHub\Core\View::escape($item['nim'] ?: $item['email']) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($item['judul']) ?><br><small><?= \CampusHub\Core\View::escape($item['tanggal_event']) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($item['status_hadir']) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['waktu_checkin'] ?? '—') ?></td>
            <td><form class="ch-attendance-picker" method="post" action="/committee/attendance/update" data-auto-submit>
                <?= \CampusHub\Core\View::csrfField() ?><input type="hidden" name="id_pendaftaran" value="<?= (int) $item['id_pendaftaran'] ?>">
                <fieldset>
                    <legend class="sr-only">Kehadiran <?= \CampusHub\Core\View::escape($item['nama']) ?></legend>
                    <label class="ch-attendance-choice ch-attendance-choice--present">
                        <input type="radio" name="status" value="<?= \CampusHub\Core\View::escape(ATTENDANCE_PRESENT) ?>" <?= $item['status_hadir'] === ATTENDANCE_PRESENT ? 'checked' : '' ?>>
                        <span>Hadir</span>
                    </label>
                    <label class="ch-attendance-choice ch-attendance-choice--absent">
                        <input type="radio" name="status" value="<?= \CampusHub\Core\View::escape(ATTENDANCE_ABSENT) ?>" <?= $item['status_hadir'] === ATTENDANCE_ABSENT ? 'checked' : '' ?>>
                        <span>Tidak hadir</span>
                    </label>
                </fieldset>
            </form></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : $query = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== '')); ?>
        <nav class="ch-admin-pagination" aria-label="Halaman kehadiran">
            <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__, 3) . '/includes/committee/layout-end.php'; ?>
