<!-- Top bar 
    <header class="topbar">
      <div class="portal-title">Admin Portal</div>
      <div class="search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search resources...">
      </div>
      <div class="topbar-actions">
        <button class="icon-btn" aria-label="Notifications">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
          <span class="dot"></span>
        </button>
        <div class="user-block">
          <div class="user-meta">
            <div class="name">Halo Admin</div>
            <div class="role">Super Admin</div>
          </div>
          <div class="avatar">HA</div>
        </div>
      </div>
    </header>-->

    <header class="topbar">

    <div class="topbar-left">

        <!-- Mobile Button -->

        <button
            id="sidebarToggle"
            class="sidebar-toggle">

            <i class="fa-solid fa-bars"></i>

        </button>

        <!-- Search -->

        <div class="search-box">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                placeholder="Search anything...">

        </div>

    </div>

    <div class="topbar-right">

        <!-- Notification -->

        <button class="notification-btn">

            <i class="fa-regular fa-bell"></i>

            <span class="badge">3</span>

        </button>

        <!-- User -->

        <div class="profile-dropdown">

            <button
                class="profile-btn"
                id="profileButton">

                <img
                    src="<?= BASE_URL ?>/assets/uploads/profile/<?=
                        $_SESSION['foto'] ?? 'default.png'; ?>">

                <div class="profile-info">

                    <strong>

                        <?= htmlspecialchars($_SESSION['nama']); ?>

                    </strong>

                    <small>

                        <?= htmlspecialchars($_SESSION['role']); ?>

                    </small>

                </div>

                <i class="fa-solid fa-chevron-down"></i>

            </button>

            <div class="dropdown-menu" id="profileMenu">

                <a href="#">

                    <i class="fa-solid fa-user"></i>

                    My Profile

                </a>

                <a href="#">

                    <i class="fa-solid fa-gear"></i>

                    Settings

                </a>

                <hr>

                <a
                    href="<?= BASE_URL ?>/logout.php">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Logout

                </a>

            </div>

        </div>

    </div>

</header>