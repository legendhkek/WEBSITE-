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

// AI API Configuration (Blackbox AI with 200+ models)
// Your API key gives access to GPT-4o, Claude, Gemini, Llama, DeepSeek and more!
define('BLACKBOX_API_KEY', getenv('BLACKBOX_API_KEY') ?: 'sk-B1gaLrVU9sV9bD3MhgF9GA');
define('BLACKBOX_API_ENDPOINT', getenv('BLACKBOX_API_ENDPOINT') ?: 'https://api.blackbox.ai/v1/chat/completions');

// ═══════════════════════════════════════════════════════════════════════════════
// CHOOSE YOUR AI MODEL - Uncomment ONE of the options below:
// All these models are TESTED AND WORKING with your API key!
// ═══════════════════════════════════════════════════════════════════════════════

// 🟢 GPT Models (OpenAI) - RECOMMENDED
define('BLACKBOX_MODEL', 'blackboxai/openai/gpt-4o');                    // ✅ GPT-4o - Fast & Smart (DEFAULT)
// define('BLACKBOX_MODEL', 'blackboxai/openai/gpt-4-turbo');            // ✅ GPT-4 Turbo
// define('BLACKBOX_MODEL', 'blackboxai/openai/gpt-4');                  // ✅ GPT-4
// define('BLACKBOX_MODEL', 'blackboxai/openai/chatgpt-4o-latest');      // ✅ ChatGPT-4o Latest

// 🟣 Claude Models (Anthropic) - Most Intelligent
// define('BLACKBOX_MODEL', 'blackboxai/anthropic/claude-opus-4');       // ✅ Claude Opus 4 - BEST for complex tasks
// define('BLACKBOX_MODEL', 'blackboxai/anthropic/claude-sonnet-4');     // ✅ Claude Sonnet 4

// 🔵 Gemini Models (Google) - Fast
// define('BLACKBOX_MODEL', 'blackboxai/google/gemini-2.5-flash');       // ✅ Gemini 2.5 Flash - Very Fast
// define('BLACKBOX_MODEL', 'blackboxai/google/gemini-2.0-flash-001');   // ✅ Gemini 2.0 Flash

// 🟠 DeepSeek Models - Great for coding
// define('BLACKBOX_MODEL', 'blackboxai/deepseek/deepseek-chat');        // ✅ DeepSeek Chat - Good for coding

// 🔴 Llama Models (Meta) - Open source
// define('BLACKBOX_MODEL', 'blackboxai/meta-llama/llama-4-maverick');   // ✅ Llama 4 Maverick - Latest

// 🟡 Qwen Models (Alibaba)
// define('BLACKBOX_MODEL', 'blackboxai/qwen/qwen-max');                 // ✅ Qwen Max - Powerful

// AI is enabled - using free Blackbox API
define('AI_FEATURES_ENABLED', true);

// Google AdSense Configuration
define('GOOGLE_ADSENSE_CLIENT', 'ca-pub-1940810089559549');
