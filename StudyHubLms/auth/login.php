<?php
$pageTitle = 'Login'; $basePath = '../';
require_once __DIR__ . '/../includes/auth.php';
public_redirect_after_login();
$error = '';
$email = trim($_POST['email'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    $account = $stmt->fetch();
    if (!$account || !password_verify($password, $account['password_hash'])) {
        $error = 'Incorrect email or password.';
    } elseif ($account['status'] !== 'active') {
        $error = 'This account is not active. Please contact the LMS administrator.';
    } else {
        login_user($account, !empty($_POST['remember']));
        flash('success', 'Welcome back, ' . $account['full_name'] . '.');
        $redirect = $_GET['redirect'] ?? '';
        if ($redirect && str_starts_with($redirect, '/')) redirect($redirect);
        redirect(dashboard_path($account['role']));
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-shell">
  <div class="auth-card card">
    <div class="auth-brand">
      <a class="navbar-brand d-inline-flex align-items-center" href="../index.php"><span class="brand-mark">L</span>College LMS</a>
      <h1 class="mt-4 mb-2">Welcome back</h1>
      <p class="text-muted mb-0">Sign in with your college account. LMS detects your role automatically.</p>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" novalidate><?= csrf_input() ?>
      <div class="mb-3"><label class="form-label" for="email">Email address</label><input id="email" name="email" type="email" class="form-control" value="<?= e($email) ?>" placeholder="you@college.edu" required></div>
      <div class="mb-3"><div class="d-flex justify-content-between"><label class="form-label" for="password">Password</label><a href="forgot-password.php" class="small">Forgot password?</a></div><div class="input-group"><input id="password" name="password" type="password" class="form-control" placeholder="Enter your password" required><button class="btn btn-outline-secondary" type="button" data-password-toggle="password"><i class="bi bi-eye"></i></button></div></div>
      <div class="form-check mb-4"><input class="form-check-input" id="remember" name="remember" type="checkbox"><label class="form-check-label" for="remember">Remember me</label></div>
      <button class="btn btn-primary w-100" type="submit">Login</button>
      <div class="text-center mt-4 small">New to the platform? <a href="register.php">Create a student account</a></div>
    </form>
    <div class="text-center mt-4"><a class="small text-muted" href="../index.php"><i class="bi bi-arrow-left me-1"></i>Back to home</a></div>
  </div>
</div>
<?php $basePath='../'; include __DIR__ . '/../includes/footer.php'; ?>
