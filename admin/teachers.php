<?php
$pageTitle='Teachers';
$active='teachers';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
require_once __DIR__.'/../includes/ui.php';
$editId=(int)($_GET['edit']??$_POST['user_id']??0);
$edit=null;
if($editId){
    $st=$pdo->prepare("SELECT * FROM users WHERE id=? AND role='teacher'");
    $st->execute([$editId]);
    $edit=$st->fetch();
}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $action=$_POST['action']??'';
        if($action==='save'){
            $name=trim($_POST['full_name']??'');
            $email=trim($_POST['email']??'');
            $dept=trim($_POST['department']??'');
            $phone=trim($_POST['phone']??'');
            $status=$_POST['status']??'active';
            if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Valid name and email are required.');
            if(!in_array($status,['active','inactive','pending'],true))$status='active';
            if($edit){
                $pdo->prepare("UPDATE users SET full_name=?,email=?,department=?,phone=?,status=? WHERE id=? AND role='teacher'")->execute([$name,$email,$dept,$phone,$status,$editId]);
                flash('success','Teacher updated.');
            }
            else{
                $password=$_POST['password']??'';
                if(strlen($password)<8)throw new RuntimeException('Teacher password must be at least 8 characters.');
                $pdo->prepare("INSERT INTO users(full_name,email,password_hash,role,status,department,phone) VALUES(?,?,?,'teacher',?,?,?)")->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$status,$dept,$phone]);
                flash('success','Teacher account created.');
            }
            redirect('teachers.php');
        }
        elseif($action==='delete'){
            $uid=(int)$_POST['user_id'];
            $pdo->prepare("DELETE FROM users WHERE id=? AND role='teacher'")->execute([$uid]);
            flash('success','Teacher deleted.');
            redirect('teachers.php');
        }
    }
    catch(Throwable $e){
        $error=$e->getMessage();
    }
}
$st=$pdo->query("SELECT u.*,COUNT(DISTINCT c.id) course_count,COUNT(DISTINCT e.student_id) student_count FROM users u LEFT JOIN courses c ON c.teacher_id=u.id LEFT JOIN enrollments e ON e.course_id=c.id WHERE u.role='teacher' GROUP BY u.id ORDER BY u.created_at DESC");
$rows=$st->fetchAll();
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Faculty management</div>
<h1>Teachers</h1>
<p>Provision instructor accounts and manage access.</p>
</div>
<a href="#teacherForm" class="btn btn-primary">Add Teacher</a>
</div>
<?php
if($error):?><div class="alert alert-danger"><?=e($error)?></div>
<?php
endif;
?><div class="card p-4 mb-4" id="teacherForm">
<h4><?= $edit?'Edit':'Add'?> Teacher</h4>
<form method="post" class="row g-3 mt-1"><?=csrf_input()?><input type="hidden" name="action" value="save">
<input type="hidden" name="user_id" value="<?=$editId?>">
<div class="col-md-6">
<label class="form-label">Full name</label>
<input name="full_name" class="form-control" value="<?=e($edit['full_name']??'')?>" required>
</div>
<div class="col-md-6">
<label class="form-label">Email</label>
<input name="email" type="email" class="form-control" value="<?=e($edit['email']??'')?>" required>
</div>
<div class="col-md-5">
<label class="form-label">Department</label>
<input name="department" class="form-control" value="<?=e($edit['department']??'')?>">
</div>
<div class="col-md-3">
<label class="form-label">Phone</label>
<input name="phone" class="form-control" value="<?=e($edit['phone']??'')?>">
</div>
<div class="col-md-2">
<label class="form-label">Status</label>
<select name="status" class="form-select">
<?php
foreach(['active','inactive','pending'] as $s):?><option <?=$s===($edit['status']??'active')?'selected':''?>><?=$s?></option>
<?php
endforeach;
?></select>
</div>
<?php
if(!$edit):?><div class="col-md-2">
<label class="form-label">Password</label>
<input name="password" type="password" class="form-control" required>
</div>
<?php
endif;
?><div class="col-12">
<button class="btn btn-primary"><?= $edit?'Save Changes':'Create Teacher'?></button>
<?php
if($edit):?><a href="teachers.php" class="btn btn-light ms-2">Cancel</a>
<?php
endif;
?></div>
</form>
</div>
<div class="card p-0 overflow-hidden">
<div class="table-responsive border-0">
<table class="table">
<thead>
<tr>
<th class="ps-4">Teacher</th>
<th>Department</th>
<th>Courses</th>
<th>Students</th>
<th>Status</th>
<th class="pe-4">Actions</th>
</tr>
</thead>
<tbody>
<?php
foreach($rows as $r):?><tr>
<td class="ps-4">
<div class="fw-semibold"><?=e($r['full_name'])?></div>
<div class="small text-muted"><?=e($r['email'])?></div>
</td>
<td><?=e($r['department']??'—')?></td>
<td><?=e($r['course_count'])?></td>
<td><?=e($r['student_count'])?></td>
<td><?=badgeSoft(ucfirst($r['status']),$r['status']==='active'?'success':($r['status']==='pending'?'warning':'secondary'))?></td>
<td class="pe-4">
<a class="btn btn-sm btn-light" href="teachers.php?edit=<?=$r['id']?>#teacherForm">Edit</a>
<form method="post" class="d-inline" data-confirm="Delete this teacher and all owned courses?"><?=csrf_input()?><input type="hidden" name="action" value="delete">
<input type="hidden" name="user_id" value="<?=$r['id']?>">
<button class="btn btn-sm btn-outline-danger">Delete</button>
</form>
</td>
</tr>
<?php
endforeach;
?></tbody>
</table>
</div>
</div>
<?php
include __DIR__.'/../includes/app_end.php';
?>
