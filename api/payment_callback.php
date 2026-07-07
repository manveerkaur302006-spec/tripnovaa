<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cashfree.php';

$orderId = $_GET['order_id'] ?? '';
$mock = isset($_GET['mock']);

if (!$orderId) {
    header('Location: ' . BASE_URL);
    exit;
}

$payment = getPaymentByOrderId($orderId);
if ($payment) {
    markPaymentSuccess($orderId);

    $refType = $payment['reference_type'];
    $refId = (int)$payment['reference_id'];

    if ($refType === 'ride') {
        db()->prepare('UPDATE rides SET status = "completed" WHERE id = ?')->execute([$refId]);
    } elseif ($refType === 'trip_plan') {
        db()->prepare('UPDATE trip_plans SET advance_paid = ?, status = "booked" WHERE id = ?')->execute([$payment['amount'], $refId]);
    } elseif ($refType === 'tour_booking') {
        $stmt = db()->prepare('SELECT * FROM tour_bookings WHERE id = ?');
        $stmt->execute([$refId]);
        $booking = $stmt->fetch();
        if ($booking) {
            $newAdvance = $booking['advance_paid'] + $payment['amount'];
            $remaining = $booking['total'] - $newAdvance;
            $status = $remaining <= 0 ? 'completed' : ($newAdvance > 0 ? 'confirmed' : 'pending');
            db()->prepare('UPDATE tour_bookings SET advance_paid = ?, remaining = ?, status = ? WHERE id = ?')
                ->execute([$newAdvance, max(0, $remaining), $status, $refId]);
        }
    } elseif ($refType === 'transport') {
        db()->prepare('UPDATE transport_bookings SET status = "confirmed" WHERE id = ?')->execute([$refId]);
    } elseif ($refType === 'hotel') {
        db()->prepare('UPDATE hotel_bookings SET status = "confirmed" WHERE id = ?')->execute([$refId]);
    }
}

$redirect = $_GET['redirect'] ?? (BASE_URL . '/customer/my-bookings.php?payment=success');
header('Location: ' . $redirect);
exit;
