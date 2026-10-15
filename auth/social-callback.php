<?php
require_once __DIR__ . '/../config/auth.php';

function socialLoginError(string $message): void
{
    $_SESSION['social_login_error'] = $message;
    header('Location: login-select.php');
    exit;
}

function postForm(string $url, array $data): array
{
    $context = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => http_build_query($data), 'ignore_errors' => true, 'timeout' => 15]]);
    $response = @file_get_contents($url, false, $context);
    $decoded = json_decode($response ?: '', true);
    return is_array($decoded) ? $decoded : [];
}

$provider = $_SESSION['social_oauth_provider'] ?? '';
$state = $_SESSION['social_oauth_state'] ?? '';
unset($_SESSION['social_oauth_provider'], $_SESSION['social_oauth_state']);

if (!$provider || !$state || !isset($_GET['state']) || !hash_equals($state, $_GET['state']) || empty($_GET['code'])) {
    socialLoginError('Unable to verify your social sign-in. Please try again.');
}

$callback = BASE_URL . '/auth/social-callback.php';
if ($provider === 'google') {
    $token = postForm('https://oauth2.googleapis.com/token', ['code' => $_GET['code'], 'client_id' => GOOGLE_CLIENT_ID, 'client_secret' => GOOGLE_CLIENT_SECRET, 'redirect_uri' => $callback, 'grant_type' => 'authorization_code']);
    $accessToken = $token['access_token'] ?? '';
    $profile = $accessToken ? json_decode(@file_get_contents('https://openidconnect.googleapis.com/v1/userinfo', false, stream_context_create(['http' => ['header' => "Authorization: Bearer {$accessToken}\r\n", 'timeout' => 15]])) ?: '', true) : [];
    $email = !empty($profile['email_verified']) ? ($profile['email'] ?? '') : '';
    $name = $profile['name'] ?? '';
} elseif ($provider === 'facebook') {
    $token = postForm('https://graph.facebook.com/oauth/access_token', ['code' => $_GET['code'], 'client_id' => FACEBOOK_CLIENT_ID, 'client_secret' => FACEBOOK_CLIENT_SECRET, 'redirect_uri' => $callback]);
    $accessToken = $token['access_token'] ?? '';
    $profile = $accessToken ? json_decode(@file_get_contents('https://graph.facebook.com/me?fields=id,name,email&access_token=' . urlencode($accessToken)), true) : [];
    $email = $profile['email'] ?? '';
    $name = $profile['name'] ?? '';
    // Facebook may not provide an email (for example, when an account has no
    // confirmed email). Its immutable user ID still lets that person sign in.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($profile['id'])) {
        $email = 'facebook-' . preg_replace('/[^0-9]/', '', (string) $profile['id']) . '@social.tripnovaa.local';
    }
} else {
    socialLoginError('Unsupported social sign-in provider.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    socialLoginError('Your social account did not provide a verified email address.');
}

$find = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$find->execute([$email]);
$user = $find->fetch();
if (!$user) {
    // Phone is required by the current schema. This unique placeholder is replaced when the user adds a real number.
    $phone = 'social' . substr(hash('sha256', $provider . ':' . strtolower($email)), 0, 13);
    $create = db()->prepare('INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, "customer")');
    $create->execute([$name ?: 'TRIPNOVAA User', $email, $phone, password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT)]);
    $find->execute([$email]);
    $user = $find->fetch();
}

if (($user['status'] ?? '') !== 'active') {
    socialLoginError('This account is not active. Please contact support.');
}
loginUser($user);
redirectByRole();
