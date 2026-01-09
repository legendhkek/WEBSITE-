<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - AI Chat Integration with Blackbox API
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Full-featured AI chatbot that can be integrated across the entire website
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ai-helper.php'; // For shared validation function

// Check if user is logged in
$user = getCurrentUser();
if (!$user) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

header('Content-Type: application/json');

// Get database connection
$db = getDatabase();

// Initialize AI chat tables
$db->exec("
    CREATE TABLE IF NOT EXISTS ai_conversations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT,
        created_at INTEGER NOT NULL,
        updated_at INTEGER NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS ai_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        conversation_id INTEGER NOT NULL,
        role TEXT NOT NULL,
        content TEXT NOT NULL,
        created_at INTEGER NOT NULL,
        FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id)
    )
");

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'chat':
        handleChat($db, $user);
        break;
    case 'conversations':
        getConversations($db, $user);
        break;
    case 'conversation':
        getConversation($db, $user);
        break;
    case 'delete':
        deleteConversation($db, $user);
        break;
    case 'available':
        echo json_encode(['success' => true, 'available' => AI_FEATURES_ENABLED]);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

exit;

/**
 * Handle chat message and get AI response
 */
function handleChat($db, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    $conversationId = $input['conversation_id'] ?? null;
    $context = $input['context'] ?? 'general'; // general, dorking, torrents, etc.
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Message is required']);
        return;
    }
    
    if (!AI_FEATURES_ENABLED) {
        echo json_encode(['success' => false, 'error' => 'AI features are not enabled']);
        return;
    }
    
    // Create new conversation if needed
    if (!$conversationId) {
        $title = substr($message, 0, 50) . (strlen($message) > 50 ? '...' : '');
        $stmt = $db->prepare("INSERT INTO ai_conversations (user_id, title, created_at, updated_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user['id'], $title, time(), time()]);
        $conversationId = $db->lastInsertId();
    } else {
        // Update conversation timestamp
        $stmt = $db->prepare("UPDATE ai_conversations SET updated_at = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([time(), $conversationId, $user['id']]);
    }
    
    // Save user message
    $stmt = $db->prepare("INSERT INTO ai_messages (conversation_id, role, content, created_at) VALUES (?, 'user', ?, ?)");
    $stmt->execute([$conversationId, $message, time()]);
    
    // Get conversation history for context
    $history = getConversationHistory($db, $conversationId, 10);
    
    // Get AI response with context
    $aiResponse = getAIResponse($message, $history, $context);
    
    // Always save and return the AI response (even if it's an error message)
    if ($aiResponse) {
        // Save AI response
        $stmt = $db->prepare("INSERT INTO ai_messages (conversation_id, role, content, created_at) VALUES (?, 'assistant', ?, ?)");
        $stmt->execute([$conversationId, $aiResponse, time()]);
        
        echo json_encode([
            'success' => true,
            'response' => $aiResponse,
            'conversation_id' => $conversationId
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'The AI service is currently unavailable. Please check the configuration or try again later.'
        ]);
    }
}

/**
 * Get AI response using Blackbox API
 */
function getAIResponse($message, $history = [], $context = 'general') {
    if (!validateBlackboxConfig()) {
        return "I apologize, but AI features are currently unavailable. The API service is not configured. Please contact the administrator to enable AI assistance.";
    }
    
    $systemPrompt = getSystemPrompt($context);
    
    // Build messages array with system prompt as first user message context
    $messages = [];
    
    // Add conversation history
    foreach ($history as $msg) {
        $messages[] = [
            'role' => $msg['role'],
            'content' => $msg['content']
        ];
    }
    
    // Add current message with system context
    $fullMessage = "Context: " . $systemPrompt . "\n\nUser: " . $message;
    $messages[] = ['role' => 'user', 'content' => $fullMessage];
    
    // Blackbox AI native request format
    $data = [
        'messages' => $messages,
        'id' => uniqid('legendhouse_chat_'),
        'previewToken' => null,
        'userId' => null,
        'codeModelMode' => true,
        'agentMode' => [],
        'trendingAgentMode' => [],
        'isMicMode' => false,
        'maxTokens' => 2000,
        'isChromeExt' => false,
        'githubToken' => null,
        'clickedAnswer2' => false,
        'clickedAnswer3' => false,
        'clickedForceWebSearch' => false,
        'visitFromDelta' => false,
        'mobileClient' => false
    ];
    
    $ch = curl_init(BLACKBOX_API_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: https://www.blackbox.ai',
            'Referer: https://www.blackbox.ai/',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
        ],
        CURLOPT_TIMEOUT => 45,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    
    if ($curlError) {
        error_log("Blackbox AI Chat CURL error (errno: $curlErrno): $curlError");
        
        // Provide specific error messages based on error type
        if ($curlErrno === 6) { // CURLE_COULDNT_RESOLVE_HOST
            return "I apologize, but I cannot reach the AI service right now. This appears to be a network connectivity issue. Please check your internet connection and try again later.";
        } elseif ($curlErrno === 7) { // CURLE_COULDNT_CONNECT
            return "I apologize, but the AI service is currently unavailable. The service may be temporarily down. Please try again in a few minutes.";
        } elseif ($curlErrno === 28) { // CURLE_OPERATION_TIMEDOUT
            return "I apologize, but the AI service is taking too long to respond. Please try again with a shorter message or wait a moment before retrying.";
        } elseif ($curlErrno === 35 || $curlErrno === 60) { // SSL errors
            return "I apologize, but there's a security certificate issue connecting to the AI service. Please contact the administrator to resolve this.";
        }
        
        // Generic error for other cases
        return "I apologize, but I'm unable to connect to the AI service at the moment. Please try again later. If the problem persists, contact support.";
    }
    
    if ($httpCode !== 200) {
        error_log("Blackbox AI Chat HTTP error: $httpCode - Response: " . substr($response, 0, 500));
        return "I apologize, but the AI service returned an error (HTTP $httpCode). Please try again later.";
    }
    
    if (!$response) {
        error_log("Blackbox AI Chat: Empty response");
        return "I apologize, but I received an empty response from the AI service. Please try again.";
    }
    
    // Blackbox returns plain text response
    $content = trim($response);
    
    // Clean up any markdown artifacts
    $content = preg_replace('/^\$@\$v=undefined-rv1\$@\$/i', '', $content);
    $content = trim($content);
    
    if (empty($content)) {
        return "I apologize, but I received an empty response. Please try again.";
    }
    
    return $content;
}

/**
 * Get system prompt based on context
 */
function getSystemPrompt($context) {
    $prompts = [
        'general' => 'You are a helpful AI assistant for Legend House, a torrent search and management platform. Help users with their questions about torrents, content discovery, and using the platform features. Be friendly and informative.',
        
        'dorking' => 'You are an expert in Google dorking and information security research. Help users understand Google dork operators, suggest effective dork queries, and explain how to use them safely and legally for research purposes. Always emphasize ethical use.',
        
        'torrents' => 'You are an expert in torrents and P2P technology. Help users understand how torrents work, suggest search terms for finding content, explain torrent clients and seeders/leechers, and provide guidance on safe torrent practices.',
        
        'search' => 'You are a search optimization expert. Help users refine their search queries, suggest related terms, and provide tips for finding specific content more effectively. Be creative with search suggestions.',
        
        'technical' => 'You are a technical support assistant for Legend House platform. Help users troubleshoot issues, understand features, and navigate the website. Provide clear step-by-step guidance.'
    ];
    
    return $prompts[$context] ?? $prompts['general'];
}

/**
 * Get conversation history
 */
function getConversationHistory($db, $conversationId, $limit = 10) {
    $stmt = $db->prepare("
        SELECT role, content 
        FROM ai_messages 
        WHERE conversation_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$conversationId, $limit]);
    
    $messages = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $messages[] = $row;
    }
    
    return array_reverse($messages);
}

/**
 * Get all conversations for user
 */
function getConversations($db, $user) {
    $stmt = $db->prepare("
        SELECT id, title, created_at, updated_at,
               (SELECT COUNT(*) FROM ai_messages WHERE conversation_id = ai_conversations.id) as message_count
        FROM ai_conversations 
        WHERE user_id = ? 
        ORDER BY updated_at DESC 
        LIMIT 50
    ");
    $stmt->execute([$user['id']]);
    
    $conversations = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $conversations[] = $row;
    }
    
    echo json_encode(['success' => true, 'conversations' => $conversations]);
}

/**
 * Get specific conversation with messages
 */
function getConversation($db, $user) {
    $conversationId = $_GET['id'] ?? null;
    
    if (!$conversationId) {
        echo json_encode(['success' => false, 'error' => 'Conversation ID required']);
        return;
    }
    
    // Verify ownership
    $stmt = $db->prepare("SELECT * FROM ai_conversations WHERE id = ? AND user_id = ?");
    $stmt->execute([$conversationId, $user['id']]);
    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$conversation) {
        echo json_encode(['success' => false, 'error' => 'Conversation not found']);
        return;
    }
    
    // Get messages
    $stmt = $db->prepare("SELECT * FROM ai_messages WHERE conversation_id = ? ORDER BY created_at ASC");
    $stmt->execute([$conversationId]);
    
    $messages = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $messages[] = $row;
    }
    
    $conversation['messages'] = $messages;
    
    echo json_encode(['success' => true, 'conversation' => $conversation]);
}

/**
 * Delete conversation
 */
function deleteConversation($db, $user) {
    $conversationId = $_GET['id'] ?? null;
    
    if (!$conversationId) {
        echo json_encode(['success' => false, 'error' => 'Conversation ID required']);
        return;
    }
    
    // Verify ownership and delete
    $stmt = $db->prepare("DELETE FROM ai_conversations WHERE id = ? AND user_id = ?");
    $stmt->execute([$conversationId, $user['id']]);
    
    if ($stmt->rowCount() > 0) {
        // Delete associated messages
        $stmt = $db->prepare("DELETE FROM ai_messages WHERE conversation_id = ?");
        $stmt->execute([$conversationId]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Conversation not found']);
    }
}
