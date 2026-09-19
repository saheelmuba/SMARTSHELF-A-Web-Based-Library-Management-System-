<?php
/**
 * admin/activity_log.php — Audit trail
 * -------------------------------------------------------------
 * Admin-only chronological log of important system actions,
 * joined with the acting user where available.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Activity Log';

$logs = db_select(
    "SELECT l.*, u.fullname FROM activity_log l
     LEFT JOIN users u ON u.user_id=l.user_id
     ORDER BY l.created_at DESC LIMIT 100"
);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header"><div><h1>Activity Log</h1><p class="text-soft">The most recent 100 system actions.</p></div></div>
        <div class="panel">
            <div class="panel-body">
                <div class="timeline">
                    <?php foreach ($logs as $l): ?>
                        <div class="timeline-item">
                            <div class="t-text"><?= e($l['action']) ?></div>
                            <div class="t-time"><i class="fa-solid fa-user" style="font-size:.7rem"></i> <?= e($l['fullname'] ?? 'System') ?> · <?= time_ago($l['created_at']) ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$logs): ?><p class="text-mute">No activity recorded yet.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
