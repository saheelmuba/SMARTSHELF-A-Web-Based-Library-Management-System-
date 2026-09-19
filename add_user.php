<?php
/**
 * admin/add_user.php — Create a user (CRUD: Create)
 * -------------------------------------------------------------
 * Admin creates any kind of account (member/librarian/admin).
 * Password is hashed; email uniqueness enforced.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Add User';

$errors = [];
$old = ['fullname'=>'','email'=>'','phone'=>'','role'=>'member'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $old['fullname'] = clean($_POST['fullname'] ?? '');
    $old['email']    = clean($_POST['email'] ?? '');
    $old['phone']    = clean($_POST['phone'] ?? '');
    $old['role']     = clean($_POST['role'] ?? 'member');
    $password        = $_POST['password'] ?? '';

    if (strlen($old['fullname']) < 3)   $errors['fullname'] = 'Full name is required.';
    if (!valid_email($old['email']))    $errors['email'] = 'Valid email required.';
    if (strlen($password) < 6)          $errors['password'] = 'Password must be at least 6 characters.';
    if (!in_array($old['role'], ['member','librarian','admin'], true)) $old['role'] = 'member';

    if (!isset($errors['email']) && db_select_one("SELECT user_id FROM users WHERE email=?", 's', [$old['email']])) {
        $errors['email'] = 'Email already registered.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $newId = db_execute(
            "INSERT INTO users (fullname, email, password, role, phone) VALUES (?,?,?,?,?)",
            'sssss', [$old['fullname'], $old['email'], $hash, $old['role'], $old['phone']]
        );
        notify($newId, 'Account created', 'An administrator created your ' . $old['role'] . ' account.', 'system');
        log_activity('Created ' . $old['role'] . ' account: ' . $old['fullname']);
        set_flash('success', 'User "' . $old['fullname'] . '" created.');
        redirect('admin/manage_user.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header">
            <div><h1>Add User</h1><p class="text-soft">Create a member, librarian or admin account.</p></div>
            <a href="<?= BASE_URL ?>admin/manage_user.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <?php render_flash(); ?>
        <div class="panel" style="max-width:680px">
            <div class="panel-body">
                <form method="post" data-validate novalidate>
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="form-group"><label for="fullname">Full name *</label>
                            <input type="text" id="fullname" name="fullname" value="<?= e($old['fullname']) ?>" required data-minlength="3">
                            <div class="field-error <?= isset($errors['fullname']) ? 'show' : '' ?>"><?= e($errors['fullname'] ?? '') ?></div>
                        </div>
                        <div class="form-group"><label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required>
                            <div class="field-error <?= isset($errors['email']) ? 'show' : '' ?>"><?= e($errors['email'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>"></div>
                        <div class="form-group"><label for="role">Role *</label>
                            <select id="role" name="role">
                                <option value="member"    <?= $old['role']==='member'?'selected':'' ?>>Member</option>
                                <option value="librarian" <?= $old['role']==='librarian'?'selected':'' ?>>Librarian</option>
                                <option value="admin"     <?= $old['role']==='admin'?'selected':'' ?>>Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label for="password">Password *</label>
                        <input type="password" id="password" name="password" required data-minlength="6">
                        <div class="pw-meter"><span id="pwMeterBar"></span></div>
                        <div class="field-error <?= isset($errors['password']) ? 'show' : '' ?>"><?= e($errors['password'] ?? '') ?></div>
                    </div>
                    <button class="btn btn-primary btn-lg"><i class="fa-solid fa-user-plus"></i> Create user</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
