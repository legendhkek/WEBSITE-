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
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '[REDACTED]');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '[REDACTED]');
define('GOOGLE_REDIRECT_URI', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/google-callback.php");

// Session Configuration
define('SESSION_LIFETIME', 86400 * 7); // 7 days in seconds

// Cache Configuration
define('CACHE_DIR', sys_get_temp_dir() . '/legendhouse_v9/');
define('CACHE_TTL', 1800); // 30 minutes
define('SEARCH_CACHE_TTL', 600); // 10 minutes
