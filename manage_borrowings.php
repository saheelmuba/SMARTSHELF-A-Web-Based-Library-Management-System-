<?php
/**
 * admin/manage_borrowings.php — Issue & return management
 * -------------------------------------------------------------
 * Staff view of all loans with filters. Librarians can issue a
 * book to any member and mark loans as returned (promoting the
 * next reservation in the queue automatically).
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Borrowings';

recalculate_fines();

/* ---- Actions: issue / mark returned ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'issue') {
        $uid = (int) ($_POST['user_id'] ?? 0);
        $bid = (int) ($_POST['book_id'] ?? 0);
        $book = db_select_one("SELECT * FROM books WHERE book_id=?", 'i', [$bid]);
        $member = db_select_one("SELECT * FROM users WHERE user_id=?", 'i', [$uid]);
        if (!$book || !$member) {
            set_flash('error', 'Select a valid member and book.');
        } elseif ($book['available_copies'] < 1) {
            set_flash('error', 'No copies available for "' . $book['title'] . '".');
        } else {
            // Atomically claim a copy so concurrent issues can't oversell it.
            $claimed = db_execute("UPDATE books SET available_copies=available_copies-1, borrow_count=borrow_count+1 WHERE book_id=? AND available_copies>0", 'i', [$bid]);
            if (!$claimed) {
                set_flash('error', 'No copies available for "' . $book['title'] . '".');
            } else {
                $due = date('Y-m-d', strtotime('+' . LOAN_PERIOD_DAYS . ' days'));
                db_execute("INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, status) VALUES (?,?,CURDATE(),?,'borrowed')", 'iis', [$uid, $bid, $due]);
                notify($uid, 'Book issued', '"' . $book['title'] . '" is due on ' . fdate($due) . '.', 'due');
                log_activity('Issued "' . $book['title'] . '" to ' . $member['fullname']);
                set_flash('success', 'Issued "' . $book['title'] . '" to ' . $member['fullname'] . '.');
            }
        }
    }

    if ($action === 'return') {
        $loanId = (int) ($_POST['borrow_id'] ?? 0);
        $loan = db_select_one("SELECT br.*, b.title FROM borrowings br JOIN books b ON b.book_id=br.book_id WHERE br.borrow_id=? AND br.return_date IS NULL", 'i', [$loanId]);
        if ($loan) {
            $daysLate = (strtotime(date('Y-m-d')) - strtotime($loan['due_date'])) / 86400;
            $fine = $daysLate > 0 ? round($daysLate * FINE_PER_DAY, 2) : 0;
            db_execute("UPDATE borrowings SET return_date=CURDATE(), status='returned', fine_amount=? WHERE borrow_id=?", 'di', [$fine, $loanId]);
            db_execute("UPDATE books SET available_copies=available_copies+1 WHERE book_id=?", 'i', [$loan['book_id']]);
            if ($fine > 0 && !db_select_one("SELECT fine_id FROM fines WHERE borrow_id=?", 'i', [$loanId])) {
                db_execute("INSERT INTO fines (user_id, borrow_id, amount, reason, status) VALUES (?,?,?,?, 'unpaid')", 'iids', [$loan['user_id'], $loanId, $fine, 'Returned late']);
            }
            // Promote next reservation.
            $next = db_select_one("SELECT * FROM reservations WHERE book_id=? AND status='pending' ORDER BY reserved_at ASC LIMIT 1", 'i', [$loan['book_id']]);
            if ($next) {
                db_execute("UPDATE reservations SET status='ready' WHERE reservation_id=?", 'i', [$next['reservation_id']]);
                notify($next['user_id'], 'Reservation ready', '"' . $loan['title'] . '" is now available for pickup.', 'reservation');
            }
            log_activity('Processed return of "' . $loan['title'] . '"');
            set_flash('success', 'Return processed.' . ($fine > 0 ? ' Late fine: ' . money($fine) : ''));
        }
    }
    redirect('admin/manage_borrowings.php');
}

/* ---- Filter ---- */
$filter = $_GET['filter'] ?? 'active';
$where = match ($filter) {
    'active'   => "br.return_date IS NULL AND br.status='borrowed'",
    'overdue'  => "br.status='overdue' AND br.return_date IS NULL",
    'returned' => "br.return_date IS NOT NULL",
    default    => "1=1",
};
$loans = db_select(
    "SELECT br.*, b.title, u.fullname FROM borrowings br
     JOIN books b ON b.book_id=br.book_id
     JOIN users u ON u.user_id=br.user_id
     WHERE $where ORDER BY br.borrow_date DESC"
);

$members = db_select("SELECT user_id, fullname FROM users WHERE role='member' AND status='active' ORDER BY fullname");
$availBooks = db_select("SELECT book_id, title FROM books WHERE available_copies > 0 ORDER BY title");

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header"><div><h1>Borrowings</h1><p class="text-soft">Issue books and process returns.</p></div></div>
        <?php render_flash(); ?>

        <!-- Quick issue -->
        <div class="panel">
            <div class="panel-head"><h2><i class="fa-solid fa-hand-holding-hand"></i> Issue a book</h2></div>
            <div class="panel-body">
                <form method="post" class="flex gap-1 wrap items-center">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="issue">
                    <select name="user_id" required style="flex:1;min-width:180px">
                        <option value="">Select member...</option>
                        <?php foreach ($members as $m): ?><option value="<?= $m['user_id'] ?>"><?= e($m['fullname']) ?></option><?php endforeach; ?>
                    </select>
                    <select name="book_id" required style="flex:1;min-width:180px">
                        <option value="">Select available book...</option>
                        <?php foreach ($availBooks as $b): ?><option value="<?= $b['book_id'] ?>"><?= e($b['title']) ?></option><?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary"><i class="fa-solid fa-check"></i> Issue (<?= LOAN_PERIOD_DAYS ?> days)</button>
                </form>
            </div>
        </div>

        <!-- Filter chips -->
        <div class="chips mb-2">
            <?php foreach (['active'=>'Active','overdue'=>'Overdue','returned'=>'Returned','all'=>'All'] as $k=>$lbl): ?>
                <a href="?filter=<?= $k ?>" class="chip <?= $filter===$k?'active':'' ?>"><?= $lbl ?></a>
            <?php endforeach; ?>
        </div>

        <div class="panel">
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Book</th><th>Member</th><th>Borrowed</th><th>Due</th><th>Status</th><th>Fine</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($loans as $l): ?>
                        <tr>
                            <td><strong><?= e($l['title']) ?></strong></td>
                            <td><?= e($l['fullname']) ?></td>
                            <td><?= fdate($l['borrow_date']) ?></td>
                            <td><?= fdate($l['due_date']) ?></td>
                            <td>
                                <?php if ($l['return_date']): ?><span class="badge badge-gray">Returned</span>
                                <?php elseif ($l['status']==='overdue'): ?><span class="badge badge-red">Overdue</span>
                                <?php else: ?><span class="badge badge-green">On loan</span><?php endif; ?>
                            </td>
                            <td><?= $l['fine_amount'] > 0 ? money($l['fine_amount']) : '—' ?></td>
                            <td>
                                <?php if (!$l['return_date']): ?>
                                    <form method="post" style="margin:0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="return">
                                        <input type="hidden" name="borrow_id" value="<?= $l['borrow_id'] ?>">
                                        <button class="btn btn-ghost btn-sm" data-confirm="Mark as returned?"><i class="fa-solid fa-rotate-left"></i> Return</button>
                                    </form>
                                <?php else: ?><span class="text-mute" style="font-size:.8rem"><?= fdate($l['return_date']) ?></span><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$loans): ?><tr><td colspan="7"><div class="empty-state" style="padding:30px"><i class="fa-solid fa-inbox"></i><h3>Nothing here</h3></div></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
