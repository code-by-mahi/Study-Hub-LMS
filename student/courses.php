<?php
$pageTitle='My Courses';
$active='courses';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
require_once __DIR__.'/../includes/ui.php';
$id=(int)user()['id'];
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='enroll'){
    verify_csrf();
    $cid=(int)$_POST['course_id'];
    $st=$pdo->prepare("SELECT id FROM courses WHERE id=? AND status='published'");
    $st->execute([$cid]);
    if($st->fetchColumn()){
        $pdo->prepare('INSERT IGNORE INTO enrollments(student_id,course_id) VALUES(?,?)')->execute([$id,$cid]);
        flash('success','Course enrolled successfully.');
    }
    redirect('courses.php');
}
$q=trim($_GET['q']??'');
$st=$pdo->prepare("SELECT c.*,u.full_name teacher,cat.name category,(SELECT COUNT(*) FROM enrollments e2 WHERE e2.course_id=c.id) learners,(SELECT progress_percent FROM enrollments e3 WHERE e3.course_id=c.id AND e3.student_id=?) progress FROM courses c JOIN users u ON u.id=c.teacher_id LEFT JOIN categories cat ON cat.id=c.category_id WHERE c.status='published' AND (c.title LIKE ? OR c.short_description LIKE ?) ORDER BY c.created_at DESC");
$like="%$q%";
$st->execute([$id,$like,$like]);
$courses=$st->fetchAll();
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Learning catalog</div>
<h1>Courses</h1>
<p>Browse published courses and continue your enrolled subjects.</p>
</div>
</div>
<form class="card p-3 mb-4" method="get">
<div class="input-group">
<span class="input-group-text bg-white">
<i class="bi bi-search">
</i>
</span>
<input name="q" value="<?=e($q)?>" class="form-control" placeholder="Search courses...">
<button class="btn btn-primary">Search</button>
</div>
</form>
<div class="row g-4">
<?php
foreach($courses as $i=>$c):$enrolled=$c['progress']!==null;
?><div class="col-md-6 col-xl-4">
<div class="card course-card card-hover h-100 overflow-hidden"><?=courseThumb($c['category']??'Course',$i)?><div class="card-body d-flex flex-column">
<div class="course-meta mb-2"><?=e($c['category']??'General')?> · <?=e($c['level'])?></div>
<h5><?=e($c['title'])?></h5>
<p class="text-muted small"><?=e($c['short_description'])?></p>
<div class="small text-muted mb-3"><?=e($c['teacher'])?> · <?=e($c['learners'])?> learners</div>
<?php
if($enrolled):?><div class="small mb-1 d-flex justify-content-between">
<span>Progress</span>
<strong><?=e((string)$c['progress'])?>%</strong>
</div>
<div class="progress mb-3">
<div class="progress-bar" style="width:<?=min(100,(float)$c['progress'])?>%">
</div>
</div>
<div class="mt-auto d-flex gap-2">
<a class="btn btn-primary btn-sm" href="learning.php?course_id=<?=$c['id']?>">Continue</a>
<a class="btn btn-light btn-sm" href="../course-details.php?id=<?=$c['id']?>">Details</a>
</div>
<?php
else:?><div class="mt-auto d-flex gap-2">
<form method="post"><?=csrf_input()?><input type="hidden" name="action" value="enroll">
<input type="hidden" name="course_id" value="<?=$c['id']?>">
<button class="btn btn-primary btn-sm">Enroll</button>
</form>
<a class="btn btn-light btn-sm" href="../course-details.php?id=<?=$c['id']?>">Details</a>
</div>
<?php
endif;
?>
</div>
</div>
</div>
<?php
endforeach;
?></div>
<?php
if(!$courses):?><div class="card empty-state">
<div class="icon">
<i class="bi bi-search">
</i>
</div>
<h5>No courses found</h5>
<p class="text-muted">Try a different search.</p>
</div>
<?php
endif;
?>
<?php
include __DIR__.'/../includes/app_end.php';
?>
