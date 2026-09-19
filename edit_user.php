<?php
/**
 * admin/edit_user.php — Edit a user (CRUD: Update)
 * -------------------------------------------------------------
 * Update a user's details, role and status. Optionally reset the
 * password. Admins cannot demote/suspend their own account here.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Edit User';

$id = (int) ($_GET['id'] ?? 0);
$user = db_select_one("SELECT * FROM users WHERE user_id = ?", 'i', [$id]);
if (!$user) { set_flash('error', 'User not found.'); redirect('admin/manage_user.php'); }

$isSelf = ($id === (int)$_SESSION['user_id']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullname = clean($_POST['fullname'] ?? '');
    $phone    = clean($_POST['phone'] ?? '');
    $role     = clean($_POST['role'] ?? $user['role']);
    $status   = clean($_POST['status'] ?? $user['status']);
    $newPass  = $_POST['password'] ?? '';

    if (strlen($fullname) < 3) $errors['fullname'] = 'Full name is required.';
    if (!in_array($role, ['member','librarian','admin'], true))   $role = $user['role'];
    if (!in_array($status, ['active','suspended'], true))         $status = $user['status'];
    // Safety: an admin can't lock themselves out.
    if ($isSelf) { $role = 'admin'; $status = 'active'; }
    if ($newPass !== '' && strlen($newPass) < 6) $errors['password'] = 'Password must be at least 6 characters.';

    if (!$errors) {
        db_execute(
            "UPDATE users SET fullname=?, phone=?, role=?, status=? WHERE user_id=?",
            'ssssi', [$fullname, $phone, $role, $status, $id]
        );
        if ($newPass !== '') {
            db_execute("UPDATE users SET password=? WHERE user_id=?", 'si', [password_hash($newPass, PASSWORD_DEFAULT), $id]);
        }
        if ($isSelf) $_SESSION['fullname'] = $fullname;
        log_activity('Updated user "' . $fullname . '"');
        set_flash('success', 'User updated.');
        redirect('admin/manage_user.php');
    }
    $user = array_merge($user, ['fullname'=>$fullname,'phone'=>$phone,'role'=>$role,'status'=>$status]);
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header">
            <div><h1>Edit User</h1><p class="text-soft">Update "<?= e($user['fullname']) ?>".</p></div>
            <a href="<?= BASE_URL ?>admin/manage_user.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <?php render_flash(); ?>
        <?php if ($isSelf): ?><div class="alert alert-info"><i class="fa-solid fa-circle-info"></i><span>This is your own account — role and status are locked for safety.</span></div><?php endif; ?>
        <div class="panel" style="max-width:680px">
            <div class="panel-body">
                <form method="post" data-validate novalidate>
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="form-group"><label for="fullname">Full name *</label>
                            <input type="text" id="fullname" name="fullname" value="<?= e($user['fullname']) ?>" required data-minlength="3">
                            <div class="field-error <?= isset($errors['fullname']) ? 'show' : '' ?>"><?= e($errors['fullname'] ?? '') ?></div>
                        </div>
                        <div class="form-group"><label>Email <span class="text-mute">(locked)</span></label>
                            <input type="email" value="<?= e($user['email']) ?>" disabled></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>"></div>
                        <div class="form-group"><label for="role">Role</label>
                            <select id="role" name="role" <?= $isSelf ? 'disabled' : '' ?>>
                                <option value="member"    <?= $user['role']==='member'?'selected':'' ?>>Member</option>
                                <option value="librarian" <?= $user['role']==='librarian'?'selected':'' ?>>Librarian</option>
                                <option value="admin"     <?= $user['role']==='admin'?'selected':'' ?>>Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="status">Status</label>
                            <select id="status" name="status" <?= $isSelf ? 'disabled' : '' ?>>
                                <option value="active"    <?= $user['status']==='active'?'selected':'' ?>>Active</option>
                                <option value="suspended" <?= $user['status']==='suspended'?'selected':'' ?>>Suspended</option>
                            </select>
                        </div>
                        <div class="form-group"><label for="password">Reset password <span class="text-mute">(optional)</span></label>
                            <input type="password" id="password" name="password" placeholder="Leave blank to keep current">
                            <div class="field-error <?= isset($errors['password']) ? 'show' : '' ?>"><?= e($errors['password'] ?? '') ?></div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-lg"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
