<?php
/**
 * config.php
 * -------------------------------------------------------------
 * Central configuration for SmartShelf Library Management System.
 * - Creates a single MySQLi database connection ($conn).
 * - Defines global constants (site name, fine rate, loan period).
 * - Starts a hardened session for every page that includes it.
 *
 * Every PHP page should `require_once` this file first.
 * -------------------------------------------------------------
 */

// ---- Error reporting (development). Turn display off in production. ----
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ---- Database credentials (default XAMPP settings) ----
// Tip: most XAMPP installs use port 3306. Some use 3307 — if you get a
// connection error, set the correct port below (or the SMARTSHELF_DB_PORT
// environment variable). The password is empty by default on XAMPP.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');                        // XAMPP default password is empty
define('DB_NAME', 'smartshelf_library');
define('DB_PORT', (int) (getenv('SMARTSHELF_DB_PORT') ?: 3306));

// ---- Application settings ----
define('SITE_NAME', 'SmartShelf');
define('SITE_TAGLINE', 'Smart Library Management System');
define('LOAN_PERIOD_DAYS', 14);              // Default borrowing period
define('FINE_PER_DAY', 10.00);               // Overdue fine in LKR per day
define('MAX_BOOKS_PER_MEMBER', 5);           // Borrowing limit per member
define('CURRENCY', 'LKR');

/**
 * BASE_URL
 * Auto-detects the folder the project runs from so links work whether
 * the app is at http://localhost/library/ or a custom virtual host.
 */
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
// Strip the /admin segment so admin pages still resolve the project root.
$scriptDir = preg_replace('#/admin$#', '', $scriptDir);
if ($scriptDir === '' || $scriptDir === '.') {
    $scriptDir = '/';
}
if (substr($scriptDir, -1) !== '/') {
    $scriptDir .= '/';
}
define('BASE_URL', $scriptDir);

// ---- Create the database connection (MySQLi, OOP style) ----
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Stop execution with a friendly message if the connection fails.
if ($conn->connect_errno) {
    die(
        '<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:24px;'
        . 'border:1px solid #f3c2c2;background:#fff5f5;border-radius:12px;color:#7a1f1f">'
        . '<h2>Database connection failed</h2>'
        . '<p>Could not connect to MySQL. Please make sure:</p><ul>'
        . '<li>Apache and MySQL are running in the XAMPP control panel.</li>'
        . '<li>You have imported <code>database/smartshelf_library.sql</code> via phpMyAdmin.</li>'
        . '</ul><p><small>Technical detail: ' . htmlspecialchars($conn->connect_error) . '</small></p>'
        . '</div>'
    );
}

// Use UTF-8 for full Unicode support (emoji, accents, etc.).
$conn->set_charset('utf8mb4');

// ---- Hardened session start ----
if (session_status() === PHP_SESSION_NONE) {
    // Only send the cookie over HTTP(S) and keep it out of JavaScript's reach.
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Default timezone (Sri Lanka).
date_default_timezone_set('Asia/Colombo');
