<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * LEGEND HOUSE - Google OAuth Callback Handler
 * ═══════════════════════════════════════════════════════════════════════════════
 */

require_once 'auth.php';

// Check if we have an authorization code
if (!isset($_GET['code'])) {
    // Check for errors
    if (isset($_GET['error'])) {
        $error = htmlspecialchars($_GET['error']);
        $errorDescription = isset($_GET['error_description']) ? htmlspecialchars($_GET['error_description']) : 'Unknown error';
        
        header('Location: login.php?error=' . urlencode($errorDescription));
        exit;
    }
    
    // No code, redirect to login
    header('Location: login.php?error=' . urlencode('Google authentication failed'));
    exit;
}

$code = $_GET['code'];

// Exchange code for access token
$tokenData = getGoogleAccessToken($code);

if (!$tokenData || !isset($tokenData['access_token'])) {
    header('Location: login.php?error=' . urlencode('Failed to get access token from Google'));
    exit;
}

// Get user information
$googleUser = getGoogleUserInfo($tokenData['access_token']);

if (!$googleUser || !isset($googleUser['id'])) {
    header('Location: login.php?error=' . urlencode('Failed to get user information from Google'));
    exit;
}

// Login or register user
$result = loginOrRegisterGoogleUser($googleUser);

if ($result['success']) {
    // Redirect to homepage
    header('Location: index.php?login=success&provider=google');
} else {
    header('Location: login.php?error=' . urlencode('Failed to authenticate with Google'));
}
exit;
