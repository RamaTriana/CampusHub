<?php

require_once dirname(__DIR__) . '/guard.php';

$studentName = is_string($userName ?? null) && trim($userName) !== '' ? trim($userName) : 'Mahasiswa';
$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$currentPath = is_string($currentPath) ? $currentPath : '/';
$activeStudentSection = is_string($activeStudentSection ?? null)
    ? $activeStudentSection
    : match (true) {
        str_starts_with($currentPath, '/student/events') => 'events',
        str_starts_with($currentPath, '/student/registrations') => 'registrations',
        str_starts_with($currentPath, '/student/schedule') => 'schedule',
        str_starts_with($currentPath, '/student/certificates') => 'certificates',
        str_starts_with($currentPath, '/student/profile') => 'profile',
        default => 'dashboard',
    };

$studentMenu = [
    'dashboard' => ['/student/dashboard', 'Dashboard', '<rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect>'],
    'events' => ['/student/events', 'Jelajahi Event', '<rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path>'],
    'registrations' => ['/student/registrations', 'Pendaftaran Saya', '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>'],
    'schedule' => ['/student/schedule', 'Jadwal Saya', '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>'],
    'certificates' => ['/student/certificates', 'Sertifikat Saya', '<circle cx="12" cy="8" r="6"></circle><path d="m8.2 13.8-1.2 9 5-3 5 3-1.2-9"></path>'],
    'profile' => ['/student/profile', 'Profil', '<circle cx="12" cy="8" r="4"></circle><path d="M4 21v-2a8 8 0 0 1 16 0v2"></path>'],
];
?>

<aside class="ch-sidebar" id="ch-sidebar" aria-label="Navigasi mahasiswa">
    <a class="ch-brand" href="/student/dashboard" aria-label="CampusHub, dashboard mahasiswa">
        <img src="/public/assets/images/logo.png" alt="" width="44" height="44">
        <span><strong>CampusHub</strong><small>Portal mahasiswa</small></span>
    </a>
    <p class="ch-nav-label">MENU MAHASISWA</p>
    <nav class="ch-nav" aria-label="Menu mahasiswa">
        <?php foreach ($studentMenu as $section => [$href, $label, $icon]) : ?>
            <a class="ch-nav-link<?= $activeStudentSection === $section ? ' is-active' : '' ?>"
                href="<?= \CampusHub\Core\View::escape($href) ?>" <?= $activeStudentSection === $section ? 'aria-current="page"' : '' ?>>
                <svg viewBox="0 0 24 24" aria-hidden="true"><?= $icon ?></svg>
                <span><?= \CampusHub\Core\View::escape($label) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="ch-sidebar-bottom">
        <p>Akun aktif</p>
        <strong><?= \CampusHub\Core\View::escape($studentName) ?></strong>
        <span>Mahasiswa</span>
        <a class="ch-logout-link" href="/logout.php">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M21 3v18"></path></svg>
            Keluar
        </a>
    </div>
</aside>
