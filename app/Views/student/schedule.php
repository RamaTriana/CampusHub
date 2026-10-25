<?php

$pageTitle = 'Jadwal Saya | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/student/layout-start.php';
$renderSchedule = static function (array $items): void { ?>
    <div class="table-container"><table class="table">
        <thead><tr><th>Event</th><th>Waktu</th><th>Lokasi</th><th>Organisasi</th><th>Status kehadiran</th></tr></thead>
        <tbody>
        <?php if ($items === []) : ?><tr><td colspan="5">Belum ada event pada bagian ini.</td></tr>
        <?php else : foreach ($items as $item) : ?><tr>
            <td><a href="/student/events/detail?id=<?= (int) $item['id_event'] ?>"><?= \CampusHub\Core\View::escape($item['judul']) ?></a><br><small><?= \CampusHub\Core\View::escape($item['nama_kategori']) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($item['tanggal']) ?><br><small><?= \CampusHub\Core\View::escape(substr((string) $item['jam'], 0, 5)) ?></small></td>
            <td><?= \CampusHub\Core\View::escape($item['lokasi']) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['nama_organisasi']) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['status_hadir'] ?? 'Belum diperiksa') ?></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
<?php }; ?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Jadwal Saya</h1><p>Event dengan pendaftaran yang sudah disetujui panitia.</p></div></div>
    <section class="ch-committee-section" aria-labelledby="upcoming-schedule">
        <div class="ch-section-heading"><div><span class="ch-eyebrow">AKAN DATANG</span><h2 id="upcoming-schedule">Jadwal mendatang</h2></div><span class="ch-section-aside"><?= (int) $upcomingTotal ?> event</span></div>
        <?php $renderSchedule($upcoming); ?>
        <?php if ($upcomingPages > 1) : ?>
            <nav class="ch-admin-pagination" aria-label="Halaman jadwal mendatang">
                <?php if ($upcomingPage > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape(http_build_query(['upcoming_page' => $upcomingPage - 1, 'completed_page' => $completedPage])) ?>">Sebelumnya</a><?php endif; ?>
                <span>Halaman <?= $upcomingPage ?> dari <?= $upcomingPages ?></span>
                <?php if ($upcomingPage < $upcomingPages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape(http_build_query(['upcoming_page' => $upcomingPage + 1, 'completed_page' => $completedPage])) ?>">Berikutnya</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
    <section class="ch-committee-section" aria-labelledby="completed-schedule">
        <div class="ch-section-heading"><div><span class="ch-eyebrow">ARSIP</span><h2 id="completed-schedule">Event selesai</h2></div><span class="ch-section-aside"><?= (int) $completedTotal ?> event</span></div>
        <?php $renderSchedule($completed); ?>
        <?php if ($completedPages > 1) : ?>
            <nav class="ch-admin-pagination" aria-label="Halaman event selesai">
                <?php if ($completedPage > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape(http_build_query(['upcoming_page' => $upcomingPage, 'completed_page' => $completedPage - 1])) ?>">Sebelumnya</a><?php endif; ?>
                <span>Halaman <?= $completedPage ?> dari <?= $completedPages ?></span>
                <?php if ($completedPage < $completedPages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape(http_build_query(['upcoming_page' => $upcomingPage, 'completed_page' => $completedPage + 1])) ?>">Berikutnya</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</section>
<?php require dirname(__DIR__, 3) . '/includes/student/layout-end.php'; ?>
