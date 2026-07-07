<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();

$fares = ['bike' => 45, 'auto' => 75, 'mini' => 120, 'sedan' => 200];
$saved = db()->prepare('SELECT * FROM saved_locations WHERE user_id = ?');
$saved->execute([$user['id']]);
$locations = $saved->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup = trim($_POST['pickup'] ?? 'Current Location');
    $drop = trim($_POST['drop'] ?? '');
    $vehicle = $_POST['vehicle_type'] ?? 'bike';
    $payment = $_POST['payment_method'] ?? 'cash';
    $fare = $fares[$vehicle] ?? 45;

    if ($drop) {
        $stmt = db()->prepare('INSERT INTO rides (customer_id, pickup_address, drop_address, pickup_lat, pickup_lng, drop_lat, drop_lng, vehicle_type, fare, status, payment_method) VALUES (?,?,?,?,?,?,?,?,?,"searching",?)');
        $stmt->execute([$user['id'], $pickup, $drop, DEFAULT_LAT, DEFAULT_LNG, DEFAULT_LAT + 0.05, DEFAULT_LNG + 0.05, $vehicle, $fare, $payment]);
        $rideId = (int) db()->lastInsertId();
        header('Location: quick-ride-riders.php?ride_id=' . $rideId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quick Ride - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;max-width:430px;margin:0 auto}
.header{background:#fff;padding:16px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.header a{color:#333;text-decoration:none}.header h2{flex:1;font-size:18px}
#map{height:220px;width:100%}
.panel{background:#fff;border-radius:16px 16px 0 0;margin-top:-16px;position:relative;padding:16px;min-height:calc(100vh - 220px)}
.field{margin-bottom:10px}
.field input{width:100%;padding:12px;border:1px solid #eee;border-radius:10px;font-size:14px}
.saved{margin:12px 0}
.saved-item{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f0f0f0;font-size:13px;cursor:pointer}
.vehicles{display:flex;gap:8px;overflow-x:auto;margin:12px 0}
.veh{border:2px solid #eee;border-radius:10px;padding:10px;min-width:70px;text-align:center;cursor:pointer;font-size:12px}
.veh.active{border-color:#007BFF;background:#e3f2fd}
.veh input{display:none}
.pay{font-size:13px;color:#666;margin:8px 0}
.btn{width:100%;padding:16px;background:#F27B21;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;margin-top:8px}
</style>
</head>
<body>
<div class="header"><a href="home.php"><i class="fas fa-arrow-left"></i></a><h2>Quick Ride</h2><i class="fas fa-ellipsis-v"></i></div>
<div id="map"></div>
<form class="panel" method="POST">
<div class="field"><input name="pickup" id="pickup" value="Current Location" placeholder="Pickup"></div>
<div class="field"><input name="drop" id="drop" placeholder="Where to? Enter destination" required></div>
<div class="saved">
<?php foreach ($locations as $loc): ?>
<div class="saved-item" onclick="document.getElementById('drop').value='<?= e($loc['address']) ?>'"><i class="fas fa-<?= $loc['label']==='Home'?'home':($loc['label']==='Work'?'briefcase':'plane') ?>" style="color:#007BFF"></i><div><strong><?= e($loc['label']) ?></strong><div style="color:#888;font-size:11px"><?= e($loc['address']) ?></div></div></div>
<?php endforeach; ?>
</div>
<div class="vehicles">
<?php foreach ($fares as $type => $price): ?>
<label class="veh <?= $type==='bike'?'active':'' ?>"><input type="radio" name="vehicle_type" value="<?= $type ?>" <?= $type==='bike'?'checked':'' ?>><div><?= ucfirst($type) ?></div><div style="color:#F27B21;font-weight:600">₹<?= $price ?></div></label>
<?php endforeach; ?>
</div>
<div class="pay">Payment: <select name="payment_method"><option value="cash">Cash - Personal</option><option value="upi">UPI</option><option value="card">Card</option></select></div>
<button type="submit" class="btn">Find Riders</button>
</form>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map=L.map('map').setView([12.9716,77.5946],13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
const pickup=L.marker([12.9716,77.5946]).addTo(map).bindPopup('Current Location');
if(navigator.geolocation){navigator.geolocation.getCurrentPosition(p=>{map.setView([p.coords.latitude,p.coords.longitude],14);pickup.setLatLng([p.coords.latitude,p.coords.longitude]);});}
document.querySelectorAll('.veh').forEach(v=>{v.addEventListener('click',()=>{document.querySelectorAll('.veh').forEach(x=>x.classList.remove('active'));v.classList.add('active');});});
</script>
</body>
</html>
