<?php
/**
 * SAKMS - Main Dashboard Router
 * Handles page navigation and authentication
 */
require_once 'includes/auth.php';
require_once 'includes/db_config.php';
require_once 'includes/functions.php';

requireLogin();
checkSessionTimeout();

// Determine which page to display
$page = $_GET['page'] ?? 'dashboard';
$valid_pages = ['dashboard', 'profiles', 'analytics', 'performance', 'settings'];

if (!in_array($page, $valid_pages)) {
    $page = 'dashboard';
}

// Get current supervisor
$supervisor_name = getCurrentSupervisor();

// For employee detail pages
$employee_id = $_GET['emp_id'] ?? null;
$period_id = $_GET['period'] ?? 4; // Default to 2025 (period_id=4)

// Determine page title
$page_titles = [
    'dashboard' => 'Supervisor Dashboard',
    'profiles' => 'Sales Assistant Profiles',
    'analytics' => 'Analytics Dashboard',
    'performance' => 'Performance Report & Training',
    'settings' => 'Settings'
];

$topbar_title = $page_titles[$page] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo $topbar_title; ?> - SAKMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="styles.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="shell">

  <!-- ══════════════════════════════════════
       SIDEBAR
  ══════════════════════════════════════ -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">S</div>

    <nav class="nav-group">
      <div class="nav-item <?php echo $page === 'dashboard' ? 'active' : ''; ?>" data-page="dashboard" onclick="navigate(this)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
          <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        <span class="nav-label">Supervisor Dashboard</span>
      </div>

      <div class="nav-item <?php echo $page === 'profiles' ? 'active' : ''; ?>" data-page="profiles" onclick="navigate(this)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <span class="nav-label">Sales Assistant Profiles</span>
      </div>

      <div class="nav-item <?php echo $page === 'analytics' ? 'active' : ''; ?>" data-page="analytics" onclick="navigate(this)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
          <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        <span class="nav-label">Analytics Dashboard</span>
      </div>

      <div class="nav-item <?php echo $page === 'performance' ? 'active' : ''; ?>" data-page="performance" onclick="navigate(this)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 20h.01M7 20v-4M12 20v-8M17 20V8M22 4l-5 5-4-4-7 7"/>
        </svg>
        <span class="nav-label">Performance &amp; Training</span>
      </div>

      <div class="nav-item <?php echo $page === 'settings' ? 'active' : ''; ?>" data-page="settings" onclick="navigate(this)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33m-2.36 0a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82M9.31 7a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33"/>
        </svg>
        <span class="nav-label">Settings</span>
      </div>
    </nav>

    <div class="nav-divider"></div>

    <div class="nav-bottom">
      <div class="nav-item nav-logout" onclick="handleLogout()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span class="nav-label">Logout</span>
      </div>
    </div>
  </aside>

  <!-- ══════════════════════════════════════
       MAIN CONTENT
  ══════════════════════════════════════ -->
  <div class="main">
    <!-- TOPBAR -->
    <header class="topbar">
      <div class="topbar-title">
        <h1 id="topbar-heading"><?php echo $topbar_title; ?></h1>
        <p>SAKMS – Sales Assistant KPI Monitoring System</p>
      </div>
      <div class="topbar-right">
        <div class="bell-btn" title="Notifications">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <div class="bell-badge">3</div>
        </div>

        <div class="avatar-wrap">
          <div class="avatar">S</div>
          <div class="avatar-info">
            <div class="avatar-name"><?php echo safe($supervisor_name); ?></div>
            <div class="avatar-role">Supervisor</div>
          </div>
        </div>
      </div>
    </header>

    <!-- CONTENT WRAPPER -->
    <div class="content-wrapper">
      <?php
      // Load page content based on $page variable
      if ($page === 'dashboard') {
          include 'pages/dashboard.php';
      } elseif ($page === 'profiles') {
          include 'pages/profiles.php';
      } elseif ($page === 'analytics') {
          include 'pages/analytics.php';
      } elseif ($page === 'performance') {
          include 'pages/performance.php';
      } elseif ($page === 'settings') {
          include 'pages/settings.php';
      }
      ?>
    </div>
  </div>

</div>

<script>
  const pageLabels = {
    dashboard:   'Supervisor Dashboard',
    profiles:    'Sales Assistant Profiles',
    analytics:   'Analytics Dashboard',
    performance: 'Performance Report & Training',
    settings:    'Settings'
  };

  function navigate(el) {
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    el.classList.add('active');

    const page = el.dataset.page;
    const url = new URL(window.location);
    url.searchParams.set('page', page);
    window.location.href = url.toString();
  }

  function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = 'logout.php';
    }
  }
</script>
</body>
</html>
