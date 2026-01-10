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
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '674654993812-krpej9648d2205dqpls1dsq7tuhvlbft.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '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');

// Build redirect URI with proper protocol and domain handling
// Google OAuth requires exact match, including trailing slashes and protocols
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dirPath = dirname($_SERVER['PHP_SELF'] ?? '/');
$dirPath = ($dirPath === '/' || $dirPath === '\\') ? '' : $dirPath;

// Construct the redirect URI
define('GOOGLE_REDIRECT_URI', $protocol . '://' . $host . $dirPath . '/google-callback.php');
define('GOOGLE_OAUTH_ENABLED', !empty(GOOGLE_CLIENT_ID) && !empty(GOOGLE_CLIENT_SECRET));

// Session Configuration
define('SESSION_LIFETIME', 86400 * 7); // 7 days in seconds

// Cache Configuration
define('CACHE_DIR', sys_get_temp_dir() . '/legendhouse_v10/');
define('CACHE_TTL', 1800); // 30 minutes
define('SEARCH_CACHE_TTL', 600); // 10 minutes

// AI API Configuration (for advanced AI features)
// Using Blackbox AI free API - no key required for basic usage
define('BLACKBOX_API_KEY', getenv('BLACKBOX_API_KEY') ?: 'free');
define('BLACKBOX_API_ENDPOINT', getenv('BLACKBOX_API_ENDPOINT') ?: 'https://www.blackbox.ai/api/chat');

// AI is enabled - using free Blackbox API
define('AI_FEATURES_ENABLED', true);

// Google AdSense Configuration
define('GOOGLE_ADSENSE_CLIENT', 'ca-pub-1940810089559549');
