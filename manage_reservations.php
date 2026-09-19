<?php
/**
 * admin/manage_reservations.php — Reservation queue management
 * -------------------------------------------------------------
 * Staff view of the reservation queues. Allows marking a "ready"
 * reservation as fulfilled or cancelling any reservation.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Reservations';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $resId = (int) ($_POST['reservation_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'fulfill') {
        db_execute("UPDATE reservations SET status='fulfilled' WHERE reservation_id=?", 'i', [$resId]);
        set_flash('success', 'Reservation marked as fulfilled.');
    } elseif ($action === 'cancel') {
        db_execute("UPDATE reservations SET status='cancelled' WHERE reservation_id=?", 'i', [$resId]);
        set_flash('info', 'Reservation cancelled.');
    }
    redirect('admin/manage_reservations.php');
}

$reservations = db_select(
    "SELECT r.*, b.title, u.fullname FROM reservations r
     JOIN books b ON b.book_id=r.book_id
     JOIN users u ON u.user_id=r.user_id
     ORDER BY FIELD(r.status,'ready','pending','fulfilled','cancelled'), r.reserved_at ASC"
);

$statusBadge = ['pending'=>'badge-amber','ready'=>'badge-green','fulfilled'=>'badge-gray','cancelled'=>'badge-red'];

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header"><div><h1>Reservations</h1><p class="text-soft">Manage the reservation queue.</p></div></div>
        <?php render_flash(); ?>
        <div class="panel">
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Book</th><th>Member</th><th>Reserved</th><th>Queue</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td><strong><?= e($r['title']) ?></strong></td>
                            <td><?= e($r['fullname']) ?></td>
                            <td><?= fdate($r['reserved_at']) ?></td>
                            <td>#<?= (int)$r['queue_position'] ?></td>
                            <td><span class="badge <?= $statusBadge[$r['status']] ?>"><?= e(ucfirst($r['status'])) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <?php if (in_array($r['status'], ['pending','ready'])): ?>
                                        <?php if ($r['status']==='ready'): ?>
                                        <form method="post" style="margin:0"><?= csrf_field() ?>
                                            <input type="hidden" name="action" value="fulfill"><input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                                            <button class="icon-btn success" title="Mark fulfilled"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="post" style="margin:0"><?= csrf_field() ?>
                                            <input type="hidden" name="action" value="cancel"><input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                                            <button class="icon-btn danger" title="Cancel" data-confirm="Cancel this reservation?"><i class="fa-solid fa-xmark"></i></button>
                                        </form>
                                    <?php else: ?><span class="text-mute" style="font-size:.8rem">—</span><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$reservations): ?><tr><td colspan="6"><div class="empty-state" style="padding:30px"><i class="fa-solid fa-clock"></i><h3>No reservations</h3></div></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
