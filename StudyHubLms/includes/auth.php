<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_input(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(419);
        exit('Invalid or expired form token. Please go back and try again.');
    }
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flashes(): array {
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function user(): ?array {
    global $pdo;
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cached = null;
    if ($cached !== null) return $cached;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $cached = $stmt->fetch() ?: null;
    return $cached;
}

function is_logged_in(): bool {
    return user() !== null;
}

function dashboard_path(string $role): string {
    return match ($role) {
        'admin' => '../admin/dashboard.php',
        'teacher' => '../teacher/dashboard.php',
        default => '../student/dashboard.php',
    };
}

function require_login(): void {
    if (!is_logged_in()) {
        $from = $_SERVER['REQUEST_URI'] ?? '';
        $query = $from ? '?redirect=' . rawurlencode($from) : '';
        redirect('../auth/login.php' . $query);
    }
}

function require_role(string|array $roles): void {
    require_login();
    $roles = (array)$roles;
    if (!in_array(user()['role'] ?? '', $roles, true)) {
        flash('danger', 'You do not have permission to access that page.');
        redirect(dashboard_path(user()['role'] ?? 'student'));
    }
}

function login_user(array $account, bool $remember = false): void {
    global $pdo;
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$account['id'];
    $_SESSION['user_role'] = $account['role'];
    $_SESSION['remember'] = $remember;
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $pdo->prepare('DELETE FROM remember_tokens WHERE user_id=? OR expires_at<NOW()')->execute([(int)$account['id']]);
        $pdo->prepare('INSERT INTO remember_tokens(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 30 DAY))')->execute([(int)$account['id'],hash('sha256',$token)]);
        setcookie('lms_remember', $account['id'].':'.$token, [
            'expires'=>time()+60*60*24*30,'path'=>'/','secure'=>isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off','httponly'=>true,'samesite'=>'Lax'
        ]);
    }
}

function try_remember_login(): void {
    global $pdo;
    if (!empty($_SESSION['user_id']) || empty($_COOKIE['lms_remember'])) return;
    [$userId,$token] = array_pad(explode(':', $_COOKIE['lms_remember'], 2), 2, '');
    if (!$userId || !$token) return;
    $stmt=$pdo->prepare('SELECT u.* FROM remember_tokens r JOIN users u ON u.id=r.user_id WHERE r.user_id=? AND r.token_hash=? AND r.expires_at>NOW() AND u.status="active" LIMIT 1');
    $stmt->execute([(int)$userId,hash('sha256',$token)]);
    $account=$stmt->fetch();
    if($account){session_regenerate_id(true);$_SESSION['user_id']=(int)$account['id'];$_SESSION['user_role']=$account['role'];}
}

try_remember_login();

function logout_user(): void {
    global $pdo;
    if (!empty($_SESSION['user_id'])) { $pdo->prepare('DELETE FROM remember_tokens WHERE user_id=?')->execute([(int)$_SESSION['user_id']]); }
    setcookie('lms_remember','',time()-3600,'/');
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?? '';
    return trim($text, '-') ?: 'item';
}

function upload_file(string $field, string $directory, array $allowed, int $maxBytes = 10485760): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('File upload failed.');
    if ($_FILES[$field]['size'] > $maxBytes) throw new RuntimeException('Uploaded file is too large.');

    $original = $_FILES[$field]['name'];
    $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed, true)) {
        throw new RuntimeException('File type not allowed. Allowed: ' . implode(', ', $allowed));
    }

    $targetDir = __DIR__ . '/../uploads/' . trim($directory, '/') . '/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
    $filename = date('YmdHis') . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
    $target = $targetDir . $filename;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        throw new RuntimeException('Could not save uploaded file.');
    }
    return 'uploads/' . trim($directory, '/') . '/' . $filename;
}

function course_teacher_ids_for(int $teacherId): array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id FROM courses WHERE teacher_id = ?');
    $stmt->execute([$teacherId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function ensure_course_owner(int $courseId, int $teacherId): array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ? AND teacher_id = ? LIMIT 1');
    $stmt->execute([$courseId, $teacherId]);
    $course = $stmt->fetch();
    if (!$course) {
        flash('danger', 'Course not found or you do not own it.');
        redirect('courses.php');
    }
    return $course;
}

function is_enrolled(int $studentId, int $courseId): bool {
    global $pdo;
    $stmt = $pdo->prepare('SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?');
    $stmt->execute([$studentId, $courseId]);
    return (bool)$stmt->fetchColumn();
}

function recalc_course_progress(int $studentId, int $courseId): float {
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM lectures l JOIN course_modules m ON m.id=l.module_id WHERE m.course_id=?');
    $stmt->execute([$courseId]);
    $total = (int)$stmt->fetchColumn();
    if ($total === 0) return 0;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM lesson_progress lp JOIN lectures l ON l.id=lp.lecture_id JOIN course_modules m ON m.id=l.module_id WHERE lp.student_id=? AND m.course_id=? AND lp.completed=1');
    $stmt->execute([$studentId, $courseId]);
    $done = (int)$stmt->fetchColumn();
    $percent = round(($done / $total) * 100, 2);
    $stmt = $pdo->prepare('UPDATE enrollments SET progress_percent=? WHERE student_id=? AND course_id=?');
    $stmt->execute([$percent, $studentId, $courseId]);
    return $percent;
}

function notify_course_teacher(int $courseId, string $title, string $message): void {
    global $pdo;
    $stmt=$pdo->prepare('SELECT teacher_id FROM courses WHERE id=?');$stmt->execute([$courseId]);$teacherId=$stmt->fetchColumn();
    if($teacherId) add_notification((int)$teacherId,$title,$message);
}

function add_notification(int $userId, string $title, string $message): void {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id,title,message) VALUES (?,?,?)');
    $stmt->execute([$userId, $title, $message]);
}

function notify_course_students(int $courseId, string $title, string $message): void {
    global $pdo;
    $stmt = $pdo->prepare('SELECT student_id FROM enrollments WHERE course_id=?');
    $stmt->execute([$courseId]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $studentId) {
        add_notification((int)$studentId, $title, $message);
    }
}

function page_flash_html(): string {
    $html = '';
    $flashes = pull_flashes();
    if (!$flashes) return '';
    $html .= '<div class="page-flash" aria-live="polite" aria-atomic="true">';
    foreach ($flashes as $f) {
        $class = match ($f['type']) {
            'success' => 'alert-success', 'danger' => 'alert-danger', 'warning' => 'alert-warning', default => 'alert-info'
        };
        $html .= '<div class="alert ' . $class . ' alert-dismissible fade show shadow-sm page-flash-item" role="alert">' . e($f['message']) . '<button class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    $html .= '</div>';
    return $html;
}

function public_redirect_after_login(): void {
    if (is_logged_in()) redirect(dashboard_path(user()['role']));
}
