<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Determine active menu item
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$currentBase = basename($currentPath);
function admin_nav_active($names): string {
    global $currentBase;
    foreach ((array)$names as $n) {
        if ($currentBase === $n) { return ' active'; }
    }
    return '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin · Simple Blog</title>
  <meta name="theme-color" content="#111827">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/blog/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="d-flex">
  <!-- Sidebar -->
  <nav class="bg-black border-end border-secondary d-none d-md-block" style="width:260px;min-height:100vh;">
    <div class="p-3">
      <a class="navbar-brand d-block fw-bold text-decoration-none text-light mb-3" href="/blog/admin/dashboard.php">Admin Panel</a>
      <hr class="border-secondary opacity-50">
      <ul class="nav nav-pills flex-column gap-1">
        <li class="nav-item"><a class="nav-link text-start text-light<?php echo admin_nav_active(['dashboard.php']); ?>" href="/blog/admin/dashboard.php"<?php echo ($currentBase === 'dashboard.php') ? ' aria-current="page"' : '' ; ?>>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-start text-light<?php echo admin_nav_active(['post_new.php','post_edit.php']); ?>" href="/blog/admin/post_new.php"<?php echo (in_array($currentBase, ['post_new.php','post_edit.php'], true)) ? ' aria-current="page"' : '' ; ?>>New Post</a></li>
        <li class="nav-item mt-2"><a class="btn btn-outline-light w-100" href="/blog/admin/logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <!-- Mobile Offcanvas Sidebar -->
  <div class="offcanvas offcanvas-start bg-black text-light" tabindex="-1" id="adminSidebarOffcanvas" aria-labelledby="adminSidebarLabel">
    <div class="offcanvas-header border-bottom border-secondary">
      <h5 class="offcanvas-title" id="adminSidebarLabel">Admin Panel</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="nav nav-pills flex-column gap-1">
        <li class="nav-item"><a class="nav-link text-start text-light<?php echo admin_nav_active(['dashboard.php']); ?>" href="/blog/admin/dashboard.php"<?php echo ($currentBase === 'dashboard.php') ? ' aria-current="page"' : '' ; ?>>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-start text-light<?php echo admin_nav_active(['post_new.php','post_edit.php']); ?>" href="/blog/admin/post_new.php"<?php echo (in_array($currentBase, ['post_new.php','post_edit.php'], true)) ? ' aria-current="page"' : '' ; ?>>New Post</a></li>
        <li class="nav-item mt-2"><a class="btn btn-outline-light w-100" href="/blog/admin/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>

  <!-- Main content area -->
  <div class="flex-grow-1 d-flex flex-column min-vh-100">
    <!-- Mobile top bar with menu button -->
    <div class="border-bottom border-secondary d-md-none p-2">
      <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebarOffcanvas" aria-controls="adminSidebarOffcanvas" aria-label="Open menu">
        Menu
      </button>
    </div>
    <!-- Toast container for flash messages -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
      <?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div id="liveToast" class="toast align-items-center text-white bg-<?php echo e(($f['type'] ?? 'info') === 'success' ? 'success' : (($f['type'] ?? 'info') === 'danger' ? 'danger' : 'primary')); ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              <?php echo e($f['message'] ?? ''); ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="container mb-5 mt-4">
