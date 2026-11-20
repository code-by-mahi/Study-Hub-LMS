<?php
$pageTitle='Course Form';
$active='courses';
require_once __DIR__.'/../includes/auth.php';
require_role('teacher');
require_once __DIR__.'/../includes/ui.php';
$id=(int)user()['id'];
$cid=(int)($_GET['id']??$_POST['course_id']??0);
$course=$cid?ensure_course_owner($cid,$id):null;
$categories=$pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $title=trim($_POST['title']??'');
        $short=trim($_POST['short_description']??'');
        $desc=trim($_POST['description']??'');
        $level=$_POST['level']??'Beginner';
        $duration=(float)($_POST['duration_hours']??0);
        $cat=(int)($_POST['category_id']??0);
        $status=$_POST['status']??'draft';
        if($title==='')throw new RuntimeException('Course title is required.');
        if(!in_array($level,['Beginner','Intermediate','Advanced'],true))$level='Beginner';
        if(!in_array($status,['draft','published'],true))$status='draft';
        $thumb=upload_file('thumbnail','courses',['jpg','jpeg','png','webp'],5242880);
        if($course){
            $sql='UPDATE courses SET category_id=?,title=?,short_description=?,description=?,level=?,duration_hours=?,status=?';
            $params=[$cat?:null,$title,$short,$desc,$level,$duration,$status];
            if($thumb){
                $sql.=',thumbnail=?';
                $params[]=$thumb;
            }
            $sql.=' WHERE id=? AND teacher_id=?';
            $params[]=$cid;
            $params[]=$id;
            $pdo->prepare($sql)->execute($params);
            flash('success','Course updated.');
            redirect('courses.php');
        }
        else{
            $pdo->prepare('INSERT INTO courses(teacher_id,category_id,title,short_description,description,level,duration_hours,status,thumbnail) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$id,$cat?:null,$title,$short,$desc,$level,$duration,$status,$thumb]);
            flash('success','Course created.');
            redirect('courses.php');
        }
    }
    catch(Throwable $e){
        $error=$e->getMessage();
    }
}
include __DIR__.'/../includes/app_start.php';
?>
<div class="page-header">
<div>
<a href="courses.php" class="small">
<i class="bi bi-arrow-left me-1">
</i>Back</a>
<h1 class="mt-2"><?= $course?'Edit':'Create'?> Course</h1>
<p>Define the course information shown to students.</p>
</div>
</div>
<?php
if($error):?><div class="alert alert-danger"><?=e($error)?></div>
<?php
endif;
?><div class="card p-4">
<form method="post" enctype="multipart/form-data"><?=csrf_input()?><input type="hidden" name="course_id" value="<?=$cid?>">
<div class="row g-3">
<div class="col-md-8">
<label class="form-label">Course title</label>
<input name="title" class="form-control" value="<?=e($course['title']??'')?>" required>
</div>
<div class="col-md-4">
<label class="form-label">Category</label>
<select name="category_id" class="form-select">
<option value="0">Uncategorized</option>
<?php
foreach($categories as $cat):?><option value="<?=$cat['id']?>" <?=((int)($course['category_id']??0)===(int)$cat['id']?'selected':'')?>><?=e($cat['name'])?></option>
<?php
endforeach;
?></select>
</div>
<div class="col-12">
<label class="form-label">Short description</label>
<input name="short_description" class="form-control" maxlength="255" value="<?=e($course['short_description']??'')?>">
</div>
<div class="col-12">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" rows="6"><?=e($course['description']??'')?></textarea>
</div>
<div class="col-md-4">
<label class="form-label">Level</label>
<select name="level" class="form-select">
<?php
foreach(['Beginner','Intermediate','Advanced'] as $v):?><option <?=$v===($course['level']??'Beginner')?'selected':''?>><?=$v?></option>
<?php
endforeach;
?></select>
</div>
<div class="col-md-4">
<label class="form-label">Duration (hours)</label>
<input name="duration_hours" type="number" min="0" step="0.5" class="form-control" value="<?=e($course['duration_hours']??'0')?>">
</div>
<div class="col-md-4">
<label class="form-label">Status</label>
<select name="status" class="form-select">
<?php
foreach(['draft','published'] as $v):?><option value="<?=$v?>" <?=$v===($course['status']??'draft')?'selected':''?>><?=ucfirst($v)?></option>
<?php
endforeach;
?></select>
</div>
<div class="col-12">
<label class="form-label">Thumbnail</label>
<input name="thumbnail" type="file" class="form-control">
<div class="form-text">JPG, PNG or WEBP up to 5 MB.</div>
</div>
</div>
<button class="btn btn-primary mt-4"><?= $course?'Save Changes':'Create Course'?></button>
</form>
</div>
<?php
include __DIR__.'/../includes/app_end.php';
?>
