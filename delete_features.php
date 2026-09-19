<?php
/**
 * admin/delete_features.php — Delete a feature (CRUD: Delete)
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();

// Destructive action: only accept a CSRF-protected POST, never a GET link.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/manage_features.php');
}
verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$feature = db_select_one("SELECT * FROM features WHERE feature_id = ?", 'i', [$id]);

if ($feature) {
    db_execute("DELETE FROM features WHERE feature_id = ?", 'i', [$id]);
    log_activity('Deleted feature "' . $feature['feature_name'] . '"');
    set_flash('success', 'Feature deleted.');
} else {
    set_flash('error', 'Feature not found.');
}
redirect('admin/manage_features.php');
