<?php
$unreadStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
$unreadStmt->execute([(int)$me['id']]);
$unread = (int)$unreadStmt->fetchColumn();
?>
<header class="app-topbar">
  <div class="d-flex align-items-center gap-3"><button class="btn btn-light d-lg-none" data-sidebar-toggle aria-label="Open navigation"><i class="bi bi-list fs-5"></i></button><div><div class="fw-semibold d-none d-md-block"><?= e($pageTitle) ?></div><div class="small text-muted d-none d-md-block">College LMS</div></div></div>
  <div class="topbar-user"><a class="btn btn-light position-relative" title="Announcements" href="announcements.php"><i class="bi bi-bell"></i><?php if($unread>0): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.62rem"><?= $unread ?></span><?php endif; ?></a><div class="avatar"><?= e(strtoupper(substr($userName,0,1))) ?></div><div class="d-none d-sm-block"><div class="fw-semibold small"><?= e($userName) ?></div><div class="text-muted" style="font-size:.74rem;text-transform:capitalize"><?= e($role) ?></div></div></div>
</header>
