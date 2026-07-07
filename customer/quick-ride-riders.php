<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');

$rideId = (int)($_GET['ride_id'] ?? 0);
$stmt = db()->prepare('SELECT r.*, u.name as captain_name, cp.rating, cp.vehicle_model, cp.vehicle_number FROM rides r LEFT JOIN users u ON r.captain_id=u.id LEFT JOIN captain_profiles cp ON u.id=cp.user_id WHERE r.id=? AND r.customer_id=?');
$stmt->execute([$rideId, $_SESSION['user_id']]);
$ride = $stmt->fetch();
if (!$ride) { header('Location: quick-ride.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['captain_id'])) {
    $captainId = (int)$_POST['captain_id'];
    $otp = generateRideOtp();
    db()->prepare('UPDATE rides SET captain_id=?, status="rider_assigned", ride_otp=? WHERE id=?')->execute([$captainId, $otp, $rideId]);
    header('Location: quick-ride-tracking.php?ride_id=' . $rideId);
    exit;
}

$vehicle = $ride['vehicle_type'];
$captains = db()->prepare('SELECT u.id, u.name, cp.rating, cp.vehicle_model, cp.vehicle_number, cp.vehicle_type FROM users u JOIN captain_profiles cp ON u.id=cp.user_id WHERE u.role="captain" AND cp.vehicle_type=? AND cp.is_online=1 LIMIT 6');
$captains->execute([$vehicle]);
$riders = $captains->fetchAll();
if (empty($riders)) {
    $riders = db()->query('SELECT u.id, u.name, cp.rating, cp.vehicle_model, cp.vehicle_number, cp.vehicle_type FROM users u JOIN captain_profiles cp ON u.id=cp.user_id WHERE u.role="captain" LIMIT 6')->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Available Riders - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;padding-bottom:20px}
.header{background:#fff;padding:16px;display:flex;align-items:center;gap:12px}
.header a{color:#333}.header h2{flex:1;font-size:18px}
.sub{padding:0 16px 12px;color:#666;font-size:13px}
.tabs{display:flex;gap:8px;padding:0 16px;overflow-x:auto;margin-bottom:12px}
.tab{padding:8px 16px;border-radius:20px;background:#fff;font-size:12px;white-space:nowrap}
.tab.active{background:#007BFF;color:#fff}
.rider-card{background:#fff;margin:0 16px 12px;border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.rider-avatar{width:48px;height:48px;border-radius:50%;background:#007BFF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600}
.rider-info{flex:1}
.rider-info h4{font-size:14px}.rider-info p{font-size:11px;color:#888}
.rating{color:#F27B21;font-size:12px}
.btn-accept{background:#F27B21;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px}
</style>
</head>
<body>
<div class="header"><a href="quick-ride.php"><i class="fas fa-arrow-left"></i></a><h2>Available Riders</h2></div>
<p class="sub"><?= count($riders) ?> Riders Found</p>
<div class="tabs">
<span class="tab active"><?= ucfirst($vehicle) ?></span>
</div>
<?php foreach ($riders as $i => $r):
    $fare = $ride['fare'] + ($i * 2);
?>
<form method="POST" class="rider-card">
<input type="hidden" name="captain_id" value="<?= $r['id'] ?>">
<div class="rider-avatar"><?= strtoupper(substr($r['name'],0,1)) ?></div>
<div class="rider-info">
<h4><?= e($r['name']) ?></h4>
<p><?= e($r['vehicle_model'] ?? ucfirst($r['vehicle_type'])) ?> · <?= e($r['vehicle_number'] ?? 'KA XX XX') ?></p>
<span class="rating">★ <?= $r['rating'] ?></span>
</div>
<div style="text-align:right">
<div style="font-weight:600;color:#F27B21">₹<?= $fare ?></div>
<button type="submit" class="btn-accept">Accept</button>
</div>
</form>
<?php endforeach; ?>
</body>
</html>
