(() => {
    const toggle = document.querySelector('.ch-mobile-menu-toggle');
    const sidebar = document.querySelector('.ch-sidebar');
    const backdrop = document.querySelector('.ch-sidebar-backdrop');
    const mobileQuery = window.matchMedia('(max-width: 900px)');

    const setMenuOpen = (open, restoreFocus = false) => {
        if (!toggle || !sidebar || !backdrop) {
            return;
        }

        const shouldOpen = mobileQuery.matches && open;
        sidebar.classList.toggle('is-open', shouldOpen);
        sidebar.inert = mobileQuery.matches && !shouldOpen;
        sidebar.setAttribute('aria-hidden', String(mobileQuery.matches && !shouldOpen));
        toggle.setAttribute('aria-expanded', String(shouldOpen));
        toggle.setAttribute('aria-label', shouldOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi');
        backdrop.hidden = !shouldOpen;
        document.body.classList.toggle('ch-menu-open', shouldOpen);

        if (restoreFocus && !shouldOpen) {
            toggle.focus();
        }
    };

    if (toggle && sidebar && backdrop) {
        setMenuOpen(false);
        toggle.addEventListener('click', () => {
            setMenuOpen(toggle.getAttribute('aria-expanded') !== 'true');
        });
        backdrop.addEventListener('click', () => setMenuOpen(false, true));
        sidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setMenuOpen(false));
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
                setMenuOpen(false, true);
            }
        });
        mobileQuery.addEventListener('change', () => setMenuOpen(false));
    }

    document.querySelectorAll('form[data-auto-submit]').forEach((form) => {
        form.addEventListener('change', (event) => {
            if (event.target instanceof HTMLInputElement && event.target.type === 'radio') {
                form.requestSubmit();
            }
        });
    });
})();