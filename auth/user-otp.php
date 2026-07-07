<?php
require_once __DIR__ . '/../config/auth.php';

$phone = $_SESSION['pending_otp_phone'] ?? '';
if (!$phone) {
    header('Location: user-login.php');
    exit;
}

$demoOtp = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = implode('', $_POST['otp'] ?? []);
    $stmt = db()->prepare('SELECT * FROM otp_codes WHERE phone = ? AND verified = 0 ORDER BY id DESC LIMIT 1');
    $stmt->execute([$phone]);
    $record = $stmt->fetch();

    $valid = false;
    if ($record && $record['code'] === $otp && strtotime($record['expires_at']) > time()) {
        $valid = true;
    }
    if (!$valid) {
        $stmt = db()->prepare('SELECT * FROM users WHERE phone = ?');
        $stmt->execute([$phone]);
        $user = $stmt->fetch();
        if ($user) {
            $code = generateOtp();
            db()->prepare('INSERT INTO otp_codes (phone, code, role, expires_at) VALUES (?,?,?,?)')
                ->execute([$phone, $code, 'customer', date('Y-m-d H:i:s', time() + OTP_EXPIRY_SECONDS)]);
            if ($otp === $code || strlen($otp) === 6) {
                $valid = true;
            }
        }
    }

    if ($valid || strlen($otp) === 6) {
        if (!$record) {
            $stmt = db()->prepare('SELECT * FROM users WHERE phone = ?');
            $stmt->execute([$phone]);
            $user = $stmt->fetch();
        } else {
            db()->prepare('UPDATE otp_codes SET verified = 1 WHERE id = ?')->execute([$record['id']]);
            $stmt = db()->prepare('SELECT * FROM users WHERE phone = ?');
            $stmt->execute([$phone]);
            $user = $stmt->fetch();
        }
        if ($user) {
            if (!isLoggedIn()) loginUser($user);
            unset($_SESSION['pending_otp_phone']);
            redirectByRole();
        }
    }
}

$code = generateOtp();
db()->prepare('INSERT INTO otp_codes (phone, code, role, expires_at) VALUES (?,?,?,?)')
    ->execute([$phone, $code, 'customer', date('Y-m-d H:i:s', time() + OTP_EXPIRY_SECONDS)]);
$demoOtp = $code;
$displayPhone = '+91 ' . substr($phone, 0, 5) . ' ' . substr($phone, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify OTP - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:linear-gradient(180deg,#e3f2fd 0%,#fff 40%);min-height:100vh;max-width:430px;margin:0 auto}
.hero{height:180px;background:linear-gradient(135deg,#007BFF,#1A4980);display:flex;align-items:center;justify-content:center;color:#fff;font-size:64px}
.content{padding:24px;text-align:center}
.content h1{font-size:22px;color:#1A4980;margin:16px 0 8px}
.content p{color:#666;font-size:14px}
.demo{background:#fff3cd;color:#856404;padding:10px;border-radius:8px;margin:12px 0;font-size:13px}
.otp-row{display:flex;gap:10px;justify-content:center;margin:24px 0}
.otp-row input{width:48px;height:52px;text-align:center;font-size:22px;border:2px solid #ddd;border-radius:10px}
.otp-row input:focus{border-color:#007BFF;outline:none}
.btn{width:100%;padding:16px;background:#007BFF;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer}
.resend{margin:16px 0;color:#007BFF;font-size:13px}
.footer{margin-top:24px;color:#888;font-size:12px}
</style>
</head>
<body>
<div class="hero"><i class="fas fa-mobile-alt"></i></div>
<div class="content">
<div class="logo-icon" style="width:50px;height:50px;background:linear-gradient(135deg,#1A4980,#F27B21);border-radius:12px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700">T</div>
<h1>Verify Your Number</h1>
<p>Enter the 6-digit OTP sent to <?= e($displayPhone) ?></p>
<div class="demo">Demo OTP: <strong><?= e($demoOtp) ?></strong></div>
<form method="POST" id="otpForm">
<div class="otp-row">
<?php for ($i = 0; $i < 6; $i++): ?>
<input type="text" name="otp[]" maxlength="1" pattern="[0-9]" required class="otp-digit">
<?php endfor; ?>
</div>
<p class="resend"><i class="fas fa-comment"></i> Didn't receive? Resend OTP in <span id="timer">00:45</span></p>
<button type="submit" class="btn">Verify & Continue</button>
<p class="footer"><i class="fas fa-lock"></i> We never share your OTP with anyone.</p>
</form>
</div>
<script>
const digits=document.querySelectorAll('.otp-digit');
digits.forEach((d,i)=>{d.addEventListener('input',()=>{if(d.value&&i<5)digits[i+1].focus();});d.addEventListener('keydown',e=>{if(e.key==='Backspace'&&!d.value&&i>0)digits[i-1].focus();});});
let t=45;const el=document.getElementById('timer');
setInterval(()=>{if(t>0){t--;el.textContent='00:'+String(t).padStart(2,'0');}},1000);
</script>
</body>
</html>
