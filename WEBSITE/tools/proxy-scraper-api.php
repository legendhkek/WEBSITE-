<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/proxy-sources.php';

header('Content-Type: application/json');

// Check if user is logged in
$user = getCurrentUser();
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$db = getDatabase();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'scrape':
        scrapeProxies();
        break;
    case 'scrape_residential':
        scrapeResidentialProxies();
        break;
    case 'validate':
        validateProxy();
        break;
    case 'batch_validate':
        batchValidateProxies();
        break;
    case 'list':
        listProxies();
        break;
    case 'delete':
        deleteProxy();
        break;
    case 'export':
        exportProxies();
        break;
    case 'sources':
        listAvailableSources();
        break;
    case 'stats':
        getProxyStats();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function scrapeProxies() {
    global $user;
    
    $sources = $_POST['sources'] ?? ['all'];
    $proxyType = $_POST['proxy_type'] ?? 'all'; // http, https, socks4, socks5, all
    $maxPerSource = intval($_POST['max_per_source'] ?? 100);
    
    $manager = new ProxySourceManager();
    
    // Get all proxies from selected sources
    $proxies = $manager->scrapeMultipleSources($sources, $maxPerSource);
    
    // Filter by proxy type if specified
    if ($proxyType !== 'all') {
        $proxies = array_filter($proxies, function($proxy) use ($proxyType) {
            return strcasecmp($proxy['protocol'], $proxyType) === 0;
        });
        $proxies = array_values($proxies); // Re-index
    }
    
    // Remove duplicates
    $unique = removeDuplicates($proxies);
    
    // Add metadata
    $response = [
        'success' => true,
        'proxies' => $unique,
        'total' => count($unique),
        'sources_used' => count($sources),
        'total_sources_available' => $manager->getSourceCount(),
        'timestamp' => time()
    ];
    
    echo json_encode($response);
}

function scrapeResidentialProxies() {
    global $user;
    
    // Residential proxies from specialized sources
    $residentialSources = [
        'https://api.proxyscrape.com/v2/?request=get&protocol=http&timeout=10000&country=all&ssl=all&anonymity=elite',
        'https://proxylist.geonode.com/api/proxy-list?limit=500&protocols=http&filterUpTime=90&anonymityLevel=elite',
        // Add more residential-focused sources
    ];
    
    $proxies = [];
    
    foreach ($residentialSources as $url) {
        try {
            $content = @file_get_contents($url, false, stream_context_create([
                'http' => [
                    'timeout' => 15,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ]
            ]));
            
            if ($content) {
                // Parse response
                $json = @json_decode($content, true);
                if ($json && isset($json['data'])) {
                    foreach ($json['data'] as $proxy) {
                        if (isset($proxy['ip']) && isset($proxy['port'])) {
                            $proxies[] = [
                                'ip' => $proxy['ip'],
                                'port' => intval($proxy['port']),
                                'protocol' => $proxy['protocols'][0] ?? 'http',
                                'type' => 'residential',
                                'country' => $proxy['country'] ?? 'Unknown',
                                'anonymity' => 'elite',
                                'uptime' => $proxy['upTime'] ?? 0,
                                'source' => 'residential'
                            ];
                        }
                    }
                } else {
                    // Parse as text
                    $lines = explode("\n", $content);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (preg_match('/(\d+\.\d+\.\d+\.\d+):(\d+)/', $line, $matches)) {
                            $proxies[] = [
                                'ip' => $matches[1],
                                'port' => intval($matches[2]),
                                'protocol' => 'http',
                                'type' => 'residential',
                                'anonymity' => 'elite',
                                'source' => 'residential'
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Residential proxy scraping error: " . $e->getMessage());
        }
    }
    
    // Remove duplicates
    $unique = removeDuplicates($proxies);
    
    echo json_encode([
        'success' => true,
        'proxies' => $unique,
        'total' => count($unique),
        'type' => 'residential',
        'timestamp' => time()
    ]);
}

function validateProxy() {
    global $db, $user;
    
    $ip = $_POST['ip'] ?? '';
    $port = $_POST['port'] ?? 0;
    $protocol = $_POST['protocol'] ?? 'http';
    $timeout = intval($_POST['timeout'] ?? 10);
    
    // Real proxy validation
    $result = testProxyConnection($ip, $port, $protocol, $timeout);
    
    if ($result['working']) {
        // Save to database
        $stmt = $db->prepare("INSERT OR REPLACE INTO scraped_proxies (user_id, ip, port, protocol, country, anonymity, speed, tested_at, is_working) VALUES (:user_id, :ip, :port, :protocol, :country, :anonymity, :speed, :tested_at, :is_working)");
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->bindValue(':port', $port, SQLITE3_INTEGER);
        $stmt->bindValue(':protocol', $protocol, SQLITE3_TEXT);
        $stmt->bindValue(':country', $result['country'], SQLITE3_TEXT);
        $stmt->bindValue(':anonymity', $result['anonymity'], SQLITE3_TEXT);
        $stmt->bindValue(':speed', $result['speed'], SQLITE3_INTEGER);
        $stmt->bindValue(':tested_at', time(), SQLITE3_INTEGER);
        $stmt->bindValue(':is_working', 1, SQLITE3_INTEGER);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'is_working' => $result['working'],
        'speed' => $result['speed'],
        'anonymity' => $result['anonymity'],
        'country' => $result['country'],
        'response_time' => $result['response_time']
    ]);
}

function batchValidateProxies() {
    $proxies = $_POST['proxies'] ?? [];
    $timeout = intval($_POST['timeout'] ?? 5);
    $maxConcurrent = intval($_POST['max_concurrent'] ?? 10);
    
    $results = [];
    $chunks = array_chunk($proxies, $maxConcurrent);
    
    foreach ($chunks as $chunk) {
        $chunkResults = [];
        
        foreach ($chunk as $proxy) {
            $result = testProxyConnection(
                $proxy['ip'],
                $proxy['port'],
                $proxy['protocol'] ?? 'http',
                $timeout
            );
            
            $chunkResults[] = [
                'ip' => $proxy['ip'],
                'port' => $proxy['port'],
                'working' => $result['working'],
                'speed' => $result['speed'],
                'anonymity' => $result['anonymity']
            ];
        }
        
        $results = array_merge($results, $chunkResults);
    }
    
    $workingCount = count(array_filter($results, fn($r) => $r['working']));
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'total_tested' => count($results),
        'working' => $workingCount,
        'failed' => count($results) - $workingCount
    ]);
}

function testProxyConnection($ip, $port, $protocol, $timeout = 10) {
    $result = [
        'working' => false,
        'speed' => 0,
        'anonymity' => 'Unknown',
        'country' => 'Unknown',
        'response_time' => 0
    ];
    
    $startTime = microtime(true);
    
    try {
        // Test URL - using a service that returns our IP
        $testUrl = 'http://httpbin.org/ip';
        
        // Build proxy URL
        $proxyUrl = "$protocol://$ip:$port";
        
        $context = stream_context_create([
            'http' => [
                'proxy' => "tcp://$ip:$port",
                'request_fulluri' => true,
                'timeout' => $timeout,
                'user_agent' => 'Mozilla/5.0'
            ]
        ]);
        
        $response = @file_get_contents($testUrl, false, $context);
        $endTime = microtime(true);
        
        if ($response) {
            $result['working'] = true;
            $result['response_time'] = round(($endTime - $startTime) * 1000); // ms
            $result['speed'] = $result['response_time'];
            
            // Check anonymity level
            $json = @json_decode($response, true);
            if ($json && isset($json['origin'])) {
                $returnedIp = $json['origin'];
                // If IP is different from proxy IP, it's working
                $result['anonymity'] = ($returnedIp === $ip) ? 'Transparent' : 'Anonymous';
            }
            
            // Try to detect country (would need GeoIP database for accurate results)
            $result['country'] = 'Unknown';
        }
    } catch (Exception $e) {
        error_log("Proxy test error for $ip:$port - " . $e->getMessage());
    }
    
    return $result;
}

function removeDuplicates($proxies) {
    $unique = [];
    $seen = [];
    
    foreach ($proxies as $proxy) {
        $key = $proxy['ip'] . ':' . $proxy['port'];
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $unique[] = $proxy;
        }
    }
    
    return $unique;
}

function listAvailableSources() {
    $manager = new ProxySourceManager();
    $sources = $manager->getAllSources();
    
    $categorized = [];
    foreach ($sources as $source) {
        $category = $source['category'];
        if (!isset($categorized[$category])) {
            $categorized[$category] = [];
        }
        $categorized[$category][] = [
            'name' => $source['name'],
            'url' => $source['url']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'total_sources' => count($sources),
        'categories' => $categorized,
        'category_counts' => array_map('count', $categorized)
    ]);
}

function getProxyStats() {
    global $db, $user;
    
    // Get statistics by protocol
    $stmt = $db->prepare("SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN is_working = 1 THEN 1 END) as working,
        AVG(speed) as avg_speed,
        protocol,
        COUNT(DISTINCT country) as countries
        FROM scraped_proxies 
        WHERE user_id = :user_id
        GROUP BY protocol
    ");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $stats = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $stats[] = $row;
    }
    
    // Get total count
    $stmtTotal = $db->prepare("SELECT COUNT(*) as total FROM scraped_proxies WHERE user_id = :user_id");
    $stmtTotal->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $resultTotal = $stmtTotal->execute();
    $totalRow = $resultTotal->fetchArray(SQLITE3_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total_proxies' => $totalRow['total'] ?? 0,
        'by_protocol' => $stats,
        'last_updated' => time()
    ]);
}

function listProxies() {
    global $db, $user;
    
    $protocol = $_GET['protocol'] ?? null;
    $workingOnly = isset($_GET['working_only']) ? boolval($_GET['working_only']) : true;
    $limit = intval($_GET['limit'] ?? 100);
    $offset = intval($_GET['offset'] ?? 0);
    
    $sql = "SELECT * FROM scraped_proxies WHERE user_id = :user_id";
    
    if ($protocol) {
        $sql .= " AND protocol = :protocol";
    }
    
    if ($workingOnly) {
        $sql .= " AND is_working = 1";
    }
    
    $sql .= " ORDER BY speed ASC LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    if ($protocol) {
        $stmt->bindValue(':protocol', $protocol, SQLITE3_TEXT);
    }
    $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
    $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $proxies = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $proxies[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'proxies' => $proxies,
        'count' => count($proxies),
        'limit' => $limit,
        'offset' => $offset
    ]);
}

function deleteProxy() {
    global $db, $user;
    
    $id = $_POST['id'] ?? 0;
    
    $stmt = $db->prepare("DELETE FROM scraped_proxies WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
}

function exportProxies() {
    global $db, $user;
    
    $format = $_GET['format'] ?? 'txt';
    $protocol = $_GET['protocol'] ?? null;
    
    $sql = "SELECT * FROM scraped_proxies WHERE user_id = :user_id AND is_working = 1";
    
    if ($protocol) {
        $sql .= " AND protocol = :protocol";
    }
    
    $sql .= " ORDER BY speed ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    if ($protocol) {
        $stmt->bindValue(':protocol', $protocol, SQLITE3_TEXT);
    }
    $result = $stmt->execute();
    
    $proxies = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $proxies[] = $row;
    }
    
    if ($format === 'txt') {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="proxies.txt"');
        foreach ($proxies as $proxy) {
            echo $proxy['ip'] . ':' . $proxy['port'] . "\n";
        }
    } elseif ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="proxies.csv"');
        echo "IP,Port,Protocol,Country,Anonymity,Speed\n";
        foreach ($proxies as $proxy) {
            echo implode(',', [
                $proxy['ip'],
                $proxy['port'],
                $proxy['protocol'],
                $proxy['country'],
                $proxy['anonymity'],
                $proxy['speed']
            ]) . "\n";
        }
    } elseif ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="proxies.json"');
        echo json_encode($proxies, JSON_PRETTY_PRINT);
    }
    exit;
}
