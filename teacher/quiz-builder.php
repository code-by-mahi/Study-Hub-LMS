<?php
$pageTitle='Quiz Builder';
$active='quizzes';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
require_once __DIR__.'/../includes/ui.php';
$teacherId=(int)user()['id'];
$quizId=(int)($_GET['id']??$_POST['quiz_id']??0);
$st=$pdo->prepare('SELECT q.*,c.title course_title FROM quizzes q JOIN courses c ON c.id=q.course_id WHERE q.id=? AND c.teacher_id=?');
$st->execute([$quizId,$teacherId]);
$quiz=$st->fetch();
if(!$quiz){
    flash('danger','Quiz not found.');
    redirect('quizzes.php');
}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $action=$_POST['action']??'';
        if($action==='add_question'){
            $text=trim($_POST['question_text']??'');
            $marks=max(1,(int)$_POST['marks']);
            $options=array_map('trim',$_POST['options']??[]);
            $correct=(int)($_POST['correct_index']??0);
            $options=array_values(array_filter($options,fn($v)=>$v!==''));
            if($text===''||count($options)<2)throw new RuntimeException('Question and at least two options are required.');
            $pdo->beginTransaction();
            $q=$pdo->prepare('SELECT COALESCE(MAX(sort_order),0)+1 FROM questions WHERE quiz_id=?');
            $q->execute([$quizId]);
            $order=(int)$q->fetchColumn();
            $pdo->prepare('INSERT INTO questions(quiz_id,question_text,marks,sort_order) VALUES(?,?,?,?)')->execute([$quizId,$text,$marks,$order]);
            $qid=(int)$pdo->lastInsertId();
            foreach($options as $idx=>$opt)$pdo->prepare('INSERT INTO quiz_options(question_id,option_text,is_correct) VALUES(?,?,?)')->execute([$qid,$opt,$idx===$correct?1:0]);
            $pdo->commit();
            flash('success','Question added.');
        }
        elseif($action==='delete_question'){
            $pdo->prepare('DELETE q FROM questions q WHERE q.id=? AND q.quiz_id=?')->execute([(int)$_POST['question_id'],$quizId]);
            flash('success','Question deleted.');
        }
        redirect('quiz-builder.php?id='.$quizId);
    }
    catch(Throwable $e){
        if($pdo->inTransaction())$pdo->rollBack();
        $error=$e->getMessage();
    }
}
$qs=$pdo->prepare('SELECT * FROM questions WHERE quiz_id=? ORDER BY sort_order,id');
$qs->execute([$quizId]);
$questions=$qs->fetchAll();
foreach($questions as &$q){
    $os=$pdo->prepare('SELECT * FROM quiz_options WHERE question_id=? ORDER BY id');
    $os->execute([$q['id']]);
    $q['options']=$os->fetchAll();
}
unset($q);
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<a href="quizzes.php" class="small">
<i class="bi bi-arrow-left me-1">
</i>Back to quizzes</a>
<h1 class="mt-2"><?=e($quiz['title'])?></h1>
<p><?=e($quiz['course_title'])?> · <?=e($quiz['time_limit_minutes'])?> minutes</p>
</div>
</div>
<?php
if($error):?><div class="alert alert-danger"><?=e($error)?></div>
<?php
endif;
?><div class="card p-4 mb-4">
<h4>Add Question</h4>
<form method="post" class="mt-2"><?=csrf_input()?><input type="hidden" name="action" value="add_question">
<input type="hidden" name="quiz_id" value="<?=$quizId?>">
<div class="mb-3">
<label class="form-label">Question</label>
<textarea name="question_text" class="form-control" rows="3" required>
</textarea>
</div>
<div class="row g-2">
<?php
for($i=0;
$i<4;
$i++):?><div class="col-md-6">
<label class="form-label">Option <?=$i+1?></label>
<input name="options[]" class="form-control" <?= $i<2?'required':''?>>
</div>
<?php
endfor;
?></div>
<div class="row g-3 mt-1">
<div class="col-md-3">
<label class="form-label">Marks</label>
<input name="marks" type="number" min="1" class="form-control" value="1">
</div>
<div class="col-md-4">
<label class="form-label">Correct option</label>
<select name="correct_index" class="form-select">
<option value="0">Option 1</option>
<option value="1">Option 2</option>
<option value="2">Option 3</option>
<option value="3">Option 4</option>
</select>
</div>
<div class="col-md-5 d-flex align-items-end">
<button class="btn btn-primary">Add Question</button>
</div>
</div>
</form>
</div>
<?php
foreach($questions as $idx=>$q):?><div class="card p-4 mb-3">
<div class="d-flex justify-content-between">
<div>
<strong>Question <?=$idx+1?></strong>
<div class="mt-2"><?=e($q['question_text'])?></div>
</div>
<form method="post" data-confirm="Delete this question?"><?=csrf_input()?><input type="hidden" name="action" value="delete_question">
<input type="hidden" name="quiz_id" value="<?=$quizId?>">
<input type="hidden" name="question_id" value="<?=$q['id']?>">
<button class="btn btn-sm btn-outline-danger">Delete</button>
</form>
</div>
<div class="mt-3 row g-2">
<?php
foreach($q['options'] as $o):?><div class="col-md-6">
<div class="border rounded p-2 <?= $o['is_correct']?'bg-success-subtle border-success':''?>">
<i class="bi bi-<?= $o['is_correct']?'check-circle-fill':'circle'?> me-1">
</i><?=e($o['option_text'])?></div>
</div>
<?php
endforeach;
?></div>
</div>
<?php
endforeach;
?>
<?php
include __DIR__.'/../includes/app_end.php';
?>
