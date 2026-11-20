<?php
// College LMS database connection.
// XAMPP defaults: host=localhost, database=college_lms, user=root, password=''.
$host = getenv('LMS_DB_HOST') ?: 'localhost';
$db   = getenv('LMS_DB_NAME') ?: 'college_lms';
$user = getenv('LMS_DB_USER') ?: 'root';
$pass = getenv('LMS_DB_PASS') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die('Database connection failed. Import database.sql and verify the MySQL settings in includes/db.php.');
}
