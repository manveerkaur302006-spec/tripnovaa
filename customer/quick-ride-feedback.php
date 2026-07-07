<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');

$rideId = (int)($_GET['ride_id'] ?? 0);
$stmt = db()->prepare('SELECT r.*, u.name as captain_name, cp.vehicle_model, cp.vehicle_number, cp.rating FROM rides r JOIN users u ON r.captain_id=u.id JOIN captain_profiles cp ON u.id=cp.user_id WHERE r.id=? AND r.customer_id=?');
$stmt->execute([$rideId, $_SESSION['user_id']]);
$ride = $stmt->fetch();
if (!$ride) { header('Location: home.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 5);
    $issues = $_POST['issues'] ?? [];
    db()->prepare('INSERT INTO ride_feedback (ride_id, rating, issue_tags) VALUES (?,?,?)')
        ->execute([$rideId, $rating, json_encode($issues)]);
    if ($ride['captain_id']) {
        db()->prepare('UPDATE captain_profiles SET total_trips = total_trips + 1 WHERE user_id = ?')->execute([$ride['captain_id']]);
    }
    header('Location: home.php?feedback=done');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ride Completed - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;padding:16px}
.header{text-align:center;margin:20px 0}
.header h2{color:#27AE60;font-size:22px}
.header p{color:#666;font-size:14px;margin-top:8px}
.card{background:#fff;border-radius:16px;padding:20px;margin:16px 0;text-align:center}
.rider-avatar{width:60px;height:60px;border-radius:50%;background:#007BFF;color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:600;margin:0 auto 12px}
.stars{display:flex;justify-content:center;gap:8px;margin:16px 0;font-size:28px;color:#ddd;cursor:pointer}
.stars .active{color:#F27B21}
.issues{text-align:left;margin:16px 0}
.issues label{display:block;padding:8px 0;font-size:13px}
.btn{width:100%;padding:16px;background:#F27B21;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer}
</style>
</head>
<body>
<div class="header">
<h2><i class="fas fa-check-circle"></i> Ride Completed</h2>
<p>Thank you for riding with us! Hope you had a great trip.</p>
</div>
<div class="card">
<div class="rider-avatar"><?= strtoupper(substr($ride['captain_name'],0,1)) ?></div>
<strong><?= e($ride['captain_name']) ?></strong>
<p style="font-size:12px;color:#888"><?= e($ride['vehicle_model']) ?> · ★ <?= $ride['rating'] ?></p>
<p style="margin-top:12px">Total Fare Paid by <?= ucfirst($ride['payment_method']) ?>: <strong>₹<?= number_format($ride['fare'],0) ?></strong></p>
</div>
<form method="POST">
<h3 style="text-align:center;margin:16px 0">How was your ride?</h3>
<div class="stars" id="stars">
<?php for ($i=1;$i<=5;$i++): ?><span data-rating="<?= $i ?>" class="<?= $i<=5?'active':'' ?>">★</span><?php endfor; ?>
</div>
<input type="hidden" name="rating" id="rating" value="5">
<p style="text-align:center;color:#27AE60;font-weight:600" id="ratingLabel">Excellent</p>
<div class="issues">
<p style="font-weight:600;margin-bottom:8px">Any issues? Select all that apply</p>
<label><input type="checkbox" name="issues[]" value="extra_money"> Demanded extra money</label>
<label><input type="checkbox" name="issues[]" value="rash_driving"> Rash driving</label>
<label><input type="checkbox" name="issues[]" value="wrong_route"> Wrong route taken</label>
<label><input type="checkbox" name="issues[]" value="not_clean"> Vehicle not clean</label>
<label><input type="checkbox" name="issues[]" value="other"> Other issue</label>
<label><input type="checkbox" name="issues[]" value="none" checked> No issues</label>
</div>
<button type="submit" class="btn">Submit Feedback</button>
</form>
<script>
const labels=['','Poor','Fair','Good','Very Good','Excellent'];
document.querySelectorAll('#stars span').forEach(s=>{s.addEventListener('click',()=>{const r=s.dataset.rating;document.getElementById('rating').value=r;document.querySelectorAll('#stars span').forEach((x,i)=>x.classList.toggle('active',i<r));document.getElementById('ratingLabel').textContent=labels[r];});});
</script>
</body>
</html>
