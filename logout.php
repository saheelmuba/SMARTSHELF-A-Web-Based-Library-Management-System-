<?php
/**
 * logout.php — End the user session securely
 * -------------------------------------------------------------
 * Clears all session data, destroys the session cookie and the
 * session itself, then redirects to the login page.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    log_activity('Logged out', $_SESSION['user_id']);
}

// Clear the session array.
$_SESSION = [];

// Delete the session cookie from the browser.
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Destroy the session on the server.
session_destroy();

redirect('login.php?msg=logged_out');
