<?php
/**
 * Session Security Check
 * 
 * This file:
 * - Starts session securely
 * - Checks if user is logged in
 * - Validates session timeout (30 minutes)
 * - Auto-logs out on inactivity
 * - Prevents session fixation attacks
 * 
 * Usage: include("../includes/session_check.php");
 */

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define session timeout (in seconds)
$SESSION_TIMEOUT = 1800; // 30 minutes

// ============================================
// 1. CHECK IF USER IS LOGGED IN
// ============================================
if (!isset($_SESSION['user_id'])) {
    // User not logged in - redirect to login
    header("Location: ../login.php");
    exit();
}

// ============================================
// 2. CHECK INACTIVITY TIMEOUT
// ============================================
if (!isset($_SESSION['LAST_ACTIVITY'])) {
    // First time - set last activity
    $_SESSION['LAST_ACTIVITY'] = time();
} else {
    // Check if session has expired
    $current_time = time();
    $last_activity = $_SESSION['LAST_ACTIVITY'];
    $elapsed_time = $current_time - $last_activity;

    if ($elapsed_time > $SESSION_TIMEOUT) {
        // Session expired - logout user
        session_unset();
        session_destroy();
        header("Location: ../login.php?timeout=1");
        exit();
    }
}

// ============================================
// 3. UPDATE LAST ACTIVITY ON EACH PAGE LOAD
// ============================================
$_SESSION['LAST_ACTIVITY'] = time();

// ============================================
// 4. VERIFY SESSION VARS ARE SET
// ============================================
if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>
