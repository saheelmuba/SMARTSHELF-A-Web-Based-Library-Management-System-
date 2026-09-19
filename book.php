<?php
/**
 * book.php — Single book details page
 * -------------------------------------------------------------
 * Shows full details for one book, its reviews, availability and
 * action buttons (borrow / reserve / read eBook / QR code).
 * Increments the view counter and lists "you may also like".
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';

$bookId = (int) ($_GET['id'] ?? 0);

// Fetch the book with its category (prepared statement).
$book = db_select_one(
    "SELECT b.*, c.name AS category_name
     FROM books b LEFT JOIN categories c ON c.category_id = b.category_id
     WHERE b.book_id = ?",
    'i',
    [$bookId]
);

if (!$book) {
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty-state">'
        . '<i class="fa-solid fa-circle-question"></i><h3>Book not found</h3>'
        . '<p>The title you are looking for does not exist.</p>'
        . '<a href="catalog.php" class="btn btn-primary mt-2">Back to catalog</a></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $book['title'];

// Increment view counter (popularity analytics).
db_execute("UPDATE books SET views = views + 1 WHERE book_id = ?", 'i', [$bookId]);

// Reviews for this book.
$reviews = db_select(
    "SELECT r.*, u.fullname FROM reviews r
     JOIN users u ON u.user_id = r.user_id
     WHERE r.book_id = ? ORDER BY r.created_at DESC",
    'i',
    [$bookId]
);

// Similar books in the same category.
$similar = db_select(
    "SELECT * FROM books WHERE category_id = ? AND book_id <> ? LIMIT 4",
    'ii',
    [$book['category_id'], $bookId]
);

// Reservation queue length (for the "join queue" hint).
$queueLen = (int) db_scalar(
    "SELECT COUNT(*) FROM reservations WHERE book_id = ? AND status='pending'",
    'i',
    [$bookId]
);

$qrPayload = 'SMARTSHELF-BOOK-' . $book['book_id'] . '|' . $book['isbn'];

require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="breadcrumb" style="justify-content:flex-start;margin-bottom:24px">
            <a href="index.php">Home</a> / <a href="catalog.php">Catalog</a> / <span><?= e($book['title']) ?></span>
        </div>

        <div class="grid" style="grid-template-columns:320px 1fr;gap:40px;align-items:start">

            <!-- Cover + actions -->
            <div>
                <div class="book-cover" style="background:<?= color_from_string($book['title']) ?>;border-radius:var(--radius);aspect-ratio:3/4;box-shadow:var(--shadow-lg)">
                    <?php if ($book['cover_image'] && file_exists(__DIR__ . '/uploads/covers/' . $book['cover_image'])): ?>
                        <img src="<?= BASE_URL ?>uploads/covers/<?= e($book['cover_image']) ?>" alt="<?= e($book['title']) ?>">
                    <?php else: ?>
                        <span class="cover-title" style="font-size:1.3rem"><?= e($book['title']) ?></span>
                        <span class="cover-author"><?= e($book['author']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="card card-pad mt-2">
                    <div class="flex between items-center mb-2">
                        <span class="text-soft">Availability</span>
                        <?php if ($book['available_copies'] > 0): ?>
                            <span class="badge badge-green"><?= $book['available_copies'] ?> of <?= $book['total_copies'] ?></span>
                        <?php else: ?>
                            <span class="badge badge-red">All on loan</span>
                        <?php endif; ?>
                    </div>

                    <?php if (is_logged_in() && current_role() === 'member'): ?>
                        <?php if ($book['available_copies'] > 0): ?>
                            <form method="post" action="dashboard.php">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="borrow">
                                <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
                                <button class="btn btn-primary btn-block mb-1"><i class="fa-solid fa-book-bookmark"></i> Borrow this book</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="dashboard.php">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reserve">
                                <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
                                <button class="btn btn-warn btn-block mb-1"><i class="fa-solid fa-clock"></i> Reserve (queue: <?= $queueLen ?>)</button>
                            </form>
                        <?php endif; ?>
                    <?php elseif (!is_logged_in()): ?>
                        <a href="login.php" class="btn btn-primary btn-block mb-1"><i class="fa-solid fa-right-to-bracket"></i> Login to borrow</a>
                    <?php endif; ?>

                    <?php if ($book['ebook_file']): ?>
                        <a href="<?= is_logged_in() ? 'read.php?id=' . $book['book_id'] : 'login.php' ?>" class="btn btn-success btn-block mb-1">
                            <i class="fa-solid fa-tablet-screen-button"></i> Read eBook
                        </a>
                    <?php endif; ?>

                    <button class="btn btn-ghost btn-block" onclick="showQR('<?= e($qrPayload) ?>', '<?= e(addslashes($book['title'])) ?>')">
                        <i class="fa-solid fa-qrcode"></i> Show QR code
                    </button>
                </div>
            </div>

            <!-- Details -->
            <div>
                <span class="badge badge-brand mb-2"><i class="fa-solid fa-tag"></i> <?= e($book['category_name'] ?? 'General') ?></span>
                <h1 style="font-size:2.2rem;margin:6px 0"><?= e($book['title']) ?></h1>
                <p class="text-soft" style="font-size:1.1rem">by <strong><?= e($book['author']) ?></strong></p>

                <div class="flex gap-2 wrap mt-2 mb-3">
                    <span class="rating"><i class="fa-solid fa-star"></i> <?= number_format($book['rating'], 1) ?> rating</span>
                    <span class="text-mute"><i class="fa-solid fa-eye"></i> <?= (int)$book['views'] ?> views</span>
                    <span class="text-mute"><i class="fa-solid fa-book-bookmark"></i> <?= (int)$book['borrow_count'] ?> borrows</span>
                </div>

                <p class="text-soft mb-3" style="font-size:1.02rem;line-height:1.7"><?= e($book['description']) ?></p>

                <!-- Meta table -->
                <div class="grid grid-2 mb-3" style="gap:14px">
                    <?php
                    $meta = [
                        ['fa-barcode', 'ISBN', $book['isbn'] ?: 'N/A'],
                        ['fa-building', 'Publisher', $book['publisher'] ?: 'N/A'],
                        ['fa-calendar', 'Published', $book['publish_year'] ?: 'N/A'],
                        ['fa-location-dot', 'Shelf', $book['shelf_location'] ?: 'N/A'],
                    ];
                    foreach ($meta as $m): ?>
                        <div class="flex items-center gap-1 card card-pad" style="padding:14px 16px">
                            <i class="fa-solid <?= $m[0] ?>" style="color:var(--brand);width:22px"></i>
                            <div>
                                <div class="text-mute" style="font-size:.78rem"><?= $m[1] ?></div>
                                <strong><?= e($m[2]) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Reviews -->
                <h2 style="font-size:1.4rem;margin:30px 0 16px"><i class="fa-solid fa-comments"></i> Reader reviews (<?= count($reviews) ?>)</h2>
                <?php if ($reviews): ?>
                    <?php foreach ($reviews as $rv): ?>
                        <div class="card card-pad mb-2">
                            <div class="flex items-center gap-1 mb-1">
                                <span class="avatar-sm" style="background:<?= color_from_string($rv['fullname']) ?>"><?= e(initials($rv['fullname'])) ?></span>
                                <div>
                                    <strong><?= e($rv['fullname']) ?></strong>
                                    <div class="rating" style="font-size:.82rem">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="fa-<?= $i < $rv['rating'] ? 'solid' : 'regular' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span class="text-mute" style="margin-left:auto;font-size:.82rem"><?= time_ago($rv['created_at']) ?></span>
                            </div>
                            <p class="text-soft"><?= e($rv['comment']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-mute">No reviews yet. Be the first to review this book after borrowing it!</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Similar books -->
        <?php if ($similar): ?>
            <div class="section-head" style="margin:60px auto 30px">
                <h2 style="font-size:1.6rem">You may also like</h2>
            </div>
            <div class="book-grid">
                <?php foreach ($similar as $s): ?>
                    <article class="book-card">
                        <a href="book.php?id=<?= $s['book_id'] ?>" class="book-cover" style="background:<?= color_from_string($s['title']) ?>">
                            <span class="cover-title"><?= e($s['title']) ?></span>
                            <span class="cover-author"><?= e($s['author']) ?></span>
                        </a>
                        <div class="book-body">
                            <h3><?= e($s['title']) ?></h3>
                            <div class="book-meta-row">
                                <span class="rating"><i class="fa-solid fa-star"></i> <?= number_format($s['rating'], 1) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
