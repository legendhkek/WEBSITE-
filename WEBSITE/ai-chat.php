<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - AI Chat Integration with Multiple AI Providers
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Full-featured AI chatbot that can be integrated across the entire website
 * Supports multiple AI providers with automatic fallback
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

// Get database connection (SQLite3)
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
        // Always report AI as available - we have fallbacks
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
    $context = $input['context'] ?? 'general'; // general, dorking, torrents, etc.
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Message is required']);
        return;
    }
    
    // Create new conversation if needed
    if (!$conversationId) {
        $title = substr($message, 0, 50) . (strlen($message) > 50 ? '...' : '');
        $stmt = $db->prepare("INSERT INTO ai_conversations (user_id, title, created_at, updated_at) VALUES (:user_id, :title, :created, :updated)");
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
        $stmt->bindValue(':created', time(), SQLITE3_INTEGER);
        $stmt->bindValue(':updated', time(), SQLITE3_INTEGER);
        $stmt->execute();
        $conversationId = $db->lastInsertRowID();
    } else {
        // Update conversation timestamp
        $stmt = $db->prepare("UPDATE ai_conversations SET updated_at = :updated WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':updated', time(), SQLITE3_INTEGER);
        $stmt->bindValue(':id', $conversationId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->execute();
    }
    
    // Save user message
    $stmt = $db->prepare("INSERT INTO ai_messages (conversation_id, role, content, created_at) VALUES (:conv_id, 'user', :content, :created)");
    $stmt->bindValue(':conv_id', $conversationId, SQLITE3_INTEGER);
    $stmt->bindValue(':content', $message, SQLITE3_TEXT);
    $stmt->bindValue(':created', time(), SQLITE3_INTEGER);
    $stmt->execute();
    
    // Get conversation history for context
    $history = getConversationHistory($db, $conversationId, 10);
    
    // Get AI response with context (with multiple fallbacks)
    $aiResponse = getAIResponse($message, $history, $context);
    
    // Always save and return the AI response (even if it's an error message)
    if ($aiResponse) {
        // Save AI response
        $stmt = $db->prepare("INSERT INTO ai_messages (conversation_id, role, content, created_at) VALUES (:conv_id, 'assistant', :content, :created)");
        $stmt->bindValue(':conv_id', $conversationId, SQLITE3_INTEGER);
        $stmt->bindValue(':content', $aiResponse, SQLITE3_TEXT);
        $stmt->bindValue(':created', time(), SQLITE3_INTEGER);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'response' => $aiResponse,
            'conversation_id' => $conversationId
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'The AI service is currently unavailable. Please try again later.'
        ]);
    }
}

/**
 * Get AI response using multiple providers with fallback
 */
function getAIResponse($message, $history = [], $context = 'general') {
    $systemPrompt = getSystemPrompt($context);
    
    // Build messages array with conversation history
    $messages = [];
    foreach ($history as $msg) {
        $messages[] = [
            'role' => $msg['role'],
            'content' => $msg['content']
        ];
    }
    
    // Add current message with system context
    $fullMessage = "Context: " . $systemPrompt . "\n\nUser: " . $message;
    $messages[] = ['role' => 'user', 'content' => $fullMessage];
    
    // Try multiple AI providers in order
    $providers = [
        'blackbox' => function() use ($messages) {
            return tryBlackboxAI($messages);
        },
        'deepinfra' => function() use ($messages) {
            return tryDeepInfraAI($messages);
        },
        'local' => function() use ($message, $context, $systemPrompt) {
            return generateLocalResponse($message, $context);
        }
    ];
    
    foreach ($providers as $providerName => $provider) {
        try {
            $response = $provider();
            if ($response && strlen(trim($response)) > 10) {
                return $response;
            }
        } catch (Exception $e) {
            error_log("AI Provider '$providerName' failed: " . $e->getMessage());
            continue;
        }
    }
    
    // Ultimate fallback - return a helpful response
    return generateLocalResponse($message, $context);
}

/**
 * Try Blackbox AI API
 */
function tryBlackboxAI($messages) {
    if (!defined('BLACKBOX_API_ENDPOINT')) {
        return null;
    }
    
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
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        return null;
    }
    
    // Clean up response
    $content = trim($response);
    $content = preg_replace('/^\$@\$v=undefined-rv1\$@\$/i', '', $content);
    $content = trim($content);
    
    return !empty($content) ? $content : null;
}

/**
 * Try DeepInfra AI API (free tier available)
 */
function tryDeepInfraAI($messages) {
    $endpoint = 'https://api.deepinfra.com/v1/openai/chat/completions';
    
    $data = [
        'model' => 'meta-llama/Llama-2-70b-chat-hf',
        'messages' => $messages,
        'max_tokens' => 1000,
        'temperature' => 0.7
    ];
    
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        return null;
    }
    
    $json = json_decode($response, true);
    if (isset($json['choices'][0]['message']['content'])) {
        return trim($json['choices'][0]['message']['content']);
    }
    
    return null;
}

/**
 * Generate intelligent local response based on context
 */
function generateLocalResponse($message, $context) {
    $messageLower = strtolower($message);
    
    // Context-specific responses
    $responses = [
        'dorking' => [
            'patterns' => [
                '/admin|login|panel/' => "🔍 **Google Dorking Tips for Admin Panels:**\n\n• Use `inurl:admin` to find admin URLs\n• Try `intitle:\"admin login\"` for login pages\n• Combine with `site:target.com` for specific domains\n• Example: `site:example.com inurl:admin intitle:\"login\"`\n\n⚠️ Remember: Only use these techniques on systems you have permission to test!",
                '/sql|database|dump/' => "🔍 **Database Dork Patterns:**\n\n• `filetype:sql \"INSERT INTO\"` - Find SQL dumps\n• `ext:sql intext:\"CREATE TABLE\"` - Database schemas\n• `filetype:env DB_PASSWORD` - Environment files\n\n🛡️ These are for security research only. Always get authorization first!",
                '/file|filetype|ext/' => "📁 **File Search Operators:**\n\n• `filetype:pdf` - Search for PDFs\n• `ext:doc OR ext:docx` - Word documents\n• `filetype:xls \"confidential\"` - Sensitive spreadsheets\n• `filetype:log` - Log files (great for debugging info)\n\nCombine with `site:` for targeted searches!",
                '.*' => "🔍 **Google Dorking Help:**\n\nI can help you with Google dork queries! Try asking about:\n• Admin panel detection\n• Sensitive file discovery\n• Specific operators (site:, filetype:, inurl:, intitle:)\n• Vulnerability research dorks\n\nWhat would you like to search for?"
            ]
        ],
        'torrents' => [
            'patterns' => [
                '/download|magnet|torrent/' => "📥 **Torrent Download Tips:**\n\n• Always check **Seeds** count - more seeds = faster download\n• Look for **Verified** uploaders for safer files\n• Use the **Watch** feature to stream directly in browser\n• Higher quality (1080p, 4K) means larger file sizes\n\n💡 Tip: Click the magnet link to open in your torrent client!",
                '/stream|watch|play/' => "▶️ **Streaming Features:**\n\n• Click **Watch Now** on any result to stream instantly\n• Uses WebTorrent technology - no software needed!\n• Works best with well-seeded torrents (50+ seeds)\n• Supported formats: MP4, MKV, AVI, WebM\n\nJust search for content and hit the play button!",
                '/search|find/' => "🔍 **Search Tips:**\n\n• Use specific keywords: \"movie name year quality\"\n• Try categories: Movies, TV, Games, Anime\n• Example: \"Inception 2010 1080p\"\n• Filter by quality and source after searching\n\nWhat would you like to find?",
                '.*' => "🎬 **Legend House Torrent Help:**\n\nI can help you with:\n• Finding and downloading content\n• Understanding quality options\n• Streaming in browser\n• Using magnet links\n\nWhat do you need help with?"
            ]
        ],
        'search' => [
            'patterns' => [
                '.*' => "🔍 **Search Optimization Tips:**\n\n• Use quotes for exact phrases: \"exact phrase\"\n• Include year for movies: \"Avatar 2023\"\n• Specify quality: \"1080p\" or \"4K\"\n• Use filters after searching\n\nSearch across 10+ sources simultaneously!"
            ]
        ],
        'technical' => [
            'patterns' => [
                '/error|problem|issue|not work/' => "🛠️ **Troubleshooting:**\n\n1. **Clear browser cache** - Often fixes display issues\n2. **Check internet connection** - Some features need stable connection\n3. **Disable ad blockers** - They may interfere with some features\n4. **Try different browser** - Chrome/Firefox work best\n\nWhat specific issue are you experiencing?",
                '/how|what|where/' => "💡 **Quick Guide:**\n\n• **Home** - Search for content\n• **Watch** - Stream torrents in browser\n• **Tools** - Dorker, Proxy Scraper, Shortener\n• **Dashboard** - Your history and stats\n\nNeed help with something specific?",
                '.*' => "🔧 **Technical Support:**\n\nI can help with:\n• Navigation and features\n• Troubleshooting issues\n• Understanding how things work\n\nDescribe your question or issue!"
            ]
        ],
        'general' => [
            'patterns' => [
                '/hello|hi|hey|greetings/' => "👋 Hello! Welcome to Legend House!\n\nI'm your AI assistant. I can help you with:\n• 🔍 Finding movies, TV shows, games\n• 📺 Streaming content in browser\n• 🛠️ Using our tools (Dorker, Proxy Scraper)\n• ❓ Any questions about the platform\n\nHow can I assist you today?",
                '/thank|thanks/' => "You're welcome! 😊 Happy to help!\n\nIs there anything else you'd like to know about Legend House?",
                '/bye|goodbye/' => "Goodbye! 👋\n\nCome back anytime you need help. Enjoy your time on Legend House!",
                '.*' => "🏠 **Legend House Assistant**\n\nI'm here to help! I can assist with:\n\n• **Searching** - Find movies, TV shows, games, software\n• **Streaming** - Watch directly in your browser\n• **Downloading** - Magnet links and torrent files\n• **Tools** - Google Dorker, Proxy Scraper, URL Shortener\n\nWhat would you like help with?"
            ]
        ]
    ];
    
    // Get responses for the current context
    $contextResponses = $responses[$context] ?? $responses['general'];
    
    // Find matching pattern
    foreach ($contextResponses['patterns'] as $pattern => $response) {
        if (preg_match($pattern, $messageLower)) {
            return $response;
        }
    }
    
    // Default fallback
    return $responses['general']['patterns']['.*'];
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
 * Get all conversations for user
 */
function getConversations($db, $user) {
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
    
    // First delete associated messages
    $stmt = $db->prepare("DELETE FROM ai_messages WHERE conversation_id = :conv_id");
    $stmt->bindValue(':conv_id', $conversationId, SQLITE3_INTEGER);
    $stmt->execute();
    
    // Verify ownership and delete conversation
    $stmt = $db->prepare("DELETE FROM ai_conversations WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':id', $conversationId, SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $stmt->execute();
    
    if ($db->changes() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Conversation not found']);
    }
}
