<?php
$pageTitle='Quizzes';
$active='quizzes';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
require_once __DIR__.'/../includes/ui.php';
$teacherId=(int)user()['id'];
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $action=$_POST['action']??'';
        $id=(int)($_POST['quiz_id']??0);
        if($action==='save'){
            $cid=(int)$_POST['course_id'];
            $v=$pdo->prepare('SELECT id FROM courses WHERE id=? AND teacher_id=?');
            $v->execute([$cid,$teacherId]);
            if(!$v->fetchColumn())throw new RuntimeException('Invalid course.');
            $title=trim($_POST['title']??'');
            $limit=max(1,(int)$_POST['time_limit_minutes']);
            $status=$_POST['status']??'draft';
            if(!in_array($status,['draft','published'],true))$status='draft';
            if($title==='')throw new RuntimeException('Quiz title is required.');
            $pdo->prepare('INSERT INTO quizzes(course_id,title,time_limit_minutes,status) VALUES(?,?,?,?)')->execute([$cid,$title,$limit,$status]);
            $qid=(int)$pdo->lastInsertId();
            flash('success','Quiz created. Add questions next.');
            redirect('quiz-builder.php?id='.$qid);
        }
        elseif($action==='toggle'){
            $pdo->prepare("UPDATE quizzes q JOIN courses c ON c.id=q.course_id SET q.status=IF(q.status='published','draft','published') WHERE q.id=? AND c.teacher_id=?")->execute([$id,$teacherId]);
            flash('success','Quiz status updated.');
        }
        elseif($action==='delete'){
            $pdo->prepare('DELETE q FROM quizzes q JOIN courses c ON c.id=q.course_id WHERE q.id=? AND c.teacher_id=?')->execute([$id,$teacherId]);
            flash('success','Quiz deleted.');
        }
        redirect('quizzes.php');
    }
    catch(Throwable $e){
        $error=$e->getMessage();
    }
}
$cq=$pdo->prepare('SELECT id,title FROM courses WHERE teacher_id=? ORDER BY title');
$cq->execute([$teacherId]);
$courses=$cq->fetchAll();
$st=$pdo->prepare('SELECT q.*,c.title course_title,(SELECT COUNT(*) FROM questions x WHERE x.quiz_id=q.id) question_count FROM quizzes q JOIN courses c ON c.id=q.course_id WHERE c.teacher_id=? ORDER BY q.created_at DESC');
$st->execute([$teacherId]);
$rows=$st->fetchAll();
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Assessment tools</div>
<h1>Quizzes</h1>
<p>Create MCQ quizzes, publish them, and manage questions.</p>
</div>
</div>
<?php
if($error):?><div class="alert alert-danger"><?=e($error)?></div>
<?php
endif;
?><div class="card p-4 mb-4">
<h4>Create Quiz</h4>
<form method="post" class="row g-3 mt-1"><?=csrf_input()?><input type="hidden" name="action" value="save">
<div class="col-md-5">
<label class="form-label">Course</label>
<select name="course_id" class="form-select" required>
<?php
foreach($courses as $c):?><option value="<?=$c['id']?>"><?=e($c['title'])?></option>
<?php
endforeach;
?></select>
</div>
<div class="col-md-4">
<label class="form-label">Title</label>
<input name="title" class="form-control" required>
</div>
<div class="col-md-2">
<label class="form-label">Time (min)</label>
<input name="time_limit_minutes" type="number" min="1" class="form-control" value="10">
</div>
<div class="col-md-1 d-flex align-items-end">
<button class="btn btn-primary w-100">
<i class="bi bi-plus-lg">
</i>
</button>
</div>
<div class="col-md-4">
<label class="form-label">Initial status</label>
<select name="status" class="form-select">
<option value="draft">Draft</option>
<option value="published">Published</option>
</select>
</div>
</form>
</div>
<div class="card p-0 overflow-hidden">
<div class="table-responsive border-0">
<table class="table">
<thead>
<tr>
<th class="ps-4">Quiz</th>
<th>Course</th>
<th>Questions</th>
<th>Time</th>
<th>Status</th>
<th class="pe-4">Actions</th>
</tr>
</thead>
<tbody>
<?php
foreach($rows as $r):?><tr>
<td class="ps-4 fw-semibold"><?=e($r['title'])?></td>
<td><?=e($r['course_title'])?></td>
<td><?=e($r['question_count'])?></td>
<td><?=e($r['time_limit_minutes'])?> min</td>
<td><?=badgeSoft(ucfirst($r['status']),$r['status']==='published'?'success':'secondary')?></td>
<td class="pe-4">
<a href="quiz-builder.php?id=<?=$r['id']?>" class="btn btn-sm btn-light">Questions</a>
<form method="post" class="d-inline"><?=csrf_input()?><input type="hidden" name="action" value="toggle">
<input type="hidden" name="quiz_id" value="<?=$r['id']?>">
<button class="btn btn-sm btn-light"><?=$r['status']==='published'?'Draft':'Publish'?></button>
</form>
<form method="post" class="d-inline" data-confirm="Delete this quiz and its questions?"><?=csrf_input()?><input type="hidden" name="action" value="delete">
<input type="hidden" name="quiz_id" value="<?=$r['id']?>">
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
