<?php

$pageTitle = 'Pendaftaran Saya | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/student/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Pendaftaran Saya</h1><p><?= (int) $total ?> pendaftaran · pantau status semua event yang pernah Anda daftarkan.</p></div><a class="btn btn-primary" href="/student/events">Cari event</a></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <div class="table-container"><table class="table">
        <thead><tr><th>Event</th><th>Jadwal &amp; lokasi</th><th>Tanggal daftar</th><th>Status pendaftaran</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if ($items === []) : ?><tr><td colspan="5">Belum ada pendaftaran. Jelajahi event yang sedang dibuka.</td></tr>
        <?php else : foreach ($items as $item) : ?>
            <?php $canCancel = in_array($item['status'], [STATUS_PENDING, STATUS_APPROVED], true) && $item['event_status'] === 'Publish' && $item['tanggal'] >= date('Y-m-d'); ?>
            <tr>
                <td><a href="/student/events/detail?id=<?= (int) $item['id_event'] ?>"><strong><?= \CampusHub\Core\View::escape($item['judul']) ?></strong></a><br><small><?= \CampusHub\Core\View::escape($item['nama_organisasi']) ?></small></td>
                <td><?= \CampusHub\Core\View::escape($item['tanggal']) ?> · <?= \CampusHub\Core\View::escape(substr((string) $item['jam'], 0, 5)) ?><br><small><?= \CampusHub\Core\View::escape($item['lokasi']) ?></small></td>
                <td><?= \CampusHub\Core\View::escape($item['tanggal_daftar']) ?></td>
                <td><?= \CampusHub\Core\View::escape($item['status']) ?></td>
                <td><?php if ($canCancel) : ?><form method="post" action="/student/registration/cancel" onsubmit="return confirm('Batalkan pendaftaran event ini?')">
                    <?= \CampusHub\Core\View::csrfField() ?><input type="hidden" name="id_pendaftaran" value="<?= (int) $item['id_pendaftaran'] ?>">
                    <button class="btn btn-warning btn-sm" type="submit">Batalkan</button>
                </form><?php else : ?><?= in_array($item['status'], [STATUS_PENDING, STATUS_APPROVED], true) ? 'Pembatalan ditutup' : '—' ?><?php endif; ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : ?>
        <nav class="ch-admin-pagination" aria-label="Halaman pendaftaran">
            <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__, 3) . '/includes/student/layout-end.php'; ?>
