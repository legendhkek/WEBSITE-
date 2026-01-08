<?php
session_start();
require_once __DIR__ . '/auth.php';

// Check if user is logged in, if not redirect to login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// User is logged in, redirect to dashboard
header('Location: dashboard.php');
exit;
?>
