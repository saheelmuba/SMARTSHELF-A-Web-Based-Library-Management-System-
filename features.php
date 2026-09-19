<?php
/**
 * features.php — Public features showcase
 * -------------------------------------------------------------
 * Lists all system features (read from the `features` table, which
 * admins manage via the CRUD pages). Demonstrates dynamic content
 * driven entirely by the database.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Features';

// Pull every feature managed in the admin panel.
$features = db_select("SELECT * FROM features ORDER BY feature_id");

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Powerful features, beautifully simple</h1>
        <p>Every tool your library, librarians and members need — thoughtfully designed and ready to use.</p>
        <div class="breadcrumb"><a href="index.php">Home</a> / <span>Features</span></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-3">
            <?php foreach ($features as $f): ?>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fa-solid <?= e($f['icon']) ?>"></i></div>
                    <h3><?= e($f['feature_name']) ?></h3>
                    <p><?= e($f['description']) ?></p>
                    <?php if (!empty($f['facilities'])): ?>
                        <div class="facilities">
                            <?php foreach (explode(',', $f['facilities']) as $fac): ?>
                                <span class="badge badge-brand"><i class="fa-solid fa-check"></i> <?= e(trim($fac)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Comparison highlight -->
<section class="section" style="background:var(--surface)">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Old vs. new</span>
            <h2>SmartShelf vs. traditional systems</h2>
        </div>
        <div class="table-wrap" style="max-width:820px;margin:0 auto">
            <table class="data">
                <thead>
                    <tr><th>Capability</th><th>Traditional library</th><th><?= e(SITE_NAME) ?></th></tr>
                </thead>
                <tbody>
                    <?php
                    $rows = [
                        ['Book search', 'Manual card catalog', 'Instant live search & filters'],
                        ['Inventory', 'Counted by hand', 'Real-time automatic updates'],
                        ['Fines', 'Calculated manually', 'Automated daily calculation'],
                        ['Reminders', 'None / phone calls', 'Auto email & SMS reminders'],
                        ['Reservations', 'Paper waiting list', 'Smart digital queue'],
                        ['eBooks', 'Not available', 'Built-in digital library'],
                        ['Analytics', 'Spreadsheets', 'Live dashboard insights'],
                    ];
                    foreach ($rows as $r): ?>
                        <tr>
                            <td><strong><?= $r[0] ?></strong></td>
                            <td class="text-mute"><i class="fa-solid fa-xmark" style="color:var(--red)"></i> <?= $r[1] ?></td>
                            <td><i class="fa-solid fa-check" style="color:var(--green)"></i> <?= $r[2] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="cta-band reveal">
            <h2>Experience it yourself</h2>
            <p>Create a free account and explore the full member dashboard in under a minute.</p>
            <a href="register.php" class="btn btn-white btn-lg">Join SmartShelf free</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
