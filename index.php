<?php
require_once __DIR__ . '/config/auth.php';
if (isLoggedIn()) {
    redirectByRole();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#fff;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center}
.logo-wrap{text-align:center;animation:fadeIn 1s ease}
.logo-icon{width:100px;height:100px;background:linear-gradient(135deg,#1A4980,#F27B21);border-radius:24px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:48px;font-weight:700;color:#fff}
.logo-text{font-size:28px;font-weight:700;color:#1A4980;letter-spacing:2px}
.tagline{color:#888;font-size:14px;margin-top:8px}
.mountains{position:fixed;bottom:0;left:0;right:0;height:120px;background:linear-gradient(transparent,#e8eef3);clip-path:polygon(0 100%,15% 60%,30% 80%,45% 50%,60% 70%,75% 40%,90% 65%,100% 45%,100% 100%)}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<div class="logo-wrap">
<div class="logo-icon">T</div>
<div class="logo-text">TRIPNOVAA</div>
<div class="tagline">Travel. Book. Explore.</div>
</div>
<div class="mountains"></div>
<script>setTimeout(()=>location.href='auth/onboarding.php',2000);</script>
</body>
</html>
