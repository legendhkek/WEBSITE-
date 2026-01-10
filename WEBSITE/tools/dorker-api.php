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

    // Advanced dork preprocessing for better Google results
    $optimizedQuery = optimizeDorkQuery($query);
    
    // Validate and suggest improvements if needed
    $suggestions = analyzeDorkQuery($optimizedQuery);

    // No rate limiting - unlimited dorking for discovery

    // Save query
    $stmt = $db->prepare("INSERT INTO dorker_queries (user_id, dork_query, created_at) VALUES (:user_id, :dork_query, :created_at)");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $stmt->bindValue(':dork_query', $query, SQLITE3_TEXT);
    $stmt->bindValue(':created_at', time(), SQLITE3_INTEGER);
    $stmt->execute();
    $queryId = $db->lastInsertRowID();

    // Perform advanced dorking with optimized query
    $results = scrapeGoogle($optimizedQuery);
    
    // Apply post-processing to enhance result quality
    $enhancedResults = enhanceResults($results, $query);

    // Save results
    foreach ($enhancedResults as $result) {
        $stmt = $db->prepare("
            INSERT OR IGNORE INTO dorker_results 
            (query_id, title, url, description, cached_url, found_at) 
            VALUES (:query_id, :title, :url, :description, :cached_url, :found_at)
        ");
        $stmt->bindValue(':query_id', $queryId, SQLITE3_INTEGER);
        $stmt->bindValue(':title', $result['title'], SQLITE3_TEXT);
        $stmt->bindValue(':url', $result['url'], SQLITE3_TEXT);
        $stmt->bindValue(':description', $result['description'], SQLITE3_TEXT);
        $stmt->bindValue(':cached_url', $result['cached'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':found_at', time(), SQLITE3_INTEGER);
        $stmt->execute();
    }

    // Get stats
    $stats = getStats($db, $user);

    echo json_encode([
        'success' => true,
        'results' => $enhancedResults,
        'stats' => $stats,
        'query_info' => [
            'original' => $query,
            'optimized' => $optimizedQuery,
            'suggestions' => $suggestions
        ]
    ]);
}

// Advanced dork query optimizer for better Google results
function optimizeDorkQuery($query) {
    // Remove excessive spaces
    $query = preg_replace('/\s+/', ' ', trim($query));
    
    // Optimize operator usage for better Google results
    // Convert common mistakes to proper format
    $query = str_replace(['site :', 'filetype :', 'inurl :', 'intitle :'], 
                        ['site:', 'filetype:', 'inurl:', 'intitle:'], $query);
    
    // Handle OR operators properly (Google prefers OR in uppercase)
    $query = preg_replace('/\s+or\s+/i', ' OR ', $query);
    
    // Handle AND operators (implicit, but make explicit if needed)
    $query = preg_replace('/\s+and\s+/i', ' ', $query);
    
    // Optimize quoted phrases - ensure proper spacing
    $query = preg_replace('/"([^"]+)"/', '"$1"', $query);
    
    // Combine adjacent site: operators if they're OR'd
    $query = preg_replace('/site:(\S+)\s+OR\s+site:/', 'site:$1 OR site:', $query);
    
    return $query;
}

// Analyze dork query and provide suggestions
function analyzeDorkQuery($query) {
    $suggestions = [];
    
    // Check for common Google dork operators
    $operators = [
        'site:', 'filetype:', 'ext:', 'inurl:', 'intitle:', 'intext:', 
        'allinurl:', 'allintitle:', 'allintext:', 'cache:', 'link:', 
        'related:', 'info:', 'define:', 'stocks:', 'weather:', 'movie:'
    ];
    
    $foundOperators = [];
    foreach ($operators as $op) {
        if (stripos($query, $op) !== false) {
            $foundOperators[] = $op;
        }
    }
    
    // Provide suggestions based on query structure
    if (empty($foundOperators)) {
        $suggestions[] = 'Consider adding operators like site:, filetype:, or inurl: for more targeted results';
    }
    
    // Check for potential improvements
    if (strpos($query, 'site:') !== false && strpos($query, 'inurl:') === false) {
        $suggestions[] = 'Combine with inurl: to narrow down specific pages on the site';
    }
    
    if (strpos($query, 'filetype:') !== false && strpos($query, 'site:') === false) {
        $suggestions[] = 'Add site: to limit file searches to specific domains';
    }
    
    // Check for overly broad queries
    $wordCount = str_word_count($query);
    if ($wordCount < 3 && empty($foundOperators)) {
        $suggestions[] = 'Query may be too broad - add more specific terms or operators';
    }
    
    // Advanced operator combinations
    if (count($foundOperators) >= 2) {
        $suggestions[] = 'Great! Using multiple operators for precise targeting';
    }
    
    return [
        'operators_used' => $foundOperators,
        'tips' => $suggestions,
        'complexity' => count($foundOperators) >= 3 ? 'advanced' : (count($foundOperators) >= 1 ? 'intermediate' : 'basic')
    ];
}

// Enhance results with additional metadata and filtering
function enhanceResults($results, $originalQuery) {
    $enhanced = [];
    
    foreach ($results as $result) {
        // Add relevance metadata
        $result['relevance_factors'] = calculateRelevanceFactors($result, $originalQuery);
        
        // Check for potential high-value indicators
        $result['indicators'] = detectValueIndicators($result);
        
        // Extract file information if applicable
        if (preg_match('/\.(pdf|doc|docx|xls|xlsx|sql|txt|log|conf|cfg|env|bak)$/i', $result['url'], $match)) {
            $result['file_type'] = strtolower($match[1]);
            $result['is_file'] = true;
        } else {
            $result['is_file'] = false;
        }
        
        $enhanced[] = $result;
    }
    
    return $enhanced;
}

// Calculate relevance factors for better result ranking
function calculateRelevanceFactors($result, $query) {
    $factors = [];
    
    // Extract search terms from query (excluding operators)
    $cleanQuery = preg_replace('/\b(site|filetype|ext|inurl|intitle|intext|allinurl|allintitle|allintext):[^\s]+/', '', $query);
    $searchTerms = array_filter(explode(' ', strtolower($cleanQuery)));
    
    // Check title relevance
    $titleMatches = 0;
    foreach ($searchTerms as $term) {
        if (stripos($result['title'], $term) !== false) {
            $titleMatches++;
        }
    }
    $factors['title_match_count'] = $titleMatches;
    
    // Check URL relevance
    $urlMatches = 0;
    foreach ($searchTerms as $term) {
        if (stripos($result['url'], $term) !== false) {
            $urlMatches++;
        }
    }
    $factors['url_match_count'] = $urlMatches;
    
    // Check description relevance
    $descMatches = 0;
    if (isset($result['description'])) {
        foreach ($searchTerms as $term) {
            if (stripos($result['description'], $term) !== false) {
                $descMatches++;
            }
        }
    }
    $factors['desc_match_count'] = $descMatches;
    
    return $factors;
}

// Detect high-value indicators in results
function detectValueIndicators($result) {
    $indicators = [];
    
    $url = strtolower($result['url']);
    $title = strtolower($result['title']);
    
    // Security-related indicators
    if (preg_match('/(admin|login|dashboard|panel|control)/', $url . ' ' . $title)) {
        $indicators[] = 'admin_access';
    }
    
    if (preg_match('/(config|env|settings|credentials)/', $url . ' ' . $title)) {
        $indicators[] = 'configuration';
    }
    
    if (preg_match('/(backup|bak|old|copy|archive)/', $url . ' ' . $title)) {
        $indicators[] = 'backup';
    }
    
    if (preg_match('/(api|endpoint|swagger|graphql)/', $url . ' ' . $title)) {
        $indicators[] = 'api';
    }
    
    if (preg_match('/(database|sql|db|mysql|postgres)/', $url . ' ' . $title)) {
        $indicators[] = 'database';
    }
    
    if (preg_match('/(\.git|\.svn|\.env|\.config)/', $url)) {
        $indicators[] = 'version_control';
    }
    
    // Cloud storage indicators
    if (preg_match('/(s3\.amazonaws|storage\.googleapis|blob\.core\.windows)/', $url)) {
        $indicators[] = 'cloud_storage';
    }
    
    return $indicators;
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
    
    // Detect if query needs special handling
    $hasFileType = stripos($dork, 'filetype:') !== false || stripos($dork, 'ext:') !== false;
    $hasSite = stripos($dork, 'site:') !== false;
    
    // Adjust max pages based on query type (file searches often have fewer results)
    if ($hasFileType && !$hasSite) {
        $maxPages = min($maxPages, 2); // Reduce for broad file searches
    }
    
    // Multi-page scraping for more comprehensive results
    for ($currentPage = $page; $currentPage < $page + $maxPages; $currentPage++) {
        // Build advanced Google search URL
        $url = "https://www.google.com/search?q=" . urlencode($dork);
        $url .= "&start=" . ($currentPage * 10);
        $url .= "&num=10";
        $url .= "&filter=0"; // Disable auto-filtering of similar results
        $url .= "&pws=0"; // Disable personalization
        
        // Add advanced search parameters for better dorking
        if ($hasFileType) {
            $url .= "&as_filetype="; // Let Google optimize file search
        }

        $ch = curl_init();
        
        // Rotate through different Google domains for better coverage
        $googleDomains = ['www.google.com', 'www.google.co.uk', 'www.google.de'];
        $selectedDomain = $googleDomains[array_rand($googleDomains)];
        $url = str_replace('www.google.com', $selectedDomain, $url);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING => '', // Enable gzip/deflate decompression
            CURLOPT_HTTPHEADER => [
                'User-Agent: ' . $userAgents[array_rand($userAgents)],
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language: en-US,en;q=0.9,de;q=0.8,fr;q=0.7',
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
            CURLOPT_REFERER => 'https://' . $selectedDomain . '/'
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || !$html) {
            // Log error for debugging (in production, use proper logging)
            error_log("Google scrape failed: HTTP $httpCode, Error: $curlError");
            break; // Stop if request fails
        }

        // Check for CAPTCHA or rate limiting
        if (strpos($html, 'recaptcha') !== false || 
            strpos($html, 'unusual traffic') !== false ||
            strpos($html, 'automated queries') !== false ||
            strpos($html, 'not a robot') !== false) {
            error_log("Google CAPTCHA detected, stopping scrape");
            break; // Stop if blocked
        }

        // Parse results from this page
        $pageResults = parseGoogleResults($html);
        
        if (empty($pageResults)) {
            // Try alternative parsing if first method fails
            $pageResults = parseGoogleResultsAlternative($html);
            
            if (empty($pageResults)) {
                error_log("No results parsed from page $currentPage");
                break; // Stop if no more results
            }
        }
        
        $allResults = array_merge($allResults, $pageResults);
        
        // Smart delay: longer for first page, shorter for subsequent
        if ($currentPage < $page + $maxPages - 1) {
            $baseDelay = ($currentPage === $page) ? 500000 : 300000; // 0.5s first, 0.3s after
            $randomDelay = rand($baseDelay, $baseDelay + 500000);
            usleep($randomDelay);
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

// Alternative parsing method for improved result extraction
function parseGoogleResultsAlternative($html) {
    $results = [];
    
    // Remove scripts and styles that might interfere
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
    
    // Try parsing with JSON-LD structured data if available
    if (preg_match_all('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/is', $html, $jsonMatches)) {
        foreach ($jsonMatches[1] as $jsonData) {
            $data = json_decode($jsonData, true);
            if (isset($data['itemListElement']) && is_array($data['itemListElement'])) {
                foreach ($data['itemListElement'] as $item) {
                    if (isset($item['item']) && isset($item['item']['url'])) {
                        $result = [
                            'url' => $item['item']['url'] ?? '',
                            'title' => $item['item']['name'] ?? '',
                            'description' => $item['item']['description'] ?? ''
                        ];
                        
                        if (!empty($result['url']) && !empty($result['title'])) {
                            $url = cleanGoogleUrl($result['url']);
                            if ($url) {
                                $result['url'] = $url;
                                $results[] = $result;
                            }
                        }
                    }
                }
            }
        }
    }
    
    // If structured data didn't work, try regex patterns
    if (empty($results)) {
        // Pattern for links with titles
        preg_match_all('/<a[^>]*href="([^"]+)"[^>]*><h3[^>]*>(.*?)<\/h3>/is', $html, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $url = cleanGoogleUrl($match[1]);
            if ($url) {
                $results[] = [
                    'url' => $url,
                    'title' => cleanText($match[2]),
                    'description' => ''
                ];
            }
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
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM dorker_queries WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $totalDorks = $row ? $row['count'] : 0;

    // Total results found
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT dr.id) as count 
        FROM dorker_results dr
        JOIN dorker_queries dq ON dr.query_id = dq.id
        WHERE dq.user_id = :user_id
    ");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $totalResults = $row ? $row['count'] : 0;

    // Success rate
    $successRate = $totalDorks > 0 ? round(($totalResults / $totalDorks) * 10) : 0;

    return [
        'total_dorks' => $totalDorks,
        'total_results' => $totalResults,
        'saved_queries' => $totalDorks,
        'success_rate' => $successRate
    ];
}
