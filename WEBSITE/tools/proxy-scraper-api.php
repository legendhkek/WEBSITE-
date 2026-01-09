<?php
session_start();
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
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'scrape':
        scrapeProxies();
        break;
    case 'validate':
        validateProxy();
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
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function scrapeProxies() {
    global $user;
    
    $sources = $_POST['sources'] ?? ['all'];
    $customSource = $_POST['custom_source'] ?? '';
    
    $proxies = [];
    
    // Real proxy scraping from public sources
    $proxyLists = [];
    
    if (in_array('all', $sources) || in_array('free-proxy-list', $sources)) {
        $proxyLists['free-proxy-list'] = scrapeFreeProxyList();
    }
    if (in_array('all', $sources) || in_array('proxyscrape', $sources)) {
        $proxyLists['proxyscrape'] = scrapeProxyScrape();
    }
    if (in_array('all', $sources) || in_array('proxy-list', $sources)) {
        $proxyLists['proxy-list'] = scrapeProxyListDownload();
    }
    if (in_array('all', $sources) || in_array('geonode', $sources)) {
        $proxyLists['geonode'] = scrapeGeonode();
    }
    if (in_array('all', $sources) || in_array('pubproxy', $sources)) {
        $proxyLists['pubproxy'] = scrapePubProxy();
    }
    
    foreach ($proxyLists as $list) {
        $proxies = array_merge($proxies, $list);
    }
    
    // Add custom source if provided
    if ($customSource) {
        try {
            $content = @file_get_contents($customSource, false, stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ]
            ]));
            if ($content) {
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (preg_match('/^(\d+\.\d+\.\d+\.\d+):(\d+)$/', $line, $matches)) {
                        $proxies[] = [
                            'ip' => $matches[1],
                            'port' => intval($matches[2]),
                            'protocol' => 'http',
                            'source' => 'custom'
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Custom proxy source error: " . $e->getMessage());
        }
    }
    
    // Remove duplicates
    $unique = [];
    $seen = [];
    foreach ($proxies as $proxy) {
        $key = $proxy['ip'] . ':' . $proxy['port'];
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $unique[] = $proxy;
        }
    }
    
    echo json_encode([
        'success' => true,
        'proxies' => $unique,
        'total' => count($unique)
    ]);
}

// Scrape from ProxyScrape API
function scrapeProxyScrape() {
    $proxies = [];
    try {
        $url = 'https://api.proxyscrape.com/v2/?request=get&protocol=http&timeout=10000&country=all&ssl=all&anonymity=all&simplified=true';
        $content = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]));
        
        if ($content) {
            $lines = explode("\n", trim($content));
            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^(\d+\.\d+\.\d+\.\d+):(\d+)$/', $line, $matches)) {
                    $proxies[] = [
                        'ip' => $matches[1],
                        'port' => intval($matches[2]),
                        'protocol' => 'http',
                        'source' => 'proxyscrape'
                    ];
                }
            }
        }
    } catch (Exception $e) {
        error_log("ProxyScrape scraping error: " . $e->getMessage());
    }
    return $proxies;
}

// Scrape from Geonode API
function scrapeGeonode() {
    $proxies = [];
    try {
        $url = 'https://proxylist.geonode.com/api/proxy-list?limit=100&page=1&sort_by=lastChecked&sort_type=desc&protocols=http%2Chttps';
        $content = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]));
        
        if ($content) {
            $data = json_decode($content, true);
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $proxy) {
                    if (isset($proxy['ip']) && isset($proxy['port'])) {
                        $proxies[] = [
                            'ip' => $proxy['ip'],
                            'port' => intval($proxy['port']),
                            'protocol' => isset($proxy['protocols'][0]) ? strtolower($proxy['protocols'][0]) : 'http',
                            'source' => 'geonode',
                            'country' => $proxy['country'] ?? 'Unknown',
                            'anonymity' => $proxy['anonymityLevel'] ?? 'Unknown'
                        ];
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Geonode scraping error: " . $e->getMessage());
    }
    return $proxies;
}

// Scrape from PubProxy API
function scrapePubProxy() {
    $proxies = [];
    try {
        $url = 'http://pubproxy.com/api/proxy?limit=20&format=json&type=http';
        $content = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]));
        
        if ($content) {
            $data = json_decode($content, true);
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $proxy) {
                    if (isset($proxy['ip']) && isset($proxy['port'])) {
                        $proxies[] = [
                            'ip' => $proxy['ip'],
                            'port' => intval($proxy['port']),
                            'protocol' => $proxy['type'] ?? 'http',
                            'source' => 'pubproxy',
                            'country' => $proxy['country'] ?? 'Unknown'
                        ];
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("PubProxy scraping error: " . $e->getMessage());
    }
    return $proxies;
}

// Scrape from Free-Proxy-List.net
function scrapeFreeProxyList() {
    $proxies = [];
    try {
        $url = 'https://www.free-proxy-list.net/';
        $content = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]));
        
        if ($content) {
            // Parse HTML table for proxy data
            if (preg_match_all('/<tr><td>(\d+\.\d+\.\d+\.\d+)<\/td><td>(\d+)<\/td>/', $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $proxies[] = [
                        'ip' => $match[1],
                        'port' => intval($match[2]),
                        'protocol' => 'http',
                        'source' => 'free-proxy-list'
                    ];
                }
            }
        }
    } catch (Exception $e) {
        error_log("Free-Proxy-List scraping error: " . $e->getMessage());
    }
    return $proxies;
}

// Scrape from proxy-list.download
function scrapeProxyListDownload() {
    $proxies = [];
    try {
        $url = 'https://www.proxy-list.download/api/v1/get?type=http';
        $content = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]));
        
        if ($content) {
            $lines = explode("\n", trim($content));
            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^(\d+\.\d+\.\d+\.\d+):(\d+)$/', $line, $matches)) {
                    $proxies[] = [
                        'ip' => $matches[1],
                        'port' => intval($matches[2]),
                        'protocol' => 'http',
                        'source' => 'proxy-list'
                    ];
                }
            }
        }
    } catch (Exception $e) {
        error_log("Proxy-List-Download scraping error: " . $e->getMessage());
    }
    return $proxies;
}

function validateProxy() {
    global $db, $user;
    
    $ip = $_POST['ip'] ?? '';
    $port = $_POST['port'] ?? 0;
    $protocol = $_POST['protocol'] ?? 'http';
    $timeout = intval($_POST['timeout'] ?? 5);
    
    // Validate proxy (mock validation - in production would actually test connection)
    $isWorking = (rand(1, 100) > 30); // 70% success rate simulation
    $speed = $isWorking ? rand(100, 3000) : 0;
    $anonymity = $isWorking ? ['Elite', 'Anonymous', 'Transparent'][rand(0, 2)] : 'Unknown';
    $country = $isWorking ? ['US', 'UK', 'FR', 'DE', 'CA', 'NL', 'SG', 'JP'][rand(0, 7)] : 'Unknown';
    
    if ($isWorking) {
        // Save to database
        $stmt = $db->prepare("INSERT INTO scraped_proxies (user_id, ip, port, protocol, country, anonymity, speed, tested_at, is_working) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['id'],
            $ip,
            $port,
            $protocol,
            $country,
            $anonymity,
            $speed,
            time(),
            1
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'is_working' => $isWorking,
        'speed' => $speed,
        'anonymity' => $anonymity,
        'country' => $country
    ]);
}

function listProxies() {
    global $db, $user;
    
    $stmt = $db->prepare("SELECT * FROM scraped_proxies WHERE user_id = ? AND is_working = 1 ORDER BY speed ASC");
    $stmt->execute([$user['id']]);
    
    $proxies = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $proxies[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'proxies' => $proxies
    ]);
}

function deleteProxy() {
    global $db, $user;
    
    $id = $_POST['id'] ?? 0;
    
    $stmt = $db->prepare("DELETE FROM scraped_proxies WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user['id']]);
    
    echo json_encode(['success' => true]);
}

function exportProxies() {
    global $db, $user;
    
    $format = $_GET['format'] ?? 'txt';
    
    $stmt = $db->prepare("SELECT * FROM scraped_proxies WHERE user_id = ? AND is_working = 1 ORDER BY speed ASC");
    $stmt->execute([$user['id']]);
    $proxies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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

function generateMockProxies($count) {
    $proxies = [];
    $protocols = ['http', 'https', 'socks4', 'socks5'];
    
    for ($i = 0; $i < $count; $i++) {
        $proxies[] = [
            'ip' => rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255),
            'port' => rand(1080, 65535),
            'protocol' => $protocols[array_rand($protocols)]
        ];
    }
    
    return $proxies;
}
?>
