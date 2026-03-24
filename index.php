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

// Include the HTML template
include 'index.html';
?>
