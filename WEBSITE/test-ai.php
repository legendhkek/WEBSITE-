<?php
/**
 * AI Test Script - Test if AI providers are working
 * Access this file directly in your browser: yourdomain.com/test-ai.php
 */

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ai-helper.php';

echo "<html><head><title>AI Test - Legend House</title>";
echo "<style>
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #0d1117; color: #e6edf3; }
h1 { color: #58a6ff; }
h2 { color: #7ee787; margin-top: 30px; }
.success { background: #238636; padding: 15px; border-radius: 8px; margin: 10px 0; }
.error { background: #da3633; padding: 15px; border-radius: 8px; margin: 10px 0; }
.info { background: #1f6feb; padding: 15px; border-radius: 8px; margin: 10px 0; }
.warning { background: #9e6a03; padding: 15px; border-radius: 8px; margin: 10px 0; }
pre { background: #161b22; padding: 15px; border-radius: 8px; overflow-x: auto; white-space: pre-wrap; }
code { background: #30363d; padding: 2px 6px; border-radius: 4px; }
.response-box { background: #21262d; padding: 20px; border-radius: 8px; border: 1px solid #30363d; margin: 15px 0; }
</style></head><body>";

echo "<h1>🤖 Legend House AI Test</h1>";

// Check config
echo "<h2>📋 Configuration Check</h2>";

$apiKey = defined('BLACKBOX_API_KEY') ? BLACKBOX_API_KEY : 'not defined';
$endpoint = defined('BLACKBOX_API_ENDPOINT') ? BLACKBOX_API_ENDPOINT : 'not defined';
$aiEnabled = defined('AI_FEATURES_ENABLED') ? (AI_FEATURES_ENABLED ? 'Yes' : 'No') : 'not defined';

echo "<div class='info'>";
echo "<strong>AI Features Enabled:</strong> " . $aiEnabled . "<br>";
echo "<strong>Blackbox API Key:</strong> " . ($apiKey === 'free' ? '<code>free</code> (default - no custom key)' : '<code>Custom key set (' . strlen($apiKey) . ' chars)</code>') . "<br>";
echo "<strong>Blackbox Endpoint:</strong> <code>" . htmlspecialchars($endpoint) . "</code><br>";
echo "</div>";

if ($apiKey === 'free') {
    echo "<div class='warning'>";
    echo "⚠️ <strong>No custom API key detected!</strong><br>";
    echo "You're using the free mode. To use your Blackbox API key, edit <code>config.php</code> and change:<br>";
    echo "<pre>define('BLACKBOX_API_KEY', getenv('BLACKBOX_API_KEY') ?: 'YOUR_API_KEY_HERE');</pre>";
    echo "</div>";
}

// Test message
$testMessage = "Say hello in one sentence.";

echo "<h2>🧪 Testing AI Providers</h2>";
echo "<p>Test message: <code>" . htmlspecialchars($testMessage) . "</code></p>";

// Test each provider individually
$providers = [
    'DuckDuckGo AI' => 'callDuckDuckGoAI',
    'Blackbox AI' => 'callBlackbox',
    'DeepInfra' => 'callDeepInfra',
    'HuggingFace' => 'callHuggingFace'
];

$systemPrompt = "You are a helpful assistant. Be brief.";
$workingProviders = [];

foreach ($providers as $name => $function) {
    echo "<h3>Testing: $name</h3>";
    
    if (!function_exists($function)) {
        echo "<div class='error'>❌ Function <code>$function</code> not found!</div>";
        continue;
    }
    
    $startTime = microtime(true);
    
    try {
        $response = $function($testMessage, $systemPrompt, []);
        $duration = round((microtime(true) - $startTime) * 1000);
        
        if ($response && strlen($response) > 5) {
            echo "<div class='success'>✅ <strong>$name is WORKING!</strong> (Response time: {$duration}ms)</div>";
            echo "<div class='response-box'><strong>Response:</strong><br>" . nl2br(htmlspecialchars($response)) . "</div>";
            $workingProviders[] = $name;
        } else {
            echo "<div class='error'>❌ <strong>$name returned empty response</strong> (Time: {$duration}ms)</div>";
        }
    } catch (Exception $e) {
        $duration = round((microtime(true) - $startTime) * 1000);
        echo "<div class='error'>❌ <strong>$name failed:</strong> " . htmlspecialchars($e->getMessage()) . " (Time: {$duration}ms)</div>";
    }
}

// Test the main function
echo "<h2>🔄 Testing Main AI Function (with fallback)</h2>";
echo "<p>This uses all providers with automatic fallback.</p>";

$startTime = microtime(true);
$mainResponse = getAIResponse("What is 2+2? Answer in one word.", 'general', []);
$duration = round((microtime(true) - $startTime) * 1000);

if ($mainResponse && strlen($mainResponse) > 3) {
    echo "<div class='success'>✅ <strong>Main AI function is WORKING!</strong> (Response time: {$duration}ms)</div>";
    echo "<div class='response-box'><strong>Response:</strong><br>" . nl2br(htmlspecialchars($mainResponse)) . "</div>";
} else {
    echo "<div class='error'>❌ <strong>Main AI function failed to get response</strong></div>";
}

// Summary
echo "<h2>📊 Summary</h2>";

if (count($workingProviders) > 0) {
    echo "<div class='success'>";
    echo "✅ <strong>" . count($workingProviders) . " provider(s) working:</strong> " . implode(', ', $workingProviders);
    echo "</div>";
    echo "<p>Your AI chat should be functional! The system will automatically use the first working provider.</p>";
} else {
    echo "<div class='error'>";
    echo "❌ <strong>No providers are working!</strong><br>";
    echo "This could be due to:<br>";
    echo "• Network/firewall blocking outgoing requests<br>";
    echo "• API rate limits<br>";
    echo "• Server doesn't have <code>curl</code> extension enabled<br>";
    echo "</div>";
}

// Check curl
echo "<h2>🔧 Server Info</h2>";
echo "<div class='info'>";
echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br>";
echo "<strong>cURL Enabled:</strong> " . (function_exists('curl_init') ? '✅ Yes' : '❌ No') . "<br>";
echo "<strong>JSON Enabled:</strong> " . (function_exists('json_encode') ? '✅ Yes' : '❌ No') . "<br>";
echo "</div>";

echo "<p style='margin-top: 40px; color: #8b949e;'><a href='dashboard.php' style='color: #58a6ff;'>← Back to Dashboard</a> | Generated at " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
