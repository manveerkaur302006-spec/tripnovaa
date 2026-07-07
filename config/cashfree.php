<?php
require_once __DIR__ . '/app.php';

function createCashfreeOrder(int $userId, float $amount, string $refType, int $refId, string $returnUrl): array
{
    $orderId = 'TN_' . time() . '_' . random_int(1000, 9999);

    $stmt = db()->prepare('INSERT INTO payments (user_id, reference_type, reference_id, amount, method, cashfree_order_id, status) VALUES (?,?,?,?,"online",?,?)');
    $stmt->execute([$userId, $refType, $refId, $amount, $orderId, CASHFREE_MOCK ? 'success' : 'pending']);
    $paymentId = (int) db()->lastInsertId();

    if (CASHFREE_MOCK || empty(CASHFREE_CLIENT_ID) || CASHFREE_CLIENT_ID === 'TEST_xxxxx') {
        return [
            'mock' => true,
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'payment_session_id' => null,
            'redirect_url' => BASE_URL . '/api/payment_callback.php?order_id=' . urlencode($orderId) . '&mock=1',
        ];
    }

    $vendorPath = dirname(__DIR__) . '/vendor/autoload.php';
    if (!file_exists($vendorPath)) {
        return createCashfreeOrder($userId, $amount, $refType, $refId, $returnUrl);
    }

    require_once $vendorPath;

    try {
        $cashfree = new \Cashfree\Cashfree(
            CASHFREE_ENV === 'PRODUCTION' ? \Cashfree\Cashfree::$PRODUCTION : \Cashfree\Cashfree::$SANDBOX,
            CASHFREE_CLIENT_ID,
            CASHFREE_CLIENT_SECRET,
            '', '', '', true
        );

        $user = db()->prepare('SELECT * FROM users WHERE id = ?');
        $user->execute([$userId]);
        $u = $user->fetch();

        $request = new \Cashfree\Model\CreateOrderRequest();
        $request->setOrderId($orderId);
        $request->setOrderAmount($amount);
        $request->setOrderCurrency('INR');

        $customer = new \Cashfree\Model\CustomerDetails();
        $customer->setCustomerId((string) $userId);
        $customer->setCustomerPhone($u['phone'] ?? '9999999999');
        $customer->setCustomerEmail($u['email'] ?? 'user@tripnovaa.com');
        $customer->setCustomerName($u['name'] ?? 'User');
        $request->setCustomerDetails($customer);

        $meta = new \Cashfree\Model\OrderMeta();
        $meta->setReturnUrl(BASE_URL . '/api/payment_callback.php?order_id={order_id}');
        $meta->setNotifyUrl(BASE_URL . '/api/payment_webhook.php');
        $request->setOrderMeta($meta);

        $response = $cashfree->PGCreateOrder('2023-08-01', $request);
        $sessionId = $response[0]->getPaymentSessionId();

        return [
            'mock' => false,
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'payment_session_id' => $sessionId,
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage(), 'mock' => true, 'payment_id' => $paymentId, 'order_id' => $orderId];
    }
}

function markPaymentSuccess(string $orderId): bool
{
    $stmt = db()->prepare('UPDATE payments SET status = "success", updated_at = NOW() WHERE cashfree_order_id = ?');
    return $stmt->execute([$orderId]);
}

function getPaymentByOrderId(string $orderId): ?array
{
    $stmt = db()->prepare('SELECT * FROM payments WHERE cashfree_order_id = ?');
    $stmt->execute([$orderId]);
    return $stmt->fetch() ?: null;
}
