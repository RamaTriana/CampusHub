<?php

$pageTitle = 'Laporan | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css', 'alerts.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
$exportQuery = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== ''));
?>
<section class="ch-content ch-admin-page">
    <div class="page-header">
        <div><h1>Laporan operasional</h1><p>Rekap event, pendaftar disetujui, kehadiran, dan sertifikat.</p></div>
        <a class="btn btn-primary" href="/admin/reports/export<?= $exportQuery !== '' ? '?' . \CampusHub\Core\View::escape($exportQuery) : '' ?>">Export CSV</a>
    </div>
    <form class="ch-admin-filters" method="get">
        <label>Dari tanggal<input class="form-control" type="date" name="from" value="<?= \CampusHub\Core\View::escape($filters['from']) ?>"></label>
        <label>Sampai tanggal<input class="form-control" type="date" name="to" value="<?= \CampusHub\Core\View::escape($filters['to']) ?>"></label>
        <label>Status event<select class="form-control" name="status"><option value="">Semua status</option><?php foreach (['Draft', 'Publish', 'Selesai'] as $status) : ?><option value="<?= $status ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select></label>
        <label>Kategori<select class="form-control" name="category"><option value="">Semua kategori</option><?php foreach ($categories as $category) : ?><option value="<?= (int) $category['id_kategori'] ?>" <?= $filters['category'] === (string) $category['id_kategori'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($category['nama_kategori']) ?></option><?php endforeach; ?></select></label>
        <label>Organisasi<select class="form-control" name="organization"><option value="">Semua organisasi</option><?php foreach ($organizations as $organization) : ?><option value="<?= (int) $organization['id_organisasi'] ?>" <?= $filters['organization'] === (string) $organization['id_organisasi'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($organization['nama_organisasi']) ?></option><?php endforeach; ?></select></label>
        <button class="btn btn-primary" type="submit">Terapkan filter</button>
    </form>
    <div class="table-container"><table class="table">
        <thead><tr><th>Event</th><th>Tanggal</th><th>Status</th><th>Organisasi / Kategori</th><th>Panitia</th><th>Kuota</th><th>Disetujui</th><th>Hadir</th><th>Sertifikat</th></tr></thead>
        <tbody>
        <?php if ($rows === []) : ?><tr><td colspan="9">Tidak ada data laporan pada rentang dan filter ini.</td></tr><?php else : foreach ($rows as $row) : ?><tr>
            <td><?= \CampusHub\Core\View::escape($row['judul']) ?></td><td><?= \CampusHub\Core\View::escape($row['tanggal']) ?></td><td><?= \CampusHub\Core\View::escape($row['status']) ?></td>
            <td><?= \CampusHub\Core\View::escape($row['nama_organisasi']) ?><br><small><?= \CampusHub\Core\View::escape($row['nama_kategori']) ?></small></td><td><?= \CampusHub\Core\View::escape($row['nama_panitia']) ?></td>
            <td><?= (int) $row['kuota'] ?></td><td><?= (int) $row['pendaftar_disetujui'] ?></td><td><?= (int) $row['hadir'] ?></td><td><?= (int) $row['sertifikat'] ?></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
</section>
<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
