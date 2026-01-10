<?php
/**
 * AI Test Script v3.0 - Test if AI providers are working
 * Access: legendbl.tech/test-ai.php
 */

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ai-helper.php';

// Simple test mode for AJAX
if (isset($_GET['test'])) {
    header('Content-Type: application/json');
    $testMessage = $_GET['message'] ?? 'Hello, how are you?';
    $response = getAIResponse($testMessage, 'general', []);
    echo json_encode([
        'success' => true,
        'message' => $testMessage,
        'response' => $response,
        'length' => strlen($response)
    ]);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>AI Test v3.0 - Legend House</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #0d1117;
            color: #e6edf3;
        }
        h1 { color: #58a6ff; }
        h2 { color: #7ee787; margin-top: 30px; }
        .success { background: #238636; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #da3633; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { background: #1f6feb; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .warning { background: #9e6a03; padding: 15px; border-radius: 8px; margin: 10px 0; }
        pre { background: #161b22; padding: 15px; border-radius: 8px; overflow-x: auto; white-space: pre-wrap; }
        code { background: #30363d; padding: 2px 6px; border-radius: 4px; }
        .response-box {
            background: #21262d;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #30363d;
            margin: 15px 0;
            white-space: pre-wrap;
        }
        .test-btn {
            background: #238636;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            margin: 10px 5px 10px 0;
            font-size: 14px;
        }
        .test-btn:hover { background: #2ea043; }
        .test-btn:disabled { background: #484f58; cursor: not-allowed; }
        input[type="text"] {
            background: #21262d;
            border: 1px solid #30363d;
            border-radius: 8px;
            padding: 12px;
            color: #e6edf3;
            width: 100%;
            max-width: 500px;
            margin: 10px 0;
        }
        #testResult {
            margin-top: 20px;
            min-height: 100px;
        }
    </style>
</head>
<body>
    <h1>🤖 Legend House AI Test v3.0</h1>
    
    <h2>📋 Configuration</h2>
    <div class="info">
        <strong>AI Features:</strong> <?php echo defined('AI_FEATURES_ENABLED') && AI_FEATURES_ENABLED ? '✅ Enabled' : '⚠️ Disabled'; ?><br>
        <strong>Blackbox API Key:</strong> <?php 
            $apiKey = defined('BLACKBOX_API_KEY') ? BLACKBOX_API_KEY : '';
            echo empty($apiKey) || $apiKey === 'free' ? '⚠️ Not configured' : '✅ Configured (' . strlen($apiKey) . ' chars)';
        ?><br>
        <strong>Model:</strong> <code><?php echo defined('BLACKBOX_MODEL') ? BLACKBOX_MODEL : 'default'; ?></code>
    </div>
    
    <h2>🧪 Interactive Test</h2>
    <p>Test the AI with any message:</p>
    
    <input type="text" id="testMessage" placeholder="Type a message to test..." value="Hello, what can you help me with?">
    <br>
    <button class="test-btn" onclick="runTest()">🚀 Test AI Response</button>
    <button class="test-btn" onclick="runTest('How do I search for movies?')">🎬 Movies</button>
    <button class="test-btn" onclick="runTest('Help me with Google dorking')">🔍 Dorking</button>
    <button class="test-btn" onclick="runTest('How to download torrents?')">🧲 Torrents</button>
    
    <div id="testResult"></div>
    
    <h2>⚡ Quick Tests</h2>
    <?php
    $tests = [
        ['message' => 'Hello!', 'desc' => 'Greeting test'],
        ['message' => 'How do I use Google Dorker?', 'desc' => 'Dorking help'],
        ['message' => 'Help me download a movie', 'desc' => 'Download help'],
    ];
    
    foreach ($tests as $test) {
        echo "<h3>{$test['desc']}</h3>";
        echo "<p>Input: <code>{$test['message']}</code></p>";
        
        $startTime = microtime(true);
        $response = getAIResponse($test['message'], 'general', []);
        $duration = round((microtime(true) - $startTime) * 1000);
        
        if ($response && strlen($response) > 10) {
            echo "<div class='success'>✅ Success! ({$duration}ms)</div>";
            echo "<div class='response-box'>" . htmlspecialchars($response) . "</div>";
        } else {
            echo "<div class='error'>❌ Failed or empty response ({$duration}ms)</div>";
        }
    }
    ?>
    
    <h2>🔧 System Info</h2>
    <div class="info">
        <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
        <strong>cURL:</strong> <?php echo function_exists('curl_init') ? '✅ Available' : '❌ Not available'; ?><br>
        <strong>JSON:</strong> <?php echo function_exists('json_encode') ? '✅ Available' : '❌ Not available'; ?><br>
        <strong>SQLite3:</strong> <?php echo class_exists('SQLite3') ? '✅ Available' : '❌ Not available'; ?>
    </div>
    
    <p style="margin-top: 40px; color: #8b949e;">
        <a href="dashboard.php" style="color: #58a6ff;">← Back to Dashboard</a> | 
        Generated: <?php echo date('Y-m-d H:i:s'); ?>
    </p>
    
    <script>
    async function runTest(message) {
        const input = document.getElementById('testMessage');
        const result = document.getElementById('testResult');
        
        if (message) {
            input.value = message;
        }
        
        const testMsg = input.value.trim();
        if (!testMsg) {
            result.innerHTML = '<div class="warning">Please enter a message</div>';
            return;
        }
        
        result.innerHTML = '<div class="info">⏳ Testing...</div>';
        
        try {
            const response = await fetch('test-ai.php?test=1&message=' + encodeURIComponent(testMsg));
            const data = await response.json();
            
            if (data.success && data.response) {
                result.innerHTML = `
                    <div class="success">✅ AI Response (${data.length} chars)</div>
                    <div class="response-box">${escapeHtml(data.response)}</div>
                `;
            } else {
                result.innerHTML = '<div class="error">❌ No response received</div>';
            }
        } catch (error) {
            result.innerHTML = `<div class="error">❌ Error: ${error.message}</div>`;
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    }
    </script>
</body>
</html>
