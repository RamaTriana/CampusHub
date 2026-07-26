<?php

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/functions.php';
require_once '../config/role.php';

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

// Harus login
if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

// Validasi Role
requireRole(ROLE_ADMIN);

// Jika masih memakai validasi committee, tetap dipertahankan
/*if (!isCommittee()) {
    redirect(BASE_URL . '/login.php');
}

// Jika masih memakai redirectByRole(), tetap dipertahankan
/*if ($_SESSION['role'] !== ROLE_ADMIN) {
    redirectByRole($_SESSION['role']);
}


/*
|--------------------------------------------------------------------------
| Page Setting
|--------------------------------------------------------------------------
*/

$pageTitle = "Dashboard Admin | " . APP_NAME;

$pageCSS = [
    "admin.css",
    "sidebar.css",
    "topbar.css",
    "dashboard.css"
];


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

include '../includes/head.php';
include '../includes/admin/header.php';
/*include '../includes/admin/sidebar.php';*/
/*include '../includes/admin/topbar.php';*/

?>

<div class="admin-layout">

    <!-- Sidebar -->
    <?php include '../includes/admin/sidebar.php'; ?>

    <main class="admin-main">

        <!-- Topbar -->
        <?php include '../includes/admin/topbar.php'; ?>

        <!-- Dashboard -->
        <section class="dashboard-content">

            <h1>Dashboard</h1>

            <p>
                Selamat Datang,
                <strong><?= e($_SESSION['nama']); ?></strong> 👋
            </p>

            <p>
                Role :
                <strong><?= e($_SESSION['role']); ?></strong>
            </p>

            <!-- Tombol Logout tetap ada 
            <div style="margin-top:20px;">
                <a href="../logout.php" class="btn btn-danger">
                    Logout
                </a>
            </div>-->

        </section>

    </main>

</div>

<?php

$pageJS = [
    "admin.js",
    "sidebar.js",
    "dashboard.js"
];

include '../includes/admin/footer.php';
include '../includes/scripts.php';
?>