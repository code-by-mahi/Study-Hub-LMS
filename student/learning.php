<?php
$pageTitle = 'Learning';
$active = 'learning';
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
require_once __DIR__ . '/../includes/ui.php';
$id = (int) user()['id'];
$courseId = (int) ($_GET['course_id'] ?? 0);
if (!$courseId) {
    $list = $pdo->prepare('SELECT c.id, c.title, e.progress_percent FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.student_id=? ORDER BY c.title');
    $list->execute([$id]);
    $available = $list->fetchAll();
    require_once __DIR__ . '/../includes/app_start.php';
    echo '<div class="page-header"><div><div class="section-kicker mb-2">Learning</div><h1>Choose a course</h1><p>Select an enrolled course to continue learning.</p></div></div><div class="row g-3">';
    foreach ($available as $c) {
        echo '<div class="col-md-6 col-xl-4"><div class="card p-4 h-100"><h4 class="h5">' . e($c['title']) . '</h4><div class="small text-muted mb-2">Progress ' . e($c['progress_percent']) . '%</div><div class="progress mb-3"><div class="progress-bar" style="width:' . min(100, (float) $c['progress_percent']) . '%"></div></div><a class="btn btn-primary" href="learning.php?course_id=' . $c['id'] . '">Continue reading</a></div></div>';
    }
    if (!$available) {
        echo '<div class="col-12"><div class="card empty-state"><div class="icon"><i class="bi bi-journal-text"></i></div><h5>No enrolled courses</h5><a href="courses.php" class="btn btn-primary">Browse Courses</a></div></div>';
    }
    echo '</div>';
    include __DIR__ . '/../includes/app_end.php';
    exit;
}
$st = $pdo->prepare('SELECT c.*, u.full_name teacher, e.progress_percent FROM enrollments e JOIN courses c ON c.id=e.course_id JOIN users u ON u.id=c.teacher_id WHERE e.student_id=? AND c.id=?');
$st->execute([$id, $courseId]);
$course = $st->fetch();
if (!$course) {
    flash('warning', 'Enroll in a course to open its learning area.');
    redirect('courses.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'complete') {
    verify_csrf();
    $lectureId = (int) ($_POST['lecture_id'] ?? 0);
    $v = $pdo->prepare('SELECT l.id FROM lectures l JOIN course_modules m ON m.id=l.module_id WHERE l.id=? AND m.course_id=?');
    $v->execute([$lectureId, $courseId]);
    if ($v->fetchColumn()) {
        $pdo->prepare('INSERT INTO lesson_progress(student_id,lecture_id,completed,completed_at) VALUES(?,?,1,NOW()) ON DUPLICATE KEY UPDATE completed=1,completed_at=NOW()')->execute([$id, $lectureId]);
        recalc_course_progress($id, $courseId);
        flash('success', 'Reading notes marked as complete.');
    }
    redirect('learning.php?course_id=' . $courseId . '&lesson=' . $lectureId);
}
$mods = $pdo->prepare('SELECT * FROM course_modules WHERE course_id=? ORDER BY sort_order,id');
$mods->execute([$courseId]);
$modules = $mods->fetchAll();
$allLectures = [];
foreach ($modules as &$m) {
    $ls = $pdo->prepare('SELECT l.*, COALESCE(lp.completed,0) completed FROM lectures l LEFT JOIN lesson_progress lp ON lp.lecture_id=l.id AND lp.student_id=? WHERE l.module_id=? ORDER BY l.sort_order,l.id');
    $ls->execute([$id, $m['id']]);
    $m['lectures'] = $ls->fetchAll();
    foreach ($m['lectures'] as $l) $allLectures[] = $l;
}
unset($m);
$lessonId = (int) ($_GET['lesson'] ?? ($allLectures[0]['id'] ?? 0));
$lesson = null;
foreach ($allLectures as $l) {
    if ((int) $l['id'] === $lessonId) {
        $lesson = $l;
        break;
    }
}
include __DIR__ . '/../includes/app_start.php';
?>
<div class="page-header">
  <div>
<div class="section-kicker mb-2">Now learning</div>
<h1><?= e($course['title'])?></h1>
<p>Instructor: <?= e($course['teacher'])?></p>
</div>
  <a class="btn btn-light" href="../course-details.php?id=<?= $courseId?>">Course details</a>
</div>
<div class="learning-layout">
  <aside class="module-nav">
    <div class="card p-3">
      <div class="small text-muted mb-2">Course progress</div>
      <div class="d-flex justify-content-between mb-2">
<strong><?= e((string) $course['progress_percent'])?>%</strong>
<span class="small text-muted">complete</span>
</div>
      <div class="progress mb-3">
<div class="progress-bar" style="width:<?= min(100, (float) $course['progress_percent'])?>%">
</div>
</div>
<?php
foreach ($modules as $m):?>
        <div class="small text-uppercase text-muted fw-bold mt-3 mb-2">Module <?= e($m['sort_order'])?></div>
<?php
foreach ($m['lectures'] as $l):?>
          <a class="module-item <?= ((int) $l['id'] === $lessonId ? 'active' : '')?>" href="learning.php?course_id=<?= $courseId?>&lesson=<?= $l['id']?>">
            <i class="bi bi-<?= $l['completed'] ? 'check-circle-fill text-success' : 'journal-text'?>">
</i>
<span class="small"><?= e($l['title'])?></span>
          </a>
<?php
endforeach;
?>
      <?php
endforeach;
?>
    </div>
  </aside>
  <section>
    <div class="card p-4 p-lg-5 reading-lesson-card">
      <div class="reading-banner mb-4">
        <div class="d-flex align-items-center gap-3">
          <div class="reading-icon">
<i class="bi bi-book-half">
</i>
</div>
          <div>
<div class="fw-semibold">Reading notes</div>
<div class="small text-muted">Study the notes below at your own pace, then mark the lesson complete.</div>
</div>
        </div>
      </div>
<?php
if ((float) $course['progress_percent'] >= 100):?>
        <div class="alert alert-success d-flex justify-content-between align-items-center">
<span>
<i class="bi bi-award me-2">
</i>Course completed!</span>
<a class="btn btn-sm btn-success" href="certificate.php?course_id=<?= $courseId?>">View Certificate</a>
</div>
<?php
endif;
?>
      <?php
if ($lesson):?>
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
          <div>
<div class="small text-muted text-uppercase fw-semibold">Lesson notes</div>
<h2 class="h3 mt-1 mb-2"><?= e($lesson['title'])?></h2>
<div class="small text-muted">
<i class="bi bi-clock me-1">
</i><?= (int) ($lesson['duration_minutes'] ?? 0)?> min study time <span class="mx-2">•</span>
<i class="bi bi-file-text me-1">
</i>Text lesson</div>
</div>
<?php
if ($lesson['completed']):?><?= badgeSoft('Completed', 'success')?>
<?php
endif;
?>
        </div>
        <hr class="my-4">
        <article class="reading-content">
<?php
$noteText = trim((string) ($lesson['content'] ?? ''));
if ($noteText === '') $noteText = 'Reading notes for this lesson will be added by your instructor.';
$paragraphs = preg_split('/\R\s*\R/', $noteText);
foreach ($paragraphs as $paragraph):
if (trim($paragraph) !== ''):?><p><?= nl2br(e(trim($paragraph)))?></p>
<?php
endif;
endforeach;
?>
          <div class="reading-tip mt-4">
<i class="bi bi-lightbulb me-2">
</i>
<strong>Study tip:</strong> Write down the key terms and try a small example before moving to the next lesson.</div>
        </article>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top pt-4 mt-4">
          <span class="small text-muted">
<i class="bi bi-book me-1">
</i>Take your time and read carefully.</span>
<?php
if (!$lesson['completed']):?>
            <form method="post"><?= csrf_input()?><input type="hidden" name="action" value="complete">
<input type="hidden" name="lecture_id" value="<?= $lesson['id']?>">
<button class="btn btn-primary">
<i class="bi bi-check2-circle me-1">
</i>Mark as complete</button>
</form>
<?php
else:?>
            <span class="text-success fw-semibold">
<i class="bi bi-check-circle-fill me-1">
</i>Lesson completed</span>
<?php
endif;
?>
        </div>
<?php
else:?>
        <div class="empty-state py-5">
<div class="icon">
<i class="bi bi-journal-text">
</i>
</div>
<h4>No reading notes yet</h4>
<p class="text-muted">Your instructor has not added lesson notes yet.</p>
</div>
<?php
endif;
?>
    </div>
  </section>
</div>
<?php
include __DIR__ . '/../includes/app_end.php';
?>
