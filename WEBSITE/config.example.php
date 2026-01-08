<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Configuration File (EXAMPLE)
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Copy this file to config.php and update with your credentials
 */

// Database Configuration
define('DB_FILE', __DIR__ . '/users.db');

// Google OAuth Configuration
// Get your credentials from: https://console.cloud.google.com/
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/google-callback.php");

// Session Configuration
define('SESSION_LIFETIME', 86400 * 7); // 7 days in seconds

// Cache Configuration
define('CACHE_DIR', sys_get_temp_dir() . '/legendhouse_v10/');
define('CACHE_TTL', 1800); // 30 minutes
define('SEARCH_CACHE_TTL', 600); // 10 minutes

// Blackbox API Configuration (for advanced AI features)
// Get your API key from: https://www.blackbox.ai/
define('BLACKBOX_API_KEY', getenv('BLACKBOX_API_KEY') ?: 'YOUR_BLACKBOX_API_KEY_HERE');
define('BLACKBOX_API_ENDPOINT', 'https://api.blackbox.ai/v1/chat/completions');

// Google AdSense Configuration
define('GOOGLE_ADSENSE_CLIENT', 'ca-pub-1940810089559549');
