<?php
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
