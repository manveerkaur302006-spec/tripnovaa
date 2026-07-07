<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$tripId = (int)($_GET['trip_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM trip_plans WHERE id=? AND customer_id=?');
$stmt->execute([$tripId, $_SESSION['user_id']]);
$trip = $stmt->fetch();
if (!$trip) { header('Location: plan-trip.php'); exit; }
$type = $_GET['type'] ?? 'bus';
if (isset($_GET['type'])) $_SESSION['transport_type_' . $tripId] = $type;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Choose Transport - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;padding:16px}
h2{color:#1A4980;margin-bottom:8px}.summary{background:#fff;padding:12px;border-radius:12px;margin:16px 0;font-size:13px;color:#666}
.transport{display:flex;gap:16px;justify-content:center;margin:24px 0}
.t-opt{text-align:center;padding:20px;background:#fff;border-radius:16px;flex:1;text-decoration:none;color:inherit;border:2px solid #eee}
.t-opt.active{border-color:#007BFF;background:#e3f2fd}
.t-opt i{font-size:32px;color:#007BFF;margin-bottom:8px;display:block}
.btn{display:block;width:100%;padding:16px;background:#007BFF;color:#fff;border:none;border-radius:12px;font-weight:600;text-align:center;text-decoration:none;margin-top:16px}
</style>
</head>
<body>
<a href="plan-trip.php" style="color:#333"><i class="fas fa-arrow-left"></i></a>
<h2>Reach <?= e(explode(',', $trip['to_city'])[0]) ?></h2>
<div class="summary"><?= e($trip['from_city']) ?> → <?= e($trip['to_city']) ?> · <?= e($trip['start_date']) ?> · <?= $trip['travelers'] ?> travelers</div>
<div class="transport">
<a href="?trip_id=<?= $tripId ?>&type=bus" class="t-opt <?= $type==='bus'?'active':'' ?>"><i class="fas fa-bus"></i>Bus</a>
<a href="?trip_id=<?= $tripId ?>&type=train" class="t-opt <?= $type==='train'?'active':'' ?>"><i class="fas fa-train"></i>Train</a>
<a href="?trip_id=<?= $tripId ?>&type=flight" class="t-opt <?= $type==='flight'?'active':'' ?>"><i class="fas fa-plane"></i>Flight</a>
</div>
<a href="plan-transport-list.php?trip_id=<?= $tripId ?>&type=<?= $type ?>" class="btn">Search <?= ucfirst($type) ?>es</a>
</body>
</html>
