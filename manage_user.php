<?php
/**
 * admin/manage_user.php — User list (CRUD: Read)
 * -------------------------------------------------------------
 * Admin-only page listing all users with role, status, fines and
 * edit/delete actions. Supports search by name/email.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Manage Users';

$q = clean($_GET['q'] ?? '');
$sql = "SELECT u.*,
          (SELECT COUNT(*) FROM borrowings b WHERE b.user_id=u.user_id AND b.return_date IS NULL) AS active_loans,
          (SELECT COALESCE(SUM(amount),0) FROM fines f WHERE f.user_id=u.user_id AND f.status='unpaid') AS fines
        FROM users u";
$types=''; $params=[];
if ($q !== '') {
    $sql .= " WHERE u.fullname LIKE ? OR u.email LIKE ?";
    $like="%$q%"; $types='ss'; $params=[$like,$like];
}
$sql .= " ORDER BY u.user_id DESC";
$users = db_select($sql, $types, $params);

$roleBadge = ['admin'=>'badge-red','librarian'=>'badge-blue','member'=>'badge-green'];

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header">
            <div><h1>Manage Users</h1><p class="text-soft"><?= count($users) ?> accounts.</p></div>
            <a href="<?= BASE_URL ?>admin/add_user.php" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Add user</a>
        </div>
        <?php render_flash(); ?>

        <form method="get" class="search-bar mb-3">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by name or email...">
        </form>

        <div class="panel">
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>User</th><th>Role</th><th>Phone</th><th>Loans</th><th>Fines</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="flex items-center gap-1">
                                <span class="avatar-sm" style="background:<?= color_from_string($u['fullname']) ?>"><?= e(initials($u['fullname'])) ?></span>
                                <div><strong><?= e($u['fullname']) ?></strong><br><span class="text-mute" style="font-size:.8rem"><?= e($u['email']) ?></span></div>
                            </td>
                            <td><span class="badge <?= $roleBadge[$u['role']] ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                            <td class="text-mute" style="font-size:.85rem"><?= e($u['phone'] ?: '—') ?></td>
                            <td><?= (int)$u['active_loans'] ?></td>
                            <td><?= $u['fines'] > 0 ? '<span style="color:var(--red);font-weight:600">' . money($u['fines']) . '</span>' : '—' ?></td>
                            <td><span class="badge <?= $u['status']==='active'?'badge-green':'badge-red' ?>"><?= e(ucfirst($u['status'])) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= BASE_URL ?>admin/edit_user.php?id=<?= $u['user_id'] ?>" class="icon-btn" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                        <form method="post" action="<?= BASE_URL ?>admin/delete_user.php" style="display:inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
                                            <button type="submit" class="icon-btn danger" title="Delete" data-confirm="Delete &quot;<?= e(addslashes($u['fullname'])) ?>&quot; and all their records?"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
