<?php
$pageTitle='Courses';
$active='courses';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
require_once __DIR__.'/../includes/ui.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $action=$_POST['action']??'';
    $cid=(int)$_POST['course_id'];
    if($action==='status'){
        $status=$_POST['status']??'draft';
        if(!in_array($status,['draft','published','pending'],true))$status='draft';
        $pdo->prepare('UPDATE courses SET status=? WHERE id=?')->execute([$status,$cid]);
        flash('success','Course status updated.');
    }
    elseif($action==='delete'){
        $pdo->prepare('DELETE FROM courses WHERE id=?')->execute([$cid]);
        flash('success','Course deleted.');
    }
    redirect('courses.php');
}
$rows=$pdo->query("SELECT c.*,u.full_name teacher,cat.name category,COUNT(DISTINCT e.id) learners FROM courses c JOIN users u ON u.id=c.teacher_id LEFT JOIN categories cat ON cat.id=c.category_id LEFT JOIN enrollments e ON e.course_id=c.id GROUP BY c.id ORDER BY c.updated_at DESC")->fetchAll();
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Catalog administration</div>
<h1>Courses</h1>
<p>Review course status and manage the college-wide catalog.</p>
</div>
</div>
<div class="card p-0 overflow-hidden">
<div class="table-responsive border-0">
<table class="table">
<thead>
<tr>
<th class="ps-4">Course</th>
<th>Teacher</th>
<th>Category</th>
<th>Learners</th>
<th>Status</th>
<th class="pe-4">Actions</th>
</tr>
</thead>
<tbody>
<?php
foreach($rows as $r):?><tr>
<td class="ps-4">
<div class="fw-semibold"><?=e($r['title'])?></div>
<div class="small text-muted"><?=e($r['level'])?> · <?=e($r['duration_hours'])?> hrs</div>
</td>
<td><?=e($r['teacher'])?></td>
<td><?=e($r['category']??'—')?></td>
<td><?=e($r['learners'])?></td>
<td><?=badgeSoft(ucfirst($r['status']),$r['status']==='published'?'success':($r['status']==='pending'?'warning':'secondary'))?></td>
<td class="pe-4">
<form method="post" class="d-flex gap-1 align-items-center"><?=csrf_input()?><input type="hidden" name="action" value="status">
<input type="hidden" name="course_id" value="<?=$r['id']?>">
<select name="status" class="form-select form-select-sm" style="width:125px">
<option <?=$r['status']==='draft'?'selected':''?>>draft</option>
<option <?=$r['status']==='published'?'selected':''?>>published</option>
<option <?=$r['status']==='pending'?'selected':''?>>pending</option>
</select>
<button class="btn btn-sm btn-light">Save</button>
</form>
<form method="post" class="d-inline" data-confirm="Delete this course and all of its content?"><?=csrf_input()?><input type="hidden" name="action" value="delete">
<input type="hidden" name="course_id" value="<?=$r['id']?>">
<button class="btn btn-sm btn-outline-danger mt-1">Delete</button>
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
