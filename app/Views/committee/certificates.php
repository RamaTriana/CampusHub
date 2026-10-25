<?php

$pageTitle = 'Sertifikat | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/committee/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Sertifikat</h1><p><?= (int) $total ?> peserta pada event yang ditugaskan. Panitia dapat menerbitkan sertifikat dan mencatat poin SKKM setelah kehadiran terverifikasi.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <form class="ch-admin-filters" method="get">
        <label>Cari peserta / event<input class="form-control" type="search" name="q" value="<?= \CampusHub\Core\View::escape($filters['q']) ?>" placeholder="Nama, NIM, event"></label>
        <label>Event<select class="form-control" name="event"><option value="">Semua event saya</option><?php foreach ($events as $event) : ?><option value="<?= (int) $event['id_event'] ?>" <?= (string) $event['id_event'] === $filters['event'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($event['judul']) ?></option><?php endforeach; ?></select></label>
        <label>Status sertifikat<select class="form-control" name="status"><option value="">Semua status</option><?php foreach (['Belum diterbitkan', 'Terbit', 'Dicabut'] as $status) : ?><option value="<?= \CampusHub\Core\View::escape($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($status) ?></option><?php endforeach; ?></select></label>
        <button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline" href="/committee/certificates">Reset</a>
    </form>
    <div class="table-container"><table class="table">
        <thead><tr><th>Peserta</th><th>Event</th><th>Kehadiran</th><th>Poin SKKM</th><th>Sertifikat</th></tr></thead>
        <tbody>
        <?php if ($items === []) : ?><tr><td colspan="5">Belum ada peserta yang cocok dengan filter.</td></tr>
        <?php else : foreach ($items as $item) :
            $filename = basename((string) ($item['file_sertifikat'] ?? ''));
            $safePdf = $filename !== ''
                && preg_match('/\A[a-zA-Z0-9._-]+\.pdf\z/i', $filename) === 1
                && is_file(dirname(__DIR__, 3) . '/assets/uploads/sertifikat/' . $filename);
            $eligible = $item['status_hadir'] === ATTENDANCE_PRESENT;
            $certificateStatus = (string) ($item['status_sertifikat'] ?? '');
        ?><tr>
            <td><?= \CampusHub\Core\View::escape($item['nama']) ?><br><small><?= \CampusHub\Core\View::escape($item['nim'] ?? '') ?></small></td>
            <td><?= \CampusHub\Core\View::escape($item['judul']) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['status_hadir']) ?></td>
            <td><?= $certificateStatus !== '' ? \CampusHub\Core\View::escape(number_format((float) $item['skkm_points'], 2, ',', '.')) : '—' ?></td>
            <td>
                <?php if ($certificateStatus === 'Terbit' && $safePdf) : ?>
                    <a class="btn btn-outline btn-sm" href="/certificates/download?id=<?= (int) $item['id_sertifikat'] ?>">Lihat PDF</a>
                <?php elseif ($certificateStatus === 'Dicabut') : ?>
                    Sertifikat dicabut
                <?php elseif ($eligible) : ?>
                    <form class="ch-committee-certificate-form" action="/committee/certificates/issue" method="post" enctype="multipart/form-data">
                        <?= \CampusHub\Core\View::csrfField() ?>
                        <input type="hidden" name="id_pendaftaran" value="<?= (int) $item['id_pendaftaran'] ?>">
                        <label>Poin SKKM<input class="form-control" type="number" name="skkm_points" min="0" max="999999.99" step="0.01" value="0" required></label>
                        <label>File PDF (maks. 5 MB)<input class="form-control" type="file" name="certificate" accept=".pdf,application/pdf" required></label>
                        <button class="btn btn-primary btn-sm" type="submit">Terbitkan sertifikat</button>
                    </form>
                <?php else : ?>
                    Menunggu konfirmasi kehadiran
                <?php endif; ?>
            </td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : $query = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== '')); ?>
        <nav class="ch-admin-pagination" aria-label="Halaman sertifikat event">
            <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__, 3) . '/includes/committee/layout-end.php'; ?>
