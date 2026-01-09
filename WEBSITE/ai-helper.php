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
    
    return true;
}

/**
 * Call Blackbox AI API (OpenAI-compatible)
 */
function callBlackboxAPI($prompt) {
    if (!validateBlackboxConfig()) {
        return null;
    }
    
    // OpenAI-compatible request format
    $data = [
        'model' => 'blackboxai',
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 500,
        'temperature' => 0.7
    ];
    
    $ch = curl_init(BLACKBOX_API_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . BLACKBOX_API_KEY
        ],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        error_log("Blackbox API CURL error: $curlError");
        // Don't expose detailed errors to users, just log them
        return null;
    }
    
    if ($httpCode !== 200) {
        error_log("Blackbox API HTTP error: $httpCode - Response: " . substr($response, 0, 200));
        return null;
    }
    
    if (!$response) {
        error_log("Blackbox API: Empty response");
        return null;
    }
    
    $result = json_decode($response, true);
    
    if (!$result || !isset($result['choices'][0]['message']['content'])) {
        error_log("Blackbox API: Invalid response format");
        return null;
    }
    
    $content = $result['choices'][0]['message']['content'];
    
    // Try to parse JSON response
    $parsed = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if (is_array($parsed)) {
            return ['suggestions' => $parsed];
        }
        return $parsed;
    }
    
    // If not JSON, return as improved query
    return ['improved_query' => trim($content)];
}

/**
 * Check if Blackbox AI is available
 */
function isBlackboxAvailable() {
    return defined('BLACKBOX_API_KEY') && !empty(BLACKBOX_API_KEY) && BLACKBOX_API_KEY !== 'YOUR_BLACKBOX_API_KEY_HERE';
}

/**
 * API endpoint for frontend JavaScript
 */
if (isset($_GET['action'])) {
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
