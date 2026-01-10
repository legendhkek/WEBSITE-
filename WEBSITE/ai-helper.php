<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - AI Helper v3.0 - Always Working Edition
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Features:
 * 1. Multiple AI providers with fallback
 * 2. Intelligent local responses that ALWAYS work
 * 3. Context-aware responses
 * 4. Keyword matching for accurate help
 */

error_reporting(E_ALL);
ini_set('log_errors', 1);

// Config is loaded by the calling script (ai-chat.php)
if (!defined('DB_FILE')) {
    require_once __DIR__ . '/config.php';
}

// Ensure cache directory exists
if (!file_exists(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0755, true);
}

/**
 * Main AI chat function - ALWAYS returns a response
 */
function getAIResponse($message, $context = 'general', $conversationHistory = []) {
    if (empty(trim($message))) {
        return "Please enter a message and I'll help you! 😊";
    }
    
    $message = trim($message);
    $systemPrompt = getSystemPrompt($context);
    
    // Try external AI providers first
    $providers = [
        'blackbox' => 'callBlackbox',
        'duckduckgo' => 'callDuckDuckGoAI',
    ];
    
    foreach ($providers as $name => $function) {
        try {
            if (function_exists($function)) {
                error_log("AI: Trying $name...");
                $response = $function($message, $systemPrompt, $conversationHistory);
                
                if ($response && strlen(trim($response)) > 15) {
                    error_log("AI: $name succeeded!");
                    return $response;
                }
            }
        } catch (Exception $e) {
            error_log("AI: $name failed - " . $e->getMessage());
        }
    }
    
    // Always fall back to intelligent local response
    error_log("AI: Using smart local response");
    return getSmartLocalResponse($message, $context);
}

/**
 * Smart local response - ALWAYS works
 */
function getSmartLocalResponse($message, $context = 'general') {
    $msg = strtolower(trim($message));
    
    // Greetings
    if (preg_match('/^(hi|hello|hey|yo|sup|greetings|good morning|good afternoon|good evening)/i', $msg)) {
        return "Hello! 👋 Welcome to Legend House!\n\nI'm your AI assistant. I can help you with:\n\n• 🔍 **Search** - Find movies, TV shows, games\n• 🧲 **Torrents** - Download & stream content\n• 🛠️ **Tools** - Google Dorker, Proxy Scraper, etc.\n• ❓ **Questions** - Platform help & tips\n\nWhat would you like to know?";
    }
    
    // Thanks
    if (preg_match('/(thank|thanks|thx|ty|appreciate)/i', $msg)) {
        return "You're welcome! 😊 Happy to help!\n\nIs there anything else you'd like to know about Legend House?";
    }
    
    // How are you
    if (preg_match('/(how are you|how\'s it going|what\'s up|wassup)/i', $msg)) {
        return "I'm doing great, thanks for asking! 🤖✨\n\nI'm here 24/7 to help you with Legend House. What can I assist you with today?";
    }
    
    // What can you do
    if (preg_match('/(what can you|help me|what do you|your features|capabilities)/i', $msg)) {
        return "🤖 **What I Can Help With:**\n\n**🔍 Search & Discovery**\n• How to search for content\n• Finding movies, TV shows, games\n• Search tips and filters\n\n**🧲 Torrents & Downloads**\n• Using magnet links\n• Streaming in browser\n• Download troubleshooting\n\n**🛠️ Tools**\n• Google Dorker (100+ operators)\n• Proxy Scraper (100+ sources)\n• Link Shortener\n• Rotating Proxy Maker\n\n**📊 Account**\n• Dashboard features\n• Settings & preferences\n• Profile management\n\nJust ask me anything! 😊";
    }
    
    // Google Dorker / Dorking
    if (preg_match('/(dork|dorking|dorker|google search|site:|intitle:|inurl:|filetype:)/i', $msg)) {
        return "🔍 **Google Dorking Help**\n\n**What is Google Dorking?**\nAdvanced search techniques using special operators to find specific content.\n\n**Common Operators:**\n• `site:example.com` - Search specific website\n• `intitle:keyword` - Search in page titles\n• `inurl:admin` - Search in URLs\n• `filetype:pdf` - Find specific file types\n• `\"exact phrase\"` - Exact match search\n• `-exclude` - Exclude terms\n\n**Examples:**\n• `site:github.com python tutorial`\n• `intitle:index of movies`\n• `filetype:pdf machine learning`\n\n**Try our tool:** [Google Dorker](tools/dorker.php) - 100+ operators with AI suggestions!\n\nNeed help with a specific dork query?";
    }
    
    // Torrent / Download / Magnet
    if (preg_match('/(torrent|magnet|download|seed|leech|peer|stream)/i', $msg)) {
        return "🧲 **Torrent & Download Help**\n\n**How to Download:**\n1. Go to [Home](home.php) and search\n2. Click a result to see details\n3. Copy the magnet link\n4. Open in your torrent client\n\n**How to Stream in Browser:**\n1. Search for content on [Home](home.php)\n2. Click the \"▶️ Stream\" button\n3. Wait for peers to connect\n4. Enjoy streaming!\n\n**Tips:**\n• More seeders = faster downloads\n• Look for quality tags (1080p, 4K)\n• Check file sizes before downloading\n\n**Our Tools:**\n• [Torrent Center](tools/torrent.php) - Process magnets & files\n• [Watch](watch.php) - Stream directly in browser\n\nWhat would you like to download?";
    }
    
    // Proxy
    if (preg_match('/(proxy|proxies|vpn|ip address|anonymous|scrape proxy)/i', $msg)) {
        return "🌐 **Proxy Tools Help**\n\n**Proxy Scraper** [→ Open](tools/proxy-scraper.php)\n• Scrapes from 100+ sources\n• Auto-validates proxies\n• Filters by type (HTTP, SOCKS)\n• Export as TXT, CSV, JSON\n\n**Rotating Proxy Maker** [→ Open](tools/rotating-proxy.php)\n• Upload your proxy list\n• Creates rotating pool\n• Health monitoring\n• API access\n\n**Residential Proxy Maker** [→ Open](tools/residential-proxy-maker.php)\n• Premium proxy features\n• Real-time stats\n• Auto-rotation\n\n**Tips:**\n• HTTP proxies are faster\n• SOCKS5 for better anonymity\n• Always test before using\n\nWhich proxy tool do you need help with?";
    }
    
    // Search help
    if (preg_match('/(how to search|search for|find movie|find game|looking for)/i', $msg)) {
        return "🔎 **Search Tips**\n\n**Basic Search:**\n1. Go to [Home](home.php)\n2. Type your search (e.g., \"Avatar 2\")\n3. Press Enter or click Search\n\n**Better Results:**\n• Add quality: `Avatar 2 4K`\n• Add year: `Avatar 2 2022`\n• Add type: `Avatar 2 BluRay`\n\n**Use Filters:**\n• Quality: 4K, 1080p, 720p\n• Type: Movie, TV Show, Game\n• Sort by: Seeds, Size, Name\n\n**Categories:**\n🎬 Movies | 📺 TV Shows | 🎮 Games\n💻 Software | 🎌 Anime | 🎵 Music\n\nWhat are you looking for?";
    }
    
    // Link Shortener
    if (preg_match('/(short|shorten|link|url|shortener)/i', $msg)) {
        return "🔗 **Link Shortener Help**\n\n**Features:**\n• Create short, shareable links\n• Track click analytics\n• Generate QR codes\n• Set expiration dates\n• Password protection\n\n**How to Use:**\n1. Go to [Link Shortener](tools/shortener.php)\n2. Paste your long URL\n3. Click \"Shorten\"\n4. Copy and share!\n\n**Analytics Include:**\n• Total clicks\n• Geographic data\n• Referrer info\n• Device types\n\nNeed help shortening a link?";
    }
    
    // Account / Settings / Profile
    if (preg_match('/(account|profile|settings|password|email|logout|login)/i', $msg)) {
        return "👤 **Account Help**\n\n**Your Pages:**\n• [Dashboard](dashboard.php) - Overview & stats\n• [Profile](profile.php) - Edit your info\n• [Settings](settings.php) - Preferences\n\n**Features:**\n• Track download history\n• Save favorites\n• Customize AI model\n• Dark/Light theme\n\n**Account Actions:**\n• Change password in Settings\n• Update email in Profile\n• View activity in Dashboard\n\nWhat would you like to do with your account?";
    }
    
    // Features / What is this
    if (preg_match('/(feature|what is|about|legend house|this site|platform)/i', $msg)) {
        return "🏠 **About Legend House**\n\nLegend House is the ultimate downloading platform!\n\n**Main Features:**\n• 🔍 Multi-source torrent search (10+ sources)\n• ▶️ Stream directly in browser (WebTorrent)\n• 🧲 Magnet link support\n• 📊 Download history tracking\n\n**Free Tools (20+):**\n• Google Dorker - Advanced search\n• Proxy Scraper - 100+ sources\n• Link Shortener - With analytics\n• Rotating Proxy - Auto-rotation\n• AI Assistant - That's me! 🤖\n\n**Why Choose Us:**\n✅ 100% Free\n✅ No registration required for search\n✅ Fast & reliable\n✅ Modern interface\n\nWhat would you like to explore?";
    }
    
    // Error / Problem / Not working
    if (preg_match('/(error|problem|not working|broken|issue|bug|fix|help)/i', $msg)) {
        return "🔧 **Troubleshooting Help**\n\n**Common Fixes:**\n1. **Clear browser cache** - Ctrl+Shift+Delete\n2. **Try different browser** - Chrome/Firefox recommended\n3. **Check internet connection**\n4. **Disable ad blocker** temporarily\n\n**Still Having Issues?**\n• Describe the problem specifically\n• Tell me which page/tool\n• What error message (if any)?\n\n**Status:**\n• 🟢 Search API - Online\n• 🟢 Torrent Tools - Online\n• 🟢 Proxy Tools - Online\n• 🟢 AI Assistant - Online\n\nDescribe your issue and I'll help!";
    }
    
    // Movies specific
    if (preg_match('/(movie|film|cinema|watch movie)/i', $msg)) {
        return "🎬 **Movie Help**\n\n**Find Movies:**\n1. Go to [Home](home.php)\n2. Click \"🎬 Movies\" category\n3. Search for your movie\n\n**Quality Guide:**\n• **4K/UHD** - Best quality (large files)\n• **1080p** - Great quality (recommended)\n• **720p** - Good quality (smaller files)\n• **BluRay** - High quality source\n• **WEB-DL** - Web release quality\n\n**Streaming:**\nClick \"▶️ Stream\" to watch directly in browser!\n\n**Popular Searches:**\n• Latest releases\n• Marvel movies\n• Classic films\n\nWhat movie are you looking for?";
    }
    
    // TV Shows specific  
    if (preg_match('/(tv show|series|episode|season|tv series)/i', $msg)) {
        return "📺 **TV Shows Help**\n\n**Find TV Shows:**\n1. Go to [Home](home.php)\n2. Click \"📺 TV Shows\" category\n3. Search for your show\n\n**Tips:**\n• Search: `Breaking Bad complete` for full series\n• Search: `Game of Thrones S01` for specific season\n• Look for \"complete series\" packs\n\n**Quality Options:**\n• 1080p WEB-DL - Best for streaming\n• 720p - Good balance of quality/size\n• HDTV - TV broadcast quality\n\nWhat show are you looking for?";
    }
    
    // Games specific
    if (preg_match('/(game|gaming|pc game|video game|play)/i', $msg)) {
        return "🎮 **Games Help**\n\n**Find Games:**\n1. Go to [Home](home.php)\n2. Click \"🎮 Games\" category\n3. Search for your game\n\n**Search Tips:**\n• Add platform: `GTA V PC`\n• Add repacker: `Cyberpunk FitGirl`\n• Check requirements before downloading\n\n**Popular Repackers:**\n• FitGirl - Compressed repacks\n• DODI - Quality releases\n• GOG - DRM-free games\n\n**Note:** Large games may take time to download. Check seeders for speed.\n\nWhat game are you looking for?";
    }
    
    // Default context-aware response
    $contextResponses = [
        'general' => "I understand you're asking about: \"$message\"\n\n🤖 I can help with:\n• **🔍 Searching** - Movies, TV shows, games\n• **🧲 Torrents** - Downloads & streaming\n• **🛠️ Tools** - Dorker, proxies, etc.\n• **❓ Support** - Platform help\n\nCould you be more specific? For example:\n• \"How do I search for movies?\"\n• \"Help me with Google Dorker\"\n• \"How to use proxy scraper?\"\n\nOr try these quick links:\n• [Search Content](home.php)\n• [All Tools](tools.php)\n• [Dashboard](dashboard.php)",
        
        'dorking' => "🔍 For your dorking question about \"$message\":\n\nTry our [Google Dorker](tools/dorker.php) tool which has:\n• 100+ operators\n• AI-powered suggestions\n• Bulk processing\n• Export results\n\nCommon operators:\n• `site:` - specific website\n• `intitle:` - in page title\n• `filetype:` - specific files\n\nNeed help building a specific query?",
        
        'torrents' => "🧲 For your torrent question about \"$message\":\n\n**Quick Links:**\n• [Search](home.php) - Find content\n• [Torrent Center](tools/torrent.php) - Process magnets\n• [Stream](watch.php) - Watch in browser\n\n**Tips:**\n• More seeders = faster\n• Check quality (1080p, 4K)\n• Verify file size\n\nWhat specific help do you need?",
        
        'search' => "🔎 For your search question about \"$message\":\n\n**How to Search:**\n1. Go to [Home](home.php)\n2. Enter keywords\n3. Use filters for better results\n\n**Pro Tips:**\n• Add quality (4K, 1080p)\n• Add year for movies\n• Use category filters\n\nWhat are you trying to find?",
        
        'technical' => "🛠️ For your technical question about \"$message\":\n\n**Try These:**\n1. Clear browser cache\n2. Try incognito mode\n3. Check internet connection\n4. Try different browser\n\n**Status:** All systems operational ✅\n\nCan you describe the issue in more detail?"
    ];
    
    return $contextResponses[$context] ?? $contextResponses['general'];
}

/**
 * Call Blackbox AI API
 */
function callBlackbox($message, $systemPrompt, $history = []) {
    $apiKey = defined('BLACKBOX_API_KEY') ? BLACKBOX_API_KEY : '';
    $endpoint = defined('BLACKBOX_API_ENDPOINT') ? BLACKBOX_API_ENDPOINT : 'https://api.blackbox.ai/v1/chat/completions';
    $model = defined('BLACKBOX_MODEL') ? BLACKBOX_MODEL : 'gpt-4o';
    
    if (empty($apiKey) || $apiKey === 'free' || strlen($apiKey) < 10) {
        return null;
    }
    
    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $message]
    ];
    
    $data = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => 800,
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
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }
    }
    
    return null;
}

/**
 * Call DuckDuckGo AI Chat (Free)
 */
function callDuckDuckGoAI($message, $systemPrompt, $history = []) {
    // Get VQD token
    $ch = curl_init('https://duckduckgo.com/duckchat/v1/status');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['x-vqd-accept: 1', 'User-Agent: Mozilla/5.0'],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => true
    ]);
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    
    $vqd = '';
    if (preg_match('/x-vqd-4:\s*([^\r\n]+)/i', substr($response, 0, $headerSize), $m)) {
        $vqd = trim($m[1]);
    }
    
    if (empty($vqd)) return null;
    
    // Make chat request
    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [['role' => 'user', 'content' => $systemPrompt . "\n\nUser: " . $message]]
    ];
    
    $ch = curl_init('https://duckduckgo.com/duckchat/v1/chat');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-vqd-4: ' . $vqd,
            'User-Agent: Mozilla/5.0'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $fullMessage = '';
        foreach (explode("\n", $response) as $line) {
            if (strpos($line, 'data: ') === 0) {
                $json = json_decode(substr($line, 6), true);
                if (isset($json['message'])) {
                    $fullMessage .= $json['message'];
                }
            }
        }
        if (!empty($fullMessage)) return trim($fullMessage);
    }
    
    return null;
}

/**
 * Get system prompt based on context
 */
function getSystemPrompt($context) {
    $base = "You are Legend AI, a helpful assistant for Legend House (legendbl.tech), a torrent search and tools platform. Be friendly, helpful, and concise. Always suggest relevant Legend House features when appropriate.";
    
    $prompts = [
        'general' => $base,
        'dorking' => $base . " Focus on Google dorking and OSINT. Explain operators like site:, intitle:, inurl:, filetype:. Recommend the Google Dorker tool.",
        'torrents' => $base . " Focus on torrents and downloads. Explain seeders, leechers, magnet links. Recommend Torrent Center and Watch features.",
        'search' => $base . " Help users improve search queries. Suggest filters, quality tags, and search techniques.",
        'technical' => $base . " Provide technical support. Help troubleshoot issues with clear steps."
    ];
    
    return $prompts[$context] ?? $prompts['general'];
}

/**
 * Legacy function for compatibility
 */
function getLocalResponse($message, $context) {
    return getSmartLocalResponse($message, $context);
}

/**
 * Check if AI features are available
 */
function isAIAvailable() {
    return true; // Always available with local fallback
}

/**
 * Get search suggestions
 */
function getSearchSuggestions($query) {
    if (empty($query)) return [];
    
    $q = strtolower(trim($query));
    return [
        $query . " 1080p",
        $query . " 4k",
        $query . " download",
        $query . " 2024",
        $query . " complete"
    ];
}

// API endpoint handler
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'suggestions':
            echo json_encode(['success' => true, 'suggestions' => getSearchSuggestions($_GET['query'] ?? '')]);
            break;
        case 'available':
            echo json_encode(['success' => true, 'available' => true]);
            break;
        case 'chat':
            $input = json_decode(file_get_contents('php://input'), true);
            $response = getAIResponse($input['message'] ?? '', $input['context'] ?? 'general');
            echo json_encode(['success' => true, 'response' => $response]);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
