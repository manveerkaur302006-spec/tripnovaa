<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();
$destinations = db()->query('SELECT * FROM popular_destinations ORDER BY rating DESC LIMIT 6')->fetchAll();
$offers = db()->query('SELECT * FROM offers WHERE is_active = 1 LIMIT 4')->fetchAll();
$name = explode(' ', $user['name'])[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;padding-bottom:80px}
.header{padding:16px;background:#fff;display:flex;align-items:center;justify-content:space-between}
.header-left{display:flex;align-items:center;gap:10px}
.avatar{width:36px;height:36px;border-radius:50%;background:#007BFF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600}
.location{font-size:13px;color:#007BFF;font-weight:500}
.search-wrap{padding:0 16px 16px;background:#fff}
.search{display:flex;align-items:center;background:#f0f4f8;border-radius:12px;padding:12px 16px;gap:10px}
.search input{flex:1;border:none;background:none;font-size:14px;outline:none}
.quick-ride{margin:16px;background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.quick-ride h3{font-size:16px;color:#1A4980;margin-bottom:12px}
.loc-row{display:flex;gap:8px;margin-bottom:8px}
.loc-row input{flex:1;padding:10px;border:1px solid #eee;border-radius:8px;font-size:13px}
.vehicles{display:flex;gap:8px;margin:12px 0;overflow-x:auto}
.veh{border:2px solid #eee;border-radius:10px;padding:8px 12px;text-align:center;font-size:11px;min-width:60px;cursor:pointer}
.veh.active{border-color:#007BFF;background:#e3f2fd}
.veh .price{font-weight:600;color:#F27B21;margin-top:4px}
.ride-footer{display:flex;justify-content:space-between;align-items:center;margin-top:12px}
.ride-info{font-size:12px;color:#666}
.btn-orange{background:#F27B21;color:#fff;border:none;padding:10px 20px;border-radius:10px;font-weight:600;cursor:pointer;text-decoration:none;font-size:13px}
.banner{margin:16px;background:linear-gradient(135deg,#1A4980,#007BFF);border-radius:12px;padding:16px;color:#fff;font-size:14px}
.services{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:0 16px;margin:16px 0}
.svc{text-align:center;text-decoration:none;color:#333}
.svc-icon{width:52px;height:52px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;box-shadow:0 2px 8px rgba(0,0,0,.08);color:#007BFF;font-size:20px}
.svc span{font-size:11px}
.section{padding:0 16px;margin:20px 0}
.section-head{display:flex;justify-content:space-between;margin-bottom:12px}
.section-head h3{font-size:16px;color:#1A4980}
.section-head a{font-size:12px;color:#007BFF;text-decoration:none}
.dest-scroll{display:flex;gap:12px;overflow-x:auto;padding-bottom:8px}
.dest-card{min-width:140px;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);text-decoration:none;color:inherit}
.dest-card img{width:100%;height:90px;object-fit:cover}
.dest-info{padding:8px}
.dest-info h4{font-size:13px}
.dest-info .price{font-size:12px;color:#F27B21;font-weight:600}
.offer-card{background:#fff;border-radius:12px;padding:12px;margin-bottom:8px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 8px rgba(0,0,0,.04)}
.bottom-nav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;background:#fff;display:flex;justify-content:space-around;padding:10px 0;box-shadow:0 -2px 10px rgba(0,0,0,.08)}
.bottom-nav a{text-align:center;text-decoration:none;color:#999;font-size:10px;flex:1}
.bottom-nav a.active{color:#007BFF}
.bottom-nav i{font-size:20px;display:block;margin-bottom:2px}
.bottom-nav .fab-btn{width:48px;height:48px;background:#007BFF;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;margin-top:-20px;font-size:22px}
</style>
</head>
<body>
<div class="header">
<div class="header-left">
<div class="avatar"><?= strtoupper(substr($name,0,1)) ?></div>
<div><div style="font-size:14px;font-weight:600">Hey, <?= e($name) ?></div><div class="location"><i class="fas fa-map-marker-alt"></i> Bengaluru ▾</div></div>
</div>
<i class="fas fa-bell" style="color:#666;font-size:18px"></i>
</div>
<div class="search-wrap">
<div class="search"><i class="fas fa-search" style="color:#999"></i><input placeholder="Where do you want to go today? Flights, Hotels, etc."><i class="fas fa-sliders-h" style="color:#999"></i></div>
</div>
<div class="quick-ride">
<h3><i class="fas fa-bolt" style="color:#F27B21"></i> Quick Ride</h3>
<div class="loc-row"><input value="Current Location" readonly><input placeholder="Where to?"></div>
<div class="vehicles">
<div class="veh"><i class="fas fa-motorcycle"></i><div>Bike</div><div class="price">₹45</div></div>
<div class="veh"><i class="fas fa-shuttle-van"></i><div>Auto</div><div class="price">₹75</div></div>
<div class="veh active"><i class="fas fa-car"></i><div>Car</div><div class="price">₹120</div></div>
</div>
<div class="ride-footer">
<div class="ride-info">8 Minutes &nbsp;|&nbsp; Est Fare: <strong>₹120</strong></div>
<a href="quick-ride.php" class="btn-orange">Book Ride Now</a>
</div>
</div>
<div class="banner">Explore the World Smartly with TRIPNOVAA</div>
<div class="services">
<a href="bus-booking.php" class="svc"><div class="svc-icon"><i class="fas fa-bus"></i></div><span>Book Now</span></a>
<a href="bus-booking.php" class="svc"><div class="svc-icon"><i class="fas fa-plane"></i></div><span>Flights</span></a>
<a href="hotel-booking.php" class="svc"><div class="svc-icon"><i class="fas fa-hotel"></i></div><span>Hotels</span></a>
<a href="bus-booking.php" class="svc"><div class="svc-icon"><i class="fas fa-bus"></i></div><span>Buses</span></a>
<a href="train-booking.php" class="svc"><div class="svc-icon"><i class="fas fa-train"></i></div><span>Trains</span></a>
<a href="group-tours.php" class="svc"><div class="svc-icon"><i class="fas fa-route"></i></div><span>Tours</span></a>
</div>
<div class="section">
<div class="section-head"><h3>Popular Destinations</h3><a href="#">View All</a></div>
<div class="dest-scroll">
<?php foreach ($destinations as $d): ?>
<a href="plan-trip.php?to=<?= urlencode($d['name']) ?>" class="dest-card">
<img src="<?= e($d['image_url']) ?>" alt="">
<div class="dest-info"><h4><?= e($d['name']) ?></h4><div class="price">₹<?= number_format($d['price']) ?></div><div style="font-size:11px;color:#888">★ <?= $d['rating'] ?></div></div>
</a>
<?php endforeach; ?>
</div>
</div>
<div class="section">
<div class="section-head"><h3>Exclusive Offers</h3></div>
<?php foreach ($offers as $o): ?>
<div class="offer-card"><div style="flex:1"><strong><?= e($o['title']) ?></strong><div style="font-size:12px;color:#007BFF">Use Code: <?= e($o['code']) ?></div></div><i class="fas fa-plane" style="font-size:24px;color:#007BFF"></i></div>
<?php endforeach; ?>
</div>
<nav class="bottom-nav">
<a href="home.php" class="active"><i class="fas fa-home"></i>Home</a>
<a href="quick-ride.php"><i class="fas fa-car"></i>Quick Ride</a>
<a href="plan-trip.php"><div class="fab-btn"><i class="fas fa-plus"></i></div></a>
<a href="my-bookings.php"><i class="fas fa-calendar"></i>Bookings</a>
<a href="profile.php"><i class="fas fa-user"></i>Profile</a>
</nav>
</body>
</html>
