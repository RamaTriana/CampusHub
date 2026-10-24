<?php
/**
 * Dashboard Mahasiswa - CampusHub
 * Lokasi: app/Views/dashboard/student.php
 *
 * Variabel dari StudentController (semua opsional, ada fallback):
 *   $userName, $userAvatar
 *   $stats        ['joined' => 6, 'certificates' => 4, 'upcoming' => 2]
 *   $recommended  [ ['id','title','date','location','category','poster_url'], ... ]
 *   $activeCategory (string), $searchValue (string)
 */

$pageTitle = 'Dashboard Mahasiswa | ' . APP_NAME;
$pageCSS   = ['dashboard-portals.css', 'student.css'];
require dirname(__DIR__, 3) . '/includes/head.php';

$esc = static fn($v) => \CampusHub\Core\View::escape((string) $v);

$studentName   = is_string($userName ?? null) && trim($userName) !== '' ? trim($userName) : 'Mahasiswa';
$studentAvatar = $userAvatar ?? '/public/assets/images/image.png';
$headerVariant = 'home';
$searchValue   = $searchValue ?? '';
$activeCategory = strtolower((string) ($activeCategory ?? ''));

$stats = array_merge(['joined' => 0, 'certificates' => 0, 'upcoming' => 0], $stats ?? []);
$recommended = $recommended ?? [];

$categories = [
    'workshop' => ['Workshop', 'fa-graduation-cap'],
    'seminar'  => ['Seminar',  'fa-users-line'],
    'lomba'    => ['Lomba',    'fa-trophy'],
];
$pad = static fn($n) => str_pad((string) (int) $n, 2, '0', STR_PAD_LEFT);
?>

<body class="ch-dashboard ch-dashboard--student sp">
    <a class="ch-skip-link" href="#main-content">Lewati ke konten utama</a>

    <?php require dirname(__DIR__, 3) . '/includes/student/header.php'; ?>

    <main class="sp-main" id="main-content">

        <!-- Hero + pencarian -->
        <section class="sp-hero" aria-labelledby="hero-title">
            <div class="sp-wrap">
                <h1 id="hero-title">Cari Event Kampus Favoritmu</h1>

                <form class="sp-hero-search" action="/student/events" method="get" role="search">
                    <input type="search" name="q" value="<?= $esc($searchValue) ?>"
                        placeholder="Ketik workshop, kompetisi, atau seminar..." aria-label="Cari event kampus">
                    <button class="sp-btn-find" type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Temukan</button>
                </form>

                <div class="sp-chips">
                    <?php foreach ($categories as $slug => [$label, $iconClass]): ?>
                        <a class="sp-chip <?= $activeCategory === $slug ? 'is-active' : '' ?>"
                            href="/student/events?category=<?= $esc($slug) ?>">
                            <i class="fa-solid <?= $esc($iconClass) ?>" aria-hidden="true"></i> <?= $esc($label) ?>
                        </a>
                    <?php endforeach; ?>
                    <a class="sp-chip sp-chip--outline" href="/student/events"><i class="fa-solid fa-sliders" aria-hidden="true"></i> Filter Lainnya</a>
                </div>
            </div>
        </section>

        <div class="sp-wrap">
            <!-- Statistik -->
            <section class="sp-stats" aria-label="Ringkasan aktivitas">
                <article class="sp-stat">
                    <span class="sp-stat-icon"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span>
                    <div>
                        <p>Event Diikuti</p><strong><?= $pad($stats['joined']) ?></strong>
                    </div>
                </article>
                <article class="sp-stat">
                    <span class="sp-stat-icon"><i class="fa-solid fa-certificate" aria-hidden="true"></i></span>
                    <div>
                        <p>Sertifikat</p><strong><?= $pad($stats['certificates']) ?></strong>
                    </div>
                </article>
                <article class="sp-stat">
                    <span class="sp-stat-icon"><i class="fa-solid fa-inbox" aria-hidden="true"></i></span>
                    <div>
                        <p>Event Akan Datang</p><strong><?= $pad($stats['upcoming']) ?></strong>
                    </div>
                </article>
            </section>

            <!-- Rekomendasi -->
            <section class="sp-section" aria-labelledby="rec-title">
                <div class="sp-section-head">
                    <div>
                        <h2 id="rec-title">Rekomendasi Untukmu</h2>
                        <p>Berdasarkan minat dan riwayat event yang kamu ikuti.</p>
                    </div>
                    <a class="sp-link-all" href="/student/events">Lihat Semua <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>

                <?php if (!$recommended): ?>
                    <div class="sp-empty">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                        <h3>Belum ada rekomendasi event</h3>
                        <p>Ikuti beberapa kegiatan atau jelajahi semua event untuk mendapat rekomendasi.</p>
                    </div>
                <?php else: ?>
                    <div class="sp-grid">
                        <?php foreach ($recommended as $ev):
                            $detailUrl = '/student/events/' . (int) $ev['id'];
                            $catSlug   = strtolower((string) ($ev['category'] ?? 'workshop'));
                        ?>
                            <article class="sp-card">
                                <a class="sp-card-media" href="<?= $detailUrl ?>" tabindex="-1" aria-hidden="true">
                                    <?php if (!empty($ev['poster_url'])): ?>
                                        <img src="<?= $esc($ev['poster_url']) ?>" alt="" loading="lazy">
                                    <?php else: ?>
                                        <div class="sp-ph"><i class="fa-regular fa-image"></i></div>
                                    <?php endif; ?>
                                    <span class="sp-badge k-<?= $esc($catSlug) ?>"><?= $esc($ev['category'] ?? 'Event') ?></span>
                                </a>

                                <div class="sp-card-body">
                                    <h3><a href="<?= $detailUrl ?>"><?= $esc($ev['title']) ?></a></h3>
                                    <div class="sp-card-meta">
                                        <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= $esc($ev['date']) ?></span>
                                        <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?= $esc($ev['location']) ?></span>
                                    </div>
                                    <!-- Klik "Daftar" membuka halaman deskripsi event -->
                                    <a class="sp-btn-daftar" href="<?= $detailUrl ?>">Daftar</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php require dirname(__DIR__, 3) . '/includes/student/footer.php'; ?>
</body>

</html>