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
