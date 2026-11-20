<?php
$role = $role ?? 'student';
$active = $active ?? 'dashboard';
$navs = [
'student' => [
'MAIN'=>[['dashboard','Dashboard','speedometer2'],['courses','My Courses','collection-play'],['learning','Learning','book'],['assignments','Assignments','file-earmark-text'],['quizzes','Quizzes','ui-checks-grid'],['results','Results','bar-chart'],['announcements','Announcements','megaphone']],
'ACCOUNT'=>[['profile','Profile','person-circle']]],
'teacher' => [
'MAIN'=>[['dashboard','Dashboard','speedometer2'],['courses','My Courses','collection'],['course-content','Course Content','layers'],['assignments','Assignments','file-earmark-text'],['submissions','Submissions','inbox'],['quizzes','Quizzes','ui-checks-grid'],['students','Students','people'],['announcements','Announcements','megaphone']],
'ACCOUNT'=>[['profile','Profile','person-circle']]],
'admin' => [
'MAIN'=>[['dashboard','Dashboard','speedometer2'],['students','Students','people'],['teachers','Teachers','person-badge'],['courses','Courses','collection'],['categories','Categories','tags'],['enrollments','Enrollments','person-check'],['announcements','Announcements','megaphone'],['reports','Reports','graph-up']],
'ACCOUNT'=>[['profile','Profile','person-circle']]],
];
foreach ($navs[$role] as $section => $items):?>
<div class="sidebar-section"><?= e($section)?></div>
<nav class="sidebar-nav nav flex-column gap-1">
<?php
foreach ($items as [$key,$label,$icon]):
$href = $key==='dashboard' ? 'dashboard.php' : $key.'.php';
if ($role==='teacher' && $key==='course-content') $href='course-content.php';
?>
<a class="nav-link <?= $active===$key?'active':''?>" href="<?= e($href)?>">
<i class="bi bi-<?= e($icon)?>">
</i>
<span><?= e($label)?></span>
</a>
<?php
endforeach;
?>
</nav>
<?php
endforeach;
?>
<div class="sidebar-bottom">
<a class="nav-link" href="../auth/logout.php">
<i class="bi bi-box-arrow-left">
</i>
<span>Logout</span>
</a>
</div>
