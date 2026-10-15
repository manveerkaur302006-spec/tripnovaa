<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();
$defaultTo = $_GET['to'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare('INSERT INTO trip_plans (customer_id, from_city, to_city, travelers, start_date, end_date, budget_min, budget_max, places, status) VALUES (?,?,?,?,?,?,?,?,?,"draft")');
    $stmt->execute([
        $user['id'], $_POST['from_city'], $_POST['to_city'], (int)$_POST['travelers'],
        $_POST['start_date'], $_POST['end_date'], $_POST['budget_min'], $_POST['budget_max'], $_POST['places']
    ]);
    header('Location: plan-transport.php?trip_id=' . db()->lastInsertId());
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Plan Trip - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto}
.hero{height:160px;background:url('https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=600') center/cover;position:relative}
.hero::after{content:'';position:absolute;inset:0;background:linear-gradient(transparent,rgba(0,0,0,.4))}
.form{background:#fff;margin:-20px 16px 16px;border-radius:16px;padding:20px;position:relative;z-index:1;box-shadow:0 4px 15px rgba(0,0,0,.08)}
.form h2{color:#1A4980;margin-bottom:16px;font-size:18px}
.field{margin-bottom:14px}.field label{display:block;font-size:12px;color:#666;margin-bottom:4px}
.field input,.field select,.field textarea{width:100%;padding:12px;border:1px solid #eee;border-radius:10px;font-size:14px}
.btn{width:100%;padding:16px;background:#007BFF;color:#fff;border:none;border-radius:12px;font-weight:600;cursor:pointer;margin-top:8px}
.note{font-size:11px;color:#888;text-align:center;margin-top:12px}
</style>
</head>
<body>
<div class="hero"></div>
<form class="form" method="POST">
<h2><i class="fas fa-map-marked-alt"></i> Plan Your Trip</h2>
<div class="field"><label>From</label><input name="from_city" value="Bangalore" required></div>
<div class="field"><label>To</label><input name="to_city" value="<?= e($defaultTo ?: 'Manali, Himachal Pradesh') ?>" required></div>
<div class="field"><label>Travelers</label><select name="travelers"><option>1 Adult</option><option selected>2 Adults</option><option>3 Adults</option><option>4 Adults</option></select></div>
<div class="field"><label>Start Date</label><input type="date" name="start_date" value="2026-05-20" required></div>
<div class="field"><label>End Date</label><input type="date" name="end_date" value="2026-05-24" required></div>
<div class="field"><label>Budget (₹)</label><input name="budget_min" type="number" value="15000" placeholder="Min"> to <input name="budget_max" type="number" value="25000" placeholder="Max" style="margin-top:8px"></div>
<div class="field"><label>Places to visit</label><textarea name="places" rows="2">Solang Valley, Rohtang Pass, Old Manali</textarea></div>
<button type="submit" class="btn">Find Best Options</button>
<p class="note">Book transport at least 2 days in advance</p>
</form>
</body>
</html>
