<?php
/**
 * admin/delete_user.php — Delete a user (CRUD: Delete)
 * -------------------------------------------------------------
 * Removes a user (and cascades their records). Guards against
 * deleting your own account or a user with active loans.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Destructive action: only accept a CSRF-protected POST, never a GET link.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/manage_user.php');
}
verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$user = db_select_one("SELECT * FROM users WHERE user_id = ?", 'i', [$id]);

if (!$user) {
    set_flash('error', 'User not found.');
    redirect('admin/manage_user.php');
}
if ($id === (int)$_SESSION['user_id']) {
    set_flash('error', 'You cannot delete your own account.');
    redirect('admin/manage_user.php');
}

$active = (int) db_scalar("SELECT COUNT(*) FROM borrowings WHERE user_id=? AND return_date IS NULL", 'i', [$id]);
if ($active > 0) {
    set_flash('error', $user['fullname'] . ' has ' . $active . ' active loan(s). Returns must be processed first.');
    redirect('admin/manage_user.php');
}

db_execute("DELETE FROM users WHERE user_id = ?", 'i', [$id]);
log_activity('Deleted user "' . $user['fullname'] . '"');
set_flash('success', 'User "' . $user['fullname'] . '" deleted.');
redirect('admin/manage_user.php');
