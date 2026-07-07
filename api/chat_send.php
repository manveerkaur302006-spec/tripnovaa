<?php
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

requireLogin();
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$receiverId = (int)($data['receiver_id'] ?? 0);
$message = trim($data['message'] ?? '');
$rideId = !empty($data['ride_id']) ? (int)$data['ride_id'] : null;
$tripId = !empty($data['trip_id']) ? (int)$data['trip_id'] : null;

if (!$receiverId || !$message) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

db()->prepare('INSERT INTO messages (sender_id, receiver_id, ride_id, trip_id, message) VALUES (?,?,?,?,?)')
    ->execute([$_SESSION['user_id'], $receiverId, $rideId, $tripId, $message]);

echo json_encode(['success' => true]);
