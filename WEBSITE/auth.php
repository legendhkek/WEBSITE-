<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Authentication System v1.0
 * ═══════════════════════════════════════════════════════════════════════════════
 */

session_start();

// Load configuration
require_once __DIR__ . '/config.php';

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
    
    // Add Google OAuth columns if they don't exist
    // SQLite doesn't support adding UNIQUE constraint in ALTER TABLE
    // So we add columns without UNIQUE and handle uniqueness in application logic
    $result = $db->query("PRAGMA table_info(users)");
    $columns = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $columns[] = $row['name'];
    }
    
    if (!in_array('google_id', $columns)) {
        $db->exec('ALTER TABLE users ADD COLUMN google_id TEXT');
    }
    if (!in_array('profile_picture', $columns)) {
        $db->exec('ALTER TABLE users ADD COLUMN profile_picture TEXT');
    }
    if (!in_array('auth_provider', $columns)) {
        $db->exec('ALTER TABLE users ADD COLUMN auth_provider TEXT DEFAULT "local"');
    }
    
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

 cursor/ai-functionality-check-c7f4
// Helper function to get database connection (SQLite3 for auth)

// Helper function to get database connection (SQLite3)
 main
function getDB() {
    return new SQLite3(DB_FILE);
}

 cursor/ai-functionality-check-c7f4
// PDO connection for tools that need it (ai-chat, dorker-api, etc.)
function getDatabase() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return
// Alias for compatibility with tools - returns PDO for AI chat
function getDatabase() {
    try {
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw $e;
    }
 main
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
        
        // Create session token for auto-login
        $token = generateSessionToken();
        $expiresAt = date('Y-m-d H:i:s', time() + 86400 * 7); // 7 days
        
        $stmt = $db->prepare('INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (:user_id, :token, :expires_at)');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':token', $token, SQLITE3_TEXT);
        $stmt->bindValue(':expires_at', $expiresAt, SQLITE3_TEXT);
        
        if (!$stmt->execute()) {
            $db->close();
            return ['success' => false, 'error' => 'Failed to create session. Please try logging in.'];
        }
        
        $db->close();
        
        // Set session for auto-login
        // Note: username and email are already validated above (lines 119-129)
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['session_token'] = $token;
        
        return [
            'success' => true,
            'userId' => $userId,
            'username' => $username,
            'email' => $email,
            'token' => $token,
            'message' => 'Account created successfully!'
        ];
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
    $stmt = $db->prepare('SELECT id, username, email, created_at, last_login, google_id, profile_picture, auth_provider FROM users WHERE id = :id');
    $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    $db->close();
    
    return $user;
}

// Google OAuth functions
function getGoogleAuthUrl() {
    if (!defined('GOOGLE_OAUTH_ENABLED') || !GOOGLE_OAUTH_ENABLED) {
        return null;
    }
    
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

function isGoogleOAuthEnabled() {
    return defined('GOOGLE_OAUTH_ENABLED') && GOOGLE_OAUTH_ENABLED;
}

function getGoogleAccessToken($code) {
    $data = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        // Log detailed error information
        error_log("Google OAuth Token Exchange Failed - HTTP {$httpCode}");
        error_log("Redirect URI used: " . GOOGLE_REDIRECT_URI);
        if ($curlError) {
            error_log("cURL Error: " . $curlError);
        }
        if ($response) {
            error_log("Google Response: " . $response);
            $errorData = json_decode($response, true);
            if (isset($errorData['error_description'])) {
                error_log("Error Description: " . $errorData['error_description']);
            }
        }
        return null;
    }
    
    return json_decode($response, true);
}

function getGoogleUserInfo($accessToken) {
    $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return null;
    }
    
    return json_decode($response, true);
}

function loginOrRegisterGoogleUser($googleUser) {
    $db = getDB();
    
    // Check if user exists with this Google ID
    $stmt = $db->prepare('SELECT id, username, email FROM users WHERE google_id = :google_id');
    $stmt->bindValue(':google_id', $googleUser['id'], SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($user) {
        // Update last login
        $stmt = $db->prepare('UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':id', $user['id'], SQLITE3_INTEGER);
        $stmt->execute();
        
        $userId = $user['id'];
        $username = $user['username'];
        $email = $user['email'];
    } else {
        // Check if email already exists
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->bindValue(':email', $googleUser['email'], SQLITE3_TEXT);
        $result = $stmt->execute();
        $existingUser = $result->fetchArray();
        
        if ($existingUser) {
            // Link Google account to existing user
            $stmt = $db->prepare('UPDATE users SET google_id = :google_id, profile_picture = :picture, auth_provider = :provider WHERE email = :email');
            $stmt->bindValue(':google_id', $googleUser['id'], SQLITE3_TEXT);
            $stmt->bindValue(':picture', $googleUser['picture'] ?? null, SQLITE3_TEXT);
            $stmt->bindValue(':provider', 'google', SQLITE3_TEXT);
            $stmt->bindValue(':email', $googleUser['email'], SQLITE3_TEXT);
            $stmt->execute();
            
            $userId = $existingUser['id'];
            
            $stmt = $db->prepare('SELECT username, email FROM users WHERE id = :id');
            $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
            $result = $stmt->execute();
            $user = $result->fetchArray(SQLITE3_ASSOC);
            $username = $user['username'];
            $email = $user['email'];
        } else {
            // Create new user
            $username = generateUsernameFromEmail($googleUser['email']);
            
            // Ensure unique username
            $originalUsername = $username;
            $counter = 1;
            while (true) {
                $stmt = $db->prepare('SELECT id FROM users WHERE username = :username');
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $result = $stmt->execute();
                if (!$result->fetchArray()) break;
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            $stmt = $db->prepare('INSERT INTO users (username, email, password, google_id, profile_picture, auth_provider) VALUES (:username, :email, :password, :google_id, :picture, :provider)');
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->bindValue(':email', $googleUser['email'], SQLITE3_TEXT);
            $stmt->bindValue(':password', '', SQLITE3_TEXT); // No password for Google users
            $stmt->bindValue(':google_id', $googleUser['id'], SQLITE3_TEXT);
            $stmt->bindValue(':picture', $googleUser['picture'] ?? null, SQLITE3_TEXT);
            $stmt->bindValue(':provider', 'google', SQLITE3_TEXT);
            $stmt->execute();
            
            $userId = $db->lastInsertRowID();
            $email = $googleUser['email'];
        }
    }
    
    // Create session token
    $token = generateSessionToken();
    $expiresAt = date('Y-m-d H:i:s', time() + 86400 * 7); // 7 days
    
    $stmt = $db->prepare('INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (:user_id, :token, :expires_at)');
    $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $stmt->bindValue(':token', $token, SQLITE3_TEXT);
    $stmt->bindValue(':expires_at', $expiresAt, SQLITE3_TEXT);
    $stmt->execute();
    
    $db->close();
    
    // Set session
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['session_token'] = $token;
    
    return [
        'success' => true,
        'userId' => $userId,
        'username' => $username,
        'email' => $email,
        'token' => $token
    ];
}

function generateUsernameFromEmail($email) {
    $username = explode('@', $email)[0];
    $username = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
    if (strlen($username) < 3) {
        $username = 'user' . $username;
    }
    if (strlen($username) > 20) {
        $username = substr($username, 0, 20);
    }
    return $username;
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
        
        case 'google_auth_url':
            if (!isGoogleOAuthEnabled()) {
                echo json_encode(['success' => false, 'error' => 'Google OAuth is not configured. Please set up Google OAuth credentials in config.php or contact the administrator.']);
                break;
            }
            echo json_encode(['success' => true, 'url' => getGoogleAuthUrl()]);
            break;
        
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}

// GET endpoint for Google OAuth URL and logout
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'google_auth_url':
            header('Content-Type: application/json');
            if (!isGoogleOAuthEnabled()) {
                echo json_encode(['success' => false, 'error' => 'Google OAuth is not configured. Please set up Google OAuth credentials in config.php or contact the administrator.']);
                exit;
            }
            echo json_encode(['success' => true, 'url' => getGoogleAuthUrl()]);
            exit;
            
        case 'logout':
            // Handle GET logout request
            logoutUser();
            header('Location: index.php');
            exit;
    }
}
