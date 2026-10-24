<?php

$pageTitle = 'Detail Event | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header">
        <div>
            <h1><?= \CampusHub\Core\View::escape($event['judul']) ?></h1>
            <p>Detail event dan ringkasan pendaftaran.</p>
        </div>
        <div class="ch-admin-inline-form">
            <a class="btn btn-warning" href="/admin/event/edit?id=<?= (int) $event['id_event'] ?>">Edit event</a>
            <a class="btn btn-outline" href="/admin/participants?event=<?= (int) $event['id_event'] ?>">Lihat peserta</a>
            <a class="btn btn-outline" href="/admin/event/">Kembali</a>
        </div>
    </div>
    <div class="table-container">
        <table class="table">
            <tbody>
            <tr><th scope="row">Status</th><td><?= \CampusHub\Core\View::escape($event['status']) ?></td></tr>
            <tr><th scope="row">Kategori</th><td><?= \CampusHub\Core\View::escape($event['nama_kategori']) ?></td></tr>
            <tr><th scope="row">Organisasi</th><td><?= \CampusHub\Core\View::escape($event['nama_organisasi']) ?></td></tr>
            <tr><th scope="row">Panitia</th><td><?= \CampusHub\Core\View::escape($event['nama_panitia']) ?></td></tr>
            <tr><th scope="row">Jadwal</th><td><?= \CampusHub\Core\View::escape($event['tanggal']) ?> <?= \CampusHub\Core\View::escape(substr((string) $event['jam'], 0, 5)) ?></td></tr>
            <tr><th scope="row">Lokasi</th><td><?= \CampusHub\Core\View::escape($event['lokasi']) ?></td></tr>
            <tr><th scope="row">Kuota</th><td><?= (int) $event['kuota'] ?></td></tr>
            <tr><th scope="row">Pendaftar</th><td><?= (int) $event['jumlah_pendaftar'] ?> (<?= (int) $event['jumlah_disetujui'] ?> disetujui)</td></tr>
            <tr><th scope="row">Deskripsi</th><td><?= nl2br(\CampusHub\Core\View::escape($event['deskripsi'])) ?></td></tr>
            </tbody>
        </table>
    </div>
</section>
<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
