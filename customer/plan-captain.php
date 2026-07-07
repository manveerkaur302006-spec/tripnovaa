<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();
$tripId = (int)($_GET['trip_id'] ?? 0);
$stmt = db()->prepare('SELECT tp.*, u.name AS captain_name, cp.vehicle_model, cp.vehicle_number FROM trip_plans tp LEFT JOIN trip_assignments ta ON ta.trip_plan_id=tp.id LEFT JOIN users u ON u.id=ta.captain_id LEFT JOIN captain_profiles cp ON cp.user_id = u.id WHERE tp.id = ? AND tp.customer_id = ?');
$stmt->execute([$tripId, $user['id']]);
$trip = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Captain Details - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.small{font-size:13px;color:#666}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="plan-summary.php?trip_id=<?= $tripId ?>" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Captain</h1></div>
<div class="card">
<?php if ($trip && $trip['captain_name']): ?>
<h2><?= e($trip['captain_name']) ?></h2>
<p class="small"><?= e($trip['vehicle_model']) ?> · <?= e($trip['vehicle_number']) ?></p>
<p class="small">Status: <?= e($trip['status'] ?? 'Pending') ?></p>
<?php else: ?>
<p>No captain assigned yet.</p>
<?php endif; ?>
</div>
</div>
</body>
</html>
