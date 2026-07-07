<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = trim($_POST['from'] ?? '');
    $to = trim($_POST['to'] ?? '');
    $stmt = db()->prepare('SELECT * FROM transport_inventory WHERE type="train" AND from_city LIKE ? AND to_city LIKE ? ORDER BY price ASC');
    $stmt->execute(['%'.$from.'%','%'.$to.'%']);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Train Booking - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.input-field{width:100%;padding:12px;border:1px solid #eee;border-radius:12px;margin-top:8px}.btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border:none;border-radius:14px;background:#007BFF;color:#fff;font-weight:600;text-decoration:none;cursor:pointer;width:100%}.small{font-size:13px;color:#666}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="home.php" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Train Booking</h1></div>
<form method="POST" class="card"><label class="small">From</label><input class="input-field" type="text" name="from" placeholder="Bangalore" required><label class="small">To</label><input class="input-field" type="text" name="to" placeholder="Manali" required><button type="submit" class="btn">Search Trains</button></form>
<?php foreach ($results as $r): ?><div class="card"><strong><?= e($r['operator']) ?></strong><p class="small"><?= e($r['departure_time']) ?> → <?= e($r['arrival_time']) ?> · ₹<?= number_format($r['price'],2) ?></p></div><?php endforeach; ?>
</div>
</body>
</html>
