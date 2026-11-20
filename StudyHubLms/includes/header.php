<?php
$pageTitle = $pageTitle ?? 'College LMS';
$basePath = $basePath ?? '';
$dashboard = $dashboard ?? false;
require_once __DIR__ . '/auth.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="College Learning Management System">
  <title><?= e($pageTitle) ?> · College LMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= e($basePath) ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="<?= $dashboard ? 'dashboard-page' : 'public-page' ?>">
<?php echo page_flash_html(); ?>
