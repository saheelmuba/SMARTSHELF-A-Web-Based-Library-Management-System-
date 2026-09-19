<?php
/**
 * About-Us.php — Public about page
 * -------------------------------------------------------------
 * Static informational page describing the library, its mission,
 * values and team. Pulls a couple of live numbers for credibility.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About Us';

$totalBooks   = (int) db_scalar("SELECT COUNT(*) FROM books");
$totalMembers = (int) db_scalar("SELECT COUNT(*) FROM users WHERE role='member'");
$totalCats    = (int) db_scalar("SELECT COUNT(*) FROM categories");

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>About <?= e(SITE_NAME) ?></h1>
        <p>We're on a mission to make every library smarter, friendlier and fully digital.</p>
        <div class="breadcrumb"><a href="index.php">Home</a> / <span>About</span></div>
    </div>
</section>

<section class="section">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow" style="display:inline-block;color:var(--brand);background:var(--brand-soft);padding:6px 14px;border-radius:999px;font-size:.78rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:16px">Our story</span>
            <h2 style="font-size:2.2rem;margin-bottom:18px">Built by readers, for readers</h2>
            <p class="text-soft" style="margin-bottom:16px">
                <?= e(SITE_NAME) ?> began with a simple frustration: finding a book in most
                libraries still meant searching dusty registers and waiting in line. We knew
                technology could do better.
            </p>
            <p class="text-soft" style="margin-bottom:16px">
                Today our platform powers a complete library experience — instant search,
                QR-based checkout, automatic fine tracking, smart reservations and a growing
                digital library of eBooks. Librarians spend less time on paperwork and more
                time helping people discover their next great read.
            </p>
            <div class="hero-stats">
                <div class="stat"><strong data-count="<?= $totalBooks ?>">0</strong><span>Titles</span></div>
                <div class="stat"><strong data-count="<?= $totalMembers ?>">0</strong><span>Members</span></div>
                <div class="stat"><strong data-count="<?= $totalCats ?>">0</strong><span>Categories</span></div>
            </div>
        </div>
        <div class="hero-card reveal" style="background:var(--brand-grad);color:#fff;border:none">
            <h3 style="color:#fff"><i class="fa-solid fa-bullseye"></i> Our mission</h3>
            <p style="opacity:.92;margin:12px 0 20px">To give every community free, frictionless access to knowledge through
               technology that just works.</p>
            <h3 style="color:#fff"><i class="fa-solid fa-eye"></i> Our vision</h3>
            <p style="opacity:.92;margin-top:12px">A world where borrowing a book is as easy as a single tap, and no reader
               is ever turned away.</p>
        </div>
    </div>
</section>

<!-- Values -->
<section class="section" style="background:var(--surface)">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">What we value</span>
            <h2>The principles behind every feature</h2>
        </div>
        <div class="grid grid-4">
            <?php
            $values = [
                ['fa-universal-access', 'Accessibility', 'Usable by everyone, on any device, with a clean and inclusive interface.'],
                ['fa-shield-halved', 'Security', 'Hashed passwords, prepared statements and protected sessions by default.'],
                ['fa-gauge-high', 'Performance', 'Fast pages and instant search, even with thousands of records.'],
                ['fa-heart', 'Community', 'Reviews, recommendations and reading history bring readers together.'],
            ];
            foreach ($values as $v): ?>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fa-solid <?= $v[0] ?>"></i></div>
                    <h3><?= $v[1] ?></h3>
                    <p><?= $v[2] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Team -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Meet the team</span>
            <h2>The people keeping the shelves smart</h2>
        </div>
        <div class="grid grid-4">
            <?php
            $team = [
                ['Nimali Perera', 'Head Librarian'],
                ['Amaya Wijesinghe', 'Digital Services Lead'],
                ['Kasun Silva', 'Community Manager'],
                ['System Admin', 'Platform Engineer'],
            ];
            foreach ($team as $t): ?>
                <div class="feature-card reveal text-center">
                    <div class="avatar-sm" style="width:80px;height:80px;font-size:1.6rem;margin:0 auto 16px;background:<?= color_from_string($t[0]) ?>">
                        <?= e(initials($t[0])) ?>
                    </div>
                    <h3><?= $t[0] ?></h3>
                    <p class="text-mute"><?= $t[1] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
