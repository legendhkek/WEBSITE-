<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Google OAuth Callback Handler
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// Enable error reporting for debugging
// TODO: Disable display_errors in production and use error_log only
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log that callback was reached
error_log('Google OAuth Callback reached with code: ' . (isset($_GET['code']) ? 'present' : 'missing'));

try {
    require_once 'auth.php';
} catch (Exception $e) {
    error_log('Error loading auth.php: ' . $e->getMessage());
    die('An error occurred during authentication. Please try again or contact support if the issue persists.');
}

// Check if we have an authorization code
if (!isset($_GET['code'])) {
    // Check for errors
    if (isset($_GET['error'])) {
        // Log detailed error server-side
        error_log('Google OAuth Error: ' . $_GET['error']);
        if (isset($_GET['error_description'])) {
            error_log('Error Description: ' . $_GET['error_description']);
        }
        
        // Show generic user-friendly message
        $errorMessage = 'Google authentication failed. Please try again.';
        header('Location: login.php?error=' . urlencode($errorMessage));
        exit;
    }
    
    // No code, redirect to login
    error_log('No authorization code received in callback');
    header('Location: login.php?error=' . urlencode('Google authentication failed'));
    exit;
}

$code = $_GET['code'];
error_log('Processing authorization code...');

// Exchange code for access token
try {
    $tokenData = getGoogleAccessToken($code);
    error_log('Token exchange result: ' . ($tokenData ? 'success' : 'failed'));
} catch (Exception $e) {
    error_log('Exception during token exchange: ' . $e->getMessage());
    header('Location: login.php?error=' . urlencode('Error exchanging authorization code'));
    exit;
}

if (!$tokenData || !isset($tokenData['access_token'])) {
    $errorMsg = 'Failed to get access token from Google. ';
    $errorMsg .= 'Please ensure the Google OAuth redirect URI in Google Cloud Console is set to: ';
    $errorMsg .= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $dirPath = dirname($_SERVER['PHP_SELF']);
    $dirPath = ($dirPath === '/' || $dirPath === '\\') ? '' : $dirPath;
    $errorMsg .= $dirPath . '/google-callback.php';
    
    error_log('Token exchange failed. Expected redirect URI: ' . $errorMsg);
    error_log('Token data structure: ' . (is_array($tokenData) ? 'array with keys: ' . implode(', ', array_keys($tokenData)) : 'not an array'));
    header('Location: login.php?error=' . urlencode($errorMsg));
    exit;
}

error_log('Access token obtained successfully');

// Get user information
try {
    $googleUser = getGoogleUserInfo($tokenData['access_token']);
    error_log('User info result: ' . ($googleUser ? 'success' : 'failed'));
} catch (Exception $e) {
    error_log('Exception getting user info: ' . $e->getMessage());
    header('Location: login.php?error=' . urlencode('Error getting user information'));
    exit;
}

if (!$googleUser || !isset($googleUser['id'])) {
    error_log('Failed to get user information. Has email: ' . (isset($googleUser['email']) ? 'yes' : 'no'));
    header('Location: login.php?error=' . urlencode('Failed to get user information from Google'));
    exit;
}

error_log('User info obtained: ' . $googleUser['email']);

// Login or register user
try {
    $result = loginOrRegisterGoogleUser($googleUser);
    error_log('Login/register result: ' . ($result['success'] ? 'success' : 'failed'));
} catch (Exception $e) {
    error_log('Exception during login/register: ' . $e->getMessage());
    header('Location: login.php?error=' . urlencode('Error creating user session'));
    exit;
}

if ($result['success']) {
    // Redirect to dashboard
    error_log('Redirecting to dashboard for user: ' . $result['username']);
    header('Location: dashboard.php?login=success&provider=google');
} else {
    error_log('Authentication failed: ' . ($result['error'] ?? 'unknown error'));
    header('Location: login.php?error=' . urlencode('Failed to authenticate with Google'));
}
exit;
