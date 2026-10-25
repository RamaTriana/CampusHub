<?php

require_once dirname(__DIR__) . '/guard.php';

$committeeName = is_string($userName ?? null) && trim($userName) !== '' ? trim($userName) : 'Panitia';
$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$currentPath = is_string($currentPath) ? $currentPath : '/';
$activeCommitteeSection = is_string($activeCommitteeSection ?? null)
    ? $activeCommitteeSection
    : match (true) {
        str_starts_with($currentPath, '/committee/events') => 'events',
        str_starts_with($currentPath, '/committee/participants') => 'participants',
        str_starts_with($currentPath, '/committee/attendance') => 'attendance',
        str_starts_with($currentPath, '/committee/documentation') => 'documentation',
        str_starts_with($currentPath, '/committee/certificates') => 'certificates',
        str_starts_with($currentPath, '/committee/profile') => 'profile',
        default => 'dashboard',
    };

$committeeMenu = [
    'dashboard' => ['/committee/dashboard', 'Dashboard Panitia', '<rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect>'],
    'events' => ['/committee/events', 'Event Saya', '<rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>'],
    'participants' => ['/committee/participants', 'Peserta', '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>'],
    'attendance' => ['/committee/attendance', 'Kehadiran', '<path d="M3 7V5a2 2 0 0 1 2-2h2m10 0h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 8h3v3H7zM14 8h3v3h-3zM7 14h3v3H7zM14 14h3v3h-3z"></path>'],
    'documentation' => ['/committee/documentation', 'Dokumentasi', '<rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="m21 15-5-5L5 21"></path>'],
    'certificates' => ['/committee/certificates', 'Sertifikat', '<circle cx="12" cy="8" r="6"></circle><path d="m8.2 13.8-1.2 9 5-3 5 3-1.2-9"></path>'],
    'profile' => ['/committee/profile', 'Profil', '<circle cx="12" cy="8" r="4"></circle><path d="M4 21v-2a8 8 0 0 1 16 0v2"></path>'],
];
?>

<aside class="ch-sidebar" id="ch-sidebar" aria-label="Navigasi panitia">
    <a class="ch-brand" href="/committee/dashboard" aria-label="CampusHub, dashboard panitia">
        <img src="/public/assets/images/logo.png" alt="" width="44" height="44">
        <span><strong>CampusHub</strong><small>Ruang kerja panitia</small></span>
    </a>
    <p class="ch-nav-label">MENU PANITIA</p>
    <nav class="ch-nav" aria-label="Menu panitia">
        <?php foreach ($committeeMenu as $section => [$href, $label, $icon]) : ?>
            <a class="ch-nav-link<?= $activeCommitteeSection === $section ? ' is-active' : '' ?>"
                href="<?= \CampusHub\Core\View::escape($href) ?>" <?= $activeCommitteeSection === $section ? 'aria-current="page"' : '' ?>>
                <svg viewBox="0 0 24 24" aria-hidden="true"><?= $icon ?></svg>
                <span><?= \CampusHub\Core\View::escape($label) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="ch-sidebar-bottom">
        <p>Akun aktif</p>
        <strong><?= \CampusHub\Core\View::escape($committeeName) ?></strong>
        <span>Panitia</span>
        <a class="ch-logout-link" href="/logout.php">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M21 3v18"></path></svg>
            Keluar
        </a>
    </div>
</aside>
