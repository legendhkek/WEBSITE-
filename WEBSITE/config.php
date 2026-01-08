<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Configuration File
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * IMPORTANT: For production, use environment variables instead of hardcoding credentials
 * Example: $_ENV['GOOGLE_CLIENT_ID'] or getenv('GOOGLE_CLIENT_ID')
 */

// Database Configuration
define('DB_FILE', __DIR__ . '/users.db');

// Google OAuth Configuration
// NOTE: Replace these with your own credentials or use environment variables
// Get credentials from: https://console.cloud.google.com/
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('GOOGLE_REDIRECT_URI', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/google-callback.php");
define('GOOGLE_OAUTH_ENABLED', !empty(GOOGLE_CLIENT_ID) && !empty(GOOGLE_CLIENT_SECRET));

// Session Configuration
define('SESSION_LIFETIME', 86400 * 7); // 7 days in seconds

// Cache Configuration
define('CACHE_DIR', sys_get_temp_dir() . '/legendhouse_v10/');
define('CACHE_TTL', 1800); // 30 minutes
define('SEARCH_CACHE_TTL', 600); // 10 minutes

// Blackbox API Configuration (for advanced AI features)
// Get your API key from: https://www.blackbox.ai/
// Leave empty or set to placeholder value to disable AI features
define('BLACKBOX_API_KEY', getenv('BLACKBOX_API_KEY') ?: '');
define('BLACKBOX_API_ENDPOINT', 'https://api.blackbox.ai/v1/chat/completions');
define('AI_FEATURES_ENABLED', !empty(BLACKBOX_API_KEY) && BLACKBOX_API_KEY !== 'YOUR_BLACKBOX_API_KEY_HERE');

// Google AdSense Configuration
define('GOOGLE_ADSENSE_CLIENT', 'ca-pub-1940810089559549');
