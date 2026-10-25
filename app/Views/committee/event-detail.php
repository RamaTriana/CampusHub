<?php

$pageTitle = 'Detail Event | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/committee/layout-start.php';
$poster = basename((string) ($event['poster'] ?? ''));
$posterPath = dirname(__DIR__, 3) . '/assets/uploads/poster/' . $poster;
$showPoster = $poster !== '' && is_file($posterPath)
    && preg_match('/\A[a-zA-Z0-9._-]+\.(?:jpg|jpeg|png)\z/i', $poster) === 1;
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1><?= \CampusHub\Core\View::escape($event['judul']) ?></h1><p>Informasi event yang menjadi penugasan Anda. Perubahan data utama event hanya dapat dilakukan Admin.</p></div><a class="btn btn-outline" href="/committee/events">Kembali ke Event Saya</a></div>
    <div class="card ch-committee-detail-card">
        <?php if ($showPoster) : ?><img class="ch-committee-event-poster" src="/assets/uploads/poster/<?= rawurlencode($poster) ?>" alt="Poster <?= \CampusHub\Core\View::escape($event['judul']) ?>"><?php endif; ?>
        <dl class="ch-committee-event-details">
            <div><dt>Status</dt><dd><?= \CampusHub\Core\View::escape($event['status']) ?></dd></div>
            <div><dt>Organisasi</dt><dd><?= \CampusHub\Core\View::escape($event['nama_organisasi']) ?></dd></div>
            <div><dt>Kategori</dt><dd><?= \CampusHub\Core\View::escape($event['nama_kategori']) ?></dd></div>
            <div><dt>Jadwal</dt><dd><?= \CampusHub\Core\View::escape($event['tanggal']) ?> · <?= \CampusHub\Core\View::escape(substr((string) $event['jam'], 0, 5)) ?></dd></div>
            <div><dt>Lokasi</dt><dd><?= \CampusHub\Core\View::escape($event['lokasi']) ?></dd></div>
            <div><dt>Kuota</dt><dd><?= (int) $event['approved_participants'] ?> dari <?= (int) $event['kuota'] ?> peserta disetujui</dd></div>
            <div><dt>Kehadiran</dt><dd><?= (int) $event['attended'] ?> peserta tercatat hadir</dd></div>
        </dl>
        <div class="ch-committee-event-description"><h2>Deskripsi</h2><p><?= nl2br(\CampusHub\Core\View::escape($event['deskripsi'])) ?></p></div>
        <div class="ch-committee-shortcuts">
            <a class="btn btn-primary" href="/committee/participants?event=<?= (int) $event['id_event'] ?>">Lihat peserta</a>
            <a class="btn btn-outline" href="/committee/attendance?event=<?= (int) $event['id_event'] ?>">Kehadiran</a>
            <a class="btn btn-outline" href="/committee/documentation?event=<?= (int) $event['id_event'] ?>">Dokumentasi</a>
            <a class="btn btn-outline" href="/committee/certificates?event=<?= (int) $event['id_event'] ?>">Sertifikat</a>
        </div>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/includes/committee/layout-end.php'; ?>
