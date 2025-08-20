<?php
// config.php - Database configuration and helpers
// Update these values to match your MySQL setup
$DB_HOST = '127.0.0.1';
$DB_NAME = 'blog_db';
$DB_USER = 'root';
$DB_PASS = '';

// Admin credentials (MVP)
// For simplicity, we use a default username and password. Change them!
// Note: We still use password_hash/password_verify at login time.
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD_PLAIN = 'admin123'; // CHANGE THIS IN PRODUCTION

// Create PDO connection
try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

// Helper to escape output
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
