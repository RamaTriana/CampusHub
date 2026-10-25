<?php

$pageTitle = 'Sertifikat Saya | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/student/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Sertifikat Saya</h1><p><?= (int) $total ?> event pada pendaftaran yang disetujui, termasuk status penerbitan sertifikat.</p></div></div>
    <div class="table-container"><table class="table">
        <thead><tr><th>Event</th><th>Tanggal event</th><th>Kehadiran</th><th>Status sertifikat</th><th>Poin SKKM</th><th>File</th></tr></thead>
        <tbody>
        <?php if ($items === []) : ?><tr><td colspan="6">Belum ada pendaftaran event yang disetujui. Status sertifikat akan muncul setelah event diikuti.</td></tr>
        <?php else : foreach ($items as $item) :
            $filename = basename((string) ($item['file_sertifikat'] ?? ''));
            $safePdf = $filename !== ''
                && preg_match('/\A[a-zA-Z0-9._-]+\.pdf\z/i', $filename) === 1
                && is_file(dirname(__DIR__, 3) . '/assets/uploads/sertifikat/' . $filename);
        ?><tr>
            <td><a href="/student/events/detail?id=<?= (int) $item['id_event'] ?>"><?= \CampusHub\Core\View::escape($item['judul']) ?></a></td>
            <td><?= \CampusHub\Core\View::escape($item['tanggal']) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['status_hadir'] ?? 'Belum diperiksa') ?></td>
            <td><?= \CampusHub\Core\View::escape($item['certificate_status'] ?? 'Belum diterbitkan') ?></td>
            <td><?= $item['certificate_status'] === 'Terbit' ? \CampusHub\Core\View::escape(number_format((float) $item['skkm_points'], 2, ',', '.')) : '—' ?></td>
            <td><?php if ($item['certificate_status'] === 'Terbit' && $safePdf) : ?><a class="btn btn-outline btn-sm" href="/certificates/download?id=<?= (int) $item['id_sertifikat'] ?>">Unduh sertifikat</a><?php elseif ($item['certificate_status'] === 'Dicabut') : ?>Sertifikat dicabut<?php else : ?><?= $item['status_hadir'] === ATTENDANCE_PRESENT ? 'Belum diterbitkan' : 'Menunggu kehadiran' ?><?php endif; ?></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : ?>
        <nav class="ch-admin-pagination" aria-label="Halaman sertifikat saya">
            <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__, 3) . '/includes/student/layout-end.php'; ?>
