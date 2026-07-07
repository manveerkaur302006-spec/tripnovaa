<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#f5f7fa;min-height:100vh;max-width:430px;margin:0 auto;padding:24px}
.logo{text-align:center;margin:32px 0}
.logo-icon{width:60px;height:60px;background:linear-gradient(135deg,#1A4980,#F27B21);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:28px;font-weight:700}
.logo h2{color:#1A4980;margin-top:12px;font-size:22px}
.cards{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:32px 0}
.card{background:#fff;border-radius:16px;padding:24px 16px;text-align:center;text-decoration:none;color:inherit;box-shadow:0 4px 15px rgba(0,0,0,.08);transition:transform .2s}
.card:hover{transform:translateY(-4px)}
.card img{width:80px;height:80px;object-fit:contain;margin-bottom:12px}
.card h3{font-size:16px;color:#333}
.card p{font-size:12px;color:#888;margin-top:4px}
.social{text-align:center;margin-top:40px}
.social p{color:#888;font-size:13px;margin-bottom:16px}
.social-icons{display:flex;justify-content:center;gap:16px}
.social-icons a{width:44px;height:44px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.1);color:#333;font-size:18px;text-decoration:none}
</style>
</head>
<body>
<div class="logo">
<div class="logo-icon">T</div>
<h2>TRIPNOVAA</h2>
</div>
<div class="cards">
<a href="user-login.php" class="card">
<div style="font-size:48px;color:#007BFF"><i class="fas fa-user"></i></div>
<h3>User Login</h3>
<p>Book rides & trips</p>
</a>
<a href="captain-login.php" class="card">
<div style="font-size:48px;color:#F27B21"><i class="fas fa-id-card"></i></div>
<h3>Captain Login</h3>
<p>Drive & earn</p>
</a>
</div>
<div class="social">
<p>or continue with</p>
<div class="social-icons">
<a href="#"><i class="fab fa-google"></i></a>
<a href="#"><i class="fab fa-facebook-f"></i></a>
<a href="#"><i class="fab fa-apple"></i></a>
</div>
</div>
</body>
</html>
