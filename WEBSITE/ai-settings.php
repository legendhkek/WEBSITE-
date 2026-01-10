<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - AI Settings API
 * ═══════════════════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$user = getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';

// Initialize settings table
$db = getDB();
$db->exec('CREATE TABLE IF NOT EXISTS user_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE NOT NULL,
    ai_model TEXT DEFAULT "blackboxai/openai/gpt-4o",
    theme TEXT DEFAULT "dark",
    settings_json TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)');

switch ($action) {
    case 'save_model':
        $model = $input['model'] ?? '';
        
        // Validate model
        $validModels = [
            'blackboxai/openai/gpt-4o',
            'blackboxai/openai/gpt-4-turbo',
            'blackboxai/openai/gpt-4',
            'blackboxai/openai/chatgpt-4o-latest',
            'blackboxai/anthropic/claude-opus-4',
            'blackboxai/anthropic/claude-sonnet-4',
            'blackboxai/google/gemini-2.5-flash',
            'blackboxai/google/gemini-2.0-flash-001',
            'blackboxai/deepseek/deepseek-chat',
            'blackboxai/meta-llama/llama-4-maverick',
            'blackboxai/qwen/qwen-max',
        ];
        
        if (!in_array($model, $validModels)) {
            echo json_encode(['success' => false, 'error' => 'Invalid model selected']);
            exit;
        }
        
        // Save to database
        $stmt = $db->prepare('INSERT OR REPLACE INTO user_settings (user_id, ai_model, updated_at) 
                              VALUES (:user_id, :model, CURRENT_TIMESTAMP)');
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':model', $model, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'model' => $model]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save settings']);
        }
        break;
        
    case 'get_model':
        $stmt = $db->prepare('SELECT ai_model FROM user_settings WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        $model = $row['ai_model'] ?? 'blackboxai/openai/gpt-4o';
        echo json_encode(['success' => true, 'model' => $model]);
        break;
        
    case 'test_model':
        $model = $input['model'] ?? 'blackboxai/openai/gpt-4o';
        $startTime = microtime(true);
        
        // Test the model with Blackbox API
        $apiKey = defined('BLACKBOX_API_KEY') ? BLACKBOX_API_KEY : '';
        $endpoint = 'https://api.blackbox.ai/v1/chat/completions';
        
        if (empty($apiKey) || $apiKey === 'free') {
            echo json_encode(['success' => false, 'error' => 'No API key configured']);
            exit;
        }
        
        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant. Be very brief.'],
                ['role' => 'user', 'content' => 'Say "Hello! I am working perfectly." in one sentence.']
            ],
            'max_tokens' => 50,
            'temperature' => 0.7
        ];
        
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        $elapsed = round((microtime(true) - $startTime) * 1000) . 'ms';
        
        if ($curlError) {
            echo json_encode(['success' => false, 'error' => 'Connection error: ' . $curlError]);
            exit;
        }
        
        if ($httpCode === 200 && $response) {
            $responseData = json_decode($response, true);
            
            if (isset($responseData['choices'][0]['message']['content'])) {
                echo json_encode([
                    'success' => true,
                    'response' => $responseData['choices'][0]['message']['content'],
                    'time' => $elapsed,
                    'model' => $model
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid response format', 'raw' => $response]);
            }
        } else {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? "HTTP $httpCode error";
            echo json_encode(['success' => false, 'error' => $errorMsg]);
        }
        break;
        
    case 'clear_history':
        $stmt = $db->prepare('DELETE FROM download_history WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->execute();
        
        // Also clear search history
        $stmt = $db->prepare('DELETE FROM search_history WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        @$stmt->execute(); // May not exist
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$db->close();
