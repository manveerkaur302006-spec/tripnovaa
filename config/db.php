<?php
require_once __DIR__ . '/app.php';

$dbHost = 'localhost';
$dbName = 'tripnovaa_db';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please import database/tripnovaa.sql in phpMyAdmin.');
}

function db(): PDO
{
    global $pdo;
    return $pdo;
}
