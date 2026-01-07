<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Authentication System v1.0
 * ═══════════════════════════════════════════════════════════════════════════════
 */

session_start();

// Database configuration - using SQLite for portability
define('DB_FILE', __DIR__ . '/users.db');

// Initialize database
function initDatabase() {
    $db = new SQLite3(DB_FILE);
    
    // Create users table
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME,
        is_active INTEGER DEFAULT 1
    )');
    
    // Create user sessions table
    $db->exec('CREATE TABLE IF NOT EXISTS user_sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        session_token TEXT UNIQUE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )');
    
    // Create download history table
    $db->exec('CREATE TABLE IF NOT EXISTS download_history (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        torrent_name TEXT NOT NULL,
        torrent_hash TEXT,
        magnet_url TEXT,
        size TEXT,
        downloaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )');
    
    $db->close();
}

initDatabase();

// Helper function to get database connection
function getDB() {
    return new SQLite3(DB_FILE);
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate username
function isValidUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// Validate password
function isValidPassword($password) {
    return strlen($password) >= 6;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate session token
function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

// Register new user
function registerUser($username, $email, $password) {
    $db = getDB();
    
    // Validate inputs
    if (!isValidUsername($username)) {
        return ['success' => false, 'error' => 'Invalid username. Use 3-20 alphanumeric characters and underscores.'];
    }
    
    if (!isValidEmail($email)) {
        return ['success' => false, 'error' => 'Invalid email address.'];
    }
    
    if (!isValidPassword($password)) {
        return ['success' => false, 'error' => 'Password must be at least 6 characters long.'];
    }
    
    // Check if username exists
    $stmt = $db->prepare('SELECT id FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    if ($result->fetchArray()) {
        $db->close();
        return ['success' => false, 'error' => 'Username already exists.'];
    }
    
    // Check if email exists
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    if ($result->fetchArray()) {
        $db->close();
        return ['success' => false, 'error' => 'Email already registered.'];
    }
    
    // Create user
    $hashedPassword = hashPassword($password);
    $stmt = $db->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
    
    if ($stmt->execute()) {
        $userId = $db->lastInsertRowID();
        $db->close();
        return ['success' => true, 'userId' => $userId, 'message' => 'Account created successfully!'];
    } else {
        $db->close();
        return ['success' => false, 'error' => 'Failed to create account. Please try again.'];
    }
}

// Login user
function loginUser($usernameOrEmail, $password) {
    $db = getDB();
    
    // Find user by username or email
    $stmt = $db->prepare('SELECT id, username, email, password, is_active FROM users WHERE username = :identifier OR email = :identifier');
    $stmt->bindValue(':identifier', $usernameOrEmail, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$user) {
        $db->close();
        return ['success' => false, 'error' => 'Invalid credentials.'];
    }
    
    if (!$user['is_active']) {
        $db->close();
        return ['success' => false, 'error' => 'Account is inactive.'];
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        $db->close();
        return ['success' => false, 'error' => 'Invalid credentials.'];
    }
    
    // Update last login
    $stmt = $db->prepare('UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->bindValue(':id', $user['id'], SQLITE3_INTEGER);
    $stmt->execute();
    
    // Create session token
    $token = generateSessionToken();
    $expiresAt = date('Y-m-d H:i:s', time() + 86400 * 7); // 7 days
    
    $stmt = $db->prepare('INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (:user_id, :token, :expires_at)');
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $stmt->bindValue(':expires_at', $expiresAt, SQLITE3_TEXT);
    $stmt->execute();
    
    $db->close();
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['session_token'] = $token;
    
    return [
        'success' => true,
        'userId' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'token' => $token
    ];
}

// Logout user
function logoutUser() {
    if (isset($_SESSION['session_token'])) {
        $db = getDB();
        $stmt = $db->prepare('DELETE FROM user_sessions WHERE session_token = :token');
        $stmt->bindValue(':token', $_SESSION['session_token'], SQLITE3_TEXT);
        $stmt->execute();
        $db->close();
    }
    
    session_destroy();
    return ['success' => true, 'message' => 'Logged out successfully.'];
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['session_token']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare('SELECT id, username, email, created_at, last_login FROM users WHERE id = :id');
    $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    $db->close();
    
    return $user;
}

// Save download to history
function saveDownload($userId, $torrentName, $torrentHash, $magnetUrl, $size) {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO download_history (user_id, torrent_name, torrent_hash, magnet_url, size) VALUES (:user_id, :name, :hash, :magnet, :size)');
    $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $stmt->bindValue(':name', $torrentName, SQLITE3_TEXT);
    $stmt->bindValue(':hash', $torrentHash, SQLITE3_TEXT);
    $stmt->bindValue(':magnet', $magnetUrl, SQLITE3_TEXT);
    $stmt->bindValue(':size', $size, SQLITE3_TEXT);
    $result = $stmt->execute();
    $db->close();
    return $result !== false;
}

// Get user download history
function getDownloadHistory($userId, $limit = 50) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM download_history WHERE user_id = :user_id ORDER BY downloaded_at DESC LIMIT :limit');
    $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $history = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $history[] = $row;
    }
    
    $db->close();
    return $history;
}

// API endpoint handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'register':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            echo json_encode(registerUser($username, $email, $password));
            break;
        
        case 'login':
            $identifier = trim($_POST['identifier'] ?? '');
            $password = $_POST['password'] ?? '';
            
            echo json_encode(loginUser($identifier, $password));
            break;
        
        case 'logout':
            echo json_encode(logoutUser());
            break;
        
        case 'check':
            if (isLoggedIn()) {
                $user = getCurrentUser();
                echo json_encode([
                    'success' => true,
                    'loggedIn' => true,
                    'user' => $user
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'loggedIn' => false
                ]);
            }
            break;
        
        case 'save_download':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'error' => 'Not logged in']);
                break;
            }
            
            $torrentName = $_POST['torrent_name'] ?? '';
            $torrentHash = $_POST['torrent_hash'] ?? '';
            $magnetUrl = $_POST['magnet_url'] ?? '';
            $size = $_POST['size'] ?? '';
            
            $result = saveDownload($_SESSION['user_id'], $torrentName, $torrentHash, $magnetUrl, $size);
            echo json_encode(['success' => $result]);
            break;
        
        case 'get_history':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'error' => 'Not logged in']);
                break;
            }
            
            $history = getDownloadHistory($_SESSION['user_id']);
            echo json_encode(['success' => true, 'history' => $history]);
            break;
        
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
