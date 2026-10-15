<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();

$fares = ['bike' => 45, 'auto' => 75, 'mini' => 120, 'sedan' => 200];
$fareRules = ['bike' => ['base' => 25, 'per_km' => 8], 'auto' => ['base' => 35, 'per_km' => 12], 'mini' => ['base' => 55, 'per_km' => 16], 'sedan' => ['base' => 80, 'per_km' => 22]];
$saved = db()->prepare('SELECT * FROM saved_locations WHERE user_id = ?');
$saved->execute([$user['id']]);
$locations = $saved->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup = trim($_POST['pickup'] ?? 'Current Location');
    $drop = trim($_POST['drop'] ?? '');
    $vehicle = $_POST['vehicle_type'] ?? 'bike';
    $payment = $_POST['payment_method'] ?? 'cash';
    $distance = filter_var($_POST['distance_km'] ?? null, FILTER_VALIDATE_FLOAT);
    $rule = $fareRules[$vehicle] ?? $fareRules['bike'];

    if ($drop && $distance !== false && $distance > 0 && $distance <= 1000) {
        $fare = (int) ceil($rule['base'] + ($distance * $rule['per_km']));
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
.fare{color:#F27B21;font-weight:600;white-space:nowrap}.estimate-note{font-size:11px;color:#888;margin:-4px 0 8px}
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
<input type="hidden" name="distance_km" id="distance_km">
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
<p class="estimate-note" id="estimate_note">Choose a destination to see fare estimates.</p>
<div class="pay">Payment: <select name="payment_method"><option value="cash">Cash - Personal</option><option value="upi">UPI</option><option value="card">Card</option></select></div>
<button type="submit" class="btn">Find Riders</button>
</form>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const map=L.map('map').setView([12.9716,77.5946],13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
const pickup=L.marker([12.9716,77.5946]).addTo(map).bindPopup('Current Location');
let pickupCoords=[12.9716,77.5946], routeLine, destinationMarker;
const distanceInput=document.getElementById('distance_km');
const note=document.getElementById('estimate_note');
const rates={bike:[25,8],auto:[35,12],mini:[55,16],sedan:[80,22]};
document.querySelectorAll('.veh div:last-child').forEach(el=>el.textContent='Enter destination');
if(navigator.geolocation){navigator.geolocation.getCurrentPosition(p=>{pickupCoords=[p.coords.latitude,p.coords.longitude];map.setView(pickupCoords,14);pickup.setLatLng(pickupCoords);});}
document.querySelectorAll('.veh').forEach(v=>{v.addEventListener('click',()=>{document.querySelectorAll('.veh').forEach(x=>x.classList.remove('active'));v.classList.add('active');});});
function showFares(distance){
  document.querySelectorAll('input[name="vehicle_type"]').forEach(input=>{const rate=rates[input.value], fare=Math.ceil(rate[0]+distance*rate[1]);input.closest('.veh').querySelector('div:last-child').textContent='₹'+fare;});
  if(note) note.textContent=distance.toFixed(1)+' km route estimate. Final fare may change if the route changes.';
}
async function estimateRoute(){
  const query=document.getElementById('drop').value.trim(); if(!query)return;
  if(note) note.textContent='Finding route and fare estimates…'; distanceInput.value='';
  try {
    const places=await fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q='+encodeURIComponent(query)).then(r=>r.json());
    if(!places.length)throw new Error('not found');
    const dest=[Number(places[0].lat),Number(places[0].lon)];
    const data=await fetch('https://router.project-osrm.org/route/v1/driving/'+pickupCoords[1]+','+pickupCoords[0]+';'+dest[1]+','+dest[0]+'?overview=full&geometries=geojson').then(r=>r.json());
    if(!data.routes||!data.routes.length)throw new Error('no route');
    const route=data.routes[0],distance=route.distance/1000; distanceInput.value=distance.toFixed(2); showFares(distance);
    if(routeLine)map.removeLayer(routeLine);if(destinationMarker)map.removeLayer(destinationMarker);
    destinationMarker=L.marker(dest).addTo(map);routeLine=L.geoJSON(route.geometry,{style:{color:'#007BFF',weight:4}}).addTo(map);map.fitBounds(routeLine.getBounds(),{padding:[25,25]});
  } catch(error) { if(note) note.textContent='Could not calculate this route. Please enter a more specific destination.'; }
}
document.getElementById('drop').addEventListener('change',estimateRoute);
document.querySelectorAll('.saved-item').forEach(item=>item.addEventListener('click',()=>setTimeout(estimateRoute,0)));
document.querySelector('form').addEventListener('submit',event=>{if(!distanceInput.value){event.preventDefault();if(note) note.textContent='Please choose a valid destination so we can calculate the route first.';document.getElementById('drop').focus();}});
</script>
</body>
</html>
