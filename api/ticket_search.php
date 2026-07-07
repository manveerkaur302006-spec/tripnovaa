<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/ticket_api.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'bus';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

echo json_encode(['results' => searchTransport($type, $from, $to, $date)]);
