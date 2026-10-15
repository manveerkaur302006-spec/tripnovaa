<?php
/**
 * Build the application's public URL from the request currently serving it.
 * This keeps local XAMPP installs (for example /TRIPNOVAA) working while
 * preventing production redirects from sending visitors to localhost.
 */
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
$basePath = preg_replace('#/(?:admin|api|auth|captain|config|customer)(?:/.*)?$#', '', $script);
$basePath = preg_replace('#/index\\.php$#', '', $basePath);
$basePath = rtrim($basePath, '/');

define('BASE_URL', $scheme . '://' . $host . $basePath);
define('APP_NAME', 'TRIPNOVAA');

// Social sign-in credentials are kept in a local, Git-ignored file. Server
// environment variables take priority, which is useful for production hosts.
$oauthConfigFile = __DIR__ . '/social-oauth.php';
$oauthConfig = is_file($oauthConfigFile) ? require $oauthConfigFile : [];
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: ($oauthConfig['google_client_id'] ?? ''));
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: ($oauthConfig['google_client_secret'] ?? ''));
define('FACEBOOK_CLIENT_ID', getenv('FACEBOOK_CLIENT_ID') ?: ($oauthConfig['facebook_client_id'] ?? ''));
define('FACEBOOK_CLIENT_SECRET', getenv('FACEBOOK_CLIENT_SECRET') ?: ($oauthConfig['facebook_client_secret'] ?? ''));

// Mock modes — set to false when real API keys are available
define('CASHFREE_MOCK', true);
define('TICKET_API_MOCK', true);

// Cashfree credentials (sandbox)
define('CASHFREE_CLIENT_ID', 'TEST_xxxxx');
define('CASHFREE_CLIENT_SECRET', 'TEST_xxxxx');
define('CASHFREE_ENV', 'SANDBOX');

// Ticket API credentials
define('TICKET_API_KEY', '');
define('TICKET_API_URL', 'https://api.aopay.in/v2');

define('OTP_EXPIRY_SECONDS', 45);
define('DEFAULT_LAT', 12.9716);
define('DEFAULT_LNG', 77.5946);
