<?php
$pageTitle='Quiz';
$active='quizzes';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
$id=(int)user()['id'];
$quizId=(int)($_GET['id']??$_POST['quiz_id']??0);
$st=$pdo->prepare("SELECT q.*,c.title course_title FROM quizzes q JOIN courses c ON c.id=q.course_id JOIN enrollments e ON e.course_id=c.id AND e.student_id=? WHERE q.id=? AND q.status='published'");
$st->execute([$id,$quizId]);
$quiz=$st->fetch();
if(!$quiz){
    flash('danger','Quiz not found or you are not enrolled in its course.');
    redirect('quizzes.php');
}
$qs=$pdo->prepare('SELECT * FROM questions WHERE quiz_id=? ORDER BY sort_order,id');
$qs->execute([$quizId]);
$questions=$qs->fetchAll();
foreach($questions as &$question){
    $os=$pdo->prepare('SELECT * FROM quiz_options WHERE question_id=? ORDER BY id');
    $os->execute([$question['id']]);
    $question['options']=$os->fetchAll();
}
unset($question);
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='submit'){
    verify_csrf();
    $score=0;
    $total=0;
    $pdo->beginTransaction();
    try{
        $stmt=$pdo->prepare('INSERT INTO quiz_attempts(quiz_id,student_id,started_at,submitted_at) VALUES(?,?,NOW(),NOW())');
        $stmt->execute([$quizId,$id]);
        $attemptId=(int)$pdo->lastInsertId();
        $ans=$pdo->prepare('INSERT INTO quiz_answers(attempt_id,question_id,option_id,marks_awarded) VALUES(?,?,?,?)');
        foreach($questions as $q){
            $total+=(int)$q['marks'];
            $selected=(int)($_POST['question'][$q['id']]??0);
            $opt=null;
            if($selected){
                $os=$pdo->prepare('SELECT * FROM quiz_options WHERE id=? AND question_id=?');
                $os->execute([$selected,$q['id']]);
                $opt=$os->fetch();
            }
            $awarded=($opt&&$opt['is_correct'])?(int)$q['marks']:0;
            $score+=$awarded;
            $ans->execute([$attemptId,$q['id'],$opt['id']??null,$awarded]);
        }
        $pct=$total?round(($score/$total)*100,2):0;
        $pdo->prepare('UPDATE quiz_attempts SET score=?,percentage=? WHERE id=?')->execute([$score,$pct,$attemptId]);
        $pdo->commit();
        flash('success','Quiz submitted. Your score is '.$score.'/'.$total.' ('.$pct.'%).');
        redirect('results.php');
    }
    catch(Throwable $e){
        $pdo->rollBack();
        flash('danger','Could not submit the quiz.');
        redirect('quizzes.php');
    }
}
require_once __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2"><?=e($quiz['course_title'])?></div>
<h1><?=e($quiz['title'])?></h1>
<p><?=count($questions)?> questions</p>
</div>
<div class="timer" data-quiz-timer="<?=max(60,(int)$quiz['time_limit_minutes']*60)?>">00:00</div>
</div>
<form method="post" id="quizForm"><?=csrf_input()?><input type="hidden" name="action" value="submit">
<input type="hidden" name="quiz_id" value="<?=$quizId?>">
<?php
foreach($questions as $idx=>$q):?><div class="card p-4 mb-3">
<div class="d-flex justify-content-between mb-3">
<strong>Question <?=$idx+1?></strong>
<span class="small text-muted"><?=e($q['marks'])?> mark(s)</span>
</div>
<h5><?=e($q['question_text'])?></h5>
<div class="mt-3 d-grid gap-2">
<?php
foreach($q['options'] as $opt):?><label class="quiz-option">
<input type="radio" name="question[<?=$q['id']?>]" value="<?=$opt['id']?>"> <?=e($opt['option_text'])?></label>
<?php
endforeach;
?></div>
</div>
<?php
endforeach;
?>
<?php
if($questions):?><button class="btn btn-primary btn-lg" data-confirm="Submit your quiz now? You will not be able to edit this attempt.">Submit Quiz</button>
<?php
else:?><div class="alert alert-info">This quiz has no questions yet.</div>
<?php
endif;
?></form>
<?php
include __DIR__.'/../includes/app_end.php';
?>
