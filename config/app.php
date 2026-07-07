<?php
define('BASE_URL', 'http://localhost/TRIPNOVAA');
define('APP_NAME', 'TRIPNOVAA');

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
