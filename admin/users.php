<?php
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
