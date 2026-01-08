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
    
    // Predefined proxy sources (mock - in production, these would be real API calls)
    $proxyLists = [
        'free-proxy-list' => generateMockProxies(20),
        'proxyscrape' => generateMockProxies(15),
        'proxy-list' => generateMockProxies(10),
        'spys' => generateMockProxies(12),
        'geonode' => generateMockProxies(18),
        'pubproxy' => generateMockProxies(8)
    ];
    
    if (in_array('all', $sources)) {
        foreach ($proxyLists as $list) {
            $proxies = array_merge($proxies, $list);
        }
    } else {
        foreach ($sources as $source) {
            if (isset($proxyLists[$source])) {
                $proxies = array_merge($proxies, $proxyLists[$source]);
            }
        }
    }
    
    // Add custom source if provided
    if ($customSource) {
        try {
            $content = @file_get_contents($customSource);
            if ($content) {
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (preg_match('/^(\d+\.\d+\.\d+\.\d+):(\d+)$/', $line, $matches)) {
                        $proxies[] = [
                            'ip' => $matches[1],
                            'port' => intval($matches[2]),
                            'protocol' => 'http'
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore custom source errors
        }
    }
    
    echo json_encode([
        'success' => true,
        'proxies' => $proxies,
        'total' => count($proxies)
    ]);
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
