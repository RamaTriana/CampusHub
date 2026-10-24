<?php

$pageTitle = 'Dashboard Panitia | ' . APP_NAME;
$pageCSS = ['dashboard-portals.css'];
require dirname(__DIR__, 3) . '/includes/head.php';
$committeeName = is_string($userName ?? null) && trim($userName) !== '' ? trim($userName) : 'Panitia';
$userAvatar = is_string($userAvatar ?? null) ? $userAvatar : '/public/assets/images/image.png';
$stats = is_array($stats ?? null) ? $stats : [
    'events' => 0,
    'participants_today' => 0,
    'certificates' => 0,
];
$events = is_array($events ?? null) ? $events : [];
$documentation = is_array($documentation ?? null) ? $documentation : [];
?>

<body class="ch-dashboard ch-dashboard--committee">
<a class="ch-skip-link" href="#main-content">Lewati ke konten utama</a>

<div class="ch-committee-shell">
    <aside class="ch-committee-rail">
        <a class="ch-brand ch-brand--committee" href="/committee/dashboard" aria-label="CampusHub, dashboard panitia">
            <img src="/public/assets/images/logo.png" alt="" width="44" height="44">
            <span>
                <strong>CampusHub</strong>
                <small>Ruang kerja panitia</small>
            </span>
        </a>
        <nav class="ch-committee-nav" aria-label="Navigasi panitia">
            <a class="is-active" href="/committee/dashboard" aria-current="page">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect></svg>
                <span>Ringkasan</span>
            </a>
        </nav>
        <div class="ch-committee-account">
            <span class="ch-account-label">AKUN PANITIA</span>
            <strong><?= \CampusHub\Core\View::escape($committeeName) ?></strong>
            <a href="/logout.php">Keluar dari akun <span aria-hidden="true">↗</span></a>
        </div>
    </aside>

    <main class="ch-committee-main" id="main-content">
        <header class="ch-committee-header">
            <div>
                <span class="ch-eyebrow">CAMPUSHUB / PANITIA</span>
                <p>Dashboard operasional</p>
            </div>
            <div class="ch-committee-identity">
                <img src="<?= \CampusHub\Core\View::escape($userAvatar) ?>"
                     alt="Foto profil <?= \CampusHub\Core\View::escape($committeeName) ?>"
                     width="40" height="40">
                <span>
                    <strong><?= \CampusHub\Core\View::escape($committeeName) ?></strong>
                    <small>Panitia</small>
                </span>
            </div>
        </header>

        <div class="ch-committee-content">
            <section class="ch-committee-welcome">
                <div class="ch-committee-welcome-copy">
                    <span class="ch-eyebrow">RUANG KERJA PANITIA</span>
                    <h1>Selamat datang, <?= \CampusHub\Core\View::escape($committeeName) ?>.</h1>
                    <p>Kelola penugasan dan pantau aktivitas peserta dari event yang menjadi tanggung jawab Anda.</p>
                </div>
                <span class="ch-committee-emblem" aria-hidden="true">
                    <svg viewBox="0 0 72 72"><path d="M13 57h46M18 57V28l18-12 18 12v29M29 57V42h14v15M25 31h.1M47 31h.1"></path><path d="M8 25l6 3m50-3-6 3M36 8v5"></path></svg>
                </span>
            </section>

            <section class="ch-committee-section" aria-labelledby="committee-overview-title">
                <div class="ch-section-heading">
                    <div>
                        <span class="ch-eyebrow">RINGKASAN</span>
                        <h2 id="committee-overview-title">Aktivitas kepanitiaan</h2>
                    </div>
                    <span class="ch-section-tag">Menunggu data</span>
                </div>

                <div class="ch-committee-overview-grid">
                    <article class="ch-committee-stat">
                        <span class="ch-committee-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                        </span>
                        <div>
                            <h3>Event ditugaskan</h3>
                                <strong><?= (int) ($stats['events'] ?? 0) ?></strong>
                                <p>Seluruh event yang ditugaskan</p>
                        </div>
                    </article>
                    <article class="ch-committee-stat">
                        <span class="ch-committee-stat-icon ch-committee-stat-icon--mint" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m3-11a4 4 0 1 0 0-8 4 4 0 0 0 0 8m17 11v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </span>
                        <div>
                            <h3>Peserta hari ini</h3>
                            <strong><?= (int) ($stats['participants_today'] ?? 0) ?></strong>
                            <p>Pendaftaran disetujui pada event hari ini</p>
                        </div>
                    </article>
                    <article class="ch-committee-stat">
                        <span class="ch-committee-stat-icon ch-committee-stat-icon--sand" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"></path></svg>
                        </span>
                        <div>
                            <h3>Sertifikat terbit</h3>
                            <strong><?= (int) ($stats['certificates'] ?? 0) ?></strong>
                            <p>Untuk seluruh event yang ditugaskan</p>
                        </div>
                    </article>
                </div>
            </section>

            <section class="ch-committee-section" aria-labelledby="committee-operations-title">
                <div class="ch-section-heading">
                    <div>
                        <span class="ch-eyebrow">OPERASIONAL</span>
                        <h2 id="committee-operations-title">Event yang ditugaskan</h2>
                    </div>
                    <span class="ch-section-tag">Ruang panitia</span>
                </div>

                <?php if ($events === []): ?>
                    <div class="ch-committee-empty">
                        <span class="ch-operation-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                        </span>
                        <div>
                            <h3>Belum ada data event</h3>
                            <p>Event dan tanggung jawab kepanitiaan akan muncul di sini setelah Anda ditugaskan ke event.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="ch-committee-event-list">
                        <?php foreach ($events as $event): ?>
                            <?php
                            $dateValue = strtotime((string) ($event['tanggal'] ?? ''));
                            $eventDate = $dateValue === false ? '' : date('d/m/Y', $dateValue);
                            $approvedParticipants = (int) ($event['approved_participants'] ?? 0);
                            ?>
                            <article class="ch-committee-event">
                                <span class="ch-operation-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>
                                </span>
                                <div class="ch-committee-event-main">
                                    <div class="ch-committee-event-heading">
                                        <h3><?= \CampusHub\Core\View::escape((string) ($event['judul'] ?? 'Event')) ?></h3>
                                        <span class="ch-committee-event-status"><?= \CampusHub\Core\View::escape((string) ($event['status'] ?? '')) ?></span>
                                    </div>
                                    <div class="ch-committee-event-meta">
                                        <?php if ($eventDate !== ''): ?>
                                            <span><?= \CampusHub\Core\View::escape($eventDate) ?></span>
                                        <?php endif; ?>
                                        <span><?= \CampusHub\Core\View::escape((string) ($event['lokasi'] ?? 'Lokasi belum ditentukan')) ?></span>
                                        <span><?= $approvedParticipants ?> peserta disetujui</span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($events) === 6): ?>
                        <p class="ch-committee-list-note">Menampilkan 6 event terbaru yang ditugaskan kepada Anda.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <section class="ch-committee-section" aria-label="Dokumentasi dan kehadiran">
                <div class="ch-committee-panels">
                    <article class="ch-committee-panel">
                        <div class="ch-committee-panel-heading">
                            <div>
                                <span class="ch-eyebrow">ARSIP KEGIATAN</span>
                                <h2>Dokumentasi terbaru</h2>
                            </div>
                            <span class="ch-committee-panel-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="m21 15-5-5L5 21"></path></svg>
                            </span>
                        </div>
                        <?php if ($documentation === []): ?>
                            <div class="ch-committee-panel-empty">
                                <p>Dokumentasi kegiatan belum tersedia.</p>
                            </div>
                        <?php else: ?>
                            <div class="ch-committee-docs">
                                <?php foreach ($documentation as $item): ?>
                                    <figure>
                                        <img src="<?= \CampusHub\Core\View::escape((string) $item['url']) ?>"
                                             alt="<?= \CampusHub\Core\View::escape((string) ($item['keterangan'] ?: $item['judul'])) ?>"
                                             loading="lazy">
                                        <figcaption>
                                            <strong><?= \CampusHub\Core\View::escape((string) $item['judul']) ?></strong>
                                            <?php if (!empty($item['keterangan'])): ?>
                                                <span><?= \CampusHub\Core\View::escape((string) $item['keterangan']) ?></span>
                                            <?php endif; ?>
                                        </figcaption>
                                    </figure>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>

                    <article class="ch-committee-panel">
                        <div class="ch-committee-panel-heading">
                            <div>
                                <span class="ch-eyebrow">KEHADIRAN</span>
                                <h2>Progres check-in</h2>
                            </div>
                            <span class="ch-committee-panel-icon ch-committee-panel-icon--mint" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2m10 0h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 8h3v3H7zM14 8h3v3h-3zM7 14h3v3H7zM14 14h3v3h-3z"></path></svg>
                            </span>
                        </div>
                        <?php
                        $checkinEvents = array_values(array_filter(
                            $events,
                            static fn (array $event): bool => (int) ($event['approved_participants'] ?? 0) > 0
                        ));
                        ?>
                        <?php if ($checkinEvents === []): ?>
                            <div class="ch-committee-panel-empty">
                                <p>Progres check-in tersedia setelah ada pendaftaran peserta yang disetujui.</p>
                            </div>
                        <?php else: ?>
                            <div class="ch-committee-checkins">
                                <?php foreach ($checkinEvents as $event): ?>
                                    <?php
                                    $approvedParticipants = (int) $event['approved_participants'];
                                    $checkedIn = (int) $event['checked_in'];
                                    $progress = (int) round(($checkedIn / $approvedParticipants) * 100);
                                    ?>
                                    <div class="ch-committee-checkin">
                                        <div>
                                            <span><?= \CampusHub\Core\View::escape((string) $event['judul']) ?></span>
                                            <strong><?= $checkedIn ?>/<?= $approvedParticipants ?></strong>
                                        </div>
                                        <div class="ch-committee-progress" role="progressbar"
                                             aria-label="Progres check-in <?= \CampusHub\Core\View::escape((string) $event['judul']) ?>"
                                             aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                            <span style="width: <?= $progress ?>%"></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                </div>
            </section>

            <div class="ch-committee-note" role="status">
                <span class="ch-notice-icon" aria-hidden="true">i</span>
                <p>Ringkasan dan progres di atas dihitung dari event yang ditugaskan kepada akun panitia ini.</p>
            </div>

            <footer class="ch-page-footer">
                <span>CampusHub <span aria-hidden="true">·</span> Panitia</span>
                <span>Bekerja bersama untuk kegiatan kampus.</span>
            </footer>
        </div>
    </main>
</div>
</body>
</html>
