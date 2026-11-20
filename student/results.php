<?php
$pageTitle='Results';
$active='results';
require_once __DIR__.'/../includes/auth.php';
require_role('student');
$id=(int)user()['id'];
$q=$pdo->prepare("SELECT qa.*,q.title quiz_title,c.title course_title FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id JOIN courses c ON c.id=q.course_id WHERE qa.student_id=? ORDER BY qa.submitted_at DESC");
$q->execute([$id]);
$quizResults=$q->fetchAll();
$a=$pdo->prepare("SELECT s.*,ass.title assignment_title,c.title course_title FROM submissions s JOIN assignments ass ON ass.id=s.assignment_id JOIN courses c ON c.id=ass.course_id WHERE s.student_id=? ORDER BY s.submitted_at DESC");
$a->execute([$id]);
$assignmentResults=$a->fetchAll();
require_once __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Performance</div>
<h1>Results</h1>
<p>Your quiz scores and assignment grades.</p>
</div>
</div>
<div class="row g-4">
<div class="col-lg-6">
<div class="card p-4">
<h4>Quiz results</h4>
<div class="table-responsive mt-3">
<table class="table">
<thead>
<tr>
<th>Quiz</th>
<th>Course</th>
<th>Score</th>
</tr>
</thead>
<tbody>
<?php
foreach($quizResults as $r):?><tr>
<td><?=e($r['quiz_title'])?></td>
<td><?=e($r['course_title'])?></td>
<td>
<strong><?=e($r['percentage'])?>%</strong>
</td>
</tr>
<?php
endforeach;
?></tbody>
</table>
</div>
<?php
if(!$quizResults):?><p class="text-muted mb-0">No quiz attempts yet.</p>
<?php
endif;
?></div>
</div>
<div class="col-lg-6">
<div class="card p-4">
<h4>Assignment results</h4>
<div class="table-responsive mt-3">
<table class="table">
<thead>
<tr>
<th>Assignment</th>
<th>Course</th>
<th>Marks</th>
</tr>
</thead>
<tbody>
<?php
foreach($assignmentResults as $r):?><tr>
<td><?=e($r['assignment_title'])?></td>
<td><?=e($r['course_title'])?></td>
<td><?=e($r['marks']!==null?(string)$r['marks']:'Pending')?></td>
</tr>
<?php
endforeach;
?></tbody>
</table>
</div>
<?php
if(!$assignmentResults):?><p class="text-muted mb-0">No submissions yet.</p>
<?php
endif;
?></div>
</div>
</div>
<?php
include __DIR__.'/../includes/app_end.php';
?>
