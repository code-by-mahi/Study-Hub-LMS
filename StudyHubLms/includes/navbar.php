<nav class="navbar site-navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= e($basePath ?? '') ?>index.php"><span class="brand-mark">L</span>College LMS</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#publicNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="publicNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="<?= e($basePath ?? '') ?>courses.php">Courses</a></li>
        <?php if (is_logged_in()): ?><li class="nav-item"><a class="btn btn-primary btn-sm px-3" href="<?= e(dashboard_path(user()['role'])) ?>">Dashboard</a></li><li class="nav-item"><a class="nav-link" href="<?= e($basePath ?? '') ?>auth/logout.php">Logout</a></li>
        <?php else: ?><li class="nav-item"><a class="nav-link" href="<?= e($basePath ?? '') ?>auth/login.php">Login</a></li><li class="nav-item"><a class="btn btn-primary btn-sm px-3" href="<?= e($basePath ?? '') ?>auth/register.php">Create account</a></li><?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
