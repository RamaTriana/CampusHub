<?php

/**
 * Header portal mahasiswa (dipakai dashboard & detail event)
 * Lokasi: includes/student/header.php
 * Variabel: $studentName, $studentAvatar (opsional), $headerVariant ('home' | 'detail')
 */
$headerVariant = $headerVariant ?? 'home';
$studentName   = $studentName ?? 'Mahasiswa';
$studentAvatar = $studentAvatar ?? '/public/assets/images/image.png';
$searchValue   = $searchValue ?? '';
?>
<header class="sp-header sp-header--<?= $headerVariant === 'detail' ? 'detail' : 'home' ?>">
    <div class="sp-wrap">
        <a class="sp-logo" href="/student/dashboard" aria-label="CampusHub, beranda mahasiswa">CampusHub</a>

        <form class="sp-search" action="/student/events" method="get" role="search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" name="q" value="<?= \CampusHub\Core\View::escape($searchValue) ?>"
                placeholder="Search events..." aria-label="Cari event">
        </form>

        <div class="sp-header-right">
            <?php if ($headerVariant === 'detail'): ?>
                <button class="sp-icon-btn" type="button" aria-label="Notifikasi"><i class="fa-regular fa-bell"></i></button>
                <img class="sp-avatar" src="<?= \CampusHub\Core\View::escape($studentAvatar) ?>" alt="Foto profil">
                <a class="sp-icon-btn" href="/student/profile" aria-label="Akun saya"><i class="fa-regular fa-circle-user"></i></a>
            <?php else: ?>
                <a class="sp-certs" href="/student/certificates"><i class="fa-regular fa-id-card" aria-hidden="true"></i> <span>My Certificates</span></a>
                <div class="sp-user">
                    <div class="sp-user-info">
                        <strong><?= \CampusHub\Core\View::escape($studentName) ?></strong>
                        <small>Student Portal</small>
                    </div>
                    <img class="sp-avatar" src="<?= \CampusHub\Core\View::escape($studentAvatar) ?>" alt="Foto profil <?= \CampusHub\Core\View::escape($studentName) ?>">
                </div>
                <button class="sp-icon-btn" type="button" aria-label="Notifikasi"><i class="fa-regular fa-bell"></i></button>
            <?php endif; ?>
        </div>
    </div>
</header>