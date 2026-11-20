<?php
$pageTitle='Profile';
$active='profile';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
$id=(int)user()['id'];
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        if(($_POST['action']??'')==='profile'){
            $name=trim($_POST['full_name']??'');
            $phone=trim($_POST['phone']??'');
            $dept=trim($_POST['department']??'');
            $bio=trim($_POST['bio']??'');
            if($name==='')throw new RuntimeException('Name is required.');
            $pdo->prepare('UPDATE users SET full_name=?,phone=?,department=?,bio=? WHERE id=?')->execute([$name,$phone,$dept,$bio,$id]);
            flash('success','Profile updated.');
        }
        else{
            $current=$_POST['current_password']??'';
            $new=$_POST['new_password']??'';
            $confirm=$_POST['confirm_password']??'';
            if(!password_verify($current,user()['password_hash']))throw new RuntimeException('Current password is incorrect.');
            if(strlen($new)<8)throw new RuntimeException('New password must contain at least 8 characters.');
            if($new!==$confirm)throw new RuntimeException('New passwords do not match.');
            $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),$id]);
            flash('success','Password changed successfully.');
        }
        redirect('profile.php');
    }
    catch(Throwable $e){
        $error=$e->getMessage();
    }
}
require_once __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Account</div>
<h1>Teacher Profile</h1>
<p>Manage your faculty information and password.</p>
</div>
</div>
<?php
if($error):?><div class="alert alert-danger"><?=e($error)?></div>
<?php
endif;
?><div class="row g-4">
<div class="col-lg-8">
<div class="card p-4">
<div class="d-flex align-items-center gap-3 mb-4">
<div class="profile-avatar"><?=e(strtoupper(substr(user()['full_name'],0,1)))?></div>
<div>
<h4 class="mb-1"><?=e(user()['full_name'])?></h4>
<div class="text-muted">Teacher · <?=e(user()['email'])?></div>
</div>
</div>
<form method="post"><?=csrf_input()?><input type="hidden" name="action" value="profile">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Full name</label>
<input name="full_name" class="form-control" value="<?=e(user()['full_name'])?>">
</div>
<div class="col-md-6">
<label class="form-label">Email</label>
<input class="form-control" value="<?=e(user()['email'])?>" readonly>
</div>
<div class="col-md-6">
<label class="form-label">Phone</label>
<input name="phone" class="form-control" value="<?=e(user()['phone']??'')?>">
</div>
<div class="col-md-6">
<label class="form-label">Department</label>
<input name="department" class="form-control" value="<?=e(user()['department']??'')?>">
</div>
<div class="col-12">
<label class="form-label">Bio</label>
<textarea name="bio" class="form-control" rows="4"><?=e(user()['bio']??'')?></textarea>
</div>
</div>
<button class="btn btn-primary mt-4">Save Profile</button>
</form>
</div>
</div>
<div class="col-lg-4">
<div class="card p-4">
<h5>Change password</h5>
<form method="post" class="mt-3"><?=csrf_input()?><input type="hidden" name="action" value="password">
<div class="mb-3">
<label class="form-label">Current password</label>
<input type="password" name="current_password" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">New password</label>
<input type="password" name="new_password" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Confirm password</label>
<input type="password" name="confirm_password" class="form-control" required>
</div>
<button class="btn btn-outline-primary w-100">Change Password</button>
</form>
</div>
</div>
</div>
<?php
include __DIR__.'/../includes/app_end.php';
?>
