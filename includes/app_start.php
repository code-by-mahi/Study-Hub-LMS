<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ui.php';
require_login();
$me = user();
$pageTitle = $pageTitle ?? 'Dashboard';
$basePath = '../';
$dashboard = true;
$userName = $me['full_name'] ?? 'User';
$role = $me['role'] ?? 'student';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-shell">
  <div class="sidebar-backdrop">
</div>
  <aside class="app-sidebar">
    <a class="sidebar-brand" href="<?= e(dashboard_path($role))?>">
<img class="brand-logo" src="../assets/images/studyhub-logo.svg" alt=""> StudyHub LMS</a>
<?php
include __DIR__ . '/../includes/sidebar.php';
?>
  </aside>
  <main class="app-main">
<?php
include __DIR__ . '/../includes/topbar.php';
?>
    <div class="app-content">
