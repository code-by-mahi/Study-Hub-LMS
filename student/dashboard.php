<?php
$pageTitle='Student Dashboard';
$active='dashboard';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
require_once __DIR__.'/../includes/ui.php';
$id=(int)user()['id'];
$stmt=$pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id=?");
$stmt->execute([$id]);
$coursesCount=(int)$stmt->fetchColumn();
$stmt=$pdo->prepare("SELECT COALESCE(AVG(percentage),0) FROM quiz_attempts WHERE student_id=? AND submitted_at IS NOT NULL");
$stmt->execute([$id]);
$avg=(float)$stmt->fetchColumn();
$stmt=$pdo->prepare("SELECT COUNT(*) FROM submissions WHERE student_id=? AND status='submitted'");
$stmt->execute([$id]);
$pending=(int)$stmt->fetchColumn();
$stmt=$pdo->prepare("SELECT e.*,c.title,c.short_description,u.full_name teacher FROM enrollments e JOIN courses c ON c.id=e.course_id JOIN users u ON u.id=c.teacher_id WHERE e.student_id=? ORDER BY e.enrolled_at DESC LIMIT 4");
$stmt->execute([$id]);
$my=$stmt->fetchAll();
$up=$pdo->prepare("SELECT a.*,c.title course_title FROM assignments a JOIN courses c ON c.id=a.course_id JOIN enrollments e ON e.course_id=c.id AND e.student_id=? WHERE a.status='open' ORDER BY a.deadline LIMIT 5");
$up->execute([$id]);
$assignments=$up->fetchAll();
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Student space</div>
<h1>Welcome, <?=e(user()['full_name'])?> 👋</h1>
<p>Keep track of your learning, assignments, and results.</p>
</div>
<a class="btn btn-primary" href="courses.php">
<i class="bi bi-plus-circle me-1">
</i>Browse Courses</a>
</div>
<div class="row g-3 mb-4">
<div class="col-6 col-xl-3"><?=statCard('collection-play','My Courses',$coursesCount,'primary')?></div>
<div class="col-6 col-xl-3"><?=statCard('ui-checks-grid','Average Quiz',round($avg).'%','success')?></div>
<div class="col-6 col-xl-3"><?=statCard('file-earmark-text','Pending Submissions',$pending,'warning')?></div>
<div class="col-6 col-xl-3"><?=statCard('award','Learning Mode','Active','primary','Keep going')?></div>
</div>
<div class="row g-4">
<div class="col-xl-8">
<div class="card p-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<div>
<h4 class="mb-1">My Courses</h4>
<div class="small text-muted">Your latest enrolled courses</div>
</div>
<a href="courses.php" class="small">View all</a>
</div>
<?php
foreach($my as $course):?><div class="border rounded-3 p-3 mb-3">
<div class="d-flex justify-content-between gap-3">
<div>
<div class="fw-semibold"><?=e($course['title'])?></div>
<div class="small text-muted"><?=e($course['teacher'])?></div>
</div>
<strong><?=e((string)$course['progress_percent'])?>%</strong>
</div>
<div class="progress mt-3">
<div class="progress-bar" style="width:<?=min(100,(float)$course['progress_percent'])?>%">
</div>
</div>
<div class="mt-3">
<a class="btn btn-sm btn-outline-primary" href="learning.php?course_id=<?=$course['course_id']?>">Continue learning</a>
</div>
</div>
<?php
endforeach;
?>
<?php
if(!$my):?><div class="empty-state py-5">
<div class="icon">
<i class="bi bi-journal-plus">
</i>
</div>
<h5>No enrolled courses</h5>
<a href="courses.php" class="btn btn-primary">Explore courses</a>
</div>
<?php
endif;
?></div>
</div>
<div class="col-xl-4">
<div class="card p-4">
<div class="d-flex justify-content-between mb-3">
<h4 class="mb-0">Upcoming</h4>
<a href="assignments.php" class="small">All</a>
</div>
<?php
foreach($assignments as $a):?><div class="notice-item mb-3">
<div class="fw-semibold"><?=e($a['title'])?></div>
<div class="small text-muted"><?=e($a['course_title'])?></div>
<div class="small mt-1">Due <?=e(date('d M Y',strtotime($a['deadline'])))?></div>
</div>
<?php
endforeach;
?>
<?php
if(!$assignments):?><p class="text-muted mb-0">No upcoming assignments.</p>
<?php
endif;
?></div>
</div>
</div>
<?php
include __DIR__.'/../includes/app_end.php';
?>
