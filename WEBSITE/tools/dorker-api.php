<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

// Check if user is logged in
$user = getCurrentUser();
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$db = getDatabase();
$action = $_GET['action'] ?? '';

// Initialize dorker tables if not exist
$db->exec("
    CREATE TABLE IF NOT EXISTS dorker_queries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        dork_query TEXT NOT NULL,
        category TEXT,
        created_at INTEGER NOT NULL
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS dorker_results (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        query_id INTEGER NOT NULL,
        title TEXT,
        url TEXT UNIQUE,
        description TEXT,
        cached_url TEXT,
        found_at INTEGER NOT NULL
    )
");

switch ($action) {
    case 'dork':
        handleDork($db, $user);
        break;
    case 'export':
        handleExport($db, $user);
        break;
    case 'stats':
        handleStats($db, $user);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function handleDork($db, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    $query = $input['query'] ?? '';
    
    if (empty($query)) {
        echo json_encode(['success' => false, 'error' => 'Query is required']);
        return;
    }

    // No rate limiting - unlimited dorking for discovery

    // Save query
    $stmt = $db->prepare("INSERT INTO dorker_queries (user_id, dork_query, created_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $query, time()]);
    $queryId = $db->lastInsertId();

    // Perform dorking (reverse-engineered Google scraping)
    $results = scrapeGoogle($query);

    // Save results
    foreach ($results as $result) {
        $stmt = $db->prepare("
            INSERT OR IGNORE INTO dorker_results 
            (query_id, title, url, description, cached_url, found_at) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $queryId,
            $result['title'],
            $result['url'],
            $result['description'],
            $result['cached'] ?? null,
            time()
        ]);
    }

    // Get stats
    $stats = getStats($db, $user);

    echo json_encode([
        'success' => true,
        'results' => $results,
        'stats' => $stats
    ]);
}

function scrapeGoogle($dork, $page = 0) {
    // User agents for rotation (anti-detection)
    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15'
    ];

    $url = "https://www.google.com/search?q=" . urlencode($dork);
    $url .= "&start=" . ($page * 10);
    $url .= "&num=10";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'User-Agent: ' . $userAgents[array_rand($userAgents)],
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'DNT: 1',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Cache-Control: max-age=0'
        ]
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$html) {
        return [];
    }

    // Check for CAPTCHA
    if (strpos($html, 'recaptcha') !== false || strpos($html, 'unusual traffic') !== false) {
        return [];
    }

    // Parse results
    return parseGoogleResults($html);
}

function parseGoogleResults($html) {
    $results = [];
    
    // Remove newlines and extra spaces
    $html = preg_replace('/\s+/', ' ', $html);
    
    // Match result blocks (this is reverse-engineered parsing)
    // Google's HTML structure: <div class="g"> contains each result
    preg_match_all('/<div class="[^"]*\bg\b[^"]*"[^>]*>(.*?)<\/div>(?=<div class="[^"]*\bg\b|$)/s', $html, $blocks);
    
    foreach ($blocks[1] as $block) {
        $result = [];
        
        // Extract URL
        if (preg_match('/<a[^>]+href="([^"]+)"[^>]*>/i', $block, $urlMatch)) {
            $url = $urlMatch[1];
            // Clean URL (remove Google redirects)
            if (preg_match('/url\?q=([^&]+)/', $url, $cleanUrl)) {
                $url = urldecode($cleanUrl[1]);
            }
            $result['url'] = $url;
        }
        
        // Extract title
        if (preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $block, $titleMatch)) {
            $result['title'] = strip_tags($titleMatch[1]);
            $result['title'] = html_entity_decode($result['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // Extract description/snippet
        if (preg_match('/<div[^>]*data-sncf="[^"]*"[^>]*>(.*?)<\/div>/is', $block, $descMatch)) {
            $result['description'] = strip_tags($descMatch[1]);
            $result['description'] = html_entity_decode($result['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/<span[^>]*>(.*?)<\/span>/is', $block, $descMatch)) {
            $result['description'] = strip_tags($descMatch[1]);
            $result['description'] = html_entity_decode($result['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // Extract cached link if available
        if (preg_match('/cache:[^"\']+/i', $block, $cacheMatch)) {
            $result['cached'] = 'https://webcache.googleusercontent.com/search?' . $cacheMatch[0];
        }
        
        // Only add if we have at least a URL and title
        if (isset($result['url']) && isset($result['title'])) {
            // Clean up
            $result['title'] = trim($result['title']);
            $result['description'] = isset($result['description']) ? trim($result['description']) : '';
            
            // Skip if title or URL is empty
            if (empty($result['title']) || empty($result['url'])) {
                continue;
            }
            
            // Skip Google's own links
            if (strpos($result['url'], 'google.com') !== false) {
                continue;
            }
            
            $results[] = $result;
        }
    }
    
    return $results;
}

function handleExport($db, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    $results = $input['results'] ?? [];
    $format = $_GET['format'] ?? 'txt';
    
    if (empty($results)) {
        echo json_encode(['success' => false, 'error' => 'No results to export']);
        return;
    }

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="dorker-results-' . time() . '.' . $format . '"');

    switch ($format) {
        case 'txt':
            foreach ($results as $result) {
                echo $result['url'] . "\n";
            }
            break;
        case 'csv':
            echo "Title,URL,Description\n";
            foreach ($results as $result) {
                echo '"' . str_replace('"', '""', $result['title']) . '",';
                echo '"' . str_replace('"', '""', $result['url']) . '",';
                echo '"' . str_replace('"', '""', $result['description']) . '"' . "\n";
            }
            break;
        case 'json':
            echo json_encode($results, JSON_PRETTY_PRINT);
            break;
    }
    exit;
}

function handleStats($db, $user) {
    $stats = getStats($db, $user);
    echo json_encode(['success' => true, 'stats' => $stats]);
}

function getStats($db, $user) {
    // Total dorks run
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM dorker_queries WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $totalDorks = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total results found
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT dr.id) as count 
        FROM dorker_results dr
        JOIN dorker_queries dq ON dr.query_id = dq.id
        WHERE dq.user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $totalResults = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Success rate
    $successRate = $totalDorks > 0 ? round(($totalResults / $totalDorks) * 10) : 0;

    return [
        'total_dorks' => $totalDorks,
        'total_results' => $totalResults,
        'saved_queries' => $totalDorks,
        'success_rate' => $successRate
    ];
}
