<?php
/**
 * index.php — Home / landing page
 * -------------------------------------------------------------
 * Public homepage featuring the hero banner, live statistics,
 * featured books pulled from the database, highlighted system
 * features and a call-to-action. Header + footer are shared.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Home';

// --- Live statistics for the hero (real data from the DB) ---
$totalBooks   = (int) db_scalar("SELECT COUNT(*) FROM books");
$totalMembers = (int) db_scalar("SELECT COUNT(*) FROM users WHERE role='member'");
$totalLoans   = (int) db_scalar("SELECT COUNT(*) FROM borrowings");
$totalCopies  = (int) db_scalar("SELECT COALESCE(SUM(total_copies),0) FROM books");

// --- Featured (top rated, available) books for the hero card ---
$heroBooks = db_select(
    "SELECT title, author, rating FROM books WHERE available_copies > 0
     ORDER BY rating DESC, borrow_count DESC LIMIT 3"
);

// --- Featured catalogue (most popular books) ---
$featured = db_select(
    "SELECT b.*, c.name AS category_name
     FROM books b LEFT JOIN categories c ON c.category_id = b.category_id
     ORDER BY b.borrow_count DESC LIMIT 8"
);

// --- Top 6 system features for the features strip ---
$features = db_select("SELECT * FROM features ORDER BY feature_id LIMIT 6");

require_once __DIR__ . '/includes/header.php';
?>

<!-- ============================ HERO ============================ -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="hero-badge"><i class="fa-solid fa-bolt"></i> Smart, modern library management</span>
            <h1>Your library, <span class="grad">reimagined</span> for the digital age.</h1>
            <p class="lead">
                Search thousands of titles in milliseconds, borrow with a QR scan, reserve
                what's out, read eBooks online and never miss a due date again — all in one
                beautifully simple platform.
            </p>
            <div class="hero-cta">
                <a href="catalog.php" class="btn btn-primary btn-lg"><i class="fa-solid fa-magnifying-glass"></i> Browse Catalog</a>
                <a href="register.php" class="btn btn-ghost btn-lg">Get Started Free</a>
            </div>
            <div class="hero-stats">
                <div class="stat"><strong><span data-count="<?= $totalCopies ?>">0</span>+</strong><span>Books in stock</span></div>
                <div class="stat"><strong><span data-count="<?= $totalMembers ?>">0</span>+</strong><span>Happy members</span></div>
                <div class="stat"><strong><span data-count="<?= $totalLoans ?>">0</span>+</strong><span>Books loaned</span></div>
            </div>
        </div>

        <!-- Floating search/preview card -->
        <div class="hero-card reveal">
            <h3>Trending this week</h3>
            <p class="muted">Top rated and available right now</p>
            <?php foreach ($heroBooks as $b): ?>
                <div class="float-book">
                    <div class="float-cover" style="background:<?= color_from_string($b['title']) ?>">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div class="meta">
                        <strong><?= e($b['title']) ?></strong>
                        <span><?= e($b['author']) ?></span>
                    </div>
                    <span class="rating" style="margin-left:auto"><i class="fa-solid fa-star"></i> <?= number_format($b['rating'], 1) ?></span>
                </div>
            <?php endforeach; ?>
            <a href="catalog.php" class="btn btn-outline btn-block mt-2">View full catalog</a>
        </div>
    </div>
</section>

<!-- ====================== FEATURED BOOKS ======================= -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Featured collection</span>
            <h2>Most popular books</h2>
            <p>Hand-picked favourites our members can't stop borrowing.</p>
        </div>

        <div class="book-grid">
            <?php foreach ($featured as $book): ?>
                <article class="book-card reveal">
                    <a href="book.php?id=<?= (int) $book['book_id'] ?>" class="book-cover" style="background:<?= color_from_string($book['title']) ?>">
                        <?php if ($book['available_copies'] > 0): ?>
                            <span class="book-badge badge badge-green">Available</span>
                        <?php else: ?>
                            <span class="book-badge badge badge-red">On loan</span>
                        <?php endif; ?>
                        <span class="cover-title"><?= e($book['title']) ?></span>
                        <span class="cover-author"><?= e($book['author']) ?></span>
                    </a>
                    <div class="book-body">
                        <h3><?= e($book['title']) ?></h3>
                        <p class="author"><?= e($book['category_name'] ?? 'General') ?></p>
                        <div class="book-meta-row">
                            <span class="rating"><i class="fa-solid fa-star"></i> <?= number_format($book['rating'], 1) ?></span>
                            <?php if ($book['ebook_file']): ?>
                                <span class="ebook-tag"><i class="fa-solid fa-tablet-screen-button"></i> eBook</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a href="catalog.php" class="btn btn-primary">Explore all <?= $totalBooks ?> books <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- ========================= FEATURES ========================== -->
<section class="section" style="background:var(--surface)">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Why SmartShelf</span>
            <h2>Everything a modern library needs</h2>
            <p>We rebuilt library management from the ground up to solve the problems
               traditional systems ignore.</p>
        </div>
        <div class="grid grid-3">
            <?php foreach ($features as $f): ?>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i class="fa-solid <?= e($f['icon']) ?>"></i></div>
                    <h3><?= e($f['feature_name']) ?></h3>
                    <p><?= e($f['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a href="features.php" class="btn btn-ghost">See all features <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- ===================== PROBLEM / SOLUTION ==================== -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Problems we solve</span>
            <h2>Goodbye to old library headaches</h2>
        </div>
        <div class="grid grid-3">
            <?php
            $problems = [
                ['fa-magnifying-glass-minus', 'Slow book searching', 'Live search across title, author, ISBN & genre returns results instantly — no more flipping through registers.'],
                ['fa-pen-ruler', 'Manual inventory', 'Copy counts update automatically on every borrow, return and reservation. Always accurate, always live.'],
                ['fa-clock', 'Overdue chaos', 'Automated fine calculation and email/SMS reminders mean fewer late returns and zero manual tracking.'],
                ['fa-users-slash', 'Limited interaction', 'Members reserve books, join queues, write reviews and read eBooks from any device.'],
                ['fa-chart-simple', 'Poor reporting', 'Real-time dashboards show borrowing trends, popular titles and member activity at a glance.'],
                ['fa-cloud', 'No digital services', 'A built-in digital library lets members read and download PDFs and eBooks online.'],
            ];
            foreach ($problems as $p): ?>
                <div class="feature-card reveal">
                    <div class="feature-icon" style="background:var(--surface-2);color:var(--brand)"><i class="fa-solid <?= $p[0] ?>"></i></div>
                    <h3><?= $p[1] ?></h3>
                    <p><?= $p[2] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========================= CTA BAND ========================== -->
<section class="section-sm">
    <div class="container">
        <div class="cta-band reveal">
            <h2>Ready to borrow your first book?</h2>
            <p>Join <?= $totalMembers ?>+ readers already using <?= e(SITE_NAME) ?>. Free membership,
               instant access, no paperwork.</p>
            <div class="hero-cta" style="justify-content:center">
                <a href="register.php" class="btn btn-white btn-lg">Create free account</a>
                <a href="Contact-Us.php" class="btn btn-outline btn-lg" style="color:#fff;border-color:rgba(255,255,255,.6)">Talk to us</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
