<?php
require_once __DIR__ . '/../config/cashfree.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data && isset($data['data']['order']['order_id'])) {
    $orderId = $data['data']['order']['order_id'];
    $status = $data['data']['payment']['payment_status'] ?? '';
    if ($status === 'SUCCESS') {
        markPaymentSuccess($orderId);
    }
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
