<?php

require_once dirname(__DIR__) . '/guard.php';

$committeeName = is_string($userName ?? null) && trim($userName) !== '' ? trim($userName) : 'Panitia';
$photoFile = basename(is_string($photo ?? null) ? $photo : 'default.png');
$profilePath = dirname(__DIR__, 2) . '/assets/uploads/profile/' . $photoFile;
$photoUrl = in_array($photoFile, ['default.png', 'default-avatar.png'], true)
    || !is_file($profilePath)
    ? '/public/assets/images/image.png'
    : '/assets/uploads/profile/' . rawurlencode($photoFile);
?>

<header class="ch-topbar">
    <button class="ch-mobile-menu-toggle" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="ch-sidebar">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>
    <div>
        <span class="ch-eyebrow">CAMPUSHUB / PANITIA</span>
        <p>Portal kepanitiaan</p>
    </div>
    <div class="ch-topbar-user">
        <img class="ch-profile-photo" src="<?= \CampusHub\Core\View::escape($photoUrl) ?>" alt="" width="40" height="40">
        <span><strong><?= \CampusHub\Core\View::escape($committeeName) ?></strong><small>Panitia</small></span>
    </div>
</header>
