<?php
$pageTitle='Course Details';
$basePath='';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/ui.php';
$courseId=(int)($_GET['id']??0);
$st=$pdo->prepare("SELECT c.*,u.full_name teacher,cat.name category FROM courses c JOIN users u ON u.id=c.teacher_id LEFT JOIN categories cat ON cat.id=c.category_id WHERE c.id=? AND c.status='published'");
$st->execute([$courseId]);
$course=$st->fetch();
if(!$course) {
    http_response_code(404);
    exit('Course not found.');
}
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='enroll'){
    require_role('student');
    verify_csrf();
    $stmt=$pdo->prepare('INSERT IGNORE INTO enrollments (student_id,course_id) VALUES (?,?)');
    $stmt->execute([user()['id'],$courseId]);
    flash('success','You are now enrolled in this course.');
    redirect('course-details.php?id='.$courseId);
}
$mods=$pdo->prepare('SELECT * FROM course_modules WHERE course_id=? ORDER BY sort_order,id');
$mods->execute([$courseId]);
$modules=$mods->fetchAll();
$assign=$pdo->prepare("SELECT * FROM assignments WHERE course_id=? AND status<>'draft' ORDER BY deadline");
$assign->execute([$courseId]);
$assignments=$assign->fetchAll();
$quiz=$pdo->prepare("SELECT * FROM quizzes WHERE course_id=? AND status='published' ORDER BY id");
$quiz->execute([$courseId]);
$quizzes=$quiz->fetchAll();
$enrolled=is_logged_in() && user()['role']==='student' ? is_enrolled(user()['id'],$courseId):false;
$ratingError='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='rate'){
    require_role('student');
    verify_csrf();
    $rating=max(1,min(5,(int)$_POST['rating']));
    $review=trim($_POST['review']??'');
    if(!is_enrolled(user()['id'],$courseId)){
        flash('danger','Enroll in the course before rating it.');
        redirect('course-details.php?id='.$courseId);
    }
    $pdo->prepare('INSERT INTO course_ratings(course_id,student_id,rating,review) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating),review=VALUES(review)')->execute([$courseId,user()['id'],$rating,$review]);
    flash('success','Your course rating was saved.');
    redirect('course-details.php?id='.$courseId);
}
$ratingStmt=$pdo->prepare('SELECT ROUND(AVG(rating),1) avg_rating,COUNT(*) rating_count FROM course_ratings WHERE course_id=?');
$ratingStmt->execute([$courseId]);
$ratingInfo=$ratingStmt->fetch();
$myRating=null;
if($enrolled&&is_logged_in()){
    $mr=$pdo->prepare('SELECT * FROM course_ratings WHERE course_id=? AND student_id=?');
    $mr->execute([$courseId,user()['id']]);
    $myRating=$mr->fetch();
}
include __DIR__.'/includes/header.php';
include __DIR__.'/includes/navbar.php';
?>
<section class="section-sm">
<div class="container">
<a href="courses.php" class="small">
<i class="bi bi-arrow-left me-1">
</i>Back to courses</a>
<div class="row g-4 mt-1">
<div class="col-lg-8">
<div class="page-header">
<div>
<div class="section-kicker mb-2"><?=e($course['category']??'Course')?> · <?=e($course['level'])?></div>
<h1><?=e($course['title'])?></h1>
<p><?=e($course['short_description'])?></p>
</div>
</div>
<div class="card p-4 mb-4">
<h4>About this course</h4>
<p class="text-muted mb-0"><?=nl2br(e($course['description']))?></p>
</div>
<div class="card p-4 mb-4">
<h4 class="mb-3">Course content</h4>
<?php
foreach($modules as $module):?><div class="mb-4">
<div class="fw-bold mb-2"><?=e($module['title'])?></div>
<div class="list-group">
<?php
$ls=$pdo->prepare('SELECT * FROM lectures WHERE module_id=? ORDER BY sort_order,id');
$ls->execute([$module['id']]);
foreach($ls->fetchAll() as $l):?><div class="list-group-item d-flex justify-content-between align-items-center">
<span>
<i class="bi bi-play-circle me-2">
</i><?=e($l['title'])?></span>
<span class="small text-muted"><?=e($l['duration_minutes'])?> min</span>
</div>
<?php
endforeach;
?></div>
</div>
<?php
endforeach;
?>
<?php
if(!$modules):?><p class="text-muted mb-0">Course content will be added by the instructor.</p>
<?php
endif;
?></div>
<?php
if($assignments):?><div class="card p-4 mb-4">
<h4 class="mb-3">Assignments</h4>
<?php
foreach($assignments as $a):?><div class="d-flex justify-content-between gap-3 border-bottom py-3">
<div>
<div class="fw-semibold"><?=e($a['title'])?></div>
<div class="small text-muted">Max marks: <?=e($a['max_marks'])?></div>
</div>
<div class="text-end small text-muted">Due <?=e(date('d M Y',strtotime($a['deadline'])))?></div>
</div>
<?php
endforeach;
?></div>
<?php
endif;
?>
<?php
if($quizzes):?><div class="card p-4">
<h4 class="mb-3">Quizzes</h4>
<?php
foreach($quizzes as $q):?><div class="d-flex justify-content-between border-bottom py-3">
<span class="fw-semibold"><?=e($q['title'])?></span>
<span class="small text-muted"><?=e($q['time_limit_minutes'])?> min</span>
</div>
<?php
endforeach;
?></div>
<?php
endif;
?><div class="card p-4 mt-4">
<div class="d-flex justify-content-between align-items-center">
<div>
<h4 class="mb-1">Course rating</h4>
<div class="small text-muted"><?=e($ratingInfo['avg_rating']??'0')?> / 5 · <?=e($ratingInfo['rating_count']??'0')?> rating(s)</div>
</div>
<div class="h4 mb-0">⭐ <?=e($ratingInfo['avg_rating']??'0')?></div>
</div>
<?php
if($enrolled):?><form method="post" class="mt-3"><?=csrf_input()?><input type="hidden" name="action" value="rate">
<div class="row g-2">
<div class="col-sm-4">
<select name="rating" class="form-select">
<option value="5" <?=((int)($myRating['rating']??0)===5?'selected':'')?>>5 — Excellent</option>
<option value="4" <?=((int)($myRating['rating']??0)===4?'selected':'')?>>4 — Good</option>
<option value="3" <?=((int)($myRating['rating']??0)===3?'selected':'')?>>3 — Average</option>
<option value="2" <?=((int)($myRating['rating']??0)===2?'selected':'')?>>2 — Needs work</option>
<option value="1" <?=((int)($myRating['rating']??0)===1?'selected':'')?>>1 — Poor</option>
</select>
</div>
<div class="col-sm-8">
<input name="review" class="form-control" value="<?=e($myRating['review']??'')?>" placeholder="Optional review">
</div>
</div>
<button class="btn btn-outline-primary mt-2">Save Rating</button>
</form>
<?php
endif;
?></div>
</div>
<div class="col-lg-4">
<div class="card p-4 position-sticky" style="top:95px">
<div class="course-thumb mb-3" style="border-radius:12px">
<i class="bi bi-mortarboard thumb-icon">
</i>
<span class="thumb-label"><?=e($course['category']??'Course')?></span>
</div>
<div class="h4 fw-bold"><?=e($course['title'])?></div>
<div class="small text-muted mb-3">
<i class="bi bi-person me-1">
</i><?=e($course['teacher'])?> · <i class="bi bi-clock ms-2 me-1">
</i><?=e((string)$course['duration_hours'])?> hrs</div>
<?php
if($enrolled):?><div class="alert alert-success">You are enrolled in this course.</div>
<a class="btn btn-primary w-100" href="student/learning.php?course_id=<?=$courseId?>">Continue Learning</a>
<?php
elseif(is_logged_in() && user()['role']==='student'):?><form method="post"><?=csrf_input()?><input type="hidden" name="action" value="enroll">
<button class="btn btn-primary w-100">Enroll in Course</button>
</form>
<?php
elseif(!is_logged_in()):?><a class="btn btn-primary w-100" href="auth/login.php?redirect=<?=urlencode($_SERVER['REQUEST_URI'])?>">Login to Enroll</a>
<?php
else:?><div class="alert alert-info">Only student accounts enroll in courses.</div>
<?php
endif;
?></div>
</div>
</div>
</div>
</section>
<?php
include __DIR__.'/includes/footer.php';
?>
