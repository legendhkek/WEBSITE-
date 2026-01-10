<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - AI Chat API Endpoint
 * ═══════════════════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ai-helper.php';

header('Content-Type: application/json');

// Check if user is logged in
$user = getCurrentUser();
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

// Get database connection
try {
    $db = getDatabase();
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Initialize AI chat tables
try {
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
} catch (Exception $e) {
    error_log("Table creation error: " . $e->getMessage());
}

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
        echo json_encode(['success' => true, 'available' => true]);
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
    $context = $input['context'] ?? 'general';
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Message is required']);
        return;
    }
    
    try {
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
        
        // Get AI response
        $aiResponse = getAIResponse($message, $context, $history);
        
        // Save AI response
        $stmt = $db->prepare("INSERT INTO ai_messages (conversation_id, role, content, created_at) VALUES (?, 'assistant', ?, ?)");
        $stmt->execute([$conversationId, $aiResponse, time()]);
        
        echo json_encode([
            'success' => true,
            'response' => $aiResponse,
            'conversation_id' => $conversationId
        ]);
        
    } catch (Exception $e) {
        error_log("Chat error: " . $e->getMessage());
        
        // Return a helpful fallback response
        $fallbackResponse = getFallbackResponse($message, $context);
        echo json_encode([
            'success' => true,
            'response' => $fallbackResponse,
            'conversation_id' => $conversationId ?? null
        ]);
    }
}

/**
 * Get fallback response when everything fails
 */
function getFallbackResponse($message, $context) {
    $responses = [
        'general' => "I'm here to help! While I'm experiencing some technical difficulties, here are some quick links:\n\n• [Home](home.php) - Search for content\n• [Tools](tools.php) - Access all tools\n• [Dashboard](dashboard.php) - View your stats\n\nWhat would you like to do?",
        
        'dorking' => "For Google dorking, try these operators:\n• `site:domain.com` - Search specific site\n• `intitle:keyword` - Search in titles\n• `filetype:pdf` - Find specific files\n\nUse our [Google Dorker](tools/dorker.php) for automated searches!",
        
        'torrents' => "For torrent help:\n1. Search on [Home](home.php)\n2. Copy magnet links\n3. Use [Torrent Center](tools/torrent.php)\n\nOr try [Watch](watch.php) to stream directly!",
        
        'search' => "Search tips:\n• Be specific with keywords\n• Add quality (1080p, 4k)\n• Include year for movies\n\nTry searching on [Home](home.php)!",
        
        'technical' => "For technical support:\n• Check [Dashboard](dashboard.php) for status\n• Visit [Settings](settings.php) for account\n• Contact support for urgent issues"
    ];
    
    return $responses[$context] ?? $responses['general'];
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
    try {
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
    } catch (Exception $e) {
        echo json_encode(['success' => true, 'conversations' => []]);
    }
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
    
    try {
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
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to load conversation']);
    }
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
    
    try {
        // Delete messages first
        $stmt = $db->prepare("DELETE FROM ai_messages WHERE conversation_id = ?");
        $stmt->execute([$conversationId]);
        
        // Verify ownership and delete
        $stmt = $db->prepare("DELETE FROM ai_conversations WHERE id = ? AND user_id = ?");
        $stmt->execute([$conversationId, $user['id']]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to delete conversation']);
    }
}
