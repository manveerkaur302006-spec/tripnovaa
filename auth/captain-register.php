<?php
require_once __DIR__ . '/../config/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $vehicleType = $_POST['vehicle_type'] ?? 'sedan';
        $vehicleNumber = trim($_POST['vehicle_number'] ?? '');
        $license = trim($_POST['license_number'] ?? '');

        if (strlen($phone) < 10) {
            $error = 'Invalid phone number.';
        } else {
            $check = db()->prepare('SELECT id FROM users WHERE phone = ?');
            $check->execute([$phone]);
            if ($check->fetch()) {
                $error = 'Phone already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                db()->prepare('INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?,?,?,?,"captain","pending")')
                    ->execute([$name, $email, $phone, $hash]);
                $userId = (int) db()->lastInsertId();
                db()->prepare('INSERT INTO captain_profiles (user_id, vehicle_type, vehicle_number, license_number) VALUES (?,?,?,?)')
                    ->execute([$userId, $vehicleType, $vehicleNumber, $license]);
                $_SESSION['pending_otp_phone'] = $phone;
                $_SESSION['pending_otp_role'] = 'captain';
                header('Location: captain-otp.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Captain Register - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#1A1A2E;min-height:100vh;max-width:430px;margin:0 auto;padding:24px;color:#fff}
.logo{text-align:center;margin:16px 0}
.logo-icon{width:50px;height:50px;background:#F27B21;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:24px;font-weight:700}
.form{background:#252540;border-radius:16px;padding:20px}
.form h2{color:#F27B21;margin-bottom:16px;text-align:center;font-size:18px}
.field{margin-bottom:12px}
.field label{display:block;font-size:12px;color:#aaa;margin-bottom:4px}
.field input,.field select{width:100%;padding:12px;border:1px solid #444;border-radius:10px;font-size:14px;background:#1A1A2E;color:#fff}
.btn{width:100%;padding:16px;background:#FF7F00;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;margin-top:8px}
.error{background:#4a2020;color:#ff6b6b;padding:10px;border-radius:8px;margin-bottom:12px;font-size:13px}
</style>
</head>
<body>
<div class="logo"><div class="logo-icon">T</div><h3 style="margin-top:8px">Captain Login</h3></div>
<form class="form" method="POST">
<h2>Register as Captain</h2>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?= csrfToken() ?>">
<div class="field"><label>Name</label><input type="text" name="name" required></div>
<div class="field"><label>Phone Number</label><input type="tel" name="phone" required></div>
<div class="field"><label>Email</label><input type="email" name="email" required></div>
<div class="field"><label>Vehicle Type</label>
<select name="vehicle_type"><option value="bike">Bike</option><option value="auto">Auto</option><option value="mini">Mini</option><option value="sedan" selected>Sedan</option><option value="suv">SUV</option><option value="bus">Bus</option></select></div>
<div class="field"><label>Vehicle Number</label><input type="text" name="vehicle_number" placeholder="KA 01 AB 1234"></div>
<div class="field"><label>License Number</label><input type="text" name="license_number"></div>
<div class="field"><label>Password</label><input type="password" name="password" minlength="6" required></div>
<button type="submit" class="btn">Register as Captain</button>
</form>
</body>
</html>
