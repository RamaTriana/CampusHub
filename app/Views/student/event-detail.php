<?php

/**
 * Detail / Deskripsi Event - CampusHub (mahasiswa)
 * Lokasi: app/Views/student/event-detail.php
 *
 * Variabel dari StudentController::showEvent():
 *   $event = [
 *     'id','title','category','level','description',
 *     'learn' => ['poin 1', 'poin 2', ...],
 *     'closing' => 'paragraf penutup',
 *     'organization' => ['name' => '...', 'logo_url' => null],
 *     'date_label' => 'Saturday, 12 Oct 2024', 'time_label' => '09:00 AM - 03:00 PM WIB',
 *     'location' => 'Lab Komputer Terpadu 3', 'location_detail' => 'Main Campus, 2nd Floor',
 *     'quota' => 50, 'remaining' => 15, 'poster_url' => '...', 'is_open' => true
 *   ]
 *   $registration = null | ['status' => 'pending'|'approved'|'rejected']
 *   $userName, $userAvatar, $flash (opsional: ['type'=>'ok|err','message'=>'...'])
 */

$pageTitle = 'Detail Event | ' . APP_NAME;
$pageCSS   = ['dashboard-portals.css', 'student.css'];
require dirname(__DIR__, 3) . '/includes/head.php';

$esc = static fn($v) => \CampusHub\Core\View::escape((string) $v);

$studentName   = is_string($userName ?? null) && trim($userName) !== '' ? trim($userName) : 'Mahasiswa';
$studentAvatar = $userAvatar ?? '/public/assets/images/image.png';
$headerVariant = 'detail';

/* Data contoh jika controller belum mengirim $event */
$event = $event ?? [
    'id' => 1,
    'title' => 'Workshop Flutter',
    'category' => 'Workshop',
    'level' => 'Professional',
    'description' => "Dive deep into the world of cross-platform mobile development with our intensive Flutter Workshop. Whether you're a beginner looking to build your first app or an intermediate developer seeking to polish your skills, this session is designed for you.",
    'learn' => [
        'Introduction to Dart Programming Language',
        'Building beautiful UIs with Flutter Widgets',
        'State Management basics (Provider/Riverpod)',
        'API Integration and Data Fetching',
        'Deploying to Play Store & App Store',
    ],
    'closing' => "Our mentors are industry professionals with years of experience in production-grade app development. Don't miss this opportunity to kickstart your career as a Mobile Developer!",
    'organization' => ['name' => 'HMJ TI (Himpunan Mahasiswa Jurusan Teknik Informatika)', 'logo_url' => null],
    'date_label' => 'Saturday, 12 Oct 2024',
    'time_label' => '09:00 AM - 03:00 PM WIB',
    'location' => 'Lab Komputer Terpadu 3',
    'location_detail' => 'Main Campus, 2nd Floor',
    'quota' => 50,
    'remaining' => 15,
    'poster_url' => null,
    'is_open' => true,
];
$registration = $registration ?? null;
$regStatus    = $registration['status'] ?? null;

$remaining = max(0, (int) ($event['remaining'] ?? 0));
$isOpen    = !empty($event['is_open']) && $remaining > 0;
$org       = $event['organization'] ?? ['name' => '-', 'logo_url' => null];

$pillClass = ['approved' => 'is-approved', 'rejected' => 'is-rejected'][$regStatus] ?? '';
$pillLabel = [
    'pending'  => 'Menunggu Verifikasi',
    'approved' => 'Pendaftaran Disetujui',
    'rejected' => 'Pendaftaran Ditolak',
][$regStatus] ?? '';
?>

<body class="ch-dashboard ch-dashboard--student sp">
    <a class="ch-skip-link" href="#main-content">Lewati ke konten utama</a>

    <?php require dirname(__DIR__, 3) . '/includes/student/header.php'; ?>

    <main class="sp-main" id="main-content">
        <div class="sp-wrap">

            <a class="sp-back" href="/student/dashboard"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Events</a>

            <?php if (!empty($flash['message'])): ?>
                <div class="sp-alert sp-alert--<?= ($flash['type'] ?? 'ok') === 'err' ? 'err' : 'ok' ?>" role="status">
                    <?= $esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="sp-detail">

                <!-- Kiri: poster + status pendaftaran -->
                <aside>
                    <figure class="sp-poster" style="margin:0">
                        <?php if (!empty($event['poster_url'])): ?>
                            <img src="<?= $esc($event['poster_url']) ?>" alt="Poster <?= $esc($event['title']) ?>">
                        <?php else: ?>
                            <div class="sp-ph"><i class="fa-regular fa-image" aria-hidden="true"></i></div>
                        <?php endif; ?>
                    </figure>

                    <div class="sp-regbox">
                        <div>
                            <small>Registration Status</small>
                            <span class="sp-status-ok <?= $isOpen ? '' : 'is-closed' ?>">
                                <i class="fa-solid <?= $isOpen ? 'fa-circle-check' : 'fa-circle-xmark' ?>" aria-hidden="true"></i>
                                <?= $isOpen ? 'Open for Registration' : 'Registration Closed' ?>
                            </span>
                        </div>
                        <div class="sp-quota">
                            <small>Quota</small>
                            <b><?= $remaining ?></b> <span>/ <?= (int) $event['quota'] ?> left</span>
                        </div>
                    </div>
                </aside>

                <!-- Kanan: informasi event -->
                <article>
                    <div class="sp-tags">
                        <span class="sp-tag sp-tag--cat"><?= $esc($event['category']) ?></span>
                        <?php if (!empty($event['level'])): ?>
                            <span class="sp-tag sp-tag--level"><?= $esc($event['level']) ?></span>
                        <?php endif; ?>
                    </div>

                    <h1><?= $esc($event['title']) ?></h1>

                    <div class="sp-org">
                        <span class="sp-org-logo">
                            <?php if (!empty($org['logo_url'])): ?>
                                <img src="<?= $esc($org['logo_url']) ?>" alt="">
                            <?php else: ?>
                                <i class="fa-regular fa-building" aria-hidden="true"></i>
                            <?php endif; ?>
                        </span>
                        <div>
                            <small>Organized by</small>
                            <strong><?= $esc($org['name']) ?></strong>
                        </div>
                    </div>

                    <div class="sp-info">
                        <div class="sp-info-card">
                            <span class="sp-info-icon"><i class="fa-regular fa-calendar" aria-hidden="true"></i></span>
                            <div>
                                <small>Date &amp; Time</small>
                                <strong><?= $esc($event['date_label']) ?></strong>
                                <span><?= $esc($event['time_label']) ?></span>
                            </div>
                        </div>
                        <div class="sp-info-card">
                            <span class="sp-info-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                            <div>
                                <small>Location</small>
                                <strong><?= $esc($event['location']) ?></strong>
                                <span><?= $esc($event['location_detail'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>

                    <section class="sp-about" aria-labelledby="about-title">
                        <h2 id="about-title">About This Event</h2>
                        <p><?= nl2br($esc($event['description'])) ?></p>

                        <?php if (!empty($event['learn'])): ?>
                            <h3>What you will learn:</h3>
                            <ul>
                                <?php foreach ($event['learn'] as $point): ?>
                                    <li><?= $esc($point) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (!empty($event['closing'])): ?>
                            <p><?= nl2br($esc($event['closing'])) ?></p>
                        <?php endif; ?>
                    </section>

                    <!-- Aksi -->
                    <div class="sp-cta">
                        <div class="sp-cta-row">
                            <form action="/student/events/<?= (int) $event['id'] ?>/register" method="post">
                                <?php
                                // Sesuaikan dengan API CsrfToken milik Anda
                                if (class_exists('\CampusHub\Core\CsrfToken') && method_exists('\CampusHub\Core\CsrfToken', 'field')) {
                                    echo \CampusHub\Core\CsrfToken::field();
                                }
                                ?>
                                <button class="sp-btn-register" type="submit" <?= (!$isOpen || $regStatus) ? 'disabled' : '' ?>>
                                    Daftar Sekarang
                                </button>
                            </form>

                            <?php if ($pillLabel): ?>
                                <span class="sp-pill <?= $pillClass ?>"><?= $esc($pillLabel) ?></span>
                            <?php endif; ?>

                            <a class="sp-report" href="/student/events/<?= (int) $event['id'] ?>/report">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Report Event
                            </a>
                        </div>

                        <p class="sp-note">* Please complete your profile before registering for this event.</p>
                    </div>
                </article>
            </div>
        </div>
    </main>

    <?php require dirname(__DIR__, 3) . '/includes/student/footer.php'; ?>
</body>

</html>