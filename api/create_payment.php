<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cashfree.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid method']);
    exit;
}

requireLogin();
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$amount = (float)($data['amount'] ?? 0);
$refType = $data['reference_type'] ?? '';
$refId = (int)($data['reference_id'] ?? 0);
$returnUrl = $data['return_url'] ?? BASE_URL;

if ($amount <= 0 || !$refType || !$refId) {
    echo json_encode(['error' => 'Invalid payment data']);
    exit;
}

$result = createCashfreeOrder($_SESSION['user_id'], $amount, $refType, $refId, $returnUrl);
echo json_encode($result);
