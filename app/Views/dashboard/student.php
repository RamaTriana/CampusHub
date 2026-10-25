<?php

$pageTitle = 'Dashboard Mahasiswa | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/student/layout-start.php';
$studentName = is_string($userName ?? null) && trim($userName) !== '' ? trim($userName) : 'Mahasiswa';
$stats = is_array($stats ?? null) ? $stats : ['registrations' => 0, 'certificates' => 0, 'upcoming' => 0];
$events = is_array($events ?? null) ? $events : [];
$registrations = is_array($registrations ?? null) ? $registrations : [];
?>
<section class="ch-content ch-admin-page">
    <section class="ch-welcome" aria-labelledby="student-welcome-title">
        <div class="ch-welcome-copy">
            <span class="ch-eyebrow">PORTAL MAHASISWA</span>
            <h1 id="student-welcome-title">Halo, <?= \CampusHub\Core\View::escape($studentName) ?>.</h1>
            <p>Temukan kegiatan kampus, pantau pendaftaran, dan siapkan jadwal event yang Anda ikuti.</p>
            <a class="btn btn-primary" href="/student/events">Jelajahi event</a>
        </div>
    </section>

    <section class="ch-admin-stats" aria-label="Ringkasan aktivitas mahasiswa">
        <a class="ch-admin-stat" href="/student/registrations">
            <span>Pendaftaran saya</span><strong><?= (int) $stats['registrations'] ?></strong><small>Seluruh status pendaftaran</small>
        </a>
        <a class="ch-admin-stat" href="/student/schedule">
            <span>Event mendatang</span><strong><?= (int) $stats['upcoming'] ?></strong><small>Pendaftaran disetujui</small>
        </a>
        <a class="ch-admin-stat" href="/student/certificates">
            <span>Sertifikat terbit</span><strong><?= (int) $stats['certificates'] ?></strong><small>Sertifikat milik Anda</small>
        </a>
    </section>

    <section class="ch-committee-section" aria-labelledby="open-events-title">
        <div class="ch-section-heading">
            <div><span class="ch-eyebrow">TERBUKA UNTUK MAHASISWA</span><h2 id="open-events-title">Event mendatang</h2></div>
            <a class="btn btn-outline btn-sm" href="/student/events">Lihat semua</a>
        </div>
        <?php if ($events === []) : ?>
            <div class="ch-student-empty"><div><h3>Belum ada event yang tersedia</h3><p>Event yang dipublikasikan dan masih memiliki kuota akan tampil di sini.</p></div></div>
        <?php else : ?>
            <div class="ch-student-event-cards">
                <?php foreach ($events as $event) : ?>
                    <article class="ch-student-event-card">
                        <a class="ch-student-event-card-media" href="/student/events/detail?id=<?= (int) $event['id_event'] ?>" aria-label="Lihat <?= \CampusHub\Core\View::escape($event['judul']) ?>">
                            <?php if ($event['poster_url'] !== null) : ?><img src="<?= \CampusHub\Core\View::escape($event['poster_url']) ?>" alt="Poster <?= \CampusHub\Core\View::escape($event['judul']) ?>" loading="lazy"><?php else : ?><span class="ch-student-poster-placeholder" aria-hidden="true">CampusHub</span><?php endif; ?>
                        </a>
                        <div class="ch-student-event-card-body">
                            <div class="ch-student-event-card-meta"><span><?= \CampusHub\Core\View::escape($event['nama_kategori']) ?></span><span><?= \CampusHub\Core\View::escape($event['tanggal']) ?> · <?= \CampusHub\Core\View::escape(substr((string) $event['jam'], 0, 5)) ?></span></div>
                            <h3><a href="/student/events/detail?id=<?= (int) $event['id_event'] ?>"><?= \CampusHub\Core\View::escape($event['judul']) ?></a></h3>
                            <p><?= \CampusHub\Core\View::escape(mb_strimwidth(trim(strip_tags((string) $event['deskripsi'])), 0, 150, '…', 'UTF-8')) ?></p>
                            <div class="ch-student-event-card-footer"><span><?= \CampusHub\Core\View::escape($event['nama_organisasi']) ?> · <?= (int) $event['remaining'] ?> slot tersedia</span><a class="btn btn-primary btn-sm" href="/student/events/detail?id=<?= (int) $event['id_event'] ?>">Lihat event</a></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="ch-committee-section" aria-labelledby="my-registrations-title">
        <div class="ch-section-heading">
            <div><span class="ch-eyebrow">AKTIVITAS SAYA</span><h2 id="my-registrations-title">Pendaftaran terbaru</h2></div>
            <a class="btn btn-outline btn-sm" href="/student/registrations">Semua pendaftaran</a>
        </div>
        <div class="table-container"><table class="table">
            <thead><tr><th>Event</th><th>Tanggal event</th><th>Tanggal daftar</th><th>Status</th></tr></thead>
            <tbody>
            <?php if ($registrations === []) : ?><tr><td colspan="4">Anda belum mendaftar event. Event publik akan muncul di halaman Jelajahi Event.</td></tr>
            <?php else : foreach ($registrations as $registration) : ?><tr>
                <td><a href="/student/events/detail?id=<?= (int) $registration['id_event'] ?>"><?= \CampusHub\Core\View::escape($registration['judul']) ?></a></td>
                <td><?= \CampusHub\Core\View::escape($registration['tanggal']) ?></td>
                <td><?= \CampusHub\Core\View::escape($registration['tanggal_daftar']) ?></td>
                <td><?= \CampusHub\Core\View::escape($registration['status']) ?></td>
            </tr><?php endforeach; endif; ?>
            </tbody>
        </table></div>
    </section>
</section>
<?php require dirname(__DIR__, 3) . '/includes/student/layout-end.php'; ?>
