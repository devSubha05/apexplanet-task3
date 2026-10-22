<?php
// Database configuration - change these to match your XAMPP/WAMP setup
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'user_management';

// Auto-detect BASE_URL (empty string for root / localhost:8000, or /user-management if hosted under subdirectory)
$isSubdir = isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/user-management');
define('BASE_URL', $isSubdir ? '/user-management' : '');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check config/db.php and import database.sql.');
}
