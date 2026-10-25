<?php

$pageTitle = 'Detail Event | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/student/layout-start.php';
$date = strtotime((string) $event['tanggal']);
$dateLabel = $date === false ? (string) $event['tanggal'] : date('d F Y', $date);
$registrationStatus = $event['registration_status'] ?? null;
?>
<section class="ch-content ch-admin-page">
    <div class="page-header">
        <div><h1><?= \CampusHub\Core\View::escape($event['judul']) ?></h1><p>Detail event yang dipublikasikan untuk mahasiswa.</p></div>
        <a class="btn btn-outline" href="/student/events">Kembali ke event</a>
    </div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <div class="card ch-student-event-detail">
        <?php if ($event['poster_url'] !== null) : ?><img class="ch-student-detail-poster" src="<?= \CampusHub\Core\View::escape($event['poster_url']) ?>" alt="Poster <?= \CampusHub\Core\View::escape($event['judul']) ?>"><?php endif; ?>
        <div class="ch-student-detail-information">
            <dl class="ch-committee-event-details">
                <div><dt>Kategori</dt><dd><?= \CampusHub\Core\View::escape($event['nama_kategori']) ?></dd></div>
                <div><dt>Penyelenggara</dt><dd><?= \CampusHub\Core\View::escape($event['nama_organisasi']) ?></dd></div>
                <div><dt>Tanggal &amp; waktu</dt><dd><?= \CampusHub\Core\View::escape($dateLabel) ?> · <?= \CampusHub\Core\View::escape(substr((string) $event['jam'], 0, 5)) ?></dd></div>
                <div><dt>Lokasi</dt><dd><?= \CampusHub\Core\View::escape($event['lokasi']) ?></dd></div>
                <div><dt>Kuota tersedia</dt><dd><?= (int) $event['remaining'] ?> dari <?= (int) $event['kuota'] ?></dd></div>
                <div><dt>Status event</dt><dd>Dipublikasikan</dd></div>
            </dl>
            <section class="ch-committee-event-description">
                <h2>Deskripsi event</h2>
                <p><?= nl2br(\CampusHub\Core\View::escape($event['deskripsi'])) ?></p>
            </section>
            <div class="ch-student-registration-action">
                <?php if ($registrationStatus !== null) : ?>
                    <div class="ch-student-registration-state">
                        <strong>Status pendaftaran: <?= \CampusHub\Core\View::escape($registrationStatus) ?></strong>
                        <?php if (!empty($event['tanggal_daftar'])) : ?><span>Terdaftar pada <?= \CampusHub\Core\View::escape($event['tanggal_daftar']) ?></span><?php endif; ?>
                    </div>
                    <a class="btn btn-outline" href="/student/registrations">Lihat pendaftaran saya</a>
                <?php elseif (!empty($event['registration_open'])) : ?>
                    <form method="post" action="/student/registration/create">
                        <?= \CampusHub\Core\View::csrfField() ?><input type="hidden" name="id_event" value="<?= (int) $event['id_event'] ?>">
                        <button class="btn btn-primary" type="submit">Daftar event</button>
                    </form>
                    <span>Pendaftaran akan menunggu persetujuan panitia.</span>
                <?php else : ?>
                    <strong>Pendaftaran event ini sudah ditutup.</strong>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/includes/student/layout-end.php'; ?>
