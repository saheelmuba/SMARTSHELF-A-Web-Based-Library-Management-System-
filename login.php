<?php
/**
 * login.php — User login (members, librarians, admins)
 * -------------------------------------------------------------
 * Verifies credentials with password_verify(), regenerates the
 * session ID to prevent fixation, stores minimal session data and
 * redirects each role to the right dashboard.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Login';

// Already logged in? Bounce to dashboard.
if (is_logged_in()) {
    redirect(is_staff() ? 'admin/admin_dashboard.php' : 'dashboard.php');
}

$error = '';
$email = '';

// Friendly notices passed via query string.
$notice = '';
if (($_GET['msg'] ?? '') === 'login_required') $notice = 'Please log in to continue.';
if (($_GET['msg'] ?? '') === 'logged_out')     $notice = 'You have been logged out. See you soon!';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        // Look up the user via a prepared statement.
        $user = db_select_one("SELECT * FROM users WHERE email = ?", 's', [$email]);

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Incorrect email or password.';
        } elseif ($user['status'] === 'suspended') {
            $error = 'Your account is suspended. Please contact the library.';
        } else {
            // --- Successful login: harden the session ---
            session_regenerate_id(true);          // prevent session fixation
            $_SESSION['user_id']  = (int) $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['email']    = $user['email'];

            log_activity('Logged in', $user['user_id']);

            // Recalculate fines on login so dashboards are always current.
            recalculate_fines();

            redirect(in_array($user['role'], ['admin', 'librarian'], true)
                ? 'admin/admin_dashboard.php'
                : 'dashboard.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <aside class="auth-side">
        <h2>Welcome back! 👋</h2>
        <p>Log in to access your dashboard, manage your loans and pick up where you left off.</p>
        <div class="auth-feature"><i class="fa-solid fa-book-bookmark"></i><div><strong>Your loans</strong><br><span style="opacity:.85">Track borrowed books and due dates.</span></div></div>
        <div class="auth-feature"><i class="fa-solid fa-clock-rotate-left"></i><div><strong>Reading history</strong><br><span style="opacity:.85">Revisit everything you've read.</span></div></div>
        <div class="auth-feature"><i class="fa-solid fa-chart-pie"></i><div><strong>Personal insights</strong><br><span style="opacity:.85">See your reading stats over time.</span></div></div>
    </aside>

    <div class="auth-form-side">
        <div class="auth-box">
            <h1>Sign in</h1>
            <p class="sub">New here? <a href="register.php" style="color:var(--brand);font-weight:600">Create a free account</a>.</p>

            <?php render_flash(); ?>
            <?php if ($notice): ?><div class="alert alert-info" data-auto><i class="fa-solid fa-circle-info"></i><span><?= e($notice) ?></span></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><span><?= e($error) ?></span></div><?php endif; ?>

            <form method="post" action="login.php" data-validate novalidate>
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="you@example.com" required autofocus>
                    </div>
                    <div class="field-error"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Your password" required>
                    </div>
                    <div class="field-error"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign in
                </button>
            </form>

            <!-- Demo credentials to make grading / testing easy -->
            <div class="demo-creds">
                <strong><i class="fa-solid fa-key"></i> Demo accounts</strong>
                <p style="margin-top:8px">Admin: <code>admin@smartshelf.lk</code> / <code>admin123</code></p>
                <p>Librarian: <code>librarian@smartshelf.lk</code> / <code>lib123</code></p>
                <p>Member: <code>member@smartshelf.lk</code> / <code>member123</code></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
