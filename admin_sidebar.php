<?php
/**
 * admin_sidebar.php — Shared admin/librarian dashboard sidebar
 * -------------------------------------------------------------
 * Renders the navigation rail used by every staff page. Highlights
 * the active link and hides admin-only items from librarians.
 * Expects $current (current filename) to be available.
 * -------------------------------------------------------------
 */
$current = basename($_SERVER['PHP_SELF']);

/** Helper to mark the active link. */
function navActive($file)
{
    global $current;
    return $current === $file ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-section">Overview</div>
    <a href="<?= BASE_URL ?>admin/admin_dashboard.php" class="<?= navActive('admin_dashboard.php') ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
    <a href="<?= BASE_URL ?>admin/reports.php" class="<?= navActive('reports.php') ?>"><i class="fa-solid fa-chart-line"></i> Reports &amp; Analytics</a>

    <div class="sidebar-section">Library</div>
    <a href="<?= BASE_URL ?>admin/manage_books.php" class="<?= navActive('manage_books.php') . ' ' . navActive('add_book.php') . ' ' . navActive('edit_book.php') ?>"><i class="fa-solid fa-book"></i> Manage Books</a>
    <a href="<?= BASE_URL ?>admin/manage_borrowings.php" class="<?= navActive('manage_borrowings.php') ?>"><i class="fa-solid fa-right-left"></i> Borrowings</a>
    <a href="<?= BASE_URL ?>admin/manage_reservations.php" class="<?= navActive('manage_reservations.php') ?>"><i class="fa-solid fa-clock"></i> Reservations</a>
    <a href="<?= BASE_URL ?>admin/manage_fines.php" class="<?= navActive('manage_fines.php') ?>"><i class="fa-solid fa-coins"></i> Fines</a>
    <a href="<?= BASE_URL ?>admin/manage_categories.php" class="<?= navActive('manage_categories.php') ?>"><i class="fa-solid fa-tags"></i> Categories</a>

    <div class="sidebar-section">Content</div>
    <a href="<?= BASE_URL ?>admin/manage_features.php" class="<?= navActive('manage_features.php') . ' ' . navActive('add_features.php') . ' ' . navActive('edit_features.php') ?>"><i class="fa-solid fa-star"></i> Manage Features</a>
    <a href="<?= BASE_URL ?>admin/messages.php" class="<?= navActive('messages.php') ?>"><i class="fa-solid fa-envelope"></i> Messages</a>

    <?php if (is_admin()): ?>
        <div class="sidebar-section">Administration</div>
        <a href="<?= BASE_URL ?>admin/manage_user.php" class="<?= navActive('manage_user.php') . ' ' . navActive('add_user.php') . ' ' . navActive('edit_user.php') ?>"><i class="fa-solid fa-users"></i> Manage Users</a>
        <a href="<?= BASE_URL ?>admin/activity_log.php" class="<?= navActive('activity_log.php') ?>"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a>
    <?php endif; ?>

    <div class="sidebar-section">Account</div>
    <a href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user"></i> My Profile</a>
    <a href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</aside>
