<?php

$pageTitle = 'Dokumentasi | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css', 'alerts.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
$paginationQuery = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== ''));
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Dokumentasi</h1><p><?= (int) $total ?> dokumentasi yang diunggah untuk event CampusHub.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <form class="ch-admin-filters" method="get">
        <label>Event<select class="form-control" name="event"><option value="">Semua event</option><?php foreach ($events as $event) : ?><option value="<?= (int) $event['id_event'] ?>" <?= (string) $event['id_event'] === $filters['event'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($event['judul']) ?></option><?php endforeach; ?></select></label>
        <label>Dari tanggal<input class="form-control" type="date" name="from" value="<?= \CampusHub\Core\View::escape($filters['from']) ?>"></label>
        <label>Sampai tanggal<input class="form-control" type="date" name="to" value="<?= \CampusHub\Core\View::escape($filters['to']) ?>"></label>
        <button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline" href="/admin/documentation">Reset</a>
    </form>
    <div class="table-container"><table class="table">
        <thead><tr><th>File</th><th>Event</th><th>Keterangan</th><th>Pengunggah</th><th>Tanggal</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if ($items === []) : ?><tr><td colspan="6">Belum ada dokumentasi untuk filter ini.</td></tr><?php else : foreach ($items as $item) :
            $filename = basename((string) $item['foto']);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $safeImage = in_array($extension, ['jpg', 'jpeg', 'png'], true);
        ?><tr>
            <td><?php if ($safeImage) : ?><a href="/assets/uploads/dokumentasi/<?= rawurlencode($filename) ?>" target="_blank" rel="noopener">Lihat file</a><?php else : ?><?= \CampusHub\Core\View::escape($filename) ?><?php endif; ?></td>
            <td><?= \CampusHub\Core\View::escape($item['judul']) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['keterangan'] ?? '') ?></td>
            <td><?= \CampusHub\Core\View::escape($item['nama_panitia']) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['created_at']) ?></td>
            <td><form method="post" action="/admin/documentation/hapus" onsubmit="return confirm('Hapus dokumentasi ini?')"><?= \CampusHub\Core\View::csrfField() ?><input type="hidden" name="id" value="<?= (int) $item['id_dokumentasi'] ?>"><button class="btn btn-warning btn-sm" type="submit">Hapus</button></form></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : ?><nav class="ch-admin-pagination" aria-label="Halaman dokumentasi">
        <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($paginationQuery) ?><?= $paginationQuery !== '' ? '&amp;' : '' ?>page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
        <span>Halaman <?= $page ?> dari <?= $pages ?></span>
        <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($paginationQuery) ?><?= $paginationQuery !== '' ? '&amp;' : '' ?>page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
    </nav><?php endif; ?>
</section>
<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
