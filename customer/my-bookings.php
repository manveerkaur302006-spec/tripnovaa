<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();
$rides = db()->prepare('SELECT * FROM rides WHERE customer_id=? ORDER BY created_at DESC');
$rides->execute([$user['id']]);
$rides = $rides->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.small{font-size:13px;color:#666}.item{margin-bottom:12px}.item strong{display:block;color:#1A4980;margin-bottom:4px}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="home.php" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>My Bookings</h1></div>
<div class="card">
<?php if ($rides): foreach ($rides as $ride): ?>
<div class="item"><strong><?= e($ride['pickup_address']) ?> → <?= e($ride['drop_address']) ?></strong><p class="small"><?= ucfirst($ride['status']) ?> · ₹<?= number_format($ride['fare'],2) ?></p></div>
<?php endforeach; else: ?>
<p class="small">No bookings found yet.</p>
<?php endif; ?>
</div>
</div>
</body>
</html>
