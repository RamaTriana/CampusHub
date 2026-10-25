<?php

$pageTitle = 'Sertifikat | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css', 'alerts.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
$paginationQuery = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== ''));
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Sertifikat</h1><p><?= (int) $total ?> sertifikat terhubung dengan kehadiran peserta.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <form class="ch-admin-filters" method="get">
        <label>Cari peserta<input class="form-control" type="search" name="q" value="<?= \CampusHub\Core\View::escape($filters['q'] ?? '') ?>" placeholder="Nama, NIM, email"></label>
        <label>Event<select class="form-control" name="event"><option value="">Semua event</option><?php foreach ($events as $event) : ?><option value="<?= (int) $event['id_event'] ?>" <?= (string) $event['id_event'] === $filters['event'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($event['judul']) ?></option><?php endforeach; ?></select></label>
        <label>Status<select class="form-control" name="status"><option value="">Semua status</option><option value="Terbit" <?= ($filters['status'] ?? '') === 'Terbit' ? 'selected' : '' ?>>Terbit</option><option value="Dicabut" <?= ($filters['status'] ?? '') === 'Dicabut' ? 'selected' : '' ?>>Dicabut</option></select></label>
        <label>Dari tanggal<input class="form-control" type="date" name="from" value="<?= \CampusHub\Core\View::escape($filters['from']) ?>"></label>
        <label>Sampai tanggal<input class="form-control" type="date" name="to" value="<?= \CampusHub\Core\View::escape($filters['to']) ?>"></label>
        <button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline" href="/admin/certificates">Reset</a>
    </form>
    <div class="table-container"><table class="table">
        <thead><tr><th>Peserta</th><th>Event</th><th>Kehadiran</th><th>Status sertifikat</th><th>Poin SKKM</th><th>Diterbitkan</th><th>File</th></tr></thead>
        <tbody>
        <?php if ($items === []) : ?><tr><td colspan="7">Belum ada sertifikat yang cocok dengan filter.</td></tr><?php else : foreach ($items as $item) :
            $filename = basename((string) $item['file_sertifikat']);
            $safePdf = preg_match('/\A[a-zA-Z0-9._-]+\.pdf\z/i', $filename) === 1;
        ?><tr>
            <td><?= \CampusHub\Core\View::escape($item['nama']) ?><br><small><?= \CampusHub\Core\View::escape($item['nim'] ?: $item['email']) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($item['judul']) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['status_hadir']) ?><br><small><?= \CampusHub\Core\View::escape($item['waktu_checkin'] ?? '') ?></small></td>
            <td><?= \CampusHub\Core\View::escape($item['status']) ?></td>
            <td><?= \CampusHub\Core\View::escape(number_format((float) $item['skkm_points'], 2, ',', '.')) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['tanggal_upload']) ?></td>
            <td><?php if ($item['status'] === 'Terbit' && $safePdf) : ?><a href="/certificates/download?id=<?= (int) $item['id_sertifikat'] ?>">Lihat PDF</a><?php else : ?><?= \CampusHub\Core\View::escape($filename) ?><?php endif; ?></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : ?><nav class="ch-admin-pagination" aria-label="Halaman sertifikat">
        <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($paginationQuery) ?><?= $paginationQuery !== '' ? '&amp;' : '' ?>page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
        <span>Halaman <?= $page ?> dari <?= $pages ?></span>
        <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($paginationQuery) ?><?= $paginationQuery !== '' ? '&amp;' : '' ?>page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
    </nav><?php endif; ?>
</section>
<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
