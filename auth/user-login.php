<?php
require_once __DIR__ . '/../config/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = db()->prepare('SELECT * FROM users WHERE (email = ? OR phone = ?) AND role = "customer" AND status = "active"');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            loginUser($user);
            $_SESSION['pending_otp_phone'] = $user['phone'];
            header('Location: user-otp.php');
            exit;
        }
        $error = 'Invalid credentials. Try password: password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Login - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#f5f7fa;min-height:100vh;max-width:430px;margin:0 auto;padding:24px}
.logo{text-align:center;margin:24px 0}
.logo-icon{width:60px;height:60px;background:linear-gradient(135deg,#1A4980,#F27B21);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:28px;font-weight:700}
.form{background:#fff;border-radius:16px;padding:24px;box-shadow:0 4px 15px rgba(0,0,0,.08)}
.form h2{color:#1A4980;margin-bottom:20px;text-align:center}
.field{margin-bottom:16px}
.field label{display:block;font-size:13px;color:#666;margin-bottom:6px}
.field input{width:100%;padding:14px;border:1px solid #ddd;border-radius:10px;font-size:15px}
.btn{width:100%;padding:16px;background:#007BFF;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;margin-top:8px}
.error{background:#fee;color:#c00;padding:12px;border-radius:8px;margin-bottom:16px;font-size:13px}
.links{text-align:center;margin-top:16px;font-size:13px}
.links a{color:#007BFF;text-decoration:none}
</style>
</head>
<body>
<div class="logo"><div class="logo-icon">T</div></div>
<form class="form" method="POST">
<h2>User Login</h2>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?= csrfToken() ?>">
<div class="field"><label>Phone Number or Email</label><input type="text" name="login" placeholder="8870463212 or email" required></div>
<div class="field"><label>Password</label><input type="password" name="password" required></div>
<button type="submit" class="btn">Login</button>
<p class="links"><a href="#">Forgot Password?</a> &nbsp;|&nbsp; <a href="user-register.php">Register</a></p>
<p class="links" style="margin-top:12px"><a href="login-select.php">← Back</a></p>
</form>
</body>
</html>
