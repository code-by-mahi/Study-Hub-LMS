<?php
$pageTitle='Forgot Password';
$basePath='../';
require_once __DIR__ . '/../includes/auth.php';
$message='';
$resetUrl='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $email=trim($_POST['email'] ?? '');
    $stmt=$pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    $id=$stmt->fetchColumn();
    if ($id) {
        $pdo->prepare('DELETE FROM password_resets WHERE user_id=?')->execute([(int)$id]);
        $token=bin2hex(random_bytes(32));
        $hash=hash('sha256',$token);
        $expires=date('Y-m-d H:i:s', time()+1800);
        $pdo->prepare('INSERT INTO password_resets (user_id,token_hash,expires_at) VALUES (?,?,?)')->execute([(int)$id,$hash,$expires]);
        $resetUrl='reset-password.php?token='.urlencode($token);
        $message='A demo reset link was generated below. In production, send this link by email.';
    }
    else $message='If that email exists, a reset link would be generated. For this local demo, no account was found.';
}
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-shell">
<div class="auth-card card">
<div class="auth-brand">
<a class="navbar-brand d-inline-flex align-items-center" href="../index.php">
<img class="brand-logo" src="../assets/images/studyhub-logo.svg" alt="">StudyHub LMS</a>
<h1 class="mt-4 mb-2">Reset your password</h1>
<p class="text-muted">Enter your account email.</p>
</div>
<?php
if($message):?><div class="alert alert-info"><?= e($message)?>
<?php
if($resetUrl):?><hr>
<div class="small text-break">
<a href="<?= e($resetUrl)?>"><?= e($resetUrl)?></a>
</div>
<?php
endif;
?></div>
<?php
endif;
?>
<form method="post"><?= csrf_input()?><div class="mb-3">
<label class="form-label">Email address</label>
<input type="email" name="email" class="form-control" placeholder="you@college.edu" required>
</div>
<button class="btn btn-primary w-100">Generate Reset Link</button>
<div class="text-center mt-4">
<a class="small" href="login.php">Back to login</a>
</div>
</form>
</div>
</div>
<?php
$basePath='../';
include __DIR__ . '/../includes/footer.php';
?>
