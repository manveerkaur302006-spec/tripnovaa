<?php
require_once __DIR__ . '/app.php';

$dbHost = 'sql109.infinityfree.com';
$dbName = 'if0_43161049_tripnovaa';
$dbUser = 'if0_43161049';
$dbPass = '2iII8WlQehrI';

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
