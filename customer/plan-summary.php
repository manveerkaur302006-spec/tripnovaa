<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');

$tripId = (int)($_GET['trip_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM trip_plans WHERE id=? AND customer_id=?');
$stmt->execute([$tripId, $_SESSION['user_id']]);
$trip = $stmt->fetch();
if (!$trip) { header('Location: plan-trip.php'); exit; }

$transport = db()->prepare('SELECT SUM(price) as total FROM transport_bookings WHERE trip_plan_id=?');
$transport->execute([$tripId]);
$transportTotal = (float)($transport->fetch()['total'] ?? 5300);

$stayActivities = 13500;
$localServices = 1000;
$platformFee = 200;
$total = $transportTotal + $stayActivities + $localServices + $platformFee;
$prepay = round($total * 0.05);

db()->prepare('UPDATE trip_plans SET total_cost=?, status="open" WHERE id=?')->execute([$total, $tripId]);

if (isset($_GET['book'])) {
    header('Location: plan-payment.php?trip_id=' . $tripId);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trip Plan - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto}
.hero{height:140px;background:url('https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=600') center/cover}
.content{padding:16px}.content h2{color:#1A4980;font-size:18px}
.highlights{background:#fff;border-radius:12px;padding:16px;margin:16px 0}
.highlights li{font-size:13px;padding:6px 0;list-style:none}.highlights li::before{content:'✓ ';color:#007BFF}
.services{display:flex;gap:12px;justify-content:center;margin:16px 0}
.svc{text-align:center;font-size:11px}.svc i{display:block;font-size:24px;color:#007BFF;margin-bottom:4px}
.breakdown{background:#fff;border-radius:12px;padding:16px;font-size:13px}
.breakdown div{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f0f0f0}
.breakdown .total{font-weight:700;font-size:16px;color:#1A4980;border:none;margin-top:8px}
.btn{display:block;width:100%;padding:16px;background:#007BFF;color:#fff;border:none;border-radius:12px;font-weight:600;text-align:center;text-decoration:none;margin-top:16px}
</style>
</head>
<body>
<div class="hero"></div>
<div class="content">
<h2>Your Trip Plan - <?= e(explode(',', $trip['to_city'])[0]) ?></h2>
<p style="color:#666;font-size:13px">3 Days / 4 Nights</p>
<ul class="highlights">
<li>Luxury hotel stay</li><li>Solang Valley adventure</li><li>Rohtang Pass visit</li><li>Local guide included</li>
</ul>
<div class="services">
<div class="svc"><i class="fas fa-bed"></i>Stay</div><div class="svc"><i class="fas fa-utensils"></i>Meals</div><div class="svc"><i class="fas fa-hiking"></i>Activities</div><div class="svc"><i class="fas fa-user-tie"></i>Guide</div>
</div>
<div class="breakdown">
<div><span>Bus (Round trip) x<?= $trip['travelers'] ?></span><span>₹<?= number_format($transportTotal) ?></span></div>
<div><span>Stay & Activities</span><span>₹<?= number_format($stayActivities) ?></span></div>
<div><span>Local Services</span><span>₹<?= number_format($localServices) ?></span></div>
<div><span>Platform Fee</span><span>₹<?= number_format($platformFee) ?></span></div>
<div class="total"><span>Total</span><span>₹<?= number_format($total) ?></span></div>
</div>
<a href="plan-captain.php?trip_id=<?= $tripId ?>" class="btn" style="background:#fff;color:#007BFF;border:2px solid #007BFF">View Captain Details</a>
<a href="?trip_id=<?= $tripId ?>&book=1" class="btn">Prepay ₹<?= number_format($prepay) ?> to Book</a>
</div>
</body>
</html>
