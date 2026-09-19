<?php
/**
 * admin/reports.php — Reports & analytics
 * -------------------------------------------------------------
 * Visual analytics: loans over the last 6 months, top members,
 * top books, category mix and inventory health. All powered by
 * live aggregate queries and pure-CSS charts.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Reports & Analytics';

recalculate_fines();

/* ---- Loans per month (last 6 months) ---- */
$months = [];
$maxMonth = 1;
for ($i = 5; $i >= 0; $i--) {
    $start = date('Y-m-01', strtotime("-$i months"));
    $end   = date('Y-m-t', strtotime("-$i months"));
    $count = (int) db_scalar("SELECT COUNT(*) FROM borrowings WHERE borrow_date BETWEEN ? AND ?", 'ss', [$start, $end]);
    $months[] = ['label' => date('M', strtotime($start)), 'count' => $count];
    $maxMonth = max($maxMonth, $count);
}

/* ---- Top members by loans ---- */
$topMembers = db_select(
    "SELECT u.fullname, COUNT(br.borrow_id) AS loans
     FROM users u JOIN borrowings br ON br.user_id=u.user_id
     GROUP BY u.user_id ORDER BY loans DESC LIMIT 5"
);

/* ---- Top books ---- */
$topBooks = db_select("SELECT title, borrow_count, views, rating FROM books ORDER BY borrow_count DESC LIMIT 5");

/* ---- Category mix ---- */
$catMix = db_select(
    "SELECT c.name, COUNT(b.book_id) AS total FROM categories c
     LEFT JOIN books b ON b.category_id=c.category_id GROUP BY c.category_id ORDER BY total DESC"
);
$catTotal = array_sum(array_column($catMix, 'total')) ?: 1;

/* ---- Inventory health ---- */
$totalCopies = (int) db_scalar("SELECT COALESCE(SUM(total_copies),0) FROM books");
$availCopies = (int) db_scalar("SELECT COALESCE(SUM(available_copies),0) FROM books");
$onLoan = $totalCopies - $availCopies;
$utilisation = $totalCopies ? round($onLoan / $totalCopies * 100) : 0;

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header"><div><h1>Reports &amp; Analytics</h1><p class="text-soft">Insights into your library's performance.</p></div>
            <button class="btn btn-ghost" onclick="window.print()"><i class="fa-solid fa-print"></i> Print report</button></div>

        <div class="grid" style="grid-template-columns:1.5fr 1fr;gap:24px;align-items:start">
            <!-- Monthly loans -->
            <div class="panel">
                <div class="panel-head"><h2><i class="fa-solid fa-chart-column"></i> Loans over last 6 months</h2></div>
                <div class="panel-body">
                    <div class="bar-chart">
                        <?php foreach ($months as $m): ?>
                            <div class="bar-col"><div class="bar" style="height:<?= max(4, $m['count']/$maxMonth*100) ?>%" data-val="<?= $m['count'] ?>"></div><div class="bar-label"><?= $m['label'] ?></div></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Inventory utilisation donut -->
            <div class="panel">
                <div class="panel-head"><h2><i class="fa-solid fa-gauge"></i> Inventory utilisation</h2></div>
                <div class="panel-body text-center">
                    <div class="ring" style="margin:0 auto;background:conic-gradient(var(--brand) <?= $utilisation*3.6 ?>deg, var(--surface-2) 0)">
                        <span class="ring-val"><?= $utilisation ?>%</span>
                    </div>
                    <p class="text-soft mt-2"><?= $onLoan ?> of <?= $totalCopies ?> copies on loan</p>
                    <div class="flex gap-2 between mt-2">
                        <div><div class="num" style="font-size:1.3rem;font-weight:800;color:var(--green)"><?= $availCopies ?></div><div class="text-mute" style="font-size:.8rem">Available</div></div>
                        <div><div class="num" style="font-size:1.3rem;font-weight:800;color:var(--brand)"><?= $onLoan ?></div><div class="text-mute" style="font-size:.8rem">On loan</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-3" style="align-items:start">
            <!-- Top members -->
            <div class="panel">
                <div class="panel-head"><h2><i class="fa-solid fa-trophy"></i> Top readers</h2></div>
                <div class="panel-body">
                    <?php foreach ($topMembers as $i => $m): ?>
                        <div class="float-book" style="border-color:var(--border)">
                            <div class="float-cover" style="background:var(--brand-grad);width:34px;height:34px;border-radius:9px;font-weight:800">#<?= $i+1 ?></div>
                            <div class="meta"><strong style="font-size:.9rem"><?= e($m['fullname']) ?></strong></div>
                            <span class="badge badge-brand" style="margin-left:auto"><?= (int)$m['loans'] ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$topMembers): ?><p class="text-mute">No data yet.</p><?php endif; ?>
                </div>
            </div>

            <!-- Top books -->
            <div class="panel">
                <div class="panel-head"><h2><i class="fa-solid fa-fire"></i> Top books</h2></div>
                <div class="panel-body">
                    <?php foreach ($topBooks as $b): ?>
                        <div class="float-book" style="border-color:var(--border)">
                            <div class="meta"><strong style="font-size:.9rem"><?= e($b['title']) ?></strong><span><i class="fa-solid fa-eye"></i> <?= (int)$b['views'] ?> · <i class="fa-solid fa-star" style="color:var(--amber)"></i> <?= number_format($b['rating'],1) ?></span></div>
                            <span class="badge badge-green" style="margin-left:auto"><?= (int)$b['borrow_count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Category mix -->
            <div class="panel">
                <div class="panel-head"><h2><i class="fa-solid fa-tags"></i> Category mix</h2></div>
                <div class="panel-body">
                    <?php foreach ($catMix as $c): $pct = round($c['total']/$catTotal*100); ?>
                        <div class="mb-2">
                            <div class="flex between" style="font-size:.85rem;margin-bottom:5px"><span><?= e($c['name']) ?></span><strong><?= $pct ?>%</strong></div>
                            <div class="pw-meter" style="height:7px"><span style="width:<?= $pct ?>%;background:var(--brand-grad)"></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
