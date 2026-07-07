<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cashfree.php';
requireRole('customer');

$rideId = (int)($_GET['ride_id'] ?? 0);
$stmt = db()->prepare('SELECT r.*, u.name as captain_name, cp.vehicle_model, cp.vehicle_number, cp.rating FROM rides r JOIN users u ON r.captain_id=u.id JOIN captain_profiles cp ON u.id=cp.user_id WHERE r.id=? AND r.customer_id=?');
$stmt->execute([$rideId, $_SESSION['user_id']]);
$ride = $stmt->fetch();
if (!$ride) { header('Location: quick-ride.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? 'cash';
    if ($method === 'cash') {
        db()->prepare('UPDATE rides SET status="completed", payment_method="cash" WHERE id=?')->execute([$rideId]);
        header('Location: quick-ride-feedback.php?ride_id=' . $rideId);
        exit;
    } else {
        $result = createCashfreeOrder($_SESSION['user_id'], $ride['fare'], 'ride', $rideId, BASE_URL . '/customer/quick-ride-feedback.php?ride_id=' . $rideId);
        if ($result['mock'] ?? false) {
            markPaymentSuccess($result['order_id']);
            db()->prepare('UPDATE rides SET status="completed", payment_method=? WHERE id=?')->execute([$method, $rideId]);
            header('Location: quick-ride-feedback.php?ride_id=' . $rideId);
        } else {
            header('Location: ' . ($result['redirect_url'] ?? 'quick-ride-feedback.php?ride_id=' . $rideId));
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;padding:16px}
.header{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.header a{color:#333}.header h2{font-size:18px}
.fare-box{background:#fff;border-radius:16px;padding:24px;text-align:center;margin-bottom:20px}
.fare-box .amount{font-size:36px;font-weight:700;color:#1A4980}
.fare-box .label{color:#888;font-size:14px}
.badge{display:inline-block;background:#e8f5e9;color:#27AE60;padding:4px 12px;border-radius:20px;font-size:11px;margin-top:8px}
.methods{background:#fff;border-radius:16px;padding:16px}
.method{display:flex;align-items:center;gap:12px;padding:14px 0;border-bottom:1px solid #f0f0f0;cursor:pointer}
.method:last-child{border:none}
.method input{margin-right:8px}
.btn{width:100%;padding:16px;background:#F27B21;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;margin-top:20px}
.note{text-align:center;font-size:11px;color:#888;margin-top:12px}
</style>
</head>
<body>
<div class="header"><a href="quick-ride-tracking.php?ride_id=<?= $rideId ?>"><i class="fas fa-arrow-left"></i></a><h2>Payment</h2></div>
<div class="fare-box">
<div class="amount">₹<?= number_format($ride['fare'],0) ?></div>
<div class="label">Total Fare</div>
<div class="badge"><i class="fas fa-shield-alt"></i> Secure Payments</div>
</div>
<form method="POST">
<div class="methods">
<label class="method"><input type="radio" name="payment_method" value="cash" checked> <i class="fas fa-money-bill-wave"></i> Cash</label>
<label class="method"><input type="radio" name="payment_method" value="upi"> <i class="fas fa-mobile-alt"></i> UPI - Pay using any UPI app</label>
<label class="method"><input type="radio" name="payment_method" value="phonepe"> PhonePe</label>
<label class="method"><input type="radio" name="payment_method" value="gpay"> Google Pay</label>
<label class="method"><input type="radio" name="payment_method" value="paytm"> Paytm</label>
<label class="method"><input type="radio" name="payment_method" value="card"> Credit / Debit Card</label>
<label class="method"><input type="radio" name="payment_method" value="wallet"> Wallet</label>
</div>
<button type="submit" class="btn">Pay ₹<?= number_format($ride['fare'],0) ?></button>
<p class="note">Your payment is safe and secure.</p>
</form>
</body>
</html>
