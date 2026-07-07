<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');

$rideId = (int)($_GET['ride_id'] ?? 0);
$stmt = db()->prepare('SELECT r.*, u.name as captain_name, u.phone as captain_phone, cp.rating, cp.vehicle_model, cp.vehicle_number FROM rides r JOIN users u ON r.captain_id=u.id JOIN captain_profiles cp ON u.id=cp.user_id WHERE r.id=? AND r.customer_id=?');
$stmt->execute([$rideId, $_SESSION['user_id']]);
$ride = $stmt->fetch();
if (!$ride) { header('Location: quick-ride.php'); exit; }

if (isset($_GET['start_ride'])) {
    db()->prepare('UPDATE rides SET status="on_ride" WHERE id=?')->execute([$rideId]);
    $ride['status'] = 'on_ride';
}
if (isset($_GET['end_ride'])) {
    db()->prepare('UPDATE rides SET status="payment_pending" WHERE id=?')->execute([$rideId]);
    header('Location: quick-ride-payment.php?ride_id=' . $rideId);
    exit;
}
if ($ride['status'] === 'rider_assigned' && !isset($_GET['start_ride'])) {
    db()->prepare('UPDATE rides SET status="on_ride" WHERE id=?')->execute([$rideId]);
    $ride['status'] = 'on_ride';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>On Ride - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;max-width:430px;margin:0 auto}
.header{background:#fff;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;position:absolute;top:0;left:0;right:0;z-index:1000;max-width:430px;margin:0 auto}
.header h2{font-size:16px}.otp{background:#e3f2fd;padding:4px 10px;border-radius:8px;font-size:12px;color:#007BFF}
.emergency{background:#fee;color:#c00;border:none;padding:6px 12px;border-radius:8px;font-size:12px}
#map{height:100vh;width:100%}
.bottom-card{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;background:#fff;border-radius:16px 16px 0 0;padding:16px;box-shadow:0 -4px 20px rgba(0,0,0,.1)}
.rider-row{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.avatar{width:44px;height:44px;border-radius:50%;background:#007BFF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600}
.trip-detail{font-size:12px;color:#666;margin:8px 0;padding:8px 0;border-top:1px solid #eee}
.actions{display:flex;justify-content:space-between;align-items:center;margin-top:12px}
.btn-share{background:#f0f4f8;border:none;padding:8px 16px;border-radius:8px;font-size:12px}
.btn-end{background:#F27B21;color:#fff;border:none;padding:10px 20px;border-radius:10px;font-weight:600;text-decoration:none;font-size:13px}
</style>
</head>
<body>
<div class="header">
<div><a href="home.php" style="color:#333;text-decoration:none"><i class="fas fa-arrow-left"></i></a> <strong>On Ride</strong></div>
<span class="otp">OTP: <?= e($ride['ride_otp']) ?></span>
<button class="emergency">Emergency</button>
</div>
<div id="map"></div>
<div class="bottom-card">
<div class="rider-row">
<div class="avatar"><?= strtoupper(substr($ride['captain_name'],0,1)) ?></div>
<div style="flex:1"><strong><?= e($ride['captain_name']) ?></strong> <span style="color:#F27B21">★ <?= $ride['rating'] ?></span><br><small><?= e($ride['vehicle_model']) ?> · <?= e($ride['vehicle_number']) ?></small></div>
<a href="tel:<?= e($ride['captain_phone']) ?>" style="color:#007BFF"><i class="fas fa-phone"></i></a>
<a href="chat.php?user_id=<?= $ride['captain_id'] ?>&ride_id=<?= $rideId ?>" style="color:#007BFF;margin-left:12px"><i class="fas fa-comment"></i></a>
</div>
<div class="trip-detail">
<div><i class="fas fa-circle" style="color:#007BFF;font-size:8px"></i> <?= e($ride['pickup_address']) ?></div>
<div style="margin-top:4px"><i class="fas fa-map-marker-alt" style="color:#27AE60;font-size:10px"></i> <?= e($ride['drop_address']) ?></div>
<div style="margin-top:8px"><strong>₹<?= number_format($ride['fare'],0) ?></strong> · <?= ucfirst($ride['payment_method']) ?></div>
</div>
<div class="actions">
<a href="#" style="color:#c00;font-size:12px">Cancel Ride</a>
<a href="?ride_id=<?= $rideId ?>&end_ride=1" class="btn-end">Complete Ride</a>
</div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const plat=<?= $ride['pickup_lat'] ?: DEFAULT_LAT ?>,plng=<?= $ride['pickup_lng'] ?: DEFAULT_LNG ?>;
const dlat=<?= $ride['drop_lat'] ?: DEFAULT_LAT+0.05 ?>,dlng=<?= $ride['drop_lng'] ?: DEFAULT_LNG+0.05 ?>;
const map=L.map('map',{zoomControl:false}).setView([plat,plng],14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
L.marker([plat,plng]).addTo(map);L.marker([dlat,dlng]).addTo(map);
L.polyline([[plat,plng],[dlat,dlng]],{color:'#27AE60',weight:4}).addTo(map);
const riderIcon=L.divIcon({html:'🏍️',className:''});
let rm=L.marker([plat,plng],{icon:riderIcon}).addTo(map);
let step=0;setInterval(()=>{step+=0.02;if(step<=1){const lat=plat+(dlat-plat)*step,lng=plng+(dlng-plng)*step;rm.setLatLng([lat,lng]);}},2000);
</script>
</body>
</html>
