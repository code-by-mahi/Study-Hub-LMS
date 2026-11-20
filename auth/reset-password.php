<?php
$pageTitle='Set New Password';
$basePath='../';
require_once __DIR__ . '/../includes/auth.php';
$token=$_GET['token'] ?? $_POST['token'] ?? '';
$error='';
$valid=false;
$reset=null;
if($token){
    $stmt=$pdo->prepare('SELECT * FROM password_resets WHERE token_hash=? AND used_at IS NULL AND expires_at>NOW() ORDER BY id DESC LIMIT 1');
    $stmt->execute([hash('sha256',$token)]);
    $reset=$stmt->fetch();
    $valid=(bool)$reset;
}
if($_SERVER['REQUEST_METHOD']==='POST' && $valid){
    verify_csrf();
    $pass=$_POST['password']??'';
    $confirm=$_POST['confirm_password']??'';
    if(strlen($pass)<8)$error='Password must contain at least 8 characters.';
    elseif($pass!==$confirm)$error='Passwords do not match.';
    else{
        $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($pass,PASSWORD_DEFAULT),(int)$reset['user_id']]);
        $pdo->prepare('UPDATE password_resets SET used_at=NOW() WHERE id=?')->execute([(int)$reset['id']]);
        flash('success','Password updated. Please log in.');
        redirect('login.php');
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-shell">
<div class="auth-card card">
<div class="auth-brand">
<a class="navbar-brand d-inline-flex align-items-center" href="../index.php">
<img class="brand-logo" src="../assets/images/studyhub-logo.svg" alt="">StudyHub LMS</a>
<h1 class="mt-4 mb-2">Choose a new password</h1>
</div>
<?php
if(!$valid):?><div class="alert alert-danger">This reset link is invalid or expired.</div>
<a class="btn btn-primary w-100" href="forgot-password.php">Generate another link</a>
<?php
else:?>
<?php
if($error):?><div class="alert alert-danger"><?=e($error)?></div>
<?php
endif;
?><form method="post"><?=csrf_input()?><input type="hidden" name="token" value="<?=e($token)?>">
<div class="mb-3">
<label class="form-label">New password</label>
<input type="password" name="password" class="form-control" required>
</div>
<div class="mb-4">
<label class="form-label">Confirm password</label>
<input type="password" name="confirm_password" class="form-control" required>
</div>
<button class="btn btn-primary w-100">Update Password</button>
</form>
<?php
endif;
?></div>
</div>
<?php
$basePath='../';
include __DIR__ . '/../includes/footer.php';
?>
