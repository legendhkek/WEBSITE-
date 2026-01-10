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
 * Check whether the configured endpoint is an OpenAI-compatible chat completions endpoint.
 */
function isBlackboxChatCompletionsEndpoint($endpoint) {
    if (empty($endpoint)) return false;
    return (strpos($endpoint, 'api.blackbox.ai') !== false) && (strpos($endpoint, '/v1/chat/completions') !== false);
}

/**
 * Low-level chat call that supports both Blackbox endpoint styles.
 *
 * Returns assistant content string on success, or null on failure.
 */
function callBlackboxChatMessages($messages, $maxTokens = 500) {
    if (!validateBlackboxConfig()) {
        error_log("Blackbox API: Configuration validation failed");
        return null;
    }

    $endpoint = BLACKBOX_API_ENDPOINT;
    $isCompletions = isBlackboxChatCompletionsEndpoint($endpoint);

    if ($isCompletions) {
        // OpenAI-compatible request format (retry once on transient empty/garbage output)
        $data = [
            'model' => defined('BLACKBOX_MODEL') && !empty(BLACKBOX_MODEL) ? BLACKBOX_MODEL : 'blackboxai/mistralai/mistral-7b-instruct:free',
            'messages' => $messages,
            'max_tokens' => $maxTokens,
            'temperature' => 0.2,
            'stream' => false
        ];

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $ch = curl_init($endpoint);
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
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                // Keep existing behavior (some hosts have strict CA bundles)
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);

            if ($curlError) {
                error_log("Blackbox API CURL error (errno: $curlErrno): $curlError");
                return null;
            }
            if ($httpCode < 200 || $httpCode >= 300) {
                error_log("Blackbox API HTTP error: $httpCode - Response: " . substr((string)$response, 0, 500));
                return null;
            }
            if (!$response) {
                error_log("Blackbox API: Empty response");
                return null;
            }

            $parsed = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
                error_log("Blackbox API: Non-JSON response from chat/completions: " . substr((string)$response, 0, 200));
                return null;
            }
            if (isset($parsed['error'])) {
                error_log("Blackbox API error: " . substr(json_encode($parsed['error']), 0, 500));
                return null;
            }

            $content = $parsed['choices'][0]['message']['content'] ?? null;
            if (!is_string($content)) {
                // Some providers use "text" instead of nested message content
                $content = $parsed['choices'][0]['text'] ?? null;
            }
            $content = is_string($content) ? trim($content) : '';

            // Some upstreams occasionally return sentinel tokens like "<s>"
            if ($content !== '' && $content !== '<s>' && $content !== '</s>') {
                return $content;
            }

            if ($attempt === 0) {
                // Retry once (very short backoff)
                usleep(150000);
                continue;
            }

            error_log("Blackbox API: Missing/invalid assistant content in response");
            return null;
        }
    }

    // Legacy Blackbox "web" endpoint format (returns plain text)
    $data = [
        'messages' => $messages,
        'id' => uniqid('legendhouse_', true),
        'previewToken' => null,
        'userId' => null,
        'codeModelMode' => true,
        'agentMode' => [],
        'trendingAgentMode' => [],
        'isMicMode' => false,
        'maxTokens' => $maxTokens,
        'isChromeExt' => false,
        'githubToken' => null,
        'clickedAnswer2' => false,
        'clickedAnswer3' => false,
        'clickedForceWebSearch' => false,
        'visitFromDelta' => false,
        'mobileClient' => false
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
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
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
        error_log("Blackbox API HTTP error: $httpCode - Response: " . substr((string)$response, 0, 500));
        return null;
    }

    if (!$response) {
        error_log("Blackbox API: Empty response");
        return null;
    }

    $content = trim((string)$response);
    $content = preg_replace('/^\$@\$v=undefined-rv1\$@\$/i', '', $content);
    $content = trim($content);
    return $content !== '' ? $content : null;
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
            $decoded = json_decode($cached, true);
            return is_array($decoded) ? $decoded : [];
        }
    }
    
    $prompt = "Given the search query '$query' for finding torrents, suggest 5 related or improved search terms. Return ONLY a JSON array of strings, no explanation. Example: [\"term1\", \"term2\", \"term3\", \"term4\", \"term5\"]";
    
    $result = callBlackboxAPI($prompt);
    
    if ($result && isset($result['suggestions']) && is_array($result['suggestions'])) {
        // Cache the array only
        @file_put_contents($cacheKey, json_encode($result['suggestions']), LOCK_EX);
        return $result['suggestions'];
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
            $decoded = json_decode($cached, true);
            return is_array($decoded) ? $decoded : [];
        }
    }
    
    if (!defined('BLACKBOX_API_KEY')) {
        return [];
    }
    
    $prompt = "List 10 currently trending movies, TV shows, or games that people are likely searching for torrents. Return ONLY a JSON array of strings.";
    
    $result = callBlackboxAPI($prompt);
    
    if ($result && isset($result['suggestions']) && is_array($result['suggestions'])) {
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
 * Call Blackbox AI API
 */
function callBlackboxAPI($prompt) {
    if (!validateBlackboxConfig()) {
        error_log("Blackbox API: Configuration validation failed");
        return null;
    }

    $content = callBlackboxChatMessages([['role' => 'user', 'content' => $prompt]], 500);
    if ($content === null) {
        return null;
    }

    // Remove any markdown code blocks if present
    $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
    $content = preg_replace('/\s*```$/i', '', $content);
    
    // Try to parse JSON response
    $parsed = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if (is_array($parsed) && !isset($parsed['suggestions'])) {
            return ['suggestions' => $parsed];
        }
        return $parsed;
    }

    // Fallback: try to extract a JSON array/object from a noisy response
    if (preg_match('/\[[\s\S]*\]/', $content, $m)) {
        $maybe = json_decode($m[0], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) {
            return ['suggestions' => $maybe];
        }
    }
    if (preg_match('/\{[\s\S]*\}/', $content, $m)) {
        $maybe = json_decode($m[0], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) {
            return $maybe;
        }
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
