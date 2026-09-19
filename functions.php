<?php
/**
 * functions.php
 * -------------------------------------------------------------
 * Reusable helper functions shared across the whole application:
 * sanitisation, security (CSRF), auth guards, fines, notifications
 * and small formatting utilities.
 * -------------------------------------------------------------
 */

require_once __DIR__ . '/config.php';

/* =============================================================
 *  INPUT SANITISATION & OUTPUT ESCAPING
 * ============================================================= */

/**
 * Clean raw user input: trim whitespace and strip slashes.
 * Use for values you will store / process.
 */
function clean($value)
{
    return trim(stripslashes($value ?? ''));
}

/**
 * Escape a value for safe output in HTML (prevents XSS).
 */
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Validate an email address. Returns the clean email or false.
 */
function valid_email($email)
{
    $email = clean($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/* =============================================================
 *  CSRF PROTECTION
 * ============================================================= */

/**
 * Return the current CSRF token, creating one if needed.
 */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF field for embedding inside forms.
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify a submitted CSRF token. Kills the request on mismatch.
 */
function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('Invalid or expired form token. Please go back and try again.');
    }
}

/* =============================================================
 *  AUTHENTICATION & AUTHORISATION GUARDS
 * ============================================================= */

/** Is anyone logged in? */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/** Current user's role, or null. */
function current_role()
{
    return $_SESSION['role'] ?? null;
}

/** Convenience role checks. */
function is_admin()      { return current_role() === 'admin'; }
function is_librarian()  { return current_role() === 'librarian'; }
function is_staff()      { return in_array(current_role(), ['admin', 'librarian'], true); }

/**
 * Force a user to be logged in; otherwise redirect to login.
 */
function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php?msg=login_required');
    }
}

/**
 * Restrict a page to staff (admin or librarian) only.
 */
function require_staff()
{
    require_login();
    if (!is_staff()) {
        redirect('dashboard.php?msg=denied');
    }
}

/**
 * Restrict a page to administrators only.
 */
function require_admin()
{
    require_login();
    if (!is_admin()) {
        redirect('dashboard.php?msg=denied');
    }
}

/**
 * Safe redirect helper that always resolves against BASE_URL
 * for relative paths and exits immediately.
 */
function redirect($path)
{
    // Allow absolute URLs to pass through untouched.
    if (preg_match('#^https?://#', $path)) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
    }
    exit;
}

/* =============================================================
 *  FLASH MESSAGES (one-time session notices)
 * ============================================================= */

function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render any pending flash message as a styled alert box.
 */
function render_flash()
{
    $flash = get_flash();
    if ($flash) {
        $icon = [
            'success' => 'fa-circle-check',
            'error'   => 'fa-circle-exclamation',
            'warning' => 'fa-triangle-exclamation',
            'info'    => 'fa-circle-info',
        ][$flash['type']] ?? 'fa-circle-info';
        echo '<div class="alert alert-' . e($flash['type']) . '">'
           . '<i class="fa-solid ' . $icon . '"></i><span>' . e($flash['message']) . '</span></div>';
    }
}

/* =============================================================
 *  DATABASE HELPERS
 * ============================================================= */

/**
 * Run a prepared SELECT and return all rows as an array.
 *
 * @param string $sql    SQL with ? placeholders
 * @param string $types  bind types, e.g. "si"
 * @param array  $params values to bind
 */
function db_select($sql, $types = '', $params = [])
{
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

/**
 * Run a prepared SELECT and return a single row (or null).
 */
function db_select_one($sql, $types = '', $params = [])
{
    $rows = db_select($sql, $types, $params);
    return $rows[0] ?? null;
}

/**
 * Run a prepared INSERT/UPDATE/DELETE.
 * Returns the insert id (for INSERT) or affected-rows count.
 */
function db_execute($sql, $types = '', $params = [])
{
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $ok = $stmt->execute();
    $id = $conn->insert_id;
    $affected = $stmt->affected_rows;
    $stmt->close();
    if (!$ok) {
        return false;
    }
    return $id > 0 ? $id : $affected;
}

/** Quick scalar COUNT/SUM helper. */
function db_scalar($sql, $types = '', $params = [])
{
    $row = db_select_one($sql, $types, $params);
    return $row ? array_values($row)[0] : 0;
}

/* =============================================================
 *  LIBRARY DOMAIN LOGIC
 * ============================================================= */

/**
 * Record an entry in the activity log (audit trail).
 */
function log_activity($action, $userId = null)
{
    $userId = $userId ?? ($_SESSION['user_id'] ?? null);
    db_execute(
        'INSERT INTO activity_log (user_id, action) VALUES (?, ?)',
        'is',
        [$userId, $action]
    );
}

/**
 * Create an in-app notification for a user.
 */
function notify($userId, $title, $message, $type = 'info')
{
    db_execute(
        'INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)',
        'isss',
        [$userId, $title, $message, $type]
    );
}

/**
 * Recalculate fines for every active loan based on today's date.
 * Marks loans overdue and updates the fine_amount automatically.
 * This is the "automated fine tracking" engine.
 */
function recalculate_fines()
{
    global $conn;
    $today = date('Y-m-d');
    $loans = db_select(
        "SELECT borrow_id, user_id, book_id, due_date
         FROM borrowings
         WHERE status IN ('borrowed','overdue') AND return_date IS NULL"
    );
    foreach ($loans as $loan) {
        $daysLate = (strtotime($today) - strtotime($loan['due_date'])) / 86400;
        if ($daysLate > 0) {
            $fine = round($daysLate * FINE_PER_DAY, 2);
            db_execute(
                "UPDATE borrowings SET status='overdue', fine_amount=? WHERE borrow_id=?",
                'di',
                [$fine, $loan['borrow_id']]
            );
            // Keep a matching row in the fines ledger (one per borrow).
            $existing = db_select_one(
                'SELECT fine_id FROM fines WHERE borrow_id=?',
                'i',
                [$loan['borrow_id']]
            );
            if ($existing) {
                db_execute(
                    "UPDATE fines SET amount=? WHERE fine_id=? AND status='unpaid'",
                    'di',
                    [$fine, $existing['fine_id']]
                );
            } else {
                db_execute(
                    'INSERT INTO fines (user_id, borrow_id, amount, reason, status) VALUES (?,?,?,?,?)',
                    'iidss',
                    [$loan['user_id'], $loan['borrow_id'], $fine, 'Overdue book', 'unpaid']
                );
            }
        }
    }
}

/**
 * Total unpaid fines for a member.
 */
function user_outstanding_fines($userId)
{
    return (float) db_scalar(
        "SELECT COALESCE(SUM(amount),0) FROM fines WHERE user_id=? AND status='unpaid'",
        'i',
        [$userId]
    );
}

/**
 * How many books a member currently has on loan.
 */
function user_active_loans_count($userId)
{
    return (int) db_scalar(
        "SELECT COUNT(*) FROM borrowings WHERE user_id=? AND return_date IS NULL",
        'i',
        [$userId]
    );
}

/**
 * Simple "AI" recommendation engine.
 * Recommends popular, available books from the genres a member has
 * read most, excluding books they already borrowed. Falls back to
 * trending titles for new members.
 */
function recommend_books($userId, $limit = 4)
{
    // Genres the user reads most often.
    $favGenres = db_select(
        "SELECT b.category_id, COUNT(*) AS c
         FROM borrowings br JOIN books b ON b.book_id = br.book_id
         WHERE br.user_id = ?
         GROUP BY b.category_id ORDER BY c DESC LIMIT 3",
        'i',
        [$userId]
    );

    if ($favGenres) {
        $ids = array_column($favGenres, 'category_id');
        $ids = array_filter($ids, fn($v) => $v !== null);
        if ($ids) {
            $place = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids)) . 'i';
            $params = array_merge($ids, [$userId]);
            $sql = "SELECT b.*, c.name AS category_name
                    FROM books b LEFT JOIN categories c ON c.category_id = b.category_id
                    WHERE b.category_id IN ($place)
                      AND b.available_copies > 0
                      AND b.book_id NOT IN (SELECT book_id FROM borrowings WHERE user_id = ?)
                    ORDER BY b.rating DESC, b.borrow_count DESC
                    LIMIT " . (int) $limit;
            $rows = db_select($sql, $types, $params);
            if ($rows) {
                return $rows;
            }
        }
    }

    // Fallback: trending available books.
    return db_select(
        "SELECT b.*, c.name AS category_name
         FROM books b LEFT JOIN categories c ON c.category_id = b.category_id
         WHERE b.available_copies > 0
         ORDER BY b.borrow_count DESC, b.rating DESC
         LIMIT " . (int) $limit
    );
}

/* =============================================================
 *  FORMATTING UTILITIES
 * ============================================================= */

/** Format a money value with the currency prefix. */
function money($amount)
{
    return CURRENCY . ' ' . number_format((float) $amount, 2);
}

/** Human friendly date. */
function fdate($date)
{
    if (!$date) return '-';
    return date('d M Y', strtotime($date));
}

/** Relative "x days ago" style label. */
function time_ago($datetime)
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)       return 'just now';
    if ($diff < 3600)     return floor($diff / 60) . 'm ago';
    if ($diff < 86400)    return floor($diff / 3600) . 'h ago';
    if ($diff < 604800)   return floor($diff / 86400) . 'd ago';
    return date('d M Y', strtotime($datetime));
}

/**
 * Produce initials from a full name for avatar placeholders.
 */
function initials($name)
{
    $parts = preg_split('/\s+/', trim($name));
    $first = $parts[0][0] ?? 'U';
    $last  = count($parts) > 1 ? $parts[count($parts) - 1][0] : '';
    return strtoupper($first . $last);
}

/** Count unread notifications for the badge. */
function unread_notifications($userId)
{
    return (int) db_scalar(
        'SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0',
        'i',
        [$userId]
    );
}

/**
 * Return a deterministic pastel colour for a string (avatars / covers).
 */
function color_from_string($str)
{
    $colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b',
               '#10b981', '#06b6d4', '#3b82f6', '#14b8a6', '#a855f7'];
    return $colors[abs(crc32($str)) % count($colors)];
}
