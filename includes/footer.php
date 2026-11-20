<footer class="site-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5">
<div class="footer-title d-flex align-items-center gap-2">
<img class="brand-logo" src="<?= e($basePath)?>assets/images/studyhub-logo.svg" alt="">StudyHub LMS</div>
<p class="mb-0">A practical academic learning platform for courses, assignments, quizzes, and progress.</p>
</div>
      <div class="col-6 col-lg-2">
<div class="footer-title">Platform</div>
<div class="d-grid gap-2">
<a href="<?= e($basePath)?>courses.php">Courses</a>
<?php
if(is_logged_in()):?><a href="<?= e(dashboard_path(user()['role']))?>">Dashboard</a>
<?php
else:?><a href="<?= e($basePath)?>auth/login.php">Login</a>
<a href="<?= e($basePath)?>auth/register.php">Register</a>
<?php
endif;
?></div>
</div>
      <div class="col-6 col-lg-2">
<div class="footer-title">Account</div>
<div class="d-grid gap-2">
<a href="<?= e($basePath)?>auth/login.php">Sign in</a>
<a href="<?= e($basePath)?>auth/register.php">Create account</a>
</div>
</div>
      <div class="col-lg-3">
<div class="footer-title">College Portal</div>
<p class="small mb-0">Built with PHP, MySQL, Bootstrap 5, and vanilla JavaScript.</p>
</div>
    </div>
    <hr class="border-secondary my-4">
<div class="small">© <?= date('Y')?> StudyHub LMS.</div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
<script src="<?= e($basePath)?>assets/js/app.js">
</script>
</body>
</html>
