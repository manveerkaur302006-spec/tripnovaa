from pathlib import Path

pages = {
    'customer/profile.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border:none;border-radius:14px;background:#007BFF;color:#fff;font-weight:600;text-decoration:none;cursor:pointer;width:100%}.small{font-size:13px;color:#666}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="home.php" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Profile</h1></div>
<div class="card"><h2><?= e($user['name']) ?></h2><p class="small"><?= e($user['email']) ?></p><p class="small"><?= e($user['phone']) ?></p></div>
<div class="card"><a href="logout.php" class="btn">Logout</a></div>
</div>
</body>
</html>
''',
    'customer/my-bookings.php': '''<?php
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
''',
    'customer/group-tours.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
tours = db()->query('SELECT * FROM tour_packages WHERE status="upcoming" ORDER BY start_date ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Group Tours - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.card a{color:#007BFF;text-decoration:none}.small{font-size:13px;color:#666}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="home.php" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Group Tours</h1></div>
<?php if ($tours): foreach ($tours as $tour): ?>
<div class="card"><strong><?= e($tour['title']) ?></strong><p class="small"><?= e($tour['destination']) ?> · ₹<?= number_format($tour['price'],2) ?></p></div>
<?php endforeach; else: ?>
<div class="card"><p class="small">No tours available right now.</p></div>
<?php endif; ?>
</div>
</body>
</html>
''',
    'customer/bus-booking.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = trim($_POST['from'] ?? '');
    $to = trim($_POST['to'] ?? '');
    $stmt = db()->prepare('SELECT * FROM transport_inventory WHERE type="bus" AND from_city LIKE ? AND to_city LIKE ? ORDER BY price ASC');
    $stmt->execute(['%'.$from.'%','%'.$to.'%']);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bus Booking - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.input-field{width:100%;padding:12px;border:1px solid #eee;border-radius:12px;margin-top:8px}.btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border:none;border-radius:14px;background:#007BFF;color:#fff;font-weight:600;text-decoration:none;cursor:pointer;width:100%}.small{font-size:13px;color:#666}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="home.php" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Bus Booking</h1></div>
<form method="POST" class="card"><label class="small">From</label><input class="input-field" type="text" name="from" placeholder="Bangalore" required><label class="small">To</label><input class="input-field" type="text" name="to" placeholder="Manali" required><button type="submit" class="btn">Search Buses</button></form>
<?php foreach ($results as $r): ?><div class="card"><strong><?= e($r['operator']) ?></strong><p class="small"><?= e($r['departure_time']) ?> → <?= e($r['arrival_time']) ?> · ₹<?= number_format($r['price'],2) ?></p></div><?php endforeach; ?>
</div>
</body>
</html>
''',
    'customer/train-booking.php': '''<?php
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
''',
    'customer/hotel-booking.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$hotels = [
    ['name' => 'Lotus Hotel', 'location' => 'Bangalore', 'price' => 3200, 'rating' => 4.5],
    ['name' => 'Mountain View Resort', 'location' => 'Manali', 'price' => 4500, 'rating' => 4.7],
    ['name' => 'Sea Breeze Hotel', 'location' => 'Goa', 'price' => 2800, 'rating' => 4.4],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hotel Booking - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.small{font-size:13px;color:#666}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="home.php" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Hotels</h1></div>
<?php foreach ($hotels as $hotel): ?>
<div class="card"><strong><?= e($hotel['name']) ?></strong><p class="small"><?= e($hotel['location']) ?> · ₹<?= number_format($hotel['price'],2) ?> · ★ <?= number_format($hotel['rating'],1) ?></p></div>
<?php endforeach; ?>
</div>
</body>
</html>
''',
    'customer/chat.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();
$receiverId = (int)($_GET['user_id'] ?? 0);
$rideId = (int)($_GET['ride_id'] ?? 0);
$messages = [];
if ($receiverId) {
    $stmt = db()->prepare('SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC');
    $stmt->execute([$user['id'], $receiverId, $receiverId, $user['id']]);
    $messages = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px;padding-bottom:120px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.message{margin-bottom:12px;padding:12px;border-radius:14px;max-width:80%;display:inline-block}.sent{background:#007BFF;color:#fff;margin-left:auto}.received{background:#fff;color:#333}.small{font-size:12px;color:#666}.input-box{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;padding:14px;max-width:430px;margin:0 auto}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="javascript:history.back()" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Chat</h1></div>
<?php foreach ($messages as $message): ?>
<div class="message <?= $message['sender_id'] === $user['id'] ? 'sent' : 'received' ?>"><p><?= e($message['message']) ?></p><div class="small"><?= e($message['created_at']) ?></div></div>
<?php endforeach; ?>
</div>
<div class="input-box">
<form method="POST" action="../api/chat_send.php">
<input type="hidden" name="receiver_id" value="<?= $receiverId ?>">
<input type="hidden" name="ride_id" value="<?= $rideId ?>">
<textarea name="message" rows="2" style="width:100%;padding:12px;border:1px solid #eee;border-radius:12px;margin-bottom:10px"></textarea>
<button type="submit" style="width:100%;padding:14px;border:none;border-radius:12px;background:#007BFF;color:#fff;font-weight:600">Send</button>
</form>
</div>
</body>
</html>
''',
    'customer/plan-captain.php': '''<?php
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
''',
    'customer/plan-payment.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cashfree.php';
requireRole('customer');
$user = currentUser();
$tripId = (int)($_GET['trip_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM trip_plans WHERE id = ? AND customer_id = ?');
$stmt->execute([$tripId, $user['id']]);
$trip = $stmt->fetch();
$advance = $trip ? max(500, round(((float)$trip['total_cost'] ?: 10000) * 0.05, 2)) : 500;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $trip) {
    $result = createCashfreeOrder($user['id'], $advance, 'trip_plan', $tripId, BASE_URL . '/customer/plan-complete.php?trip_id=' . $tripId);
    if (!empty($result['mock'])) {
        markPaymentSuccess($result['order_id']);
        db()->prepare('UPDATE trip_plans SET advance_paid = ?, status = "booked" WHERE id = ?')->execute([$advance, $tripId]);
        header('Location: plan-complete.php?trip_id=' . $tripId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Plan Payment - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border:none;border-radius:14px;background:#007BFF;color:#fff;font-weight:600;text-decoration:none;cursor:pointer;width:100%}.small{font-size:13px;color:#666}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="plan-summary.php?trip_id=<?= $tripId ?>" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Make Payment</h1></div>
<div class="card"><p class="small">Advance payment to confirm your trip plan.</p><p style="font-size:28px;font-weight:700;color:#F27B21">₹<?= number_format($advance,2) ?></p>
<form method="POST"><button type="submit" class="btn">Pay Now</button></form></div>
</div>
</body>
</html>
''',
    'customer/plan-complete.php': '''<?php
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
''',
    'captain/dashboard.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('captain');
$user = currentUser();
$rides = db()->prepare('SELECT COUNT(*) AS total FROM rides WHERE captain_id = ?');
$rides->execute([$user['id']]);
$rideCount = $rides->fetch()['total'] ?? 0;
$earnings = db()->prepare('SELECT SUM(amount) AS total FROM wallet_transactions WHERE captain_id = ? AND type = "credit"');
$earnings->execute([$user['id']]);
$earnings = $earnings->fetch()['total'] ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Captain Dashboard - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.item{padding:14px;background:#fff;border-radius:14px;box-shadow:0 4px 18px rgba(0,0,0,.06)}.item strong{display:block;margin-bottom:8px;font-size:14px;color:#333}.item span{color:#666;font-size:13px}.nav{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;padding:10px 0;display:flex;justify-content:space-around;max-width:430px;margin:0 auto}.nav a{text-decoration:none;color:#666;font-size:11px;text-align:center;flex:1}.nav i{display:block;font-size:18px;margin-bottom:4px}</style>
</head>
<body>
<div class="page">
<div class="header"><h1>Captain Dashboard</h1></div>
<div class="grid"><div class="item"><strong>Completed Trips</strong><span><?= $rideCount ?></span></div><div class="item"><strong>Earnings</strong><span>₹<?= number_format($earnings,2) ?></span></div></div>
</div>
<nav class="nav"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Home</a><a href="profile.php"><i class="fas fa-user"></i>Profile</a><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></nav>
</body>
</html>
''',
    'captain/profile.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('captain');
$user = currentUser();
$profile = db()->prepare('SELECT * FROM captain_profiles WHERE user_id = ?');
$profile->execute([$user['id']]);
$profile = $profile->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Captain Profile - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.small{font-size:13px;color:#666}.btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border:none;border-radius:14px;background:#007BFF;color:#fff;font-weight:600;text-decoration:none;cursor:pointer;width:100%}</style>
</head>
<body>
<div class="page">
<div class="header"><h1>Profile</h1></div>
<div class="card"><h2><?= e($user['name']) ?></h2><p class="small"><?= e($user['email']) ?></p><p class="small"><?= e($profile['vehicle_model'] ?? 'Vehicle not set') ?></p><p class="small"><?= e($profile['vehicle_number'] ?? '') ?></p></div>
<div class="card"><a href="logout.php" class="btn">Logout</a></div>
</div>
</body>
</html>
''',
    'admin/dashboard.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');
$userCount = db()->query('SELECT COUNT(*) AS total FROM users')->fetch()['total'] ?? 0;
$rideCount = db()->query('SELECT COUNT(*) AS total FROM rides')->fetch()['total'] ?? 0;
$revenue = db()->query('SELECT SUM(amount) AS total FROM payments WHERE status = "success"')->fetch()['total'] ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.item{padding:14px;background:#fff;border-radius:14px;box-shadow:0 4px 18px rgba(0,0,0,.06)}.item strong{display:block;margin-bottom:8px;font-size:14px;color:#333}.item span{color:#666;font-size:13px}.nav{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;padding:10px 0;display:flex;justify-content:space-around;max-width:430px;margin:0 auto}.nav a{text-decoration:none;color:#666;font-size:11px;text-align:center;flex:1}.nav i{display:block;font-size:18px;margin-bottom:4px}</style>
</head>
<body>
<div class="page">
<div class="header"><h1>Admin Dashboard</h1></div>
<div class="grid"><div class="item"><strong>Customers</strong><span><?= $userCount ?></span></div><div class="item"><strong>Rides</strong><span><?= $rideCount ?></span></div><div class="item"><strong>Revenue</strong><span>₹<?= number_format($revenue,2) ?></span></div><div class="item"><strong>Quick Links</strong><span><a href="users.php">Users</a> · <a href="settings.php">Settings</a></span></div></div>
</div>
<nav class="nav"><a href="dashboard.php"><i class="fas fa-chart-line"></i>Dash</a><a href="users.php"><i class="fas fa-users"></i>Users</a><a href="settings.php"><i class="fas fa-cog"></i>Settings</a><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></nav>
</body>
</html>
''',
    'admin/users.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');
$users = db()->query('SELECT * FROM users WHERE role = "customer" ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Users - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.small{font-size:13px;color:#666}.nav{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;padding:10px 0;display:flex;justify-content:space-around;max-width:430px;margin:0 auto}.nav a{text-decoration:none;color:#666;font-size:11px;text-align:center;flex:1}.nav i{display:block;font-size:18px;margin-bottom:4px}</style>
</head>
<body>
<div class="page">
<div class="header"><h1>Customers</h1></div>
<?php foreach ($users as $user): ?><div class="card"><strong><?= e($user['name']) ?></strong><p class="small"><?= e($user['email']) ?> · <?= e($user['phone']) ?></p></div><?php endforeach; ?>
</div>
<nav class="nav"><a href="dashboard.php"><i class="fas fa-chart-line"></i>Dash</a><a href="users.php"><i class="fas fa-users"></i>Users</a><a href="settings.php"><i class="fas fa-cog"></i>Settings</a><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></nav>
</body>
</html>
''',
    'admin/settings.php': '''<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('admin');
$settings = db()->query('SELECT * FROM platform_settings')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Settings - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px}.header{margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 4px 18px rgba(0,0,0,.07);margin-bottom:14px}.small{font-size:13px;color:#666}.nav{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;padding:10px 0;display:flex;justify-content:space-around;max-width:430px;margin:0 auto}.nav a{text-decoration:none;color:#666;font-size:11px;text-align:center;flex:1}.nav i{display:block;font-size:18px;margin-bottom:4px}</style>
</head>
<body>
<div class="page">
<div class="header"><h1>Settings</h1></div>
<?php if ($settings): foreach ($settings as $setting): ?><div class="card"><strong><?= e($setting['setting_key']) ?></strong><p class="small"><?= e($setting['setting_value']) ?></p></div><?php endforeach; else: ?><div class="card"><p class="small">No settings found.</p></div><?php endif; ?>
</div>
<nav class="nav"><a href="dashboard.php"><i class="fas fa-chart-line"></i>Dash</a><a href="users.php"><i class="fas fa-users"></i>Users</a><a href="settings.php"><i class="fas fa-cog"></i>Settings</a><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></nav>
</body>
</html>
''',
}

root = Path(__file__).resolve().parent.parent
for rel, content in pages.items():
    path = root / rel
    if not path.exists():
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(content, encoding='utf-8')
        print(f'Created {rel}')
    else:
        print(f'Skipped existing {rel}')
