<?php

$pageTitle = 'Dokumentasi | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/committee/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header"><div><h1>Dokumentasi</h1><p><?= (int) $total ?> dokumentasi untuk event yang ditugaskan kepada Anda.</p></div></div>
    <?php if (is_array($flash ?? null)) : ?><div class="alert alert-<?= \CampusHub\Core\View::escape(strtolower((string) ($flash['type'] ?? 'info'))) ?>"><?= nl2br(\CampusHub\Core\View::escape((string) ($flash['message'] ?? ''))) ?></div><?php endif; ?>
    <div class="card ch-committee-upload-card">
        <h2>Tambah dokumentasi</h2>
        <?php if ($events === []) : ?><p>Anda belum memiliki event untuk dokumentasi.</p>
        <?php else : ?><form action="/committee/documentation/save" method="post" enctype="multipart/form-data" class="ch-committee-doc-form">
            <?= \CampusHub\Core\View::csrfField() ?>
            <label>Event<select class="form-control" name="id_event" required><option value="">Pilih event</option><?php foreach ($events as $event) : ?><option value="<?= (int) $event['id_event'] ?>" <?= (string) $event['id_event'] === $filters['event'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($event['judul']) ?></option><?php endforeach; ?></select></label>
            <label>Keterangan<input class="form-control" name="keterangan" maxlength="255" placeholder="Keterangan foto (opsional)"></label>
            <label>Tanggal dokumentasi<input class="form-control" type="datetime-local" name="created_at" value="<?= date('Y-m-d\TH:i') ?>" required></label>
            <label>Foto (JPG/PNG, maks. 2 MB)<input class="form-control" type="file" name="foto" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required></label>
            <button class="btn btn-primary" type="submit">Unggah</button>
        </form><?php endif; ?>
    </div>

    <form class="ch-admin-filters" method="get">
        <label>Event<select class="form-control" name="event"><option value="">Semua event saya</option><?php foreach ($events as $event) : ?><option value="<?= (int) $event['id_event'] ?>" <?= (string) $event['id_event'] === $filters['event'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($event['judul']) ?></option><?php endforeach; ?></select></label>
        <label>Dari tanggal<input class="form-control" type="date" name="from" value="<?= \CampusHub\Core\View::escape($filters['from']) ?>"></label>
        <label>Sampai tanggal<input class="form-control" type="date" name="to" value="<?= \CampusHub\Core\View::escape($filters['to']) ?>"></label>
        <button class="btn btn-primary" type="submit">Terapkan</button><a class="btn btn-outline" href="/committee/documentation">Reset</a>
    </form>
    <div class="table-container"><table class="table ch-committee-documentation-table">
        <thead><tr><th>Foto</th><th>Event</th><th>Keterangan &amp; tanggal</th><th>Perbarui dokumentasi</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if ($items === []) : ?><tr><td colspan="5">Dokumentasi belum tersedia untuk filter ini.</td></tr>
        <?php else : foreach ($items as $item) :
            $filename = basename((string) $item['foto']);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $safeImage = preg_match('/\A[a-zA-Z0-9._-]+\.(?:jpg|jpeg|png)\z/i', $filename) === 1
                && in_array($extension, ['jpg', 'jpeg', 'png'], true)
                && is_file(dirname(__DIR__, 3) . '/assets/uploads/dokumentasi/' . $filename);
            $dateValue = strtotime((string) $item['created_at']);
        ?><tr>
            <td><?php if ($safeImage) : ?><a href="/assets/uploads/dokumentasi/<?= rawurlencode($filename) ?>" target="_blank" rel="noopener"><img class="ch-committee-doc-thumb" src="/assets/uploads/dokumentasi/<?= rawurlencode($filename) ?>" alt="<?= \CampusHub\Core\View::escape($item['keterangan'] ?: $item['judul']) ?>" loading="lazy"></a><?php else : ?>File tidak tersedia<?php endif; ?></td>
            <td><?= \CampusHub\Core\View::escape($item['judul']) ?></td>
            <td><?= \CampusHub\Core\View::escape($item['keterangan'] ?? '') ?><br><small><?= $dateValue === false ? '' : \CampusHub\Core\View::escape(date('d/m/Y H:i', $dateValue)) ?></small></td>
            <td><form class="ch-committee-doc-edit" action="/committee/documentation/save" method="post" enctype="multipart/form-data">
                <?= \CampusHub\Core\View::csrfField() ?><input type="hidden" name="id_dokumentasi" value="<?= (int) $item['id_dokumentasi'] ?>">
                <label>Event<select class="form-control" name="id_event" required><?php foreach ($events as $event) : ?><option value="<?= (int) $event['id_event'] ?>" <?= (int) $event['id_event'] === (int) $item['id_event'] ? 'selected' : '' ?>><?= \CampusHub\Core\View::escape($event['judul']) ?></option><?php endforeach; ?></select></label>
                <label>Keterangan<input class="form-control" name="keterangan" maxlength="255" value="<?= \CampusHub\Core\View::escape($item['keterangan'] ?? '') ?>"></label>
                <label>Tanggal<input class="form-control" type="datetime-local" name="created_at" value="<?= $dateValue === false ? '' : \CampusHub\Core\View::escape(date('Y-m-d\TH:i', $dateValue)) ?>" required></label>
                <label>Ganti foto (opsional)<input class="form-control" type="file" name="foto" accept=".jpg,.jpeg,.png,image/jpeg,image/png"></label>
                <button class="btn btn-primary btn-sm" type="submit">Simpan perubahan</button>
            </form></td>
            <td><form method="post" action="/committee/documentation/delete" onsubmit="return confirm('Hapus dokumentasi ini?')">
                <?= \CampusHub\Core\View::csrfField() ?><input type="hidden" name="id_dokumentasi" value="<?= (int) $item['id_dokumentasi'] ?>">
                <button class="btn btn-warning btn-sm" type="submit">Hapus</button>
            </form></td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table></div>
    <?php if ($pages > 1) : $query = http_build_query(array_filter($filters, static fn (string $value): bool => $value !== '')); ?>
        <nav class="ch-admin-pagination" aria-label="Halaman dokumentasi">
            <?php if ($page > 1) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <?php if ($page < $pages) : ?><a class="btn btn-outline btn-sm" href="?<?= \CampusHub\Core\View::escape($query) ?>&amp;page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__, 3) . '/includes/committee/layout-end.php'; ?>
