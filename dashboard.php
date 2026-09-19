<?php
/**
 * dashboard.php — Member dashboard
 * -------------------------------------------------------------
 * The member's home base. Handles the core library actions
 * (borrow, return, reserve, cancel reservation) as POST requests,
 * then renders personal stats, current loans, reservations, AI
 * recommendations and reading history.
 *
 * Staff (admin/librarian) are redirected to the admin dashboard.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

// Staff use the admin dashboard instead.
if (is_staff()) {
    redirect('admin/admin_dashboard.php');
}

$userId = $_SESSION['user_id'];

/* ===========================================================
 *  HANDLE ACTIONS (borrow / return / reserve / cancel)
 * =========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $bookId = (int) ($_POST['book_id'] ?? 0);
    $book   = db_select_one("SELECT * FROM books WHERE book_id = ?", 'i', [$bookId]);

    if (!$book) {
        set_flash('error', 'That book could not be found.');
        redirect('dashboard.php');
    }

    switch ($action) {

        /* ---- Borrow a book ---- */
        case 'borrow':
            $outstanding = user_outstanding_fines($userId);
            $activeCount = user_active_loans_count($userId);
            $already = db_select_one(
                "SELECT borrow_id FROM borrowings WHERE user_id=? AND book_id=? AND return_date IS NULL",
                'ii', [$userId, $bookId]
            );

            if ($book['available_copies'] < 1) {
                set_flash('error', 'Sorry, no copies are available. Try reserving it instead.');
            } elseif ($already) {
                set_flash('warning', 'You already have this book on loan.');
            } elseif ($activeCount >= MAX_BOOKS_PER_MEMBER) {
                set_flash('error', 'You have reached the borrowing limit of ' . MAX_BOOKS_PER_MEMBER . ' books.');
            } elseif ($outstanding > 0) {
                set_flash('error', 'Please settle your outstanding fines (' . money($outstanding) . ') before borrowing.');
            } else {
                // Atomically claim a copy first. The WHERE guard makes the
                // check-and-decrement a single operation, so two members can
                // never both grab the last copy (prevents overselling).
                $claimed = db_execute(
                    "UPDATE books SET available_copies = available_copies - 1, borrow_count = borrow_count + 1
                     WHERE book_id = ? AND available_copies > 0",
                    'i', [$bookId]
                );
                if (!$claimed) {
                    set_flash('error', 'Sorry, the last copy was just taken. Try reserving it instead.');
                } else {
                    $due = date('Y-m-d', strtotime('+' . LOAN_PERIOD_DAYS . ' days'));
                    db_execute(
                        "INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, status)
                         VALUES (?, ?, CURDATE(), ?, 'borrowed')",
                        'iis', [$userId, $bookId, $due]
                    );
                    notify($userId, 'Book borrowed', '"' . $book['title'] . '" is due on ' . fdate($due) . '.', 'due');
                    log_activity('Borrowed "' . $book['title'] . '"');
                    set_flash('success', 'You borrowed "' . $book['title'] . '". Due ' . fdate($due) . '.');
                }
            }
            break;

        /* ---- Return a borrowed book ---- */
        case 'return':
            $loan = db_select_one(
                "SELECT * FROM borrowings WHERE borrow_id = ? AND user_id = ? AND return_date IS NULL",
                'ii', [(int)($_POST['borrow_id'] ?? 0), $userId]
            );
            if ($loan) {
                // Calculate any late fine on return.
                $daysLate = (strtotime(date('Y-m-d')) - strtotime($loan['due_date'])) / 86400;
                $fine = $daysLate > 0 ? round($daysLate * FINE_PER_DAY, 2) : 0;
                db_execute(
                    "UPDATE borrowings SET return_date = CURDATE(), status='returned', fine_amount = ? WHERE borrow_id = ?",
                    'di', [$fine, $loan['borrow_id']]
                );
                db_execute("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?", 'i', [$loan['book_id']]);

                if ($fine > 0) {
                    db_execute(
                        "INSERT INTO fines (user_id, borrow_id, amount, reason, status) VALUES (?,?,?,?, 'unpaid')",
                        'iids', [$userId, $loan['borrow_id'], $fine, 'Returned ' . (int)$daysLate . ' day(s) late']
                    );
                    set_flash('warning', 'Book returned with a late fine of ' . money($fine) . '.');
                } else {
                    set_flash('success', 'Book returned on time. Thank you!');
                }
                log_activity('Returned "' . $book['title'] . '"');

                // Promote the next reservation in the queue, if any.
                $nextRes = db_select_one(
                    "SELECT * FROM reservations WHERE book_id = ? AND status='pending' ORDER BY reserved_at ASC LIMIT 1",
                    'i', [$loan['book_id']]
                );
                if ($nextRes) {
                    db_execute("UPDATE reservations SET status='ready' WHERE reservation_id = ?", 'i', [$nextRes['reservation_id']]);
                    notify($nextRes['user_id'], 'Reservation ready', 'Your reserved book "' . $book['title'] . '" is now available for pickup.', 'reservation');
                }
            }
            break;

        /* ---- Reserve a book (join the queue) ---- */
        case 'reserve':
            $dup = db_select_one(
                "SELECT reservation_id FROM reservations WHERE user_id=? AND book_id=? AND status IN ('pending','ready')",
                'ii', [$userId, $bookId]
            );
            if ($dup) {
                set_flash('warning', 'You already have a reservation for this book.');
            } else {
                $pos = (int) db_scalar("SELECT COUNT(*) FROM reservations WHERE book_id=? AND status='pending'", 'i', [$bookId]) + 1;
                db_execute(
                    "INSERT INTO reservations (user_id, book_id, status, queue_position) VALUES (?, ?, 'pending', ?)",
                    'iii', [$userId, $bookId, $pos]
                );
                notify($userId, 'Reservation placed', 'You are #' . $pos . ' in the queue for "' . $book['title'] . '".', 'reservation');
                log_activity('Reserved "' . $book['title'] . '"');
                set_flash('success', 'Reserved! You are #' . $pos . ' in the queue.');
            }
            break;

        /* ---- Cancel a reservation ---- */
        case 'cancel_reservation':
            $resId = (int) ($_POST['reservation_id'] ?? 0);
            db_execute("UPDATE reservations SET status='cancelled' WHERE reservation_id = ? AND user_id = ?", 'ii', [$resId, $userId]);
            set_flash('info', 'Reservation cancelled.');
            break;
    }
    redirect('dashboard.php');
}

/* ===========================================================
 *  DATA FOR THE DASHBOARD VIEW
 * =========================================================== */
recalculate_fines(); // keep fines current

$activeLoans = db_select(
    "SELECT br.*, b.title, b.author FROM borrowings br
     JOIN books b ON b.book_id = br.book_id
     WHERE br.user_id = ? AND br.return_date IS NULL
     ORDER BY br.due_date ASC",
    'i', [$userId]
);

$reservations = db_select(
    "SELECT r.*, b.title, b.author, b.available_copies FROM reservations r
     JOIN books b ON b.book_id = r.book_id
     WHERE r.user_id = ? AND r.status IN ('pending','ready')
     ORDER BY r.reserved_at DESC",
    'i', [$userId]
);

$history = db_select(
    "SELECT br.*, b.title, b.author FROM borrowings br
     JOIN books b ON b.book_id = br.book_id
     WHERE br.user_id = ? AND br.return_date IS NOT NULL
     ORDER BY br.return_date DESC LIMIT 8",
    'i', [$userId]
);

$recommended = recommend_books($userId, 4);

// Stat values.
$statBorrowed = (int) db_scalar("SELECT COUNT(*) FROM borrowings WHERE user_id = ?", 'i', [$userId]);
$statActive   = count($activeLoans);
$statReserved = count($reservations);
$statFines    = user_outstanding_fines($userId);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section-sm">
    <div class="container">
        <?php render_flash(); ?>

        <div class="dash-header">
            <div>
                <h1>Hi, <?= e(explode(' ', $_SESSION['fullname'])[0]) ?> 👋</h1>
                <p class="text-soft">Here's what's happening with your library account.</p>
            </div>
            <a href="catalog.php" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Find a book</a>
        </div>

        <!-- Stat cards -->
        <div class="stat-grid">
            <div class="stat-card"><div class="stat-ico" style="background:var(--brand-grad)"><i class="fa-solid fa-book"></i></div><div><div class="num"><?= $statBorrowed ?></div><div class="lbl">Total borrowed</div></div></div>
            <div class="stat-card"><div class="stat-ico" style="background:#3b82f6"><i class="fa-solid fa-book-bookmark"></i></div><div><div class="num"><?= $statActive ?></div><div class="lbl">Active loans</div></div></div>
            <div class="stat-card"><div class="stat-ico" style="background:#f59e0b"><i class="fa-solid fa-clock"></i></div><div><div class="num"><?= $statReserved ?></div><div class="lbl">Reservations</div></div></div>
            <div class="stat-card"><div class="stat-ico" style="background:<?= $statFines > 0 ? 'var(--red)' : 'var(--green)' ?>"><i class="fa-solid fa-coins"></i></div><div><div class="num"><?= number_format($statFines) ?></div><div class="lbl"><?= CURRENCY ?> in fines</div></div></div>
        </div>

        <?php if ($statFines > 0): ?>
            <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation"></i>
                <span>You have <strong><?= money($statFines) ?></strong> in outstanding fines. Please settle them at the counter to keep borrowing.</span>
            </div>
        <?php endif; ?>

        <div class="grid" style="grid-template-columns:1.6fr 1fr;gap:24px;align-items:start">

            <!-- LEFT: current loans + history -->
            <div>
                <!-- Current loans -->
                <div class="panel">
                    <div class="panel-head"><h2><i class="fa-solid fa-book-bookmark"></i> Current loans</h2><span class="badge badge-blue"><?= $statActive ?> active</span></div>
                    <div class="panel-body">
                        <?php if ($activeLoans): ?>
                            <div class="table-wrap">
                                <table class="data">
                                    <thead><tr><th>Book</th><th>Due date</th><th>Status</th><th></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($activeLoans as $loan):
                                        $overdue = strtotime($loan['due_date']) < strtotime(date('Y-m-d'));
                                        $daysLeft = ceil((strtotime($loan['due_date']) - time()) / 86400);
                                    ?>
                                        <tr>
                                            <td><strong><?= e($loan['title']) ?></strong><br><span class="text-mute" style="font-size:.82rem"><?= e($loan['author']) ?></span></td>
                                            <td><?= fdate($loan['due_date']) ?><br>
                                                <span class="text-mute" style="font-size:.8rem"><?= $overdue ? abs($daysLeft) . ' days overdue' : $daysLeft . ' days left' ?></span></td>
                                            <td><?= $overdue ? '<span class="badge badge-red">Overdue</span>' : '<span class="badge badge-green">On time</span>' ?></td>
                                            <td>
                                                <form method="post" style="margin:0">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="action" value="return">
                                                    <input type="hidden" name="book_id" value="<?= $loan['book_id'] ?>">
                                                    <input type="hidden" name="borrow_id" value="<?= $loan['borrow_id'] ?>">
                                                    <button class="btn btn-ghost btn-sm" data-confirm="Return this book now?"><i class="fa-solid fa-rotate-left"></i> Return</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state" style="padding:36px"><i class="fa-solid fa-book-open"></i><h3>No active loans</h3><p>Browse the catalog and borrow your next read.</p></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reading history -->
                <div class="panel">
                    <div class="panel-head"><h2><i class="fa-solid fa-clock-rotate-left"></i> Reading history</h2></div>
                    <div class="panel-body">
                        <?php if ($history): ?>
                            <div class="timeline">
                                <?php foreach ($history as $h): ?>
                                    <div class="timeline-item">
                                        <div class="t-text"><?= e($h['title']) ?> <span class="text-mute">— <?= e($h['author']) ?></span></div>
                                        <div class="t-time">Returned <?= fdate($h['return_date']) ?><?= $h['fine_amount'] > 0 ? ' · fine ' . money($h['fine_amount']) : '' ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-mute">Your returned books will appear here.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- RIGHT: reservations + recommendations -->
            <div>
                <!-- Reservations -->
                <div class="panel">
                    <div class="panel-head"><h2><i class="fa-solid fa-clock"></i> My reservations</h2></div>
                    <div class="panel-body">
                        <?php if ($reservations): ?>
                            <?php foreach ($reservations as $r): ?>
                                <div class="float-book" style="border-color:var(--border)">
                                    <div class="float-cover" style="background:<?= color_from_string($r['title']) ?>;width:42px;height:42px;border-radius:11px"><i class="fa-solid fa-book"></i></div>
                                    <div class="meta">
                                        <strong style="font-size:.9rem"><?= e($r['title']) ?></strong>
                                        <span>
                                            <?php if ($r['status'] === 'ready'): ?>
                                                <span class="badge badge-green">Ready for pickup</span>
                                            <?php else: ?>
                                                Queue position #<?= (int)$r['queue_position'] ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <form method="post" style="margin:0 0 0 auto">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="cancel_reservation">
                                        <input type="hidden" name="book_id" value="<?= $r['book_id'] ?>">
                                        <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                                        <button class="icon-btn danger" title="Cancel" data-confirm="Cancel this reservation?"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-mute">No active reservations.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- AI recommendations -->
                <div class="panel">
                    <div class="panel-head"><h2><i class="fa-solid fa-wand-magic-sparkles"></i> Recommended for you</h2></div>
                    <div class="panel-body">
                        <?php foreach ($recommended as $rec): ?>
                            <a href="book.php?id=<?= $rec['book_id'] ?>" class="float-book" style="border-color:var(--border)">
                                <div class="float-cover" style="background:<?= color_from_string($rec['title']) ?>;width:42px;height:56px"><i class="fa-solid fa-book"></i></div>
                                <div class="meta">
                                    <strong style="font-size:.9rem"><?= e($rec['title']) ?></strong>
                                    <span><?= e($rec['category_name'] ?? 'General') ?> · <i class="fa-solid fa-star" style="color:var(--amber)"></i> <?= number_format($rec['rating'], 1) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        <p class="text-mute text-center" style="font-size:.8rem;margin-top:8px">Based on your reading history</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
