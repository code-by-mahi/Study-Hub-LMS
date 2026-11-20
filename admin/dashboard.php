<?php
$pageTitle='Admin Dashboard';
$active='dashboard';
require_once __DIR__.'/../includes/auth.php';
require_role('admin');
require_once __DIR__.'/../includes/ui.php';
$counts=[];
foreach([['students',"SELECT COUNT(*) FROM users WHERE role='student'"],['teachers',"SELECT COUNT(*) FROM users WHERE role='teacher'"],['courses',"SELECT COUNT(*) FROM courses"],['enrollments','SELECT COUNT(*) FROM enrollments']] as [$k,$sql])$counts[$k]=(int)$pdo->query($sql)->fetchColumn();
$recent=$pdo->query("SELECT full_name,email,role,created_at FROM users ORDER BY created_at DESC LIMIT 6")->fetchAll();
$top=$pdo->query("SELECT c.title,COUNT(e.id) learners FROM courses c LEFT JOIN enrollments e ON e.course_id=c.id GROUP BY c.id ORDER BY learners DESC LIMIT 5")->fetchAll();
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Platform administration</div>
<h1>Admin Dashboard</h1>
<p>Monitor accounts, courses, enrollments, and LMS activity.</p>
</div>
<a class="btn btn-primary" href="reports.php">View Reports</a>
</div>
<div class="row g-3 mb-4">
<div class="col-6 col-xl-3"><?=statCard('people','Students',$counts['students'],'primary')?></div>
<div class="col-6 col-xl-3"><?=statCard('person-badge','Teachers',$counts['teachers'],'success')?></div>
<div class="col-6 col-xl-3"><?=statCard('collection','Courses',$counts['courses'],'warning')?></div>
<div class="col-6 col-xl-3"><?=statCard('person-check','Enrollments',$counts['enrollments'],'primary')?></div>
</div>
<div class="row g-4">
<div class="col-lg-7">
<div class="card p-4">
<div class="d-flex justify-content-between mb-3">
<h4 class="mb-0">Most enrolled courses</h4>
<a class="small" href="courses.php">Manage courses</a>
</div>
<?php
foreach($top as $r):?><div class="d-flex justify-content-between border-bottom py-3">
<span class="fw-semibold"><?=e($r['title'])?></span>
<span class="text-muted"><?=e($r['learners'])?> learners</span>
</div>
<?php
endforeach;
?></div>
</div>
<div class="col-lg-5">
<div class="card p-4">
<div class="d-flex justify-content-between mb-3">
<h4 class="mb-0">Recent registrations</h4>
<a href="students.php" class="small">View all</a>
</div>
<?php
foreach($recent as $r):?><div class="notice-item mb-3">
<div class="fw-semibold"><?=e($r['full_name'])?></div>
<div class="small text-muted"><?=e(ucfirst($r['role']))?> · <?=e($r['email'])?></div>
<div class="small text-muted"><?=e(date('d M Y, h:i A',strtotime($r['created_at'])))?></div>
</div>
<?php
endforeach;
?></div>
</div>
</div>
<?php
include __DIR__.'/../includes/app_end.php';
?>
