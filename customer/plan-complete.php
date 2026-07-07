<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();
$tripId = (int)($_GET['trip_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM trip_plans WHERE id = ? AND customer_id = ?');
$stmt->execute([$tripId, $user['id']]);
$trip = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trip Confirmed - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.small{font-size:13px;color:#666}.btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border:none;border-radius:14px;background:#007BFF;color:#fff;font-weight:600;text-decoration:none;cursor:pointer;width:100%}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="plan-summary.php?trip_id=<?= $tripId ?>" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Payment Complete</h1></div>
<div class="card"><h2>Your trip plan is confirmed</h2>
<?php if ($trip): ?><p class="small"><strong><?= e($trip['from_city']) ?> → <?= e($trip['to_city']) ?></strong></p><p class="small">Travel dates: <?= e($trip['start_date']) ?> to <?= e($trip['end_date']) ?></p><p class="small">Advance Paid: ₹<?= number_format((float)$trip['advance_paid'],2) ?></p><?php endif; ?></div>
</div>
</body>
</html>
