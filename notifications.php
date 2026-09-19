<?php
/**
 * notifications.php — In-app notification centre
 * -------------------------------------------------------------
 * Lists a user's notifications (reminders, overdue alerts, queue
 * updates) and lets them mark all as read.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Notifications';
$userId = $_SESSION['user_id'];

// Mark all as read on request.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    db_execute("UPDATE notifications SET is_read = 1 WHERE user_id = ?", 'i', [$userId]);
    set_flash('success', 'All notifications marked as read.');
    redirect('notifications.php');
}

$notifs = db_select(
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC",
    'i', [$userId]
);

// Icon + colour per notification type.
$styles = [
    'due'         => ['fa-clock', 'badge-amber'],
    'overdue'     => ['fa-triangle-exclamation', 'badge-red'],
    'reservation' => ['fa-bookmark', 'badge-blue'],
    'fine'        => ['fa-coins', 'badge-red'],
    'system'      => ['fa-gear', 'badge-brand'],
    'info'        => ['fa-circle-info', 'badge-gray'],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="section-sm">
    <div class="container" style="max-width:760px">
        <?php render_flash(); ?>
        <div class="dash-header">
            <div><h1><i class="fa-solid fa-bell"></i> Notifications</h1><p class="text-soft">Reminders, alerts and queue updates.</p></div>
            <?php if ($notifs): ?>
                <form method="post"><?= csrf_field() ?><button class="btn btn-ghost btn-sm"><i class="fa-solid fa-check-double"></i> Mark all read</button></form>
            <?php endif; ?>
        </div>

        <?php if ($notifs): ?>
            <?php foreach ($notifs as $n):
                $st = $styles[$n['type']] ?? $styles['info']; ?>
                <div class="card card-pad mb-2 flex items-center gap-2" style="<?= $n['is_read'] ? 'opacity:.7' : 'border-left:3px solid var(--brand)' ?>">
                    <div class="stat-ico" style="width:46px;height:46px;font-size:1.1rem;background:var(--surface-2);color:var(--brand)"><i class="fa-solid <?= $st[0] ?>"></i></div>
                    <div style="flex:1">
                        <div class="flex between items-center wrap gap-1">
                            <strong><?= e($n['title']) ?></strong>
                            <span class="badge <?= $st[1] ?>"><?= e(ucfirst($n['type'])) ?></span>
                        </div>
                        <p class="text-soft" style="font-size:.92rem"><?= e($n['message']) ?></p>
                        <span class="text-mute" style="font-size:.8rem"><?= time_ago($n['created_at']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state"><i class="fa-solid fa-bell-slash"></i><h3>No notifications</h3><p>You're all caught up!</p></div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
