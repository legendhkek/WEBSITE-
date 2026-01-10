<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Advanced AI Helper with Blackbox API
 * ═══════════════════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/config.php';

// Ensure cache directory exists
if (!file_exists(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0755, true);
}

/**
 * Get smart search suggestions using Blackbox AI
 */
function getSearchSuggestions($query) {
    if (empty($query) || !defined('BLACKBOX_API_KEY')) {
        return [];
    }
    
    // Check cache first
    $cacheKey = CACHE_DIR . 'suggestions_' . md5($query) . '.json';
    if (file_exists($cacheKey) && (time() - filemtime($cacheKey)) < 3600) {
        $cached = @file_get_contents($cacheKey);
        if ($cached) {
            return json_decode($cached, true);
        }
    }
    
    $prompt = "Given the search query '$query' for finding torrents, suggest 5 related or improved search terms. Return ONLY a JSON array of strings, no explanation. Example: [\"term1\", \"term2\", \"term3\", \"term4\", \"term5\"]";
    
    $result = callBlackboxAPI($prompt);
    
    if ($result && isset($result['suggestions'])) {
        // Cache the result
        @file_put_contents($cacheKey, json_encode($result), LOCK_EX);
        return $result;
    }
    
    return [];
}

/**
 * Analyze torrent content and provide insights
 */
function analyzeTorrentContent($torrentName) {
    if (empty($torrentName) || !defined('BLACKBOX_API_KEY')) {
        return [];
    }
    
    $prompt = "Analyze this torrent name and extract: genre, quality (resolution), content type (movie/tv/game/etc), year if present. Return as JSON: {\"genre\":\"...\",\"quality\":\"...\",\"type\":\"...\",\"year\":\"...\"}. Torrent name: '$torrentName'";
    
    $result = callBlackboxAPI($prompt);
    
    if ($result && is_array($result)) {
        return $result;
    }
    
    return [];
}

/**
 * Get trending topics for torrent searches
 */
function getTrendingTopics() {
    // Check cache
    $cacheKey = CACHE_DIR . 'trending_topics.json';
    if (file_exists($cacheKey) && (time() - filemtime($cacheKey)) < 3600) {
        $cached = @file_get_contents($cacheKey);
        if ($cached) {
            return json_decode($cached, true);
        }
    }
    
    if (!defined('BLACKBOX_API_KEY')) {
        return [];
    }
    
    $prompt = "List 10 currently trending movies, TV shows, or games that people are likely searching for torrents. Return ONLY a JSON array of strings.";
    
    $result = callBlackboxAPI($prompt);
    
    if ($result && isset($result['suggestions'])) {
        @file_put_contents($cacheKey, json_encode($result['suggestions']), LOCK_EX);
        return $result['suggestions'];
    }
    
    return [];
}

/**
 * Validate Blackbox API configuration
 */
function validateBlackboxConfig() {
    if (!defined('BLACKBOX_API_KEY') || empty(BLACKBOX_API_KEY)) {
        error_log("Blackbox API: API key not configured");
        return false;
    }
    
    if (!defined('BLACKBOX_API_ENDPOINT') || empty(BLACKBOX_API_ENDPOINT)) {
        error_log("Blackbox API: API endpoint not configured");
        return false;
    }

    if (!defined('BLACKBOX_MODEL') || empty(BLACKBOX_MODEL)) {
        error_log("Blackbox API: Model not configured");
        return false;
    }
    
    return true;
}

/**
 * Call Blackbox OpenAI-compatible chat completions API.
 *
 * @param array<int, array{role:string, content:string}> $messages
 */
function callBlackboxChat(array $messages, int $maxTokens = 500) {
    if (!validateBlackboxConfig()) {
        error_log("Blackbox API: Configuration validation failed");
        return null;
    }

    $data = [
        'model' => BLACKBOX_MODEL,
        'messages' => $messages,
        'max_tokens' => $maxTokens,
        'temperature' => 0.7
    ];
    
    $ch = curl_init(BLACKBOX_API_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . BLACKBOX_API_KEY,
            'User-Agent: LegendHouse/1.0 (+https://example.invalid)'
        ],
        CURLOPT_TIMEOUT => 45,
        CURLOPT_SSL_VERIFYPEER => true,
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
        error_log("Blackbox API CURL error (errno: $curlErrno): $curlError");
        // Check for specific network issues
        if ($curlErrno === 6) { // CURLE_COULDNT_RESOLVE_HOST
            error_log("Blackbox API: DNS resolution failed - check network connectivity");
        } elseif ($curlErrno === 7) { // CURLE_COULDNT_CONNECT
            error_log("Blackbox API: Connection failed - service may be down");
        } elseif ($curlErrno === 28) { // CURLE_OPERATION_TIMEDOUT
            error_log("Blackbox API: Request timeout - service may be slow");
        }
        return null;
    }
    
    if ($httpCode !== 200) {
        error_log("Blackbox API HTTP error: $httpCode - Response: " . substr($response, 0, 500));
        return null;
    }
    
    if (!$response) {
        error_log("Blackbox API: Empty response");
        return null;
    }
    
    $json = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($json)) {
        error_log("Blackbox API: Invalid JSON response");
        return null;
    }

    $content = $json['choices'][0]['message']['content'] ?? '';
    $content = is_string($content) ? trim($content) : '';
    return $content !== '' ? $content : null;
}

/**
 * Call Blackbox AI API with a single prompt and parse structured results.
 */
function callBlackboxAPI($prompt) {
    $content = callBlackboxChat([['role' => 'user', 'content' => $prompt]], 500);
    if ($content === null) return null;

    // Remove any markdown code fences if present
    $clean = preg_replace('/^```(?:json)?\s*/i', '', $content);
    $clean = preg_replace('/\s*```$/i', '', $clean);
    $clean = trim((string)$clean);

    // Try to parse JSON response
    $parsed = json_decode($clean, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if (is_array($parsed) && !isset($parsed['suggestions'])) {
            return ['suggestions' => $parsed];
        }
        return $parsed;
    }

    // If not JSON, return as improved query
    return ['improved_query' => $clean];
}

/**
 * Check if Blackbox AI is available
 */
function isBlackboxAvailable() {
    return defined('BLACKBOX_API_KEY') &&
        !empty(BLACKBOX_API_KEY) &&
        BLACKBOX_API_KEY !== 'YOUR_BLACKBOX_API_KEY_HERE' &&
        defined('BLACKBOX_API_ENDPOINT') &&
        !empty(BLACKBOX_API_ENDPOINT) &&
        defined('BLACKBOX_MODEL') &&
        !empty(BLACKBOX_MODEL);
}

/**
 * API endpoint for frontend JavaScript (ONLY when ai-helper.php is executed directly).
 *
 * IMPORTANT: This file is also included by other scripts (e.g. ai-chat.php).
 * We must not hijack their requests based on ?action=...
 */
$__ai_helper_is_direct_request = isset($_SERVER['SCRIPT_FILENAME']) &&
    realpath((string)$_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__);

if ($__ai_helper_is_direct_request && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'suggestions':
            $query = $_GET['query'] ?? '';
            $suggestions = getSearchSuggestions($query);
            echo json_encode(['success' => true, 'suggestions' => $suggestions]);
            break;
            
        case 'trending':
            $trending = getTrendingTopics();
            echo json_encode(['success' => true, 'trending' => $trending]);
            break;
            
        case 'analyze':
            $name = $_GET['name'] ?? '';
            $analysis = analyzeTorrentContent($name);
            echo json_encode(['success' => true, 'analysis' => $analysis]);
            break;
            
        case 'available':
            echo json_encode(['success' => true, 'available' => isBlackboxAvailable()]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
