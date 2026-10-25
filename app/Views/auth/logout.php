<?php

use CampusHub\Core\View;

$backUrl = $backUrl ?? '/login';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f8fa">

    <title>Konfirmasi Logout | CampusHub</title>

    <link rel="icon" type="image/png"
        href="/public/assets/images/logo.png">

    <link rel="stylesheet"
        href="/public/assets/css/style.css">
    <link rel="stylesheet"
        href="/public/assets/css/logout.css">
</head>

<body class="logout-body">

    <main class="logout-page">
        <a class="logout-brand" href="<?= View::escape($backUrl) ?>"
            aria-label="Kembali ke dashboard">
            <img src="/public/assets/images/logo.png"
                alt="Logo CampusHub">
            <span>CampusHub</span>
        </a>

        <section class="logout-card"
            aria-labelledby="logout-title">

            <div class="logout-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M10 4H5.8A1.8 1.8 0 0 0 4 5.8v12.4A1.8 1.8 0 0 0 5.8 20H10"
                        stroke="currentColor" stroke-width="1.7"
                        stroke-linecap="round" />
                    <path d="M14 8l4 4-4 4M8 12h10"
                        stroke="currentColor" stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </div>

            <p class="logout-eyebrow">AKHIRI SESI</p>

            <h1 id="logout-title">
                Yakin ingin keluar?
            </h1>

            <p class="logout-description">
                Sesi CampusHub kamu akan diakhiri.
                Pastikan pekerjaan yang sedang dilakukan sudah tersimpan.
            </p>

            <form action="/logout" method="POST"
                class="logout-form">
                <?= View::csrfField() ?>

                <button type="submit" class="logout-submit">
                    Ya, logout
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </form>

            <a class="logout-cancel"
                href="<?= View::escape($backUrl) ?>">
                Batal, kembali ke dashboard
            </a>

            <div class="logout-divider"></div>

            <p class="logout-footnote">
                CampusHub
                <span aria-hidden="true">&middot;</span>
                Campus Organization &amp; Event Management
            </p>
        </section>

        <footer class="logout-footer">
            &copy; <?= date('Y') ?> CampusHub
        </footer>
    </main>
    <script>
        (() => {
            const page = document.querySelector('.logout-page');

            if (!page) return;

            const reduceMotion = window.matchMedia(
                '(prefers-reduced-motion: reduce)'
            ).matches;

            if (reduceMotion) return;

            document.querySelectorAll('.logout-brand, .logout-cancel').forEach(link => {
                link.addEventListener('click', event => {
                    if (
                        event.defaultPrevented ||
                        event.button !== 0 ||
                        event.metaKey ||
                        event.ctrlKey ||
                        event.shiftKey ||
                        event.altKey ||
                        link.target === '_blank'
                    ) {
                        return;
                    }

                    const destination = new URL(link.href, window.location.href);

                    if (destination.origin !== window.location.origin) return;

                    event.preventDefault();
                    page.classList.add('is-leaving');

                    window.setTimeout(() => {
                        window.location.assign(destination.href);
                    }, 220);
                });
            });
        })();
    </script>
</body>

</html>