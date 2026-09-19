<?php
/**
 * register.php — Member registration
 * -------------------------------------------------------------
 * Creates a new member account. Demonstrates the required security
 * measures: input sanitisation, server + client validation,
 * password hashing with password_hash(), prepared statements and
 * CSRF protection.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Register';

// Already logged in? Send them to their dashboard.
if (is_logged_in()) {
    redirect(is_staff() ? 'admin/admin_dashboard.php' : 'dashboard.php');
}

$errors = [];
$old = ['fullname' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old['fullname'] = clean($_POST['fullname'] ?? '');
    $old['email']    = clean($_POST['email'] ?? '');
    $old['phone']    = clean($_POST['phone'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirm         = $_POST['confirm'] ?? '';

    // --- Server-side validation ---
    if (strlen($old['fullname']) < 3)        $errors['fullname'] = 'Please enter your full name.';
    if (!valid_email($old['email']))         $errors['email'] = 'Please enter a valid email.';
    if ($old['phone'] && !preg_match('/^[0-9+\-\s()]{7,15}$/', $old['phone']))
                                             $errors['phone'] = 'Please enter a valid phone number.';
    if (strlen($password) < 6)               $errors['password'] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)              $errors['confirm'] = 'Passwords do not match.';

    // --- Unique email check ---
    if (!isset($errors['email'])) {
        $exists = db_select_one("SELECT user_id FROM users WHERE email = ?", 's', [$old['email']]);
        if ($exists) $errors['email'] = 'That email is already registered.';
    }

    if (!$errors) {
        // Hash the password securely (never store plain text).
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $newId = db_execute(
            "INSERT INTO users (fullname, email, password, role, phone) VALUES (?, ?, ?, 'member', ?)",
            'ssss',
            [$old['fullname'], $old['email'], $hash, $old['phone']]
        );

        // Welcome notification + activity log.
        notify($newId, 'Welcome to ' . SITE_NAME . '!', 'Browse the catalog and borrow your first book.', 'system');
        log_activity('New member registered: ' . $old['fullname'], $newId);

        set_flash('success', 'Account created! Please log in to continue.');
        redirect('login.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <!-- Left brand panel -->
    <aside class="auth-side">
        <h2>Join the smartest library in town 📚</h2>
        <p>Create a free account to borrow books, reserve titles, read eBooks and track your reading journey.</p>
        <div class="auth-feature"><i class="fa-solid fa-bolt"></i><div><strong>Instant access</strong><br><span style="opacity:.85">Borrow your first book in minutes.</span></div></div>
        <div class="auth-feature"><i class="fa-solid fa-wand-magic-sparkles"></i><div><strong>Smart recommendations</strong><br><span style="opacity:.85">Personalised picks just for you.</span></div></div>
        <div class="auth-feature"><i class="fa-solid fa-bell"></i><div><strong>Never miss a due date</strong><br><span style="opacity:.85">Automatic reminders keep you on track.</span></div></div>
    </aside>

    <!-- Form -->
    <div class="auth-form-side">
        <div class="auth-box">
            <h1>Create your account</h1>
            <p class="sub">It only takes a minute. Already a member? <a href="login.php" style="color:var(--brand);font-weight:600">Log in</a>.</p>

            <?php render_flash(); ?>

            <form method="post" action="register.php" data-validate novalidate>
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="fullname">Full name</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="fullname" name="fullname" value="<?= e($old['fullname']) ?>" placeholder="Jane Doe" required data-minlength="3">
                    </div>
                    <div class="field-error <?= isset($errors['fullname']) ? 'show' : '' ?>"><?= e($errors['fullname'] ?? '') ?></div>
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" placeholder="jane@example.com" required>
                    </div>
                    <div class="field-error <?= isset($errors['email']) ? 'show' : '' ?>"><?= e($errors['email'] ?? '') ?></div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone <span class="text-mute">(optional)</span></label>
                    <div class="input-icon">
                        <i class="fa-solid fa-phone"></i>
                        <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" placeholder="07X XXX XXXX">
                    </div>
                    <div class="field-error <?= isset($errors['phone']) ? 'show' : '' ?>"><?= e($errors['phone'] ?? '') ?></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="At least 6 characters" required data-minlength="6">
                    </div>
                    <div class="pw-meter"><span id="pwMeterBar"></span></div>
                    <div class="field-error <?= isset($errors['password']) ? 'show' : '' ?>"><?= e($errors['password'] ?? '') ?></div>
                </div>

                <div class="form-group">
                    <label for="confirm">Confirm password</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="confirm" name="confirm" placeholder="Re-enter password" required data-match="password">
                    </div>
                    <div class="field-error <?= isset($errors['confirm']) ? 'show' : '' ?>"><?= e($errors['confirm'] ?? '') ?></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg mt-1">
                    <i class="fa-solid fa-user-plus"></i> Create account
                </button>
            </form>

            <p class="auth-switch">By registering you agree to our friendly library rules.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
