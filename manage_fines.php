<?php
/**
 * admin/manage_fines.php — Fine ledger management
 * -------------------------------------------------------------
 * Staff view of all fines with totals. Allows marking a fine as
 * paid or waiving (deleting) it.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Fines';

recalculate_fines();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fineId = (int) ($_POST['fine_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $fine = db_select_one("SELECT * FROM fines WHERE fine_id=?", 'i', [$fineId]);
    if ($fine) {
        if ($action === 'pay') {
            db_execute("UPDATE fines SET status='paid' WHERE fine_id=?", 'i', [$fineId]);
            if ($fine['borrow_id']) db_execute("UPDATE borrowings SET fine_amount=0 WHERE borrow_id=?", 'i', [$fine['borrow_id']]);
            notify($fine['user_id'], 'Fine paid', 'Your fine of ' . money($fine['amount']) . ' has been cleared.', 'fine');
            log_activity('Marked fine #' . $fineId . ' as paid');
            set_flash('success', 'Fine marked as paid.');
        } elseif ($action === 'waive') {
            db_execute("DELETE FROM fines WHERE fine_id=?", 'i', [$fineId]);
            if ($fine['borrow_id']) db_execute("UPDATE borrowings SET fine_amount=0 WHERE borrow_id=?", 'i', [$fine['borrow_id']]);
            log_activity('Waived fine #' . $fineId);
            set_flash('info', 'Fine waived.');
        }
    }
    redirect('admin/manage_fines.php');
}

$fines = db_select(
    "SELECT f.*, u.fullname, b.title FROM fines f
     JOIN users u ON u.user_id=f.user_id
     LEFT JOIN borrowings br ON br.borrow_id=f.borrow_id
     LEFT JOIN books b ON b.book_id=br.book_id
     ORDER BY FIELD(f.status,'unpaid','paid'), f.created_at DESC"
);
$totalUnpaid = (float) db_scalar("SELECT COALESCE(SUM(amount),0) FROM fines WHERE status='unpaid'");
$totalPaid   = (float) db_scalar("SELECT COALESCE(SUM(amount),0) FROM fines WHERE status='paid'");

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header"><div><h1>Fines</h1><p class="text-soft">Track and clear member fines.</p></div></div>
        <?php render_flash(); ?>

        <div class="stat-grid">
            <div class="stat-card"><div class="stat-ico" style="background:var(--red)"><i class="fa-solid fa-coins"></i></div><div><div class="num"><?= number_format($totalUnpaid) ?></div><div class="lbl"><?= CURRENCY ?> unpaid</div></div></div>
            <div class="stat-card"><div class="stat-ico" style="background:var(--green)"><i class="fa-solid fa-circle-check"></i></div><div><div class="num"><?= number_format($totalPaid) ?></div><div class="lbl"><?= CURRENCY ?> collected</div></div></div>
        </div>

        <div class="panel">
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Member</th><th>Book</th><th>Reason</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($fines as $f): ?>
                        <tr>
                            <td><strong><?= e($f['fullname']) ?></strong></td>
                            <td><?= e($f['title'] ?? '—') ?></td>
                            <td class="text-mute" style="font-size:.85rem"><?= e($f['reason']) ?></td>
                            <td><strong><?= money($f['amount']) ?></strong></td>
                            <td><span class="badge <?= $f['status']==='unpaid'?'badge-red':'badge-green' ?>"><?= e(ucfirst($f['status'])) ?></span></td>
                            <td>
                                <?php if ($f['status']==='unpaid'): ?>
                                    <div class="table-actions">
                                        <form method="post" style="margin:0"><?= csrf_field() ?>
                                            <input type="hidden" name="action" value="pay"><input type="hidden" name="fine_id" value="<?= $f['fine_id'] ?>">
                                            <button class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Paid</button>
                                        </form>
                                        <form method="post" style="margin:0"><?= csrf_field() ?>
                                            <input type="hidden" name="action" value="waive"><input type="hidden" name="fine_id" value="<?= $f['fine_id'] ?>">
                                            <button class="btn btn-ghost btn-sm" data-confirm="Waive this fine?">Waive</button>
                                        </form>
                                    </div>
                                <?php else: ?><span class="text-mute" style="font-size:.8rem">Cleared</span><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$fines): ?><tr><td colspan="6"><div class="empty-state" style="padding:30px"><i class="fa-solid fa-circle-check"></i><h3>No fines</h3><p>Everyone's account is clear.</p></div></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
