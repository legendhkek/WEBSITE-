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

function scrapeGoogle($dork, $page = 0, $maxPages = 3) {
    // Enhanced user agents for better rotation (anti-detection)
    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_2_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:122.0) Gecko/20100101 Firefox/122.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36 OPR/107.0.0.0'
    ];

    $allResults = [];
    
    // Multi-page scraping for more comprehensive results
    for ($currentPage = $page; $currentPage < $page + $maxPages; $currentPage++) {
        $url = "https://www.google.com/search?q=" . urlencode($dork);
        $url .= "&start=" . ($currentPage * 10);
        $url .= "&num=10";
        $url .= "&filter=0"; // Disable auto-filtering of similar results
        $url .= "&pws=0"; // Disable personalization

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING => '', // Enable gzip/deflate decompression
            CURLOPT_HTTPHEADER => [
                'User-Agent: ' . $userAgents[array_rand($userAgents)],
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language: en-US,en;q=0.9',
                'Accept-Encoding: gzip, deflate, br',
                'DNT: 1',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: none',
                'Sec-Fetch-User: ?1',
                'Cache-Control: max-age=0',
                'sec-ch-ua: "Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"'
            ],
            CURLOPT_REFERER => 'https://www.google.com/'
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$html) {
            break; // Stop if request fails
        }

        // Check for CAPTCHA or rate limiting
        if (strpos($html, 'recaptcha') !== false || 
            strpos($html, 'unusual traffic') !== false ||
            strpos($html, 'automated queries') !== false) {
            break; // Stop if blocked
        }

        // Parse results from this page
        $pageResults = parseGoogleResults($html);
        
        if (empty($pageResults)) {
            break; // Stop if no more results
        }
        
        $allResults = array_merge($allResults, $pageResults);
        
        // Add delay between pages to avoid detection
        if ($currentPage < $page + $maxPages - 1) {
            // Configurable delay: shorter for better UX, still effective
            usleep(rand(300000, 800000)); // 0.3-0.8 second random delay
        }
    }

    // Remove duplicates based on URL
    $uniqueResults = [];
    $seenUrls = [];
    foreach ($allResults as $result) {
        $url = $result['url'];
        if (!in_array($url, $seenUrls)) {
            $seenUrls[] = $url;
            $uniqueResults[] = $result;
        }
    }

    return $uniqueResults;
}

function parseGoogleResults($html) {
    $results = [];
    
    // Remove newlines and extra spaces for easier parsing
    $html = preg_replace('/\s+/', ' ', $html);
    
    // Enhanced parsing for multiple Google result formats
    // Method 1: Standard div class="g" results
    preg_match_all('/<div[^>]*class="[^"]*\bg\b[^"]*"[^>]*>(.*?)<\/div>(?=<div[^>]*class="[^"]*\bg\b|<div[^>]*id="botstuff"|$)/s', $html, $blocks);
    
    foreach ($blocks[1] as $block) {
        $result = parseResultBlock($block);
        if ($result) {
            $results[] = $result;
        }
    }
    
    // Method 2: Newer format with data-hveid attribute
    if (empty($results)) {
        preg_match_all('/<div[^>]*data-hveid="[^"]*"[^>]*>(.*?)<\/div>(?=<div[^>]*data-hveid=|<div[^>]*id="botstuff"|$)/s', $html, $blocks2);
        
        foreach ($blocks2[1] as $block) {
            $result = parseResultBlock($block);
            if ($result) {
                $results[] = $result;
            }
        }
    }
    
    // Method 3: Alternative parsing using different markers
    if (empty($results)) {
        preg_match_all('/<div[^>]*class="[^"]*yuRUbf[^"]*"[^>]*>(.*?)<\/div>/s', $html, $urlBlocks);
        preg_match_all('/<div[^>]*class="[^"]*VwiC3b[^"]*"[^>]*>(.*?)<\/div>/s', $html, $descBlocks);
        
        for ($i = 0; $i < min(count($urlBlocks[1]), count($descBlocks[1])); $i++) {
            $result = [];
            
            // Extract URL and title from yuRUbf block
            if (preg_match('/<a[^>]*href="([^"]+)"[^>]*>/i', $urlBlocks[1][$i], $urlMatch)) {
                $url = cleanGoogleUrl($urlMatch[1]);
                if ($url) {
                    $result['url'] = $url;
                }
            }
            
            if (preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $urlBlocks[1][$i], $titleMatch)) {
                $result['title'] = cleanText(strip_tags($titleMatch[1]));
            }
            
            // Extract description from VwiC3b block
            $result['description'] = cleanText(strip_tags($descBlocks[1][$i]));
            
            if (isset($result['url']) && isset($result['title']) && !empty($result['title'])) {
                $results[] = $result;
            }
        }
    }
    
    return $results;
}

function parseResultBlock($block) {
    $result = [];
    
    // Extract URL with multiple methods
    if (preg_match('/<a[^>]*href="([^"]+)"[^>]*>/i', $block, $urlMatch)) {
        $url = cleanGoogleUrl($urlMatch[1]);
        if ($url) {
            $result['url'] = $url;
        }
    }
    
    // Extract title with fallback methods
    if (preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $block, $titleMatch)) {
        $result['title'] = cleanText(strip_tags($titleMatch[1]));
    } elseif (preg_match('/<div[^>]*role="heading"[^>]*>(.*?)<\/div>/is', $block, $titleMatch)) {
        $result['title'] = cleanText(strip_tags($titleMatch[1]));
    }
    
    // Extract description/snippet with multiple methods
    if (preg_match('/<div[^>]*class="[^"]*VwiC3b[^"]*"[^>]*>(.*?)<\/div>/is', $block, $descMatch)) {
        $result['description'] = cleanText(strip_tags($descMatch[1]));
    } elseif (preg_match('/<div[^>]*data-sncf="[^"]*"[^>]*>(.*?)<\/div>/is', $block, $descMatch)) {
        $result['description'] = cleanText(strip_tags($descMatch[1]));
    } elseif (preg_match('/<span[^>]*class="[^"]*st[^"]*"[^>]*>(.*?)<\/span>/is', $block, $descMatch)) {
        $result['description'] = cleanText(strip_tags($descMatch[1]));
    } elseif (preg_match('/<div[^>]*style="[^"]*"[^>]*>(.*?)<\/div>/is', $block, $descMatch)) {
        $desc = cleanText(strip_tags($descMatch[1]));
        if (strlen($desc) > 20) { // Only use if it looks like a description
            $result['description'] = $desc;
        }
    }
    
    // Extract cached link if available
    if (preg_match('/webcache\.googleusercontent\.com[^"\']+/i', $block, $cacheMatch)) {
        $result['cached'] = 'https://' . $cacheMatch[0];
    }
    
    // Validate and return result
    if (isset($result['url']) && isset($result['title']) && !empty($result['title'])) {
        return $result;
    }
    
    return null;
}

function cleanGoogleUrl($url) {
    // Remove Google redirect wrappers
    if (preg_match('/url\?q=([^&]+)/', $url, $cleanUrl)) {
        $url = urldecode($cleanUrl[1]);
    }
    
    // Remove additional Google tracking parameters
    $url = preg_replace('/&(sa|ved|usg)=[^&]+/', '', $url);
    
    // Validate URL and ensure safe protocols
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        
        // Only allow HTTP and HTTPS protocols
        if (!in_array(strtolower($scheme), ['http', 'https'])) {
            return null;
        }
        
        // Skip Google's own links and invalid hosts
        if ($host && strpos($host, 'google.com') === false && strpos($host, 'google.') === false) {
            return $url;
        }
    }
    
    return null;
}

function cleanText($text) {
    // Decode HTML entities
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Remove extra whitespace
    $text = preg_replace('/\s+/', ' ', $text);
    // Trim
    $text = trim($text);
    return $text;
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
