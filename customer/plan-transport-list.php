<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/ticket_api.php';
requireRole('customer');

$tripId = (int)($_GET['trip_id'] ?? 0);
$type = $_GET['type'] ?? 'bus';
$stmt = db()->prepare('SELECT * FROM trip_plans WHERE id=? AND customer_id=?');
$stmt->execute([$tripId, $_SESSION['user_id']]);
$trip = $stmt->fetch();
if (!$trip) { header('Location: plan-trip.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invId = (int)$_POST['inventory_id'];
    $result = bookTransport($_SESSION['user_id'], $invId, $trip['start_date'], $trip['travelers']);
    if ($result['success']) {
        db()->prepare('UPDATE transport_bookings SET trip_plan_id=? WHERE id=?')->execute([$tripId, $result['booking_id']]);
        header('Location: plan-summary.php?trip_id=' . $tripId);
        exit;
    }
}

$results = searchTransport($type, $trip['from_city'], explode(',', $trip['to_city'])[0], $trip['start_date']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Available <?= ucfirst($type) ?>es</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;padding:16px}
h2{color:#1A4980;margin-bottom:4px}.count{color:#666;font-size:13px;margin-bottom:16px}
.filters{display:flex;gap:8px;overflow-x:auto;margin-bottom:16px}
.chip{padding:6px 14px;background:#fff;border-radius:20px;font-size:12px;white-space:nowrap}
.chip.active{background:#007BFF;color:#fff}
.card{background:#fff;border-radius:12px;padding:14px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center}
.card h4{font-size:14px}.card p{font-size:11px;color:#888}
.price{font-weight:700;color:#007BFF}.btn-s{background:#007BFF;color:#fff;border:none;padding:8px 16px;border-radius:8px;cursor:pointer}
.promo{background:linear-gradient(135deg,#1A4980,#007BFF);color:#fff;padding:12px;border-radius:12px;text-align:center;margin-top:16px;font-size:13px}
</style>
</head>
<body>
<a href="plan-transport.php?trip_id=<?= $tripId ?>" style="color:#333"><i class="fas fa-arrow-left"></i></a>
<h2>Available <?= ucfirst($type) ?>es</h2>
<p class="count"><?= count($results) ?> options found</p>
<div class="filters"><span class="chip active">All</span><span class="chip">AC</span><span class="chip">Sleeper</span><span class="chip">Day</span></div>
<?php foreach ($results as $r): ?>
<form method="POST" class="card">
<input type="hidden" name="inventory_id" value="<?= $r['id'] ?>">
<div><h4><?= e($r['operator']) ?></h4><p><?= $r['departure_time'] ?> → <?= $r['arrival_time'] ?> · ★ <?= $r['rating'] ?></p><p><?= e($r['bus_type'] ?? '') ?></p></div>
<div style="text-align:right"><div class="price">₹<?= number_format($r['price']) ?></div><button type="submit" class="btn-s">Select</button></div>
</form>
<?php endforeach; ?>
<div class="promo">Up to 10% OFF | Use Code: TRIPNOVA</div>
</body>
</html>
