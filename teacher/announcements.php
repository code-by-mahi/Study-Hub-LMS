<?php
$pageTitle='Announcements';
$active='announcements';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
require_once __DIR__.'/../includes/ui.php';
$pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([(int)user()['id']]);
$teacherId=(int)user()['id'];
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $action=$_POST['action']??'';
        if($action==='add'){
            $title=trim($_POST['title']??'');
            $content=trim($_POST['content']??'');
            $audience=$_POST['audience']??'course';
            $courseId=(int)($_POST['course_id']??0);
            if($title===''||$content==='')throw new RuntimeException('Title and content are required.');
            if(!in_array($audience,['all','students','course'],true))$audience='course';
            if($audience==='course'){
                $v=$pdo->prepare('SELECT id FROM courses WHERE id=? AND teacher_id=?');
                $v->execute([$courseId,$teacherId]);
                if(!$v->fetchColumn())throw new RuntimeException('Select one of your courses.');
            }
            else $courseId=null;
            $pdo->prepare('INSERT INTO announcements(author_id,title,content,audience,course_id) VALUES(?,?,?,?,?)')->execute([$teacherId,$title,$content,$audience,$courseId]);
            if($audience==='course')notify_course_students($courseId,'New announcement',$title);
            elseif($audience==='students'){
                foreach($pdo->query("SELECT id FROM users WHERE role='student' AND status='active'")->fetchAll(PDO::FETCH_COLUMN) as $uid)add_notification((int)$uid,'New announcement',$title);
            }
            else{
                foreach($pdo->query("SELECT id FROM users WHERE status='active'")->fetchAll(PDO::FETCH_COLUMN) as $uid)add_notification((int)$uid,'New announcement',$title);
            }
            flash('success','Announcement published.');
        }
        elseif($action==='delete'){
            $pdo->prepare('DELETE FROM announcements WHERE id=? AND author_id=?')->execute([(int)$_POST['id'],$teacherId]);
            flash('success','Announcement deleted.');
        }
        redirect('announcements.php');
    }
    catch(Throwable $e){
        $error=$e->getMessage();
    }
}
$cq=$pdo->prepare('SELECT id,title FROM courses WHERE teacher_id=? ORDER BY title');
$cq->execute([$teacherId]);
$courses=$cq->fetchAll();
$st=$pdo->prepare('SELECT a.*,c.title course_title FROM announcements a LEFT JOIN courses c ON c.id=a.course_id WHERE a.author_id=? ORDER BY a.created_at DESC');
$st->execute([$teacherId]);
$rows=$st->fetchAll();
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<div class="section-kicker mb-2">Communication</div>
<h1>Announcements</h1>
<p>Send updates to students and LMS users.</p>
</div>
</div>
<?php
if($error):?><div class="alert alert-danger"><?=e($error)?></div>
<?php
endif;
?>
<div class="card p-4 mb-4">
<h4>New Announcement</h4>
<form method="post" class="row g-3 mt-1"><?=csrf_input()?><input type="hidden" name="action" value="add">
<div class="col-md-7">
<label class="form-label">Title</label>
<input name="title" class="form-control" required>
</div>
<div class="col-md-5">
<label class="form-label">Audience</label>
<select name="audience" class="form-select">
<option value="course">One of my courses</option>
<option value="students">All students</option>
<option value="all">Everyone</option>
</select>
</div>
<div class="col-md-7">
<label class="form-label">Course</label>
<select name="course_id" class="form-select">
<option value="0">Select course</option>
<?php
foreach($courses as $c):?><option value="<?=$c['id']?>"><?=e($c['title'])?></option>
<?php
endforeach;
?></select>
<div class="form-text">Required when audience is course.</div>
</div>
<div class="col-12">
<label class="form-label">Message</label>
<textarea name="content" class="form-control" rows="4" required>
</textarea>
</div>
<div class="col-12">
<button class="btn btn-primary">Publish Announcement</button>
</div>
</form>
</div>
<?php
foreach($rows as $r):?><div class="card p-4 mb-3">
<div class="d-flex justify-content-between gap-3">
<div>
<h5><?=e($r['title'])?></h5>
<div class="small text-muted"><?=e($r['audience'])?><?= $r['course_title']?' · '.e($r['course_title']):''?> · <?=e(date('d M Y, h:i A',strtotime($r['created_at'])))?></div>
</div>
<form method="post" class="d-inline" data-confirm="Delete this announcement?"><?=csrf_input()?><input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?=$r['id']?>">
<button class="btn btn-sm btn-outline-danger">Delete</button>
</form>
</div>
<p class="text-muted mt-3 mb-0"><?=nl2br(e($r['content']))?></p>
</div>
<?php
endforeach;
?>
<?php
include __DIR__.'/../includes/app_end.php';
?>
