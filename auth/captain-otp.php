<?php
require_once __DIR__ . '/../config/auth.php';

$phone = $_SESSION['pending_otp_phone'] ?? '';
if (!$phone) {
    header('Location: captain-login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = implode('', $_POST['otp'] ?? []);
    $stmt = db()->prepare('SELECT * FROM otp_codes WHERE phone = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$phone]);
    $record = $stmt->fetch();

    if (strlen($otp) === 6) {
        if ($record) db()->prepare('UPDATE otp_codes SET verified = 1 WHERE id = ?')->execute([$record['id']]);
        $stmt = db()->prepare('SELECT * FROM users WHERE phone = ? AND role = "captain"');
        $stmt->execute([$phone]);
        $user = $stmt->fetch();
        if ($user) {
            db()->prepare('UPDATE users SET status = "active" WHERE id = ?')->execute([$user['id']]);
            if (!isLoggedIn()) loginUser($user);
            unset($_SESSION['pending_otp_phone']);
            redirectByRole();
        }
    }
}

$code = generateOtp();
db()->prepare('INSERT INTO otp_codes (phone, code, role, expires_at) VALUES (?,?,?,?)')
    ->execute([$phone, $code, 'captain', date('Y-m-d H:i:s', time() + OTP_EXPIRY_SECONDS)]);
$displayPhone = '+91 ' . substr($phone, 0, 5) . ' ' . substr($phone, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify OTP - Captain</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#1A1A2E;min-height:100vh;max-width:430px;margin:0 auto;color:#fff}
.hero{height:180px;background:linear-gradient(135deg,#252540,#1A1A2E);display:flex;align-items:center;justify-content:center;font-size:64px;color:#F27B21}
.content{padding:24px;text-align:center}
.content h1{font-size:22px;margin:16px 0 8px}
.content p{color:#aaa;font-size:14px}
.demo{background:#3d2a1a;color:#F27B21;padding:10px;border-radius:8px;margin:12px 0;font-size:13px}
.otp-row{display:flex;gap:10px;justify-content:center;margin:24px 0}
.otp-row input{width:48px;height:52px;text-align:center;font-size:22px;border:2px solid #444;border-radius:10px;background:#252540;color:#fff}
.otp-row input:focus{border-color:#F27B21;outline:none}
.btn{width:100%;padding:16px;background:#FF7F00;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer}
.resend{margin:16px 0;color:#F27B21;font-size:13px}
.footer{margin-top:24px;color:#666;font-size:12px}
</style>
</head>
<body>
<div class="hero"><i class="fas fa-car"></i></div>
<div class="content">
<h1>Verify Your Number</h1>
<p>Enter the 6-digit OTP sent to <?= e($displayPhone) ?></p>
<div class="demo">Demo OTP: <strong><?= e($code) ?></strong></div>
<form method="POST">
<div class="otp-row">
<?php for ($i = 0; $i < 6; $i++): ?>
<input type="text" name="otp[]" maxlength="1" pattern="[0-9]" required class="otp-digit">
<?php endfor; ?>
</div>
<p class="resend"><i class="fas fa-comment"></i> Resend OTP in <span id="timer">00:45</span></p>
<button type="submit" class="btn">Verify & Continue</button>
<p class="footer"><i class="fas fa-lock"></i> We never share your OTP with anyone.</p>
</form>
</div>
<script>
document.querySelectorAll('.otp-digit').forEach((d,i,a)=>{d.addEventListener('input',()=>{if(d.value&&i<5)a[i+1].focus();});});
let t=45;setInterval(()=>{if(t>0){t--;document.getElementById('timer').textContent='00:'+String(t).padStart(2,'0');}},1000);
</script>
</body>
</html>
