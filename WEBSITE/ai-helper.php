<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Advanced AI Helper with Multiple Providers
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Supports multiple free AI providers with automatic fallback:
 * 1. DeepInfra (Mistral/Llama) - Primary
 * 2. HuggingFace Inference - Secondary  
 * 3. Blackbox AI - Fallback
 * 4. Local response - Last resort
 */

require_once __DIR__ . '/config.php';

// Ensure cache directory exists
if (!file_exists(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0755, true);
}

/**
 * Main AI chat function with provider fallback
 */
function getAIResponse($message, $context = 'general', $conversationHistory = []) {
    $providers = [
        'deepinfra' => 'callDeepInfra',
        'huggingface' => 'callHuggingFace',
        'blackbox' => 'callBlackbox'
    ];
    
    $systemPrompt = getSystemPrompt($context);
    
    foreach ($providers as $name => $function) {
        try {
            $response = $function($message, $systemPrompt, $conversationHistory);
            if ($response && strlen($response) > 10) {
                return $response;
            }
        } catch (Exception $e) {
            error_log("AI Provider $name failed: " . $e->getMessage());
            continue;
        }
    }
    
    // Fallback to intelligent local responses
    return getLocalResponse($message, $context);
}

/**
 * Call DeepInfra API (Free tier available)
 */
function callDeepInfra($message, $systemPrompt, $history = []) {
    $endpoint = 'https://api.deepinfra.com/v1/openai/chat/completions';
    
    $messages = [];
    $messages[] = ['role' => 'system', 'content' => $systemPrompt];
    
    foreach ($history as $msg) {
        $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
    }
    $messages[] = ['role' => 'user', 'content' => $message];
    
    $data = [
        'model' => 'meta-llama/Meta-Llama-3.1-8B-Instruct',
        'messages' => $messages,
        'max_tokens' => 1024,
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
 * Call HuggingFace Inference API
 */
function callHuggingFace($message, $systemPrompt, $history = []) {
    $endpoint = 'https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.2';
    
    $prompt = "$systemPrompt\n\nUser: $message\n\nAssistant:";
    
    $data = [
        'inputs' => $prompt,
        'parameters' => [
            'max_new_tokens' => 500,
            'temperature' => 0.7,
            'return_full_text' => false
        ]
    ];
    
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data[0]['generated_text'])) {
            return trim($data[0]['generated_text']);
        }
    }
    
    return null;
}

/**
 * Call Blackbox AI API
 */
function callBlackbox($message, $systemPrompt, $history = []) {
    $endpoint = 'https://www.blackbox.ai/api/chat';
    
    $messages = [];
    foreach ($history as $msg) {
        $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
    }
    $messages[] = ['role' => 'user', 'content' => "$systemPrompt\n\n$message"];
    
    $data = [
        'messages' => $messages,
        'id' => uniqid('legendhouse_'),
        'previewToken' => null,
        'userId' => null,
        'codeModelMode' => true,
        'agentMode' => [],
        'trendingAgentMode' => [],
        'isMicMode' => false,
        'maxTokens' => 1024,
        'isChromeExt' => false,
        'githubToken' => null
    ];
    
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: https://www.blackbox.ai',
            'Referer: https://www.blackbox.ai/',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        CURLOPT_TIMEOUT => 45,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $content = trim($response);
        $content = preg_replace('/^\$@\$v=undefined-rv1\$@\$/i', '', $content);
        return trim($content);
    }
    
    return null;
}

/**
 * Intelligent local responses when AI providers fail
 */
function getLocalResponse($message, $context) {
    $message = strtolower($message);
    
    // Common greetings
    if (preg_match('/(hello|hi|hey|greetings)/i', $message)) {
        return "Hello! 👋 I'm your Legend House assistant. I can help you with:\n\n• **Google Dorking** - Advanced search techniques\n• **Torrents** - Finding and managing downloads\n• **Proxy Tools** - Scraping and rotating proxies\n• **Platform Navigation** - Using our tools effectively\n\nWhat would you like help with today?";
    }
    
    // Dorking help
    if (preg_match('/(dork|dorking|google search|site:|intitle:|inurl:)/i', $message)) {
        return "🔍 **Google Dorking Tips:**\n\n**Basic Operators:**\n• `site:example.com` - Search specific domain\n• `intitle:keyword` - Search in page titles\n• `inurl:admin` - Search in URLs\n• `filetype:pdf` - Find specific file types\n\n**Advanced Operators:**\n• `intext:password` - Search page content\n• `\"exact phrase\"` - Exact match\n• `site:*.gov filetype:pdf` - Combine operators\n\n**Pro Tips:**\n1. Combine multiple operators for precision\n2. Use `-` to exclude terms\n3. Use `|` for OR logic\n\nTry our **Google Dorker** tool for automated searching!";
    }
    
    // Torrent help
    if (preg_match('/(torrent|magnet|download|seed|leech)/i', $message)) {
        return "🧲 **Torrent Help:**\n\n**Getting Started:**\n1. Go to **Home** and search for content\n2. Copy the magnet link\n3. Use **Torrent Center** to process it\n\n**Tips:**\n• More seeders = faster downloads\n• Check file sizes before downloading\n• Verify content quality in comments\n\n**Our Tools:**\n• **Torrent Center** - Process magnets & files\n• **Watch** - Stream torrents in browser\n• **Proxy Scraper** - Hide your IP\n\nNeed help finding something specific?";
    }
    
    // Proxy help
    if (preg_match('/(proxy|proxies|ip|anonymous|vpn)/i', $message)) {
        return "🌐 **Proxy Tools:**\n\n**Proxy Scraper:**\n• Scrapes 100+ sources\n• Auto-validates proxies\n• Exports in TXT/CSV/JSON\n\n**Rotating Proxy Maker:**\n• Upload 200+ proxies\n• Creates rotating pool\n• API access available\n\n**Residential Proxy:**\n• Convert datacenter to residential\n• Real-time health monitoring\n• Auto-rotation strategies\n\n**Usage Tips:**\n1. Always test proxies before use\n2. HTTP proxies are faster\n3. SOCKS5 for better anonymity\n\nHead to **Tools > Proxy Scraper** to get started!";
    }
    
    // Help/features
    if (preg_match('/(help|feature|what can you|how to)/i', $message)) {
        return "🛠️ **Legend House Features:**\n\n**🔍 Search & Discovery:**\n• Multi-source torrent search\n• AI-powered suggestions\n• Category filtering\n\n**🧲 Torrent Tools:**\n• Magnet link processor\n• .torrent file parser\n• WebTorrent streaming\n\n**🔗 Utility Tools:**\n• Google Dorker (100+ operators)\n• Proxy Scraper (100+ sources)\n• Link Shortener with analytics\n• Rotating proxy maker\n\n**🤖 AI Features:**\n• Smart search suggestions\n• Content analysis\n• This chat assistant!\n\nExplore all tools at **Dashboard > Tools**!";
    }
    
    // Default response
    return "I understand you're asking about: \"$message\"\n\nI can help with:\n• **🔍 Google Dorking** - Search techniques\n• **🧲 Torrents** - Finding content\n• **🌐 Proxies** - Anonymity tools\n• **🛠️ Platform** - Using our features\n\nCould you be more specific about what you need help with? Or try one of our tools directly:\n\n• [Google Dorker](tools/dorker.php)\n• [Torrent Center](tools/torrent.php)\n• [Proxy Scraper](tools/proxy-scraper.php)";
}

/**
 * Get system prompt based on context
 */
function getSystemPrompt($context) {
    $prompts = [
        'general' => 'You are a helpful AI assistant for Legend House, a torrent search and tools platform. Help users with their questions about torrents, tools, and platform features. Be friendly, concise, and helpful. Always suggest relevant Legend House tools when appropriate.',
        
        'dorking' => 'You are an expert in Google dorking and OSINT techniques. Help users create effective Google dork queries for legitimate research purposes. Explain operators like site:, intitle:, inurl:, filetype:, etc. Emphasize ethical and legal use. Suggest the Legend House Google Dorker tool for automated searching.',
        
        'torrents' => 'You are a torrent and P2P expert. Help users understand how torrents work, find content effectively, and use the Legend House platform. Explain concepts like seeders, leechers, magnet links, and trackers. Recommend the Torrent Center and WebTorrent player.',
        
        'search' => 'You are a search optimization expert. Help users improve their search queries to find specific content. Suggest related terms, filtering techniques, and advanced search operators. The Legend House platform searches multiple torrent sources.',
        
        'technical' => 'You are a technical support assistant for Legend House. Help users troubleshoot issues, understand features, and navigate the platform. Provide clear step-by-step guidance. Cover authentication, tools usage, and platform features.'
    ];
    
    return $prompts[$context] ?? $prompts['general'];
}

/**
 * Get search suggestions using AI
 */
function getSearchSuggestions($query) {
    if (empty($query)) return [];
    
    $cacheKey = CACHE_DIR . 'suggestions_' . md5($query) . '.json';
    if (file_exists($cacheKey) && (time() - filemtime($cacheKey)) < 3600) {
        $cached = @file_get_contents($cacheKey);
        if ($cached) return json_decode($cached, true);
    }
    
    // Generate suggestions based on query patterns
    $suggestions = generateLocalSuggestions($query);
    
    if (!empty($suggestions)) {
        @file_put_contents($cacheKey, json_encode($suggestions), LOCK_EX);
    }
    
    return $suggestions;
}

/**
 * Generate local search suggestions
 */
function generateLocalSuggestions($query) {
    $suggestions = [];
    $query = strtolower(trim($query));
    
    // Common search patterns
    $patterns = [
        'movie' => ["{$query} 1080p", "{$query} 4k", "{$query} bluray", "{$query} hdrip", "{$query} extended"],
        'show' => ["{$query} complete series", "{$query} season 1", "{$query} all seasons", "{$query} 720p", "{$query} web-dl"],
        'game' => ["{$query} pc", "{$query} crack", "{$query} fitgirl", "{$query} gog", "{$query} steam"],
        'software' => ["{$query} crack", "{$query} full version", "{$query} portable", "{$query} latest", "{$query} activated"],
        'music' => ["{$query} flac", "{$query} mp3 320", "{$query} discography", "{$query} album", "{$query} collection"]
    ];
    
    // Detect type and return appropriate suggestions
    foreach ($patterns as $type => $typesuggestions) {
        if (strpos($query, $type) !== false || strlen($query) > 3) {
            $suggestions = array_merge($suggestions, array_slice($typesuggestions, 0, 5));
        }
    }
    
    if (empty($suggestions)) {
        $suggestions = [
            $query . " 1080p",
            $query . " download",
            $query . " hd",
            $query . " 2024",
            $query . " free"
        ];
    }
    
    return array_unique(array_slice($suggestions, 0, 5));
}

/**
 * Check if AI features are available
 */
function isAIAvailable() {
    return defined('AI_FEATURES_ENABLED') && AI_FEATURES_ENABLED === true;
}

/**
 * API endpoint handler
 */
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'suggestions':
            $query = $_GET['query'] ?? '';
            $suggestions = getSearchSuggestions($query);
            echo json_encode(['success' => true, 'suggestions' => $suggestions]);
            break;
            
        case 'available':
            echo json_encode(['success' => true, 'available' => isAIAvailable()]);
            break;
            
        case 'chat':
            $input = json_decode(file_get_contents('php://input'), true);
            $message = $input['message'] ?? '';
            $context = $input['context'] ?? 'general';
            
            if (empty($message)) {
                echo json_encode(['success' => false, 'error' => 'Message required']);
            } else {
                $response = getAIResponse($message, $context);
                echo json_encode(['success' => true, 'response' => $response]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
