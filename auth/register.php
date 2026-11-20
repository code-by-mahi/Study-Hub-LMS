<?php
$pageTitle = 'Create account';
$basePath = '../';
require_once __DIR__ . '/../includes/auth.php';
public_redirect_after_login();
$error = '';
$name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid name and email.';
    elseif (strlen($password) < 8) $error = 'Password must contain at least 8 characters.';
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $error = 'An account with this email already exists.';
        else {
            $stmt = $pdo->prepare("INSERT INTO users (full_name,email,password_hash,role,status) VALUES (?,?,?,'student','active')");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $id = (int)$pdo->lastInsertId();
            $account = $pdo->query('SELECT * FROM users WHERE id=' . $id)->fetch();
            login_user($account);
            flash('success', 'Account created successfully. You are signed in as a student.');
            redirect('../student/dashboard.php');
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-shell">
<div class="auth-card card">
  <div class="auth-brand">
<a class="navbar-brand d-inline-flex align-items-center" href="../index.php">
<img class="brand-logo" src="../assets/images/studyhub-logo.svg" alt="">StudyHub LMS</a>
<h1 class="mt-4 mb-2">Create your account</h1>
<p class="text-muted mb-0">Create a student account to start learning.</p>
</div>
<?php
if ($error):?><div class="alert alert-danger"><?= e($error)?></div>
<?php
endif;
?>
  <form method="post"><?= csrf_input()?>
    <div class="mb-3">
<label class="form-label">Full name</label>
<input name="full_name" class="form-control" value="<?= e($name)?>" placeholder="Full name" required>
</div>
    <div class="mb-3">
<label class="form-label">College email</label>
<input name="email" type="email" class="form-control" value="<?= e($email)?>" placeholder="you@college.edu" required>
</div>
    <div class="row g-3">
<div class="col-md-6">
<label class="form-label">Password</label>
<input name="password" id="regPassword" type="password" class="form-control" placeholder="At least 8 characters" required>
</div>
<div class="col-md-6">
<label class="form-label">Confirm password</label>
<input name="confirm_password" type="password" class="form-control" placeholder="Repeat password" required>
</div>
</div>
    <div class="form-check my-4">
<input class="form-check-input" id="terms" type="checkbox" required>
<label class="form-check-label" for="terms">I agree to the platform terms.</label>
</div>
    <button class="btn btn-primary w-100" type="submit">Create Student Account</button>
    <!---<div class="small text-muted mt-3">Teacher and admin accounts are created by the LMS administrator. There is no separate role selector during login.</div>-->
    <div class="text-center mt-4 small">Already have an account? <a href="login.php">Login</a>
</div>
  </form>
</div>
</div>
<?php
$basePath='../';
include __DIR__ . '/../includes/footer.php';
?>
