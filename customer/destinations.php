<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$destinations = db()->query('SELECT * FROM popular_destinations ORDER BY rating DESC, name ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Popular Destinations - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;padding-bottom:24px}.header{background:#fff;padding:16px;display:flex;align-items:center;gap:12px}.header a{color:#333;text-decoration:none}.header h2{font-size:18px;color:#1A4980}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;padding:16px}.card{background:#fff;border-radius:12px;overflow:hidden;text-decoration:none;color:#333;box-shadow:0 2px 8px rgba(0,0,0,.06)}.card img{width:100%;height:120px;object-fit:cover}.info{padding:10px}.info h3{font-size:14px}.price{color:#F27B21;font-size:13px;font-weight:600;margin-top:4px}.rating{font-size:12px;color:#888;margin-top:2px}.empty{padding:32px 16px;text-align:center;color:#777}
</style>
</head>
<body>
<div class="header"><a href="home.php" aria-label="Back"><i class="fas fa-arrow-left"></i></a><h2>Popular Destinations</h2></div>
<?php if (!$destinations): ?><p class="empty">No destinations are available right now.</p><?php else: ?>
<div class="grid">
<?php foreach ($destinations as $destination): ?>
<a class="card" href="plan-trip.php?to=<?= urlencode($destination['name']) ?>">
<img src="<?= e($destination['image_url']) ?>" alt="<?= e($destination['name']) ?>">
<div class="info"><h3><?= e($destination['name']) ?></h3><div class="price">₹<?= number_format($destination['price']) ?></div><div class="rating">★ <?= e($destination['rating']) ?></div></div>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
</body>
</html>
