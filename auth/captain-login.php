<?php
require_once __DIR__ . '/../config/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = db()->prepare('SELECT * FROM users WHERE (email = ? OR phone = ?) AND role = "captain" AND status = "active"');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            loginUser($user);
            $_SESSION['pending_otp_phone'] = $user['phone'];
            header('Location: captain-otp.php');
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
<title>Captain Login - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Poppins,sans-serif;background:#1A1A2E;min-height:100vh;max-width:430px;margin:0 auto;padding:24px;color:#fff}
.logo{text-align:center;margin:24px 0}
.logo-icon{width:60px;height:60px;background:linear-gradient(135deg,#F27B21,#FF7F00);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:28px;font-weight:700}
.form{background:#252540;border-radius:16px;padding:24px}
.form h2{color:#F27B21;margin-bottom:20px;text-align:center}
.field{margin-bottom:16px}
.field label{display:block;font-size:13px;color:#aaa;margin-bottom:6px}
.field input{width:100%;padding:14px;border:1px solid #444;border-radius:10px;font-size:15px;background:#1A1A2E;color:#fff}
.btn{width:100%;padding:16px;background:#FF7F00;color:#fff;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;margin-top:8px}
.error{background:#4a2020;color:#ff6b6b;padding:12px;border-radius:8px;margin-bottom:16px;font-size:13px}
.links{text-align:center;margin-top:16px;font-size:13px}
.links a{color:#F27B21;text-decoration:none}
</style>
</head>
<body>
<div class="logo"><div class="logo-icon">T</div></div>
<form class="form" method="POST">
<h2>Captain Login</h2>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?= csrfToken() ?>">
<div class="field"><label>Phone or Email</label><input type="text" name="login" required></div>
<div class="field"><label>Password</label><input type="password" name="password" required></div>
<button type="submit" class="btn">Login as Captain →</button>
<p class="links">Don't have an account? <a href="captain-register.php">Sign Up</a></p>
<p class="links" style="margin-top:12px"><a href="login-select.php">← Back</a></p>
</form>
</body>
</html>
