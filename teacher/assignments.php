<?php
$pageTitle='Assignments';
$active='assignments';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
require_once __DIR__.'/../includes/ui.php';
$teacherId=(int)user()['id'];
$editId=(int)($_GET['edit']??$_POST['assignment_id']??0);
$edit=null;
if($editId){
    $st=$pdo->prepare('SELECT a.* FROM assignments a JOIN courses c ON c.id=a.course_id WHERE a.id=? AND c.teacher_id=?');
    $st->execute([$editId,$teacherId]);
    $edit=$st->fetch();
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $action=$_POST['action']??'';
        if($action==='save'){
            $cid=(int)$_POST['course_id'];
            $v=$pdo->prepare('SELECT id FROM courses WHERE id=? AND teacher_id=?');
            $v->execute([$cid,$teacherId]);
            if(!$v->fetchColumn())throw new RuntimeException('Invalid course.');
            $title=trim($_POST['title']??'');
            $instructions=trim($_POST['instructions']??'');
            $marks=max(1,(int)$_POST['max_marks']);
            $deadline=trim($_POST['deadline']??'')?:null;
            $status=$_POST['status']??'open';
            if($title==='')throw new RuntimeException('Title is required.');
            if(!in_array($status,['draft','open','closed'],true))$status='open';
            if($edit){
                $pdo->prepare('UPDATE assignments SET course_id=?,title=?,instructions=?,max_marks=?,deadline=?,status=? WHERE id=?')->execute([$cid,$title,$instructions,$marks,$deadline,$status,$editId]);
                flash('success','Assignment updated.');
            }
            else{
                $pdo->prepare('INSERT INTO assignments(course_id,title,instructions,max_marks,deadline,status) VALUES(?,?,?,?,?,?)')->execute([$cid,$title,$instructions,$marks,$deadline,$status]);
                notify_course_students($cid,'New assignment','A new assignment "'.$title.'" is available.');
                flash('success','Assignment created.');
            }
            redirect('assignments.php');
        }
        elseif($action==='delete'){
            $aid=(int)$_POST['assignment_id'];
            $pdo->prepare('DELETE a FROM assignments a JOIN courses c ON c.id=a.course_id WHERE a.id=? AND c.teacher_id=?')->execute([$aid,$teacherId]);
            flash('success','Assignment deleted.');
            redirect('assignments.php');
        }
    }
    catch(Throwable $e){
        $error=$e->getMessage();
    }
}
$cq=$pdo->prepare('SELECT id,title FROM courses WHERE teacher_id=? ORDER BY title');
$cq->execute([$teacherId]);
$courses=$cq->fetchAll();
$st=$pdo->prepare('SELECT a.*,c.title course_title,(SELECT COUNT(*) FROM submissions s WHERE s.assignment_id=a.id) submissions FROM assignments a JOIN courses c ON c.id=a.course_id WHERE c.teacher_id=? ORDER BY a.deadline IS NULL,a.deadline');
$st->execute([$teacherId]);
$rows=$st->fetchAll();
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Coursework management</div>
<h1>Assignments</h1>
<p>Create deadlines, collect files, and manage grading.</p>
</div>
<a href="#assignmentForm" class="btn btn-primary">New Assignment</a>
</div>
<?php
if(!empty($error)):?><div class="alert alert-danger"><?=e($error)?></div>
<?php
endif;
?><div class="card p-4 mb-4" id="assignmentForm">
<h4><?= $edit?'Edit':'Create'?> Assignment</h4>
<form method="post" class="row g-3 mt-1"><?=csrf_input()?><input type="hidden" name="action" value="save">
<input type="hidden" name="assignment_id" value="<?=$editId?>">
<div class="col-md-5">
<label class="form-label">Course</label>
<select name="course_id" class="form-select" required>
<?php
foreach($courses as $c):?><option value="<?=$c['id']?>" <?=((int)($edit['course_id']??0)===(int)$c['id']?'selected':'')?>><?=e($c['title'])?></option>
<?php
endforeach;
?></select>
</div>
<div class="col-md-7">
<label class="form-label">Title</label>
<input name="title" class="form-control" value="<?=e($edit['title']??'')?>" required>
</div>
<div class="col-12">
<label class="form-label">Instructions</label>
<textarea name="instructions" class="form-control" rows="4"><?=e($edit['instructions']??'')?></textarea>
</div>
<div class="col-md-4">
<label class="form-label">Max marks</label>
<input name="max_marks" type="number" min="1" class="form-control" value="<?=e($edit['max_marks']??100)?>">
</div>
<div class="col-md-4">
<label class="form-label">Deadline</label>
<input name="deadline" type="datetime-local" class="form-control" value="<?=e($edit&&$edit['deadline']?date('Y-m-d\TH:i',strtotime($edit['deadline'])):'')?>">
</div>
<div class="col-md-4">
<label class="form-label">Status</label>
<select name="status" class="form-select">
<?php
foreach(['open','draft','closed'] as $s):?><option value="<?=$s?>" <?=($edit['status']??'open')===$s?'selected':''?>><?=ucfirst($s)?></option>
<?php
endforeach;
?></select>
</div>
<div class="col-12 d-flex gap-2">
<button class="btn btn-primary"><?= $edit?'Save Changes':'Create Assignment'?></button>
<?php
if($edit):?><a class="btn btn-light" href="assignments.php">Cancel</a>
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
<th class="ps-4">Assignment</th>
<th>Course</th>
<th>Deadline</th>
<th>Submissions</th>
<th>Status</th>
<th class="pe-4">Actions</th>
</tr>
</thead>
<tbody>
<?php
foreach($rows as $r):?><tr>
<td class="ps-4">
<div class="fw-semibold"><?=e($r['title'])?></div>
<div class="small text-muted">Max <?=e($r['max_marks'])?></div>
</td>
<td><?=e($r['course_title'])?></td>
<td><?=e($r['deadline']?date('d M Y, h:i A',strtotime($r['deadline'])):'No deadline')?></td>
<td><?=e($r['submissions'])?></td>
<td><?=badgeSoft(ucfirst($r['status']),$r['status']==='open'?'success':($r['status']==='closed'?'danger':'secondary'))?></td>
<td class="pe-4">
<a href="assignments.php?edit=<?=$r['id']?>#assignmentForm" class="btn btn-sm btn-light">Edit</a>
<a href="submissions.php?assignment_id=<?=$r['id']?>" class="btn btn-sm btn-light">Review</a>
<form method="post" class="d-inline" data-confirm="Delete this assignment and its submissions?"><?=csrf_input()?><input type="hidden" name="action" value="delete">
<input type="hidden" name="assignment_id" value="<?=$r['id']?>">
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
