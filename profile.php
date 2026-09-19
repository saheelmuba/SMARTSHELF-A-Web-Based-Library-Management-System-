<?php
/**
 * profile.php — User profile + account settings
 * -------------------------------------------------------------
 * Any logged-in user can view and update their own details and
 * change their password. Uses prepared statements, CSRF tokens
 * and password hashing.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'My Profile';

$userId = $_SESSION['user_id'];
$user   = db_select_one("SELECT * FROM users WHERE user_id = ?", 'i', [$userId]);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form = $_POST['form'] ?? '';

    // ---- Update profile details ----
    if ($form === 'details') {
        $fullname = clean($_POST['fullname'] ?? '');
        $phone    = clean($_POST['phone'] ?? '');
        $address  = clean($_POST['address'] ?? '');

        if (strlen($fullname) < 3) $errors['fullname'] = 'Please enter your full name.';

        if (!$errors) {
            db_execute(
                "UPDATE users SET fullname = ?, phone = ?, address = ? WHERE user_id = ?",
                'sssi',
                [$fullname, $phone, $address, $userId]
            );
            $_SESSION['fullname'] = $fullname; // keep navbar in sync
            set_flash('success', 'Profile updated successfully.');
            redirect('profile.php');
        }
    }

    // ---- Change password ----
    if ($form === 'password') {
        $current = $_POST['current'] ?? '';
        $new     = $_POST['new'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (!password_verify($current, $user['password'])) $errors['current'] = 'Current password is incorrect.';
        if (strlen($new) < 6)                               $errors['new'] = 'New password must be at least 6 characters.';
        if ($new !== $confirm)                              $errors['confirm'] = 'Passwords do not match.';

        if (!$errors) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            db_execute("UPDATE users SET password = ? WHERE user_id = ?", 'si', [$hash, $userId]);
            set_flash('success', 'Password changed successfully.');
            redirect('profile.php');
        }
    }
}

// Quick personal stats.
$totalBorrowed = (int) db_scalar("SELECT COUNT(*) FROM borrowings WHERE user_id = ?", 'i', [$userId]);
$activeLoans   = user_active_loans_count($userId);
$fines         = user_outstanding_fines($userId);

require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container" style="max-width:900px">
        <?php render_flash(); ?>

        <!-- Profile header card -->
        <div class="card card-pad mb-3 flex items-center gap-2 wrap">
            <div class="avatar-sm" style="width:84px;height:84px;font-size:1.8rem;background:<?= color_from_string($user['fullname']) ?>">
                <?= e(initials($user['fullname'])) ?>
            </div>
            <div>
                <h1 style="font-size:1.6rem"><?= e($user['fullname']) ?></h1>
                <p class="text-soft"><?= e($user['email']) ?></p>
                <span class="badge badge-brand mt-1"><i class="fa-solid fa-id-badge"></i> <?= e(ucfirst($user['role'])) ?></span>
                <span class="badge <?= $user['status'] === 'active' ? 'badge-green' : 'badge-red' ?> mt-1"><?= e(ucfirst($user['status'])) ?></span>
            </div>
            <div class="flex gap-2 wrap" style="margin-left:auto">
                <div class="text-center"><div class="num" style="font-size:1.5rem;font-weight:800"><?= $totalBorrowed ?></div><div class="text-mute" style="font-size:.82rem">Borrowed</div></div>
                <div class="text-center"><div class="num" style="font-size:1.5rem;font-weight:800"><?= $activeLoans ?></div><div class="text-mute" style="font-size:.82rem">Active</div></div>
                <div class="text-center"><div class="num" style="font-size:1.5rem;font-weight:800;color:<?= $fines > 0 ? 'var(--red)' : 'inherit' ?>"><?= number_format($fines) ?></div><div class="text-mute" style="font-size:.82rem">Fines (<?= CURRENCY ?>)</div></div>
            </div>
        </div>

        <div class="grid grid-2" style="align-items:start">
            <!-- Edit details -->
            <div class="card card-pad">
                <h2 style="font-size:1.2rem;margin-bottom:18px"><i class="fa-solid fa-user-pen"></i> Edit details</h2>
                <form method="post" data-validate novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="form" value="details">
                    <div class="form-group">
                        <label for="fullname">Full name</label>
                        <input type="text" id="fullname" name="fullname" value="<?= e($user['fullname']) ?>" required data-minlength="3">
                        <div class="field-error <?= isset($errors['fullname']) ? 'show' : '' ?>"><?= e($errors['fullname'] ?? '') ?></div>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="text-mute">(cannot change)</span></label>
                        <input type="email" value="<?= e($user['email']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>" placeholder="07X XXX XXXX">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" style="min-height:80px"><?= e($user['address']) ?></textarea>
                    </div>
                    <button class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
                </form>
            </div>

            <!-- Change password -->
            <div class="card card-pad">
                <h2 style="font-size:1.2rem;margin-bottom:18px"><i class="fa-solid fa-lock"></i> Change password</h2>
                <form method="post" data-validate novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="form" value="password">
                    <div class="form-group">
                        <label for="current">Current password</label>
                        <input type="password" id="current" name="current" required>
                        <div class="field-error <?= isset($errors['current']) ? 'show' : '' ?>"><?= e($errors['current'] ?? '') ?></div>
                    </div>
                    <div class="form-group">
                        <label for="password">New password</label>
                        <input type="password" id="password" name="new" required data-minlength="6">
                        <div class="pw-meter"><span id="pwMeterBar"></span></div>
                        <div class="field-error <?= isset($errors['new']) ? 'show' : '' ?>"><?= e($errors['new'] ?? '') ?></div>
                    </div>
                    <div class="form-group">
                        <label for="confirm">Confirm new password</label>
                        <input type="password" id="confirm" name="confirm" required data-match="password">
                        <div class="field-error <?= isset($errors['confirm']) ? 'show' : '' ?>"><?= e($errors['confirm'] ?? '') ?></div>
                    </div>
                    <button class="btn btn-primary btn-block"><i class="fa-solid fa-key"></i> Update password</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
