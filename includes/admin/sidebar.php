<?php

require_once dirname(__DIR__) . '/guard.php';

$adminName = is_string($userName ?? null) && trim($userName) !== '' ? trim($userName) : 'Admin';
$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$currentPath = is_string($currentPath) ? $currentPath : '/';
$activeAdminSection = is_string($activeAdminSection ?? null)
    ? $activeAdminSection
    : match (true) {
        str_starts_with($currentPath, '/admin/event') => 'event',
        str_starts_with($currentPath, '/admin/kategori') => 'kategori',
        str_starts_with($currentPath, '/admin/organisasi') => 'organisasi',
        str_starts_with($currentPath, '/admin/users') => 'users',
        str_starts_with($currentPath, '/admin/participants') => 'participants',
        str_starts_with($currentPath, '/admin/documentation') => 'documentation',
        str_starts_with($currentPath, '/admin/certificates') => 'certificates',
        str_starts_with($currentPath, '/admin/reports') => 'reports',
        str_starts_with($currentPath, '/admin/settings') => 'settings',
        default => 'dashboard',
    };
?>

<aside class="ch-sidebar" aria-label="Navigasi admin">
    <a class="ch-brand" href="/admin/dashboard" aria-label="CampusHub, dashboard admin">
        <img src="/public/assets/images/logo.png" alt="" width="44" height="44">
        <span>
            <strong>CampusHub</strong>
            <small>Ruang administrasi</small>
        </span>
    </a>

    <p class="ch-nav-label">MENU</p>
    <nav class="ch-nav" aria-label="Menu admin">
        <a class="ch-nav-link<?= $activeAdminSection === 'dashboard' ? ' is-active' : '' ?>"
            href="/admin/dashboard" <?= $activeAdminSection === 'dashboard' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1"></rect>
            </svg>
            <span>Dashboard</span>
        </a>
        <a class="ch-nav-link<?= $activeAdminSection === 'event' ? ' is-active' : '' ?>"
            href="/admin/event/" <?= $activeAdminSection === 'event' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                <path d="M16 3v4M8 3v4M3 10h18"></path>
            </svg>
            <span>Event</span>
        </a>
        <a class="ch-nav-link<?= $activeAdminSection === 'kategori' ? ' is-active' : '' ?>"
            href="/admin/kategori/" <?= $activeAdminSection === 'kategori' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 5h7v7H4zM13 5h7v7h-7zM4 14h7v6H4zM13 14h7v6h-7z"></path>
            </svg>
            <span>Kategori</span>
        </a>
        <a class="ch-nav-link<?= $activeAdminSection === 'organisasi' ? ' is-active' : '' ?>"
            href="/admin/organisasi/" <?= $activeAdminSection === 'organisasi' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-4h4v4"></path>
            </svg>
            <span>Organisasi</span>
        </a>
        <a class="ch-nav-link<?= $activeAdminSection === 'users' ? ' is-active' : '' ?>"
            href="/admin/users" <?= $activeAdminSection === 'users' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>User</span>
        </a>
        <a class="ch-nav-link<?= $activeAdminSection === 'participants' ? ' is-active' : '' ?>"
            href="/admin/participants" <?= $activeAdminSection === 'participants' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>
            </svg>
            <span>Peserta</span>
        </a>
        <a class="ch-nav-link<?= $activeAdminSection === 'documentation' ? ' is-active' : '' ?>"
            href="/admin/documentation" <?= $activeAdminSection === 'documentation' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <path d="m21 15-5-5L5 21"></path>
            </svg>
            <span>Dokumentasi</span>
        </a>
        <a class="ch-nav-link<?= $activeAdminSection === 'certificates' ? ' is-active' : '' ?>"
            href="/admin/certificates" <?= $activeAdminSection === 'certificates' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8" r="6"></circle>
                <path d="m8.2 13.8-1.2 9 5-3 5 3-1.2-9"></path>
            </svg>
            <span>Sertifikat</span>
        </a>
        <a class="ch-nav-link<?= $activeAdminSection === 'reports' ? ' is-active' : '' ?>"
            href="/admin/reports" <?= $activeAdminSection === 'reports' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 3v18h18M7 14l4-4 4 4 6-7"></path>
            </svg>
            <span>Laporan</span>
        </a>
        <a class="ch-nav-link<?= $activeAdminSection === 'settings' ? ' is-active' : '' ?>"
            href="/admin/settings/" <?= $activeAdminSection === 'settings' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"></path>
            </svg>
            <span>Settings</span>
        </a>
    </nav>

    <div class="ch-sidebar-bottom">
        <p>Akun aktif</p>
        <strong><?= \CampusHub\Core\View::escape($adminName) ?></strong>
        <span>Administrator</span>
        <a class="ch-logout-link" href="/logout.php">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M10 17l5-5-5-5M15 12H3M21 3v18"></path>
            </svg>
            Keluar
        </a>
    </div>
</aside>