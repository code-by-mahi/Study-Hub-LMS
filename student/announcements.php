<?php
$pageTitle='Announcements';
$active='announcements';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
$id=(int)user()['id'];
$st=$pdo->prepare("SELECT DISTINCT a.*,u.full_name author FROM announcements a JOIN users u ON u.id=a.author_id LEFT JOIN enrollments e ON e.student_id=? AND e.course_id=a.course_id WHERE a.audience='all' OR a.audience='students' OR (a.audience='course' AND e.id IS NOT NULL) ORDER BY a.created_at DESC");
$st->execute([$id]);
$anns=$st->fetchAll();
$pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([$id]);
require_once __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Updates</div>
<h1>Announcements</h1>
<p>Important LMS and course updates.</p>
</div>
</div>
<?php
foreach($anns as $a):?><div class="card p-4 mb-3">
<div class="d-flex justify-content-between gap-3">
<div>
<h4 class="h5 mb-1"><?=e($a['title'])?></h4>
<div class="small text-muted">By <?=e($a['author'])?> · <?=e(date('d M Y, h:i A',strtotime($a['created_at'])))?></div>
</div><?=badgeSoft(ucfirst($a['audience']),'primary')?></div>
<div class="text-muted mt-3"><?=nl2br(e($a['content']))?></div>
</div>
<?php
endforeach;
?>
<?php
if(!$anns):?><div class="card empty-state">
<div class="icon">
<i class="bi bi-megaphone">
</i>
</div>
<h5>No announcements</h5>
</div>
<?php
endif;
?>
<?php
include __DIR__.'/../includes/app_end.php';
?>
