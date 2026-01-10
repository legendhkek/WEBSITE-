<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$user = getCurrentUser();
$action = $_GET['action'] ?? '';

// Initialize database
$db = getDB();

// Create tables if they don't exist
$db->exec('CREATE TABLE IF NOT EXISTS proxy_pools (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    strategy TEXT DEFAULT "round-robin",
    rotation_interval INTEGER DEFAULT 60,
    max_requests INTEGER DEFAULT 100,
    created_at INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
)');

$db->exec('CREATE TABLE IF NOT EXISTS pool_proxies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pool_id INTEGER NOT NULL,
    ip TEXT NOT NULL,
    port INTEGER NOT NULL,
    protocol TEXT DEFAULT "http",
    username TEXT,
    password TEXT,
    requests_count INTEGER DEFAULT 0,
    last_used INTEGER DEFAULT 0,
    speed INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (pool_id) REFERENCES proxy_pools(id)
)');

$db->exec('CREATE TABLE IF NOT EXISTS proxy_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pool_id INTEGER NOT NULL,
    proxy_id INTEGER NOT NULL,
    success BOOLEAN DEFAULT 1,
    response_time INTEGER,
    timestamp INTEGER NOT NULL,
    FOREIGN KEY (pool_id) REFERENCES proxy_pools(id),
    FOREIGN KEY (proxy_id) REFERENCES pool_proxies(id)
)');

switch ($action) {
    case 'create':
        createProxyPool($db, $user);
        break;
    
    case 'get':
        getNextProxy($db, $user);
        break;
    
    case 'report':
        reportProxy($db, $user);
        break;
    
    case 'stats':
        getPoolStats($db, $user);
        break;
    
    case 'list':
        listPools($db, $user);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$db = null;

function createProxyPool($db, $user) {
    if (!isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        return;
    }
    
    $poolName = $_POST['poolName'] ?? 'Unnamed Pool';
    $strategy = $_POST['strategy'] ?? 'round-robin';
    $interval = intval($_POST['interval'] ?? 60);
    $maxRequests = intval($_POST['maxRequests'] ?? 100);
    
    // Read proxies from file
    $fileContent = file_get_contents($_FILES['file']['tmp_name']);
    $lines = explode("\n", $fileContent);
    $proxies = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Parse IP:PORT or IP PORT format
        if (preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})[\s:]+(\d+)/', $line, $matches)) {
            $proxies[] = [
                'ip' => $matches[1],
                'port' => intval($matches[2])
            ];
        }
    }
    
    if (count($proxies) < 10) {
        echo json_encode(['success' => false, 'message' => 'Please upload at least 10 proxies']);
        return;
    }
    
    // Create pool
    $stmt = $db->prepare('INSERT INTO proxy_pools (user_id, name, strategy, rotation_interval, max_requests, created_at) VALUES (:user_id, :name, :strategy, :interval, :max_requests, :created_at)');
    $stmt->execute([
        ':user_id' => $user['id'],
        ':name' => $poolName,
        ':strategy' => $strategy,
        ':interval' => $interval,
        ':max_requests' => $maxRequests,
        ':created_at' => time()
    ]);
    
    $poolId = (int)$db->lastInsertId();
    
    // Add proxies to pool
    $validCount = 0;
    foreach ($proxies as $proxy) {
        // Basic validation
        if (filter_var($proxy['ip'], FILTER_VALIDATE_IP) && $proxy['port'] > 0 && $proxy['port'] <= 65535) {
            $stmt = $db->prepare('INSERT INTO pool_proxies (pool_id, ip, port, protocol) VALUES (:pool_id, :ip, :port, :protocol)');
            $stmt->execute([
                ':pool_id' => $poolId,
                ':ip' => $proxy['ip'],
                ':port' => $proxy['port'],
                ':protocol' => 'http'
            ]);
            $validCount++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Proxy pool created successfully',
        'poolId' => $poolId,
        'activeProxies' => $validCount,
        'apiEndpoint' => "GET /tools/rotating-proxy-api.php?action=get&pool_id=$poolId"
    ]);
}

function getNextProxy($db, $user) {
    $poolId = intval($_GET['pool_id'] ?? 0);
    
    if ($poolId === 0) {
        echo json_encode(['success' => false, 'message' => 'Pool ID required']);
        return;
    }
    
    // Verify pool belongs to user
    $stmt = $db->prepare('SELECT * FROM proxy_pools WHERE id = :pool_id AND user_id = :user_id');
    $stmt->execute([
        ':pool_id' => $poolId,
        ':user_id' => $user['id']
    ]);
    $pool = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pool) {
        echo json_encode(['success' => false, 'message' => 'Pool not found']);
        return;
    }
    
    $strategy = $pool['strategy'];
    $now = time();
    $interval = $pool['rotation_interval'];
    
    // Select proxy based on strategy
    switch ($strategy) {
        case 'random':
            $stmt = $db->prepare('SELECT * FROM pool_proxies WHERE pool_id = :pool_id AND is_active = 1 ORDER BY RANDOM() LIMIT 1');
            break;
        
        case 'least-used':
            $stmt = $db->prepare('SELECT * FROM pool_proxies WHERE pool_id = :pool_id AND is_active = 1 ORDER BY requests_count ASC, last_used ASC LIMIT 1');
            break;
        
        case 'fastest':
            $stmt = $db->prepare('SELECT * FROM pool_proxies WHERE pool_id = :pool_id AND is_active = 1 ORDER BY speed ASC LIMIT 1');
            break;
        
        case 'round-robin':
        default:
            $stmt = $db->prepare('SELECT * FROM pool_proxies WHERE pool_id = :pool_id AND is_active = 1 AND (last_used = 0 OR last_used < :threshold) ORDER BY last_used ASC LIMIT 1');
            $stmt->bindValue(':threshold', $now - $interval, PDO::PARAM_INT);
            break;
    }

    $stmt->bindValue(':pool_id', $poolId, PDO::PARAM_INT);
    $stmt->execute();
    $proxy = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$proxy) {
        echo json_encode(['success' => false, 'message' => 'No available proxies']);
        return;
    }
    
    // Update proxy usage
    $stmt = $db->prepare('UPDATE pool_proxies SET requests_count = requests_count + 1, last_used = :now WHERE id = :id');
    $stmt->execute([
        ':now' => $now,
        ':id' => $proxy['id']
    ]);
    
    // Log request
    $stmt = $db->prepare('INSERT INTO proxy_requests (pool_id, proxy_id, timestamp) VALUES (:pool_id, :proxy_id, :timestamp)');
    $stmt->execute([
        ':pool_id' => $poolId,
        ':proxy_id' => $proxy['id'],
        ':timestamp' => $now
    ]);
    
    echo json_encode([
        'success' => true,
        'proxy' => [
            'ip' => $proxy['ip'],
            'port' => $proxy['port'],
            'protocol' => $proxy['protocol'],
            'username' => $proxy['username'],
            'password' => $proxy['password']
        ]
    ]);
}

function reportProxy($db, $user) {
    $poolId = intval($_POST['pool_id'] ?? 0);
    $proxyIp = $_POST['proxy_ip'] ?? '';
    $proxyPort = intval($_POST['proxy_port'] ?? 0);
    
    if ($poolId === 0 || empty($proxyIp)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    // Mark proxy as inactive
    $stmt = $db->prepare('UPDATE pool_proxies SET is_active = 0 WHERE pool_id = :pool_id AND ip = :ip AND port = :port');
    $stmt->execute([
        ':pool_id' => $poolId,
        ':ip' => $proxyIp,
        ':port' => $proxyPort
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Proxy reported as dead']);
}

function getPoolStats($db, $user) {
    $poolId = intval($_GET['pool_id'] ?? 0);
    
    if ($poolId === 0) {
        echo json_encode(['success' => false, 'message' => 'Pool ID required']);
        return;
    }
    
    // Get pool info
    $stmt = $db->prepare('SELECT * FROM proxy_pools WHERE id = :pool_id AND user_id = :user_id');
    $stmt->execute([
        ':pool_id' => $poolId,
        ':user_id' => $user['id']
    ]);
    $pool = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pool) {
        echo json_encode(['success' => false, 'message' => 'Pool not found']);
        return;
    }
    
    // Count active proxies
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM pool_proxies WHERE pool_id = :pool_id AND is_active = 1');
    $stmt->execute([':pool_id' => $poolId]);
    $activeCount = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    
    // Count total requests
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM proxy_requests WHERE pool_id = :pool_id');
    $stmt->execute([':pool_id' => $poolId]);
    $totalRequests = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    
    // Calculate success rate
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM proxy_requests WHERE pool_id = :pool_id AND success = 1');
    $stmt->execute([':pool_id' => $poolId]);
    $successCount = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    
    $successRate = $totalRequests > 0 ? round(($successCount / $totalRequests) * 100, 2) : 0;
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'poolName' => $pool['name'],
            'strategy' => $pool['strategy'],
            'activeProxies' => $activeCount,
            'totalRequests' => $totalRequests,
            'successRate' => $successRate . '%',
            'rotationInterval' => $pool['rotation_interval'],
            'maxRequests' => $pool['max_requests']
        ]
    ]);
}

function listPools($db, $user) {
    $stmt = $db->prepare('SELECT p.*, COUNT(pp.id) as proxy_count FROM proxy_pools p LEFT JOIN pool_proxies pp ON p.id = pp.pool_id AND pp.is_active = 1 WHERE p.user_id = :user_id GROUP BY p.id ORDER BY p.created_at DESC');
    $stmt->execute([':user_id' => $user['id']]);
    $pools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'pools' => $pools]);
}
?>
