<?php
/**
 * admin/admin_dashboard.php — Admin & librarian dashboard
 * -------------------------------------------------------------
 * Central control panel showing live statistics, a weekly
 * borrowing chart (pure CSS), category breakdown, most popular
 * books, overdue alerts and a recent activity feed.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Admin Dashboard';

// Keep fines up to date before rendering numbers.
recalculate_fines();

/* ---- Headline statistics ---- */
$stats = [
    'books'    => (int) db_scalar("SELECT COUNT(*) FROM books"),
    'copies'   => (int) db_scalar("SELECT COALESCE(SUM(total_copies),0) FROM books"),
    'members'  => (int) db_scalar("SELECT COUNT(*) FROM users WHERE role='member'"),
    'loans'    => (int) db_scalar("SELECT COUNT(*) FROM borrowings WHERE return_date IS NULL"),
    'overdue'  => (int) db_scalar("SELECT COUNT(*) FROM borrowings WHERE status='overdue' AND return_date IS NULL"),
    'reserved' => (int) db_scalar("SELECT COUNT(*) FROM reservations WHERE status IN ('pending','ready')"),
    'fines'    => (float) db_scalar("SELECT COALESCE(SUM(amount),0) FROM fines WHERE status='unpaid'"),
    'unread'   => (int) db_scalar("SELECT COUNT(*) FROM contact_messages WHERE is_read=0"),
];

/* ---- Weekly borrowing trend (last 7 days) for the bar chart ---- */
$weekData = [];
$maxWeek = 1;
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $count = (int) db_scalar("SELECT COUNT(*) FROM borrowings WHERE borrow_date = ?", 's', [$day]);
    $weekData[] = ['label' => date('D', strtotime($day)), 'count' => $count];
    $maxWeek = max($maxWeek, $count);
}

/* ---- Category distribution ---- */
$catData = db_select(
    "SELECT c.name, COUNT(b.book_id) AS total
     FROM categories c LEFT JOIN books b ON b.category_id = c.category_id
     GROUP BY c.category_id ORDER BY total DESC LIMIT 6"
);
$catMax = 1;
foreach ($catData as $c) { $catMax = max($catMax, (int)$c['total']); }

/* ---- Most popular books ---- */
$popular = db_select(
    "SELECT title, author, borrow_count, rating FROM books ORDER BY borrow_count DESC LIMIT 5"
);

/* ---- Overdue loans needing attention ---- */
$overdueList = db_select(
    "SELECT br.*, b.title, u.fullname FROM borrowings br
     JOIN books b ON b.book_id = br.book_id
     JOIN users u ON u.user_id = br.user_id
     WHERE br.status='overdue' AND br.return_date IS NULL
     ORDER BY br.due_date ASC LIMIT 5"
);

/* ---- Recent activity feed ---- */
$activity = db_select("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 8");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="dash-main">
        <button class="btn btn-ghost btn-sm hidden" id="sidebarToggle" style="display:none"><i class="fa-solid fa-bars"></i> Menu</button>

        <div class="dash-header">
            <div>
                <h1>Welcome back, <?= e(explode(' ', $_SESSION['fullname'])[0]) ?> 👋</h1>
                <p class="text-soft">Here's your library at a glance — <?= date('l, d M Y') ?>.</p>
            </div>
            <a href="<?= BASE_URL ?>admin/add_book.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add book</a>
        </div>

        <?php render_flash(); ?>

        <!-- Stat cards -->
        <div class="stat-grid">
            <div class="stat-card"><div class="stat-ico" style="background:var(--brand-grad)"><i class="fa-solid fa-book"></i></div><div><div class="num"><?= $stats['books'] ?></div><div class="lbl">Titles (<?= $stats['copies'] ?> copies)</div></div></div>
            <div class="stat-card"><div class="stat-ico" style="background:#3b82f6"><i class="fa-solid fa-users"></i></div><div><div class="num"><?= $stats['members'] ?></div><div class="lbl">Members</div></div></div>
            <div class="stat-card"><div class="stat-ico" style="background:#10b981"><i class="fa-solid fa-right-left"></i></div><div><div class="num"><?= $stats['loans'] ?></div><div class="lbl">Active loans</div></div></div>
            <div class="stat-card"><div class="stat-ico" style="background:#ef4444"><i class="fa-solid fa-triangle-exclamation"></i></div><div><div class="num"><?= $stats['overdue'] ?></div><div class="lbl">Overdue</div></div></div>
            <div class="stat-card"><div class="stat-ico" style="background:#f59e0b"><i class="fa-solid fa-clock"></i></div><div><div class="num"><?= $stats['reserved'] ?></div><div class="lbl">Reservations</div></div></div>
            <div class="stat-card"><div class="stat-ico" style="background:#8b5cf6"><i class="fa-solid fa-coins"></i></div><div><div class="num"><?= number_format($stats['fines']) ?></div><div class="lbl"><?= CURRENCY ?> unpaid fines</div></div></div>
        </div>

        <div class="grid" style="grid-template-columns:1.5fr 1fr;gap:24px;align-items:start">
            <!-- Weekly chart -->
            <div class="panel">
                <div class="panel-head"><h2><i class="fa-solid fa-chart-column"></i> Borrowing activity (last 7 days)</h2></div>
                <div class="panel-body">
                    <div class="bar-chart">
                        <?php foreach ($weekData as $d): ?>
                            <div class="bar-col">
                                <div class="bar" style="height:<?= max(4, ($d['count'] / $maxWeek) * 100) ?>%" data-val="<?= $d['count'] ?> loans"></div>
                                <div class="bar-label"><?= $d['label'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Category breakdown -->
            <div class="panel">
                <div class="panel-head"><h2><i class="fa-solid fa-tags"></i> Books by category</h2></div>
                <div class="panel-body">
                    <?php foreach ($catData as $c): ?>
                        <div class="mb-2">
                            <div class="flex between" style="font-size:.88rem;margin-bottom:5px"><span><?= e($c['name']) ?></span><strong><?= (int)$c['total'] ?></strong></div>
                            <div class="pw-meter" style="height:8px"><span style="width:<?= ($c['total'] / $catMax) * 100 ?>%;background:var(--brand-grad)"></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="grid" style="grid-template-columns:1fr 1fr;gap:24px;align-items:start">
            <!-- Overdue alerts -->
            <div class="panel">
                <div class="panel-head"><h2><i class="fa-solid fa-bell"></i> Overdue alerts</h2><a href="<?= BASE_URL ?>admin/manage_borrowings.php" class="btn btn-ghost btn-sm">View all</a></div>
                <div class="panel-body">
                    <?php if ($overdueList): ?>
                        <?php foreach ($overdueList as $o): ?>
                            <div class="float-book" style="border-color:var(--border)">
                                <div class="float-cover" style="background:var(--red-soft);color:var(--red);width:42px;height:42px;border-radius:11px"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                <div class="meta"><strong style="font-size:.9rem"><?= e($o['title']) ?></strong><span><?= e($o['fullname']) ?> · due <?= fdate($o['due_date']) ?></span></div>
                                <span class="badge badge-red" style="margin-left:auto"><?= money($o['fine_amount']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state" style="padding:30px"><i class="fa-solid fa-circle-check" style="color:var(--green);opacity:1"></i><h3>No overdue books</h3><p>Everything is on schedule.</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Popular books -->
            <div class="panel">
                <div class="panel-head"><h2><i class="fa-solid fa-fire"></i> Most popular</h2></div>
                <div class="panel-body">
                    <?php foreach ($popular as $i => $p): ?>
                        <div class="float-book" style="border-color:var(--border)">
                            <div class="float-cover" style="background:var(--brand-grad);width:36px;height:36px;border-radius:10px;font-weight:800">#<?= $i + 1 ?></div>
                            <div class="meta"><strong style="font-size:.9rem"><?= e($p['title']) ?></strong><span><?= e($p['author']) ?></span></div>
                            <span class="badge badge-brand" style="margin-left:auto"><?= (int)$p['borrow_count'] ?> loans</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Activity feed -->
        <div class="panel">
            <div class="panel-head"><h2><i class="fa-solid fa-clock-rotate-left"></i> Recent activity</h2></div>
            <div class="panel-body">
                <div class="timeline">
                    <?php foreach ($activity as $a): ?>
                        <div class="timeline-item">
                            <div class="t-text"><?= e($a['action']) ?></div>
                            <div class="t-time"><?= time_ago($a['created_at']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
