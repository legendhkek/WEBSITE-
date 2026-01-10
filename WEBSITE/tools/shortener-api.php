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
    case 'create':
        createShortUrl();
        break;
    case 'list':
        listUserUrls();
        break;
    case 'delete':
        deleteUrl();
        break;
    case 'stats':
        getUrlStats();
        break;
    case 'track':
        trackClick();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function createShortUrl() {
    global $db, $user;
    
    $originalUrl = $_POST['original_url'] ?? '';
    $customAlias = $_POST['custom_alias'] ?? '';
    $password = $_POST['password'] ?? '';
    $expiresAt = $_POST['expires_at'] ?? '';
    $clickLimit = $_POST['click_limit'] ?? null;
    
    // Validate URL
    if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid URL']);
        return;
    }
    
    // Generate short code
    if ($customAlias) {
        if (!preg_match('/^[a-zA-Z0-9-_]{3,20}$/', $customAlias)) {
            echo json_encode(['success' => false, 'error' => 'Invalid custom alias format']);
            return;
        }
        $shortCode = $customAlias;
    } else {
        $shortCode = generateShortCode();
    }
    
    // Check if short code already exists
    $stmt = $db->prepare("SELECT id FROM short_urls WHERE short_code = :short_code");
    $stmt->bindValue(':short_code', $shortCode, SQLITE3_TEXT);
    $result = $stmt->execute();
    if ($result->fetchArray()) {
        echo json_encode(['success' => false, 'error' => 'This alias is already taken']);
        return;
    }
    
    // Hash password if provided
    $hashedPassword = $password ? password_hash($password, PASSWORD_BCRYPT) : null;
    
    // Convert expiration to timestamp
    $expiresTimestamp = $expiresAt ? strtotime($expiresAt) : null;
    
    // Insert into database
    $stmt = $db->prepare("INSERT INTO short_urls (user_id, original_url, short_code, password, expires_at, click_limit, created_at) VALUES (:user_id, :original_url, :short_code, :password, :expires_at, :click_limit, :created_at)");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $stmt->bindValue(':original_url', $originalUrl, SQLITE3_TEXT);
    $stmt->bindValue(':short_code', $shortCode, SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
    $stmt->bindValue(':expires_at', $expiresTimestamp, SQLITE3_INTEGER);
    $stmt->bindValue(':click_limit', $clickLimit, SQLITE3_INTEGER);
    $stmt->bindValue(':created_at', time(), SQLITE3_INTEGER);
    $stmt->execute();
    
    $shortUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/s/" . $shortCode;
    
    echo json_encode([
        'success' => true,
        'short_url' => $shortUrl,
        'short_code' => $shortCode
    ]);
}

function listUserUrls() {
    global $db, $user;
    
    $stmt = $db->prepare("SELECT id, original_url, short_code, clicks, created_at, expires_at, click_limit, password FROM short_urls WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $urls = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $status = 'active';
        if ($row['expires_at'] && $row['expires_at'] < time()) {
            $status = 'expired';
        } elseif ($row['click_limit'] && $row['clicks'] >= $row['click_limit']) {
            $status = 'limit_reached';
        } elseif ($row['password']) {
            $status = 'protected';
        }
        
        $urls[] = [
            'id' => $row['id'],
            'original_url' => $row['original_url'],
            'short_code' => $row['short_code'],
            'short_url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/s/" . $row['short_code'],
            'clicks' => $row['clicks'],
            'created_at' => $row['created_at'],
            'status' => $status
        ];
    }
    
    echo json_encode(['success' => true, 'urls' => $urls]);
}

function deleteUrl() {
    global $db, $user;
    
    $id = $_POST['id'] ?? 0;
    
    // Verify ownership
    $stmt = $db->prepare("SELECT id FROM short_urls WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    if (!$result->fetchArray()) {
        echo json_encode(['success' => false, 'error' => 'URL not found or access denied']);
        return;
    }
    
    // Delete clicks first
    $stmt = $db->prepare("DELETE FROM url_clicks WHERE short_url_id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    
    // Delete URL
    $stmt = $db->prepare("DELETE FROM short_urls WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
}

function getUrlStats() {
    global $db, $user;
    
    $id = $_GET['id'] ?? 0;
    
    // Verify ownership
    $stmt = $db->prepare("SELECT * FROM short_urls WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $url = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$url) {
        echo json_encode(['success' => false, 'error' => 'URL not found']);
        return;
    }
    
    // Get click statistics
    $stmt = $db->prepare("SELECT * FROM url_clicks WHERE short_url_id = :id ORDER BY clicked_at DESC");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $clicks = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $clicks[] = $row;
    }
    
    // Analyze browsers
    $browsers = [];
    $referrers = [];
    foreach ($clicks as $click) {
        $ua = $click['user_agent'] ?? '';
        $browser = 'Other';
        if (strpos($ua, 'Chrome') !== false) $browser = 'Chrome';
        elseif (strpos($ua, 'Firefox') !== false) $browser = 'Firefox';
        elseif (strpos($ua, 'Safari') !== false) $browser = 'Safari';
        elseif (strpos($ua, 'Edge') !== false) $browser = 'Edge';
        
        $browsers[$browser] = ($browsers[$browser] ?? 0) + 1;
        
        $ref = $click['referrer'] ?: 'Direct';
        $referrers[$ref] = ($referrers[$ref] ?? 0) + 1;
    }
    
    echo json_encode([
        'success' => true,
        'url' => $url,
        'clicks' => $clicks,
        'browsers' => $browsers,
        'referrers' => $referrers
    ]);
}

function trackClick() {
    global $db;
    
    $shortCode = $_GET['code'] ?? '';
    
    // Get URL
    $stmt = $db->prepare("SELECT * FROM short_urls WHERE short_code = :short_code");
    $stmt->bindValue(':short_code', $shortCode, SQLITE3_TEXT);
    $result = $stmt->execute();
    $url = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$url) {
        echo json_encode(['success' => false, 'error' => 'URL not found']);
        return;
    }
    
    // Check expiration
    if ($url['expires_at'] && $url['expires_at'] < time()) {
        echo json_encode(['success' => false, 'error' => 'Link has expired']);
        return;
    }
    
    // Check click limit
    if ($url['click_limit'] && $url['clicks'] >= $url['click_limit']) {
        echo json_encode(['success' => false, 'error' => 'Click limit reached']);
        return;
    }
    
    // Check password
    if ($url['password']) {
        $password = $_POST['password'] ?? '';
        if (!password_verify($password, $url['password'])) {
            echo json_encode(['success' => false, 'error' => 'Password required', 'password_protected' => true]);
            return;
        }
    }
    
    // Track click
    $stmt = $db->prepare("INSERT INTO url_clicks (short_url_id, ip_address, user_agent, referrer, clicked_at) VALUES (:short_url_id, :ip_address, :user_agent, :referrer, :clicked_at)");
    $stmt->bindValue(':short_url_id', $url['id'], SQLITE3_INTEGER);
    $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':referrer', $_SERVER['HTTP_REFERER'] ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':clicked_at', time(), SQLITE3_INTEGER);
    $stmt->execute();
    
    // Increment click count
    $stmt = $db->prepare("UPDATE short_urls SET clicks = clicks + 1 WHERE id = :id");
    $stmt->bindValue(':id', $url['id'], SQLITE3_INTEGER);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'url' => $url['original_url']]);
}

function generateShortCode($length = 6) {
    global $db;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    do {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // Check if code exists
        $stmt = $db->prepare("SELECT id FROM short_urls WHERE short_code = :short_code");
        $stmt->bindValue(':short_code', $code, SQLITE3_TEXT);
        $result = $stmt->execute();
    } while ($result->fetchArray());
    
    return $code;
}
?>
