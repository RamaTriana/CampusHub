<?php

$pageTitle = 'Dashboard Admin | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/head.php';
$adminName = is_string($userName ?? null) && trim($userName) !== '' ? trim($userName) : 'Admin';
$role = is_string($role ?? null) && trim($role) !== '' ? trim($role) : ROLE_ADMIN;
$activeAdminSection = 'dashboard';
?>

<body class="ch-dashboard ch-dashboard--admin">
<a class="ch-skip-link" href="#main-content">Lewati ke konten utama</a>

<div class="ch-admin-shell">
    <?php require dirname(__DIR__, 3) . '/includes/admin/sidebar.php'; ?>

    <main class="ch-main" id="main-content">
        <?php require dirname(__DIR__, 3) . '/includes/admin/topbar.php'; ?>

        <div class="ch-content">
            <section class="ch-welcome">
                <div>
                    <span class="ch-eyebrow">DASHBOARD</span>
                    <h1>Selamat datang, <?= \CampusHub\Core\View::escape($adminName) ?>.</h1>
                    <p>Kelola informasi inti kegiatan dan organisasi kampus dari satu tempat.</p>
                </div>
                <span class="ch-welcome-mark" aria-hidden="true">
                    <svg viewBox="0 0 64 64"><path d="M8 48h48M14 48V22l18-10 18 10v26M25 48V35h14v13M23 25h.1M41 25h.1"></path></svg>
                </span>
            </section>

            <section class="ch-section" aria-labelledby="admin-overview-title">
                <div class="ch-section-heading">
                    <div>
                        <span class="ch-eyebrow">RINGKASAN</span>
                        <h2 id="admin-overview-title">Gambaran administrasi</h2>
                    </div>
                </div>
                <div class="ch-admin-stats">
                    <?php foreach ([
                        ['Total event', $stats['events'] ?? 0, 'event'],
                        ['Event published', $stats['published_events'] ?? 0, 'calendar'],
                        ['Organisasi', $stats['organizations'] ?? 0, 'building'],
                        ['Pengguna', $stats['users'] ?? 0, 'users'],
                        ['Pendaftaran', $stats['registrations'] ?? 0, 'list'],
                    ] as [$label, $value, $icon]) : ?>
                        <article class="ch-admin-stat">
                            <span class="ch-admin-stat-icon" aria-hidden="true">
                                <?php if ($icon === 'event' || $icon === 'calendar') : ?>
                                    <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                                <?php elseif ($icon === 'building') : ?>
                                    <svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-4h4v4"></path></svg>
                                <?php elseif ($icon === 'users') : ?>
                                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                <?php else : ?>
                                    <svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path></svg>
                                <?php endif; ?>
                            </span>
                            <span>
                                <small><?= \CampusHub\Core\View::escape($label) ?></small>
                                <strong><?= (int) $value ?></strong>
                            </span>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php if (($stats['pending_registrations'] ?? 0) > 0 || ($stats['draft_events'] ?? 0) > 0) : ?>
                    <div class="ch-notice ch-admin-alert" role="status">
                        <span class="ch-notice-icon" aria-hidden="true">!</span>
                        <p>
                            Perlu ditindaklanjuti:
                            <?php if (($stats['pending_registrations'] ?? 0) > 0) : ?>
                                <a href="/admin/participants"><?= (int) $stats['pending_registrations'] ?> pendaftaran menunggu</a>
                            <?php endif; ?>
                            <?php if (($stats['draft_events'] ?? 0) > 0) : ?>
                                <a href="/admin/event?status=Draft"><?= (int) $stats['draft_events'] ?> event berstatus Draft</a>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="ch-admin-dashboard-grid">
                    <section class="ch-admin-panel" aria-labelledby="admin-recent-events">
                        <div class="ch-admin-panel-heading">
                            <div>
                                <span class="ch-eyebrow">AKTIVITAS</span>
                                <h3 id="admin-recent-events">Event terbaru</h3>
                            </div>
                            <a href="/admin/event/">Semua event <span aria-hidden="true">→</span></a>
                        </div>
                        <?php if ($recentEvents === []) : ?>
                            <p class="ch-admin-empty">Belum ada event yang tercatat.</p>
                        <?php else : ?>
                            <div class="ch-admin-recent-list">
                                <?php foreach ($recentEvents as $event) : ?>
                                    <a href="/admin/event/edit?id=<?= (int) $event['id_event'] ?>">
                                        <span>
                                            <strong><?= \CampusHub\Core\View::escape($event['judul']) ?></strong>
                                            <small><?= \CampusHub\Core\View::escape($event['nama_organisasi']) ?> · <?= \CampusHub\Core\View::escape($event['nama_kategori']) ?></small>
                                        </span>
                                        <span class="ch-admin-event-state"><?= \CampusHub\Core\View::escape($event['status']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="ch-admin-panel" aria-labelledby="admin-upcoming-events">
                        <div class="ch-admin-panel-heading">
                            <div>
                                <span class="ch-eyebrow">JADWAL</span>
                                <h3 id="admin-upcoming-events">Event mendatang</h3>
                            </div>
                        </div>
                        <?php if ($upcomingEvents === []) : ?>
                            <p class="ch-admin-empty">Belum ada event published yang akan datang.</p>
                        <?php else : ?>
                            <div class="ch-admin-upcoming-list">
                                <?php foreach ($upcomingEvents as $event) : ?>
                                    <article>
                                        <time datetime="<?= \CampusHub\Core\View::escape($event['tanggal']) ?>">
                                            <?= \CampusHub\Core\View::escape(date('d M', strtotime($event['tanggal']))) ?>
                                        </time>
                                        <span>
                                            <strong><?= \CampusHub\Core\View::escape($event['judul']) ?></strong>
                                            <small><?= \CampusHub\Core\View::escape($event['lokasi']) ?> · <?= \CampusHub\Core\View::escape($event['nama_organisasi']) ?></small>
                                        </span>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </section>

            <section class="ch-section" aria-labelledby="admin-tools-title">
                <div class="ch-section-heading">
                    <div>
                        <span class="ch-eyebrow">AKSES CEPAT</span>
                        <h2 id="admin-tools-title">Kelola data kampus</h2>
                    </div>
                    <a class="ch-admin-export" href="/admin/reports/export">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"></path></svg>
                        Export laporan CSV
                    </a>
                </div>
                <div class="ch-module-grid">
                    <a class="ch-module-card ch-module-card--teal" href="/admin/event/">
                        <span class="ch-module-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                        </span>
                        <span class="ch-module-kicker">MODUL</span>
                        <strong>Event</strong>
                        <span class="ch-module-description">Kelola informasi dan publikasi event kampus.</span>
                        <span class="ch-module-action">Buka modul <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="ch-module-card ch-module-card--sand" href="/admin/kategori/">
                        <span class="ch-module-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 5h7v7H4zM13 5h7v7h-7zM4 14h7v6H4zM13 14h7v6h-7z"></path></svg>
                        </span>
                        <span class="ch-module-kicker">MODUL</span>
                        <strong>Kategori</strong>
                        <span class="ch-module-description">Atur kategori yang digunakan pada event.</span>
                        <span class="ch-module-action">Buka modul <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="ch-module-card ch-module-card--blue" href="/admin/organisasi/">
                        <span class="ch-module-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-4h4v4"></path></svg>
                        </span>
                        <span class="ch-module-kicker">MODUL</span>
                        <strong>Organisasi</strong>
                        <span class="ch-module-description">Kelola data organisasi yang terdaftar.</span>
                        <span class="ch-module-action">Buka modul <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="ch-module-card ch-module-card--teal" href="/admin/users">
                        <span class="ch-module-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span>
                        <span class="ch-module-kicker">MODUL</span><strong>User</strong>
                        <span class="ch-module-description">Kelola akun, role, status, dan organisasi.</span>
                        <span class="ch-module-action">Buka modul <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="ch-module-card ch-module-card--sand" href="/admin/participants">
                        <span class="ch-module-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path></svg></span>
                        <span class="ch-module-kicker">MODUL</span><strong>Peserta</strong>
                        <span class="ch-module-description">Tinjau pendaftaran dan kehadiran lintas event.</span>
                        <span class="ch-module-action">Buka modul <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="ch-module-card ch-module-card--blue" href="/admin/documentation">
                        <span class="ch-module-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="m21 15-5-5L5 21"></path></svg></span>
                        <span class="ch-module-kicker">MODUL</span><strong>Dokumentasi</strong>
                        <span class="ch-module-description">Pantau dokumentasi yang diunggah panitia.</span>
                        <span class="ch-module-action">Buka modul <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="ch-module-card ch-module-card--teal" href="/admin/certificates">
                        <span class="ch-module-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"></circle><path d="m8.2 13.8-1.2 9 5-3 5 3-1.2-9"></path></svg></span>
                        <span class="ch-module-kicker">MODUL</span><strong>Sertifikat</strong>
                        <span class="ch-module-description">Tinjau dan cabut sertifikat yang diterbitkan.</span>
                        <span class="ch-module-action">Buka modul <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="ch-module-card ch-module-card--sand" href="/admin/reports">
                        <span class="ch-module-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 3v18h18M7 14l4-4 4 4 6-7"></path></svg></span>
                        <span class="ch-module-kicker">MODUL</span><strong>Laporan</strong>
                        <span class="ch-module-description">Rekap event, peserta, kehadiran, dan sertifikat.</span>
                        <span class="ch-module-action">Buka modul <span aria-hidden="true">→</span></span>
                    </a>
                    <a class="ch-module-card ch-module-card--blue" href="/admin/settings">
                        <span class="ch-module-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06-1.4 2.42-.08-.02a1.65 1.65 0 0 0-1.68.33l-.06.06h-2.8l-.03-.08a1.65 1.65 0 0 0-1.36-1.1h-.08l-1.4-2.42.06-.06A1.65 1.65 0 0 0 10 14.4l-.08-.03v-2.8l.08-.03a1.65 1.65 0 0 0 1.1-1.36v-.08l2.42-1.4.06.06a1.65 1.65 0 0 0 1.82.33l.06-.02 2.42 1.4-.02.08a1.65 1.65 0 0 0 .33 1.68l.06.06v2.8z"></path></svg></span>
                        <span class="ch-module-kicker">AKUN</span><strong>Settings</strong>
                        <span class="ch-module-description">Perbarui profil dan password admin.</span>
                        <span class="ch-module-action">Buka pengaturan <span aria-hidden="true">→</span></span>
                    </a>
                </div>
            </section>

        </div>
        <?php require dirname(__DIR__, 3) . '/includes/admin/footer.php'; ?>
    </main>
</div>
</body>
</html>
