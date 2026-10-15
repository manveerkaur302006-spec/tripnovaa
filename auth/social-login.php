<?php
require_once __DIR__ . '/../config/auth.php';

$provider = $_GET['provider'] ?? '';
$providers = [
    'google' => ['id' => GOOGLE_CLIENT_ID, 'secret' => GOOGLE_CLIENT_SECRET, 'authorize' => 'https://accounts.google.com/o/oauth2/v2/auth', 'scope' => 'openid email profile'],
    // Leave the API version to the Meta app's configured default so a retired
    // Graph API version cannot break sign-in.
    'facebook' => ['id' => FACEBOOK_CLIENT_ID, 'secret' => FACEBOOK_CLIENT_SECRET, 'authorize' => 'https://www.facebook.com/dialog/oauth', 'scope' => 'email,public_profile'],
];

if (!isset($providers[$provider]) || !$providers[$provider]['id'] || !$providers[$provider]['secret']) {
    $_SESSION['social_login_error'] = 'Social sign-in is not configured yet. Please contact support.';
    header('Location: login-select.php');
    exit;
}

$state = bin2hex(random_bytes(32));
$_SESSION['social_oauth_state'] = $state;
$_SESSION['social_oauth_provider'] = $provider;
$callback = BASE_URL . '/auth/social-callback.php';
$params = [
    'client_id' => $providers[$provider]['id'],
    'redirect_uri' => $callback,
    'response_type' => 'code',
    'scope' => $providers[$provider]['scope'],
    'state' => $state,
];
if ($provider === 'google') {
    $params['access_type'] = 'online';
    $params['prompt'] = 'select_account';
}
header('Location: ' . $providers[$provider]['authorize'] . '?' . http_build_query($params));
exit;
