<?php
$pageTitle='Assignment';
$active='assignments';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
require_once __DIR__.'/../includes/ui.php';
$id=(int)user()['id'];
$aid=(int)($_GET['id']??0);
$st=$pdo->prepare("SELECT a.*,c.title course_title FROM assignments a JOIN courses c ON c.id=a.course_id JOIN enrollments e ON e.course_id=c.id AND e.student_id=? WHERE a.id=?");
$st->execute([$id,$aid]);
$a=$st->fetch();
if(!$a){
    flash('danger','Assignment not found or you are not enrolled.');
    redirect('assignments.php');
}
$st=$pdo->prepare('SELECT * FROM submissions WHERE assignment_id=? AND student_id=?');
$st->execute([$aid,$id]);
$sub=$st->fetch();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='submit'){
    verify_csrf();
    try{
        $path=upload_file('submission','assignments',['pdf','zip','rar','doc','docx','txt','png','jpg','jpeg'],15728640);
        $comment=trim($_POST['comment']??'');
        $late=$a['deadline'] && strtotime($a['deadline'])<time();
        if($sub){
            $pdo->prepare('UPDATE submissions SET file_path=COALESCE(?,file_path),comment=?,submitted_at=NOW(),status=?,marks=NULL,feedback=NULL,graded_at=NULL WHERE id=?')->execute([$path,$comment,$late?'late':'submitted',$sub['id']]);
        }
        else{
            $pdo->prepare('INSERT INTO submissions(assignment_id,student_id,file_path,comment,submitted_at,status) VALUES(?,?,?,?,NOW(),?)')->execute([$aid,$id,$path,$comment,$late?'late':'submitted']);
        }
        notify_course_teacher((int)$a['course_id'],'New assignment submission',user()['full_name'].' submitted '.$a['title'].'.');
        flash('success','Assignment submitted successfully.');
        redirect('assignment-details.php?id='.$aid);
    }
    catch(Throwable $e){
        $error=$e->getMessage();
    }
}
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<a class="small" href="assignments.php">
<i class="bi bi-arrow-left me-1">
</i>Back to assignments</a>
<h1 class="mt-2"><?=e($a['title'])?></h1>
<p><?=e($a['course_title'])?></p>
</div><?=badgeSoft(ucfirst($a['status']),$a['status']==='open'?'success':'secondary')?></div>
<div class="row g-4">
<div class="col-lg-8">
<div class="card p-4">
<h4>Instructions</h4>
<div class="text-muted mt-3"><?=nl2br(e($a['instructions']??'No instructions provided.'))?></div>
</div>
<?php
if($sub&&$sub['feedback']):?><div class="card p-4 mt-4">
<h4>Teacher feedback</h4>
<p class="mb-0 text-muted"><?=nl2br(e($sub['feedback']))?></p>
</div>
<?php
endif;
?></div>
<div class="col-lg-4">
<div class="card p-4">
<div class="d-flex justify-content-between">
<span class="text-muted">Max marks</span>
<strong><?=e($a['max_marks'])?></strong>
</div>
<div class="d-flex justify-content-between mt-2">
<span class="text-muted">Deadline</span>
<strong><?=e($a['deadline']?date('d M Y',strtotime($a['deadline'])):'No deadline')?></strong>
</div>
<hr>
<h5><?= $sub?'Update submission':'Submit assignment'?></h5>
<?php
if($error):?><div class="alert alert-danger"><?=e($error)?></div>
<?php
endif;
?><form method="post" enctype="multipart/form-data"><?=csrf_input()?><input type="hidden" name="action" value="submit">
<div class="mb-3">
<label class="form-label">File</label>
<input class="form-control" type="file" name="submission">
<div class="form-text">PDF, DOC, ZIP and common image/text files up to 15 MB.</div>
</div>
<div class="mb-3">
<label class="form-label">Comment</label>
<textarea name="comment" class="form-control" rows="4" placeholder="Add a note to your teacher..."><?=e($sub['comment']??'')?></textarea>
</div>
<button class="btn btn-primary w-100" <?=($a['status']==='closed'?'disabled':'')?>><?= $sub?'Update Submission':'Submit Assignment'?></button>
</form>
<?php
if($sub):?><hr>
<div class="small text-muted">Status: <?=e($sub['status'])?><?= $sub['marks']!==null?' · Marks: '.e($sub['marks']):''?></div>
<?php
endif;
?></div>
</div>
</div>
<?php
include __DIR__.'/../includes/app_end.php';
?>
