<?php

$pageTitle = 'Detail pengguna | ' . APP_NAME;
$pageCSS = ['admin.css', 'sidebar.css', 'topbar.css', 'tables.css', 'buttons.css'];
require dirname(__DIR__, 4) . '/includes/admin/layout-start.php';
?>
<section class="ch-content ch-admin-page">
    <div class="page-header">
        <div>
            <h1>Detail pengguna</h1>
            <p>Informasi akun dan aktivitas terkait. Password tidak ditampilkan.</p>
        </div>
        <a class="btn btn-outline" href="/admin/users">Kembali ke pengguna</a>
    </div>
    <div class="table-container">
        <table class="table">
            <tbody>
            <tr><th scope="row">Nama</th><td><?= \CampusHub\Core\View::escape($user['nama']) ?></td></tr>
            <tr><th scope="row">NIM</th><td><?= \CampusHub\Core\View::escape($user['nim'] ?: '—') ?></td></tr>
            <tr><th scope="row">Email</th><td><?= \CampusHub\Core\View::escape($user['email']) ?></td></tr>
            <tr><th scope="row">Username</th><td><?= \CampusHub\Core\View::escape($user['username']) ?></td></tr>
            <tr><th scope="row">Role</th><td><?= \CampusHub\Core\View::escape($user['role']) ?></td></tr>
            <tr><th scope="row">Status akun</th><td><?= \CampusHub\Core\View::escape($user['status']) ?></td></tr>
            <tr><th scope="row">Organisasi</th><td><?= \CampusHub\Core\View::escape($user['nama_organisasi'] ?? 'Tidak ditugaskan') ?></td></tr>
            <tr><th scope="row">Event yang ditugaskan</th><td><?= (int) $user['assigned_events'] ?></td></tr>
            <tr><th scope="row">Bergabung</th><td><?= \CampusHub\Core\View::escape($user['created_at']) ?></td></tr>
            </tbody>
        </table>
    </div>
</section>
<?php require dirname(__DIR__, 4) . '/includes/admin/layout-end.php'; ?>
