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

        if (strlen($phone) < 10 || strlen($password) < 6) {
            $error = 'Please fill all fields correctly.';
        } else {
            $check = db()->prepare('SELECT id FROM users WHERE phone = ? OR email = ?');
            $check->execute([$phone, $email]);
            if ($check->fetch()) {
                $error = 'Phone or email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                db()->prepare('INSERT INTO users (name, email, phone, password_hash, role) VALUES (?,?,?,?,"customer")')
                    ->execute([$name, $email, $phone, $hash]);
                $_SESSION['pending_otp_phone'] = $phone;
                $_SESSION['pending_otp_role'] = 'customer';
                header('Location: user-otp.php');
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
<title>Register - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#f5f7fa;min-height:100vh;max-width:430px;margin:0 auto;padding:24px}
.form{background:#fff;border-radius:16px;padding:24px;box-shadow:0 4px 15px rgba(0,0,0,.08);margin-top:24px}
.form h2{color:#1A4980;margin-bottom:20px;text-align:center}
.field{margin-bottom:14px}
.field label{display:block;font-size:13px;color:#666;margin-bottom:6px}
.field input{width:100%;padding:14px;border:1px solid #ddd;border-radius:10px;font-size:15px}
.btn{width:100%;padding:16px;background:#007BFF;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer}
.error{background:#fee;color:#c00;padding:12px;border-radius:8px;margin-bottom:16px;font-size:13px}
.links{text-align:center;margin-top:16px;font-size:13px}
.links a{color:#007BFF;text-decoration:none}
</style>
</head>
<body>
<form class="form" method="POST">
<h2>Create Account</h2>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?= csrfToken() ?>">
<div class="field"><label>Full Name</label><input type="text" name="name" required></div>
<div class="field"><label>Email</label><input type="email" name="email" required></div>
<div class="field"><label>Phone Number</label><input type="tel" name="phone" required></div>
<div class="field"><label>Password</label><input type="password" name="password" minlength="6" required></div>
<button type="submit" class="btn">Register</button>
<p class="links"><a href="user-login.php">Already have an account? Login</a></p>
</form>
</body>
</html>
