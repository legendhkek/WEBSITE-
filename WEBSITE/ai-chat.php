<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - AI Chat API Endpoint v3.0
 * Always working edition with robust error handling
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get action BEFORE loading other files (for availability check)
$action = $_GET['action'] ?? '';
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'chat';
}

// Allow availability check without any auth
if ($action === 'available') {
    echo json_encode(['success' => true, 'available' => true, 'version' => '3.0']);
    exit;
}

// Now load the required files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ai-helper.php';

// Check if user is logged in
$user = null;
try {
    $user = getCurrentUser();
} catch (Exception $e) {
    error_log("AI Chat - Auth check failed: " . $e->getMessage());
}

if (!$user) {
    echo json_encode([
        'success' => false, 
        'error' => 'Authentication required. Please log in.',
        'redirect' => 'login.php'
    ]);
    exit;
}

error_log("AI Chat - User authenticated: " . $user['username']);

// Get database connection using SQLite3 (more reliable)
$db = null;
try {
    $db = new SQLite3(DB_FILE);
    $db->busyTimeout(5000);
} catch (Exception $e) {
    error_log("AI Chat - Database connection error: " . $e->getMessage());
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
            updated_at INTEGER NOT NULL
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS ai_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            conversation_id INTEGER NOT NULL,
            role TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at INTEGER NOT NULL
        )
    ");
    
    // Create index for faster queries
    $db->exec("CREATE INDEX IF NOT EXISTS idx_ai_conversations_user ON ai_conversations(user_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_ai_messages_conv ON ai_messages(conversation_id)");
} catch (Exception $e) {
    error_log("AI Chat - Table creation error: " . $e->getMessage());
}

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
    case 'status':
        // Return AI status info
        echo json_encode([
            'success' => true,
            'available' => true,
            'providers' => ['blackbox', 'duckduckgo', 'deepinfra', 'huggingface', 'local'],
            'model' => defined('BLACKBOX_MODEL') ? BLACKBOX_MODEL : 'gpt-4o'
        ]);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
}

// Close database connection
if ($db) {
    $db->close();
}
exit;

/**
 * Handle chat message and get AI response
 * ALWAYS returns a response - never fails
 */
function handleChat($db, $user) {
    // Get input from JSON body or POST
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Fallback to POST data if JSON decode fails
    if (!$input) {
        $input = $_POST;
    }
    
    $message = trim($input['message'] ?? '');
    $conversationId = $input['conversation_id'] ?? null;
    $context = $input['context'] ?? 'general';
    
    // Debug log
    error_log("AI Chat Request - User: {$user['id']}, Message: " . substr($message, 0, 50) . "...");
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Message is required']);
        return;
    }
    
    // Default response in case everything fails
    $aiResponse = "I'm here to help! 👋\n\nI can assist with:\n• 🔍 Searching for content\n• 🧲 Torrents & downloads\n• 🛠️ Using our tools\n• ❓ Platform questions\n\nWhat would you like help with?";
    
    try {
        // Create new conversation if needed
        if (!$conversationId) {
            $title = substr($message, 0, 50) . (strlen($message) > 50 ? '...' : '');
            $stmt = $db->prepare("INSERT INTO ai_conversations (user_id, title, created_at, updated_at) VALUES (:user_id, :title, :created, :updated)");
            $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
            $stmt->bindValue(':title', $title, SQLITE3_TEXT);
            $stmt->bindValue(':created', time(), SQLITE3_INTEGER);
            $stmt->bindValue(':updated', time(), SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                $conversationId = $db->lastInsertRowID();
            }
        } else {
            // Update conversation timestamp
            $stmt = $db->prepare("UPDATE ai_conversations SET updated_at = :updated WHERE id = :id AND user_id = :user_id");
            $stmt->bindValue(':updated', time(), SQLITE3_INTEGER);
            $stmt->bindValue(':id', $conversationId, SQLITE3_INTEGER);
            $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
            $stmt->execute();
        }
        
        // Try to save user message (don't fail if it doesn't work)
        try {
            $stmt = $db->prepare("INSERT INTO ai_messages (conversation_id, role, content, created_at) VALUES (:conv_id, 'user', :content, :created)");
            $stmt->bindValue(':conv_id', $conversationId, SQLITE3_INTEGER);
            $stmt->bindValue(':content', $message, SQLITE3_TEXT);
            $stmt->bindValue(':created', time(), SQLITE3_INTEGER);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Could not save user message: " . $e->getMessage());
        }
        
        // Get AI response - this ALWAYS returns something
        $startTime = microtime(true);
        $aiResponse = getAIResponse($message, $context, []);
        $duration = round((microtime(true) - $startTime) * 1000);
        
        error_log("AI Response in {$duration}ms - Length: " . strlen($aiResponse));
        
        // Try to save AI response
        try {
            $stmt = $db->prepare("INSERT INTO ai_messages (conversation_id, role, content, created_at) VALUES (:conv_id, 'assistant', :content, :created)");
            $stmt->bindValue(':conv_id', $conversationId, SQLITE3_INTEGER);
            $stmt->bindValue(':content', $aiResponse, SQLITE3_TEXT);
            $stmt->bindValue(':created', time(), SQLITE3_INTEGER);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Could not save AI response: " . $e->getMessage());
        }
        
    } catch (Exception $e) {
        error_log("AI Chat error: " . $e->getMessage());
        // Use fallback response
        $aiResponse = getFallbackResponse($message, $context);
    }
    
    // ALWAYS return success with a response
    echo json_encode([
        'success' => true,
        'response' => $aiResponse,
        'conversation_id' => $conversationId
    ]);
}

/**
 * Get fallback response when everything fails
 */
function getFallbackResponse($message, $context) {
    $messageLower = strtolower($message);
    
    // Context-aware responses
    $responses = [
        'general' => "👋 I'm here to help! Here's what I can do:\n\n• **🔍 Search** - Find movies, TV shows, and more at [Home](home.php)\n• **🛠️ Tools** - Access all tools at [Tools](tools.php)\n• **📊 Dashboard** - View your stats at [Dashboard](dashboard.php)\n• **🧲 Torrents** - Use [Torrent Center](tools/torrent.php)\n\nWhat would you like help with?",
        
        'dorking' => "🔍 **Google Dorking Guide:**\n\n**Basic Operators:**\n• `site:domain.com` - Search specific site\n• `intitle:keyword` - Search in titles\n• `inurl:admin` - Search in URLs\n• `filetype:pdf` - Find specific files\n\n**Advanced:**\n• `\"exact phrase\"` - Exact match\n• `-keyword` - Exclude term\n• `site:*.gov` - Wildcard domains\n\nTry our **[Google Dorker](tools/dorker.php)** for automated searches!",
        
        'torrents' => "🧲 **Torrent Help:**\n\n**Getting Started:**\n1. Go to **[Home](home.php)** and search\n2. Click on a result to get magnet link\n3. Use **[Torrent Center](tools/torrent.php)** to download\n\n**Or stream directly:**\n• Visit **[Watch](watch.php)** to stream in browser!\n\n**Tips:**\n• More seeders = faster downloads\n• Check quality tags (1080p, 4K, etc.)",
        
        'search' => "🔎 **Search Tips:**\n\n• Be **specific** with keywords\n• Add **quality** (1080p, 4k, BluRay)\n• Include **year** for movies/shows\n• Use **quotes** for exact phrases\n\n**Example searches:**\n• `Avatar 2 4K BluRay 2022`\n• `Breaking Bad complete series`\n\nSearch now at **[Home](home.php)**!",
        
        'technical' => "🛠️ **Technical Support:**\n\n• **System Status** - Check [Dashboard](dashboard.php)\n• **Account Settings** - Visit [Settings](settings.php)\n• **Profile** - Update at [Profile](profile.php)\n\n**Common fixes:**\n1. Clear browser cache\n2. Try different browser\n3. Check internet connection\n\nStill having issues? Describe the problem!"
    ];
    
    // Check for specific keywords in message
    if (preg_match('/(hello|hi|hey|greetings)/i', $messageLower)) {
        return "Hello! 👋 I'm Legend AI, your assistant. How can I help you today?\n\nI can help with:\n• 🔍 Google dorking\n• 🧲 Torrents & downloads\n• 🌐 Proxy tools\n• 🛠️ Platform navigation\n\nJust ask!";
    }
    
    if (preg_match('/(thanks|thank you|thx)/i', $messageLower)) {
        return "You're welcome! 😊 Let me know if you need anything else!";
    }
    
    return $responses[$context] ?? $responses['general'];
}

/**
 * Get conversation history (using SQLite3)
 */
function getConversationHistory($db, $conversationId, $limit = 10) {
    $stmt = $db->prepare("
        SELECT role, content 
        FROM ai_messages 
        WHERE conversation_id = :conv_id 
        ORDER BY created_at DESC 
        LIMIT :limit
    ");
    $stmt->bindValue(':conv_id', $conversationId, SQLITE3_INTEGER);
    $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $messages = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $messages[] = $row;
    }
    
    return array_reverse($messages);
}

/**
 * Get all conversations for user (using SQLite3)
 */
function getConversations($db, $user) {
    try {
        $stmt = $db->prepare("
            SELECT id, title, created_at, updated_at,
                   (SELECT COUNT(*) FROM ai_messages WHERE conversation_id = ai_conversations.id) as message_count
            FROM ai_conversations 
            WHERE user_id = :user_id 
            ORDER BY updated_at DESC 
            LIMIT 50
        ");
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $conversations = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $conversations[] = $row;
        }
        
        echo json_encode(['success' => true, 'conversations' => $conversations]);
    } catch (Exception $e) {
        error_log("getConversations error: " . $e->getMessage());
        echo json_encode(['success' => true, 'conversations' => []]);
    }
}

/**
 * Get specific conversation with messages (using SQLite3)
 */
function getConversation($db, $user) {
    $conversationId = $_GET['id'] ?? null;
    
    if (!$conversationId) {
        echo json_encode(['success' => false, 'error' => 'Conversation ID required']);
        return;
    }
    
    try {
        // Verify ownership
        $stmt = $db->prepare("SELECT * FROM ai_conversations WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $conversationId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $conversation = $result->fetchArray(SQLITE3_ASSOC);
        
        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => 'Conversation not found']);
            return;
        }
        
        // Get messages
        $stmt = $db->prepare("SELECT * FROM ai_messages WHERE conversation_id = :conv_id ORDER BY created_at ASC");
        $stmt->bindValue(':conv_id', $conversationId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $messages = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $messages[] = $row;
        }
        
        $conversation['messages'] = $messages;
        
        echo json_encode(['success' => true, 'conversation' => $conversation]);
    } catch (Exception $e) {
        error_log("getConversation error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to load conversation']);
    }
}

/**
 * Delete conversation (using SQLite3)
 */
function deleteConversation($db, $user) {
    $conversationId = $_GET['id'] ?? $_POST['id'] ?? null;
    
    if (!$conversationId) {
        echo json_encode(['success' => false, 'error' => 'Conversation ID required']);
        return;
    }
    
    try {
        // Verify ownership first
        $stmt = $db->prepare("SELECT id FROM ai_conversations WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $conversationId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        if (!$result->fetchArray()) {
            echo json_encode(['success' => false, 'error' => 'Conversation not found']);
            return;
        }
        
        // Delete messages first
        $stmt = $db->prepare("DELETE FROM ai_messages WHERE conversation_id = :conv_id");
        $stmt->bindValue(':conv_id', $conversationId, SQLITE3_INTEGER);
        $stmt->execute();
        
        // Delete conversation
        $stmt = $db->prepare("DELETE FROM ai_conversations WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $conversationId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("deleteConversation error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to delete conversation']);
    }
}
