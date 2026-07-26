<!-- ===== Sidebar =====
  <aside class="sidebar" id="sidebar">
    <div>
      <div class="brand">
        <h1>CampusHub</h1>
        <p>Admin Portal</p>
      </div>
      <nav class="nav">
        <a href="#" class="nav-link active">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><rect x="7" y="12" width="3" height="6"/><rect x="12" y="8" width="3" height="10"/><rect x="17" y="5" width="3" height="13"/></svg>
          <span>Statistics</span>
        </a>
        <a href="#" class="nav-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Users</span>
        </a>
        <a href="#" class="nav-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg>
          <span>Organizations</span>
        </a>
        <a href="#" class="nav-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
          <span>Committees</span>
        </a>
        <a href="#" class="nav-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <span>Events</span>
        </a>
        <a href="#" class="nav-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12.99V2h10.99l8.6 8.59a2 2 0 0 1 0 2.82Z"/><circle cx="7.5" cy="7.5" r="1.5" fill="currentColor" stroke="none"/></svg>
          <span>Categories</span>
        </a>
        <a href="#" class="nav-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
          <span>Settings</span>
        </a>
      </nav>
    </div>
 
    <div class="sidebar-footer">
      <button class="btn-create">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
        Create New Event
      </button>
      <button class="link-logout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout
      </button>
    </div>
  </aside> -->
  <aside class="sidebar" id="sidebar">

    <div class="sidebar-header">

        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="sidebar-brand">

            <img
                src="<?= BASE_URL ?>/assets/img/logo.png"
                alt="CampusHub Logo">

            <div>

                <h2>CampusHub</h2>

                <span>Admin Portal</span>

            </div>

        </a>

    </div>

    <nav class="sidebar-menu">

        <a href="dashboard.php" class="menu-item active">

            <i class="fa-solid fa-house"></i>

            <span>Dashboard</span>

        </a>

        <a href="<?= BASE_URL ?>/admin/users/" class="menu-item">

            <i class="fa-solid fa-users"></i>

            <span>Users</span>

        </a>

        <a href="<?= BASE_URL ?>/admin/organisasi/" class="menu-item">

            <i class="fa-solid fa-building"></i>

            <span>Organizations</span>

        </a>

        <a href="<?= BASE_URL ?>/admin/kategori/" class="menu-item">

            <i class="fa-solid fa-layer-group"></i>

            <span>Categories</span>

        </a>

        <a href="<?= BASE_URL ?>/admin/event/" class="menu-item">

            <i class="fa-solid fa-calendar-days"></i>

            <span>Events</span>

        </a>

        <a href="<?= BASE_URL ?>/admin/peserta/" class="menu-item">

            <i class="fa-solid fa-user-graduate"></i>

            <span>Participants</span>

        </a>

        <a href="<?= BASE_URL ?>/admin/dokumentasi/" class="menu-item">

            <i class="fa-solid fa-images"></i>

            <span>Documentation</span>

        </a>

        <a href="<?= BASE_URL ?>/admin/sertifikat/" class="menu-item">

            <i class="fa-solid fa-award"></i>

            <span>Certificates</span>

        </a>

        <a href="<?= BASE_URL ?>/admin/laporan/" class="menu-item">

            <i class="fa-solid fa-chart-column"></i>

            <span>Reports</span>

        </a>

    </nav>

    <div class="sidebar-footer">

        <a href="#" class="menu-item">

            <i class="fa-solid fa-gear"></i>

            <span>Settings</span>

        </a>

        <a
            href="<?= BASE_URL ?>/logout.php"
            class="menu-item logout">

            <i class="fa-solid fa-right-from-bracket"></i>

            <span>Logout</span>

        </a>

    </div>

</aside>