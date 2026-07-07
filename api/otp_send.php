<?php
require_once __DIR__ . '/../config/auth.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$phone = preg_replace('/\D/', '', $data['phone'] ?? '');
$role = $data['role'] ?? 'customer';

if (strlen($phone) < 10) {
    echo json_encode(['error' => 'Invalid phone']);
    exit;
}

$code = generateOtp();
$expires = date('Y-m-d H:i:s', time() + OTP_EXPIRY_SECONDS);

db()->prepare('INSERT INTO otp_codes (phone, code, role, expires_at) VALUES (?,?,?,?)')
    ->execute([$phone, $code, $role, $expires]);

echo json_encode(['success' => true, 'demo_otp' => $code, 'expires_in' => OTP_EXPIRY_SECONDS]);
