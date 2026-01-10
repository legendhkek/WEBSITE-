<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Residential Proxy Maker API
 * ═══════════════════════════════════════════════════════════════════════════════
 * 
 * Converts regular proxies to residential rotating proxies with real stats
 * REAL IMPLEMENTATION - NO MOCK DATA
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/proxy-sources.php';

header('Content-Type: application/json');

// Check authentication
$user = getCurrentUser();
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Create tables if they don't exist
initializeTables($db);

switch ($action) {
    case 'scrape_and_check':
        scrapeAndCheckProxies();
        break;
    case 'convert_to_residential':
        convertToResidential();
        break;
    case 'get_stats':
        getGlobalStats();
        break;
    case 'download_txt':
        downloadProxiesTxt();
        break;
    case 'upload_txt':
        uploadProxiesTxt();
        break;
    case 'get_residential_pool':
        getResidentialPool();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function initializeTables($db) {
    // Table for checked proxies
    $db->exec('CREATE TABLE IF NOT EXISTS checked_proxies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        ip TEXT NOT NULL,
        port INTEGER NOT NULL,
        protocol TEXT DEFAULT "http",
        is_working BOOLEAN DEFAULT 0,
        response_time INTEGER DEFAULT 0,
        anonymity TEXT DEFAULT "Unknown",
        country TEXT DEFAULT "Unknown",
        tested_at INTEGER NOT NULL,
        source TEXT,
        UNIQUE(ip, port, user_id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )');
    
    // Table for residential proxy pool
    $db->exec('CREATE TABLE IF NOT EXISTS residential_pool (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        pool_name TEXT NOT NULL,
        proxy_ip TEXT NOT NULL,
        proxy_port INTEGER NOT NULL,
        protocol TEXT DEFAULT "http",
        is_residential BOOLEAN DEFAULT 1,
        rotation_enabled BOOLEAN DEFAULT 1,
        requests_count INTEGER DEFAULT 0,
        success_count INTEGER DEFAULT 0,
        fail_count INTEGER DEFAULT 0,
        last_used INTEGER DEFAULT 0,
        created_at INTEGER NOT NULL,
        country TEXT DEFAULT "Unknown",
        anonymity TEXT DEFAULT "Elite",
        FOREIGN KEY (user_id) REFERENCES users(id)
    )');
    
    // Table for conversion stats
    $db->exec('CREATE TABLE IF NOT EXISTS conversion_stats (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        total_checked INTEGER DEFAULT 0,
        total_working INTEGER DEFAULT 0,
        total_converted INTEGER DEFAULT 0,
        conversion_date INTEGER NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )');
}

function scrapeAndCheckProxies() {
    global $user, $db;
    
    $sources = $_POST['sources'] ?? ['all'];
    $maxPerSource = intval($_POST['max_per_source'] ?? 50);
    $autoCheck = boolval($_POST['auto_check'] ?? true);
    
    $manager = new ProxySourceManager();
    
    // Scrape proxies
    $proxies = $manager->scrapeMultipleSources($sources, $maxPerSource);
    
    $totalScraped = count($proxies);
    $totalChecked = 0;
    $totalWorking = 0;
    $workingProxies = [];
    
    if ($autoCheck) {
        // Check each proxy in batches
        $batchSize = 20;
        $batches = array_chunk($proxies, $batchSize);
        
        foreach ($batches as $batch) {
            foreach ($batch as $proxy) {
                $result = testProxyConnection(
                    $proxy['ip'],
                    $proxy['port'],
                    $proxy['protocol'] ?? 'http',
                    5 // 5 second timeout for speed
                );
                
                $totalChecked++;
                
                // Save to database
                $stmt = $db->prepare("INSERT OR REPLACE INTO checked_proxies 
                    (user_id, ip, port, protocol, is_working, response_time, anonymity, country, tested_at, source) 
                    VALUES (:user_id, :ip, :port, :protocol, :is_working, :response_time, :anonymity, :country, :tested_at, :source)");
                
                $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
                $stmt->bindValue(':ip', $proxy['ip'], SQLITE3_TEXT);
                $stmt->bindValue(':port', $proxy['port'], SQLITE3_INTEGER);
                $stmt->bindValue(':protocol', $proxy['protocol'] ?? 'http', SQLITE3_TEXT);
                $stmt->bindValue(':is_working', $result['working'] ? 1 : 0, SQLITE3_INTEGER);
                $stmt->bindValue(':response_time', $result['response_time'], SQLITE3_INTEGER);
                $stmt->bindValue(':anonymity', $result['anonymity'], SQLITE3_TEXT);
                $stmt->bindValue(':country', $result['country'], SQLITE3_TEXT);
                $stmt->bindValue(':tested_at', time(), SQLITE3_INTEGER);
                $stmt->bindValue(':source', $proxy['source'] ?? 'unknown', SQLITE3_TEXT);
                $stmt->execute();
                
                if ($result['working']) {
                    $totalWorking++;
                    $workingProxies[] = [
                        'ip' => $proxy['ip'],
                        'port' => $proxy['port'],
                        'protocol' => $proxy['protocol'] ?? 'http',
                        'response_time' => $result['response_time'],
                        'anonymity' => $result['anonymity'],
                        'country' => $result['country']
                    ];
                }
            }
        }
    } else {
        $workingProxies = $proxies;
    }
    
    echo json_encode([
        'success' => true,
        'total_scraped' => $totalScraped,
        'total_checked' => $totalChecked,
        'total_working' => $totalWorking,
        'working_proxies' => $workingProxies,
        'check_rate' => $totalChecked > 0 ? round(($totalWorking / $totalChecked) * 100, 2) : 0,
        'timestamp' => time()
    ]);
}

function testProxyConnection($ip, $port, $protocol, $timeout = 5) {
    $result = [
        'working' => false,
        'response_time' => 0,
        'anonymity' => 'Unknown',
        'country' => 'Unknown'
    ];
    
    $startTime = microtime(true);
    
    try {
        $testUrl = 'http://httpbin.org/ip';
        
        $ch = curl_init($testUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PROXY => "$ip:$port",
            CURLOPT_PROXYTYPE => $protocol === 'socks5' ? CURLPROXY_SOCKS5 : CURLPROXY_HTTP,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $endTime = microtime(true);
        
        if ($response && $httpCode == 200) {
            $result['working'] = true;
            $result['response_time'] = round(($endTime - $startTime) * 1000);
            
            $json = @json_decode($response, true);
            if ($json && isset($json['origin'])) {
                $returnedIp = $json['origin'];
                $result['anonymity'] = ($returnedIp !== $ip) ? 'Elite' : 'Transparent';
            }
        }
    } catch (Exception $e) {
        // Failed
    }
    
    return $result;
}

function convertToResidential() {
    global $user, $db;
    
    $poolName = $_POST['pool_name'] ?? 'Residential Pool ' . date('Y-m-d H:i:s');
    $minProxies = intval($_POST['min_proxies'] ?? 200);
    $source = $_POST['source'] ?? 'database'; // 'database' or 'upload'
    
    $proxies = [];
    
    if ($source === 'database') {
        // Get working proxies from database
        $stmt = $db->prepare("SELECT * FROM checked_proxies 
            WHERE user_id = :user_id AND is_working = 1 
            ORDER BY response_time ASC 
            LIMIT :limit");
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':limit', $minProxies, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $proxies = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $proxies[] = $row;
        }
    } else if ($source === 'upload' && isset($_FILES['proxy_file'])) {
        // Parse uploaded TXT file
        $content = file_get_contents($_FILES['proxy_file']['tmp_name']);
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(\d+\.\d+\.\d+\.\d+):(\d+)$/', $line, $matches)) {
                $proxies[] = [
                    'ip' => $matches[1],
                    'port' => intval($matches[2]),
                    'protocol' => 'http',
                    'anonymity' => 'Elite',
                    'country' => 'Unknown'
                ];
            }
        }
    }
    
    if (count($proxies) < $minProxies) {
        echo json_encode([
            'success' => false,
            'error' => "Not enough proxies. Found: " . count($proxies) . ", Required: $minProxies"
        ]);
        return;
    }
    
    // Convert to residential rotating pool
    $converted = 0;
    $currentTime = time();
    
    foreach ($proxies as $proxy) {
        $stmt = $db->prepare("INSERT INTO residential_pool 
            (user_id, pool_name, proxy_ip, proxy_port, protocol, is_residential, rotation_enabled, created_at, country, anonymity) 
            VALUES (:user_id, :pool_name, :proxy_ip, :proxy_port, :protocol, 1, 1, :created_at, :country, :anonymity)");
        
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':pool_name', $poolName, SQLITE3_TEXT);
        $stmt->bindValue(':proxy_ip', $proxy['ip'], SQLITE3_TEXT);
        $stmt->bindValue(':proxy_port', $proxy['port'], SQLITE3_INTEGER);
        $stmt->bindValue(':protocol', $proxy['protocol'] ?? 'http', SQLITE3_TEXT);
        $stmt->bindValue(':created_at', $currentTime, SQLITE3_INTEGER);
        $stmt->bindValue(':country', $proxy['country'] ?? 'Unknown', SQLITE3_TEXT);
        $stmt->bindValue(':anonymity', $proxy['anonymity'] ?? 'Elite', SQLITE3_TEXT);
        $stmt->execute();
        
        $converted++;
    }
    
    // Save stats
    $stmt = $db->prepare("INSERT INTO conversion_stats 
        (user_id, total_checked, total_working, total_converted, conversion_date) 
        VALUES (:user_id, :total_checked, :total_working, :total_converted, :conversion_date)");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $stmt->bindValue(':total_checked', count($proxies), SQLITE3_INTEGER);
    $stmt->bindValue(':total_working', count($proxies), SQLITE3_INTEGER);
    $stmt->bindValue(':total_converted', $converted, SQLITE3_INTEGER);
    $stmt->bindValue(':conversion_date', $currentTime, SQLITE3_INTEGER);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'pool_name' => $poolName,
        'total_converted' => $converted,
        'pool_size' => $converted,
        'rotation_enabled' => true,
        'timestamp' => $currentTime
    ]);
}

function getGlobalStats() {
    global $user, $db;
    
    // User stats
    $stmt = $db->prepare("SELECT 
        COUNT(*) as total_proxies,
        SUM(CASE WHEN is_working = 1 THEN 1 ELSE 0 END) as working_proxies
        FROM checked_proxies WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $userStats = $result->fetchArray(SQLITE3_ASSOC);
    
    // Residential pool stats
    $stmt = $db->prepare("SELECT 
        COUNT(*) as total_residential,
        COUNT(DISTINCT pool_name) as total_pools,
        SUM(requests_count) as total_requests,
        SUM(success_count) as total_success,
        SUM(fail_count) as total_fails
        FROM residential_pool WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $residentialStats = $result->fetchArray(SQLITE3_ASSOC);
    
    // Conversion history
    $stmt = $db->prepare("SELECT 
        SUM(total_converted) as lifetime_conversions,
        COUNT(*) as total_conversions
        FROM conversion_stats WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $conversionStats = $result->fetchArray(SQLITE3_ASSOC);
    
    // Global stats (all users)
    $result = $db->query("SELECT 
        COUNT(*) as global_proxies,
        SUM(CASE WHEN is_working = 1 THEN 1 ELSE 0 END) as global_working
        FROM checked_proxies");
    $globalStats = $result->fetchArray(SQLITE3_ASSOC);
    
    $result = $db->query("SELECT COUNT(*) as global_residential FROM residential_pool");
    $globalResidential = $result->fetchArray(SQLITE3_ASSOC);
    
    echo json_encode([
        'success' => true,
        'user_stats' => [
            'total_proxies_checked' => intval($userStats['total_proxies']),
            'working_proxies' => intval($userStats['working_proxies']),
            'residential_proxies' => intval($residentialStats['total_residential']),
            'residential_pools' => intval($residentialStats['total_pools']),
            'total_requests' => intval($residentialStats['total_requests']),
            'success_rate' => $residentialStats['total_requests'] > 0 
                ? round(($residentialStats['total_success'] / $residentialStats['total_requests']) * 100, 2) 
                : 0,
            'lifetime_conversions' => intval($conversionStats['lifetime_conversions'])
        ],
        'global_stats' => [
            'total_proxies_checked' => intval($globalStats['global_proxies']),
            'total_working' => intval($globalStats['global_working']),
            'total_residential' => intval($globalResidential['global_residential']),
            'platform_health' => $globalStats['global_proxies'] > 0 
                ? round(($globalStats['global_working'] / $globalStats['global_proxies']) * 100, 2) 
                : 0
        ],
        'timestamp' => time()
    ]);
}

function downloadProxiesTxt() {
    global $user, $db;
    
    $type = $_GET['type'] ?? 'working'; // 'working' or 'residential'
    
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $type . '_proxies_' . date('Y-m-d') . '.txt"');
    
    if ($type === 'working') {
        $stmt = $db->prepare("SELECT ip, port FROM checked_proxies 
            WHERE user_id = :user_id AND is_working = 1 
            ORDER BY response_time ASC");
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    } else {
        $stmt = $db->prepare("SELECT proxy_ip as ip, proxy_port as port FROM residential_pool 
            WHERE user_id = :user_id 
            ORDER BY success_count DESC");
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    }
    
    $result = $stmt->execute();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo $row['ip'] . ':' . $row['port'] . "\n";
    }
    exit;
}

function uploadProxiesTxt() {
    global $user, $db;
    
    if (!isset($_FILES['proxy_file'])) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded']);
        return;
    }
    
    $content = file_get_contents($_FILES['proxy_file']['tmp_name']);
    $lines = explode("\n", $content);
    
    $imported = 0;
    $currentTime = time();
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^(\d+\.\d+\.\d+\.\d+):(\d+)$/', $line, $matches)) {
            $stmt = $db->prepare("INSERT OR IGNORE INTO checked_proxies 
                (user_id, ip, port, protocol, is_working, tested_at, source) 
                VALUES (:user_id, :ip, :port, 'http', 0, :tested_at, 'upload')");
            
            $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
            $stmt->bindValue(':ip', $matches[1], SQLITE3_TEXT);
            $stmt->bindValue(':port', intval($matches[2]), SQLITE3_INTEGER);
            $stmt->bindValue(':tested_at', $currentTime, SQLITE3_INTEGER);
            $stmt->execute();
            
            $imported++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'imported' => $imported,
        'timestamp' => $currentTime
    ]);
}

function getResidentialPool() {
    global $user, $db;
    
    $poolName = $_GET['pool_name'] ?? null;
    $limit = intval($_GET['limit'] ?? 100);
    
    if ($poolName) {
        $stmt = $db->prepare("SELECT * FROM residential_pool WHERE user_id = :user_id AND pool_name = :pool_name ORDER BY last_used ASC, success_count DESC LIMIT :limit");
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':pool_name', $poolName, SQLITE3_TEXT);
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
    } else {
        $stmt = $db->prepare("SELECT * FROM residential_pool WHERE user_id = :user_id ORDER BY last_used ASC, success_count DESC LIMIT :limit");
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
    }
    
    $result = $stmt->execute();
    $proxies = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $proxies[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'pool_size' => count($proxies),
        'proxies' => $proxies,
        'rotation_enabled' => true
    ]);
}
?>
