<?php
/**
 * catalog.php — Public book catalog with smart search & filters
 * -------------------------------------------------------------
 * Browse the entire collection. Supports a server-side search
 * query (title/author/ISBN), category filtering and an
 * availability filter, plus instant client-side live search.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Book Catalog';

// --- Read filters from the query string (sanitised) ---
$q          = clean($_GET['q'] ?? '');
$catFilter  = (int) ($_GET['category'] ?? 0);
$availOnly  = isset($_GET['available']);

// --- Build the catalogue query with prepared statements ---
$sql = "SELECT b.*, c.name AS category_name
        FROM books b LEFT JOIN categories c ON c.category_id = b.category_id
        WHERE 1=1";
$types = '';
$params = [];

if ($q !== '') {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    $like = '%' . $q . '%';
    $types .= 'sss';
    array_push($params, $like, $like, $like);
}
if ($catFilter > 0) {
    $sql .= " AND b.category_id = ?";
    $types .= 'i';
    $params[] = $catFilter;
}
if ($availOnly) {
    $sql .= " AND b.available_copies > 0";
}
$sql .= " ORDER BY b.title ASC";

$books = db_select($sql, $types, $params);
$categories = db_select("SELECT * FROM categories ORDER BY name");

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero" style="padding:42px 0">
    <div class="container">
        <h1>Explore the collection</h1>
        <p>Search <?= count(db_select("SELECT book_id FROM books")) ?> titles across <?= count($categories) ?> categories.</p>
    </div>
</section>

<section class="section" style="padding-top:34px">
    <div class="container">

        <!-- Search + filter form (server-side) -->
        <form method="get" action="catalog.php" class="card card-pad mb-3">
            <div class="search-bar mb-2">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by title, author or ISBN..." id="catalogSearchInput">
            </div>
            <div class="flex wrap between items-center gap-1">
                <div class="flex wrap gap-1 items-center">
                    <select name="category" style="width:auto">
                        <option value="0">All categories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['category_id'] ?>" <?= $catFilter === (int)$c['category_id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="flex items-center gap-1" style="font-weight:600;font-size:.9rem;cursor:pointer">
                        <input type="checkbox" name="available" style="width:auto" <?= $availOnly ? 'checked' : '' ?>>
                        Available only
                    </label>
                </div>
                <div class="flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Apply</button>
                    <a href="catalog.php" class="btn btn-ghost btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <!-- Instant client-side live search across the loaded results -->
        <div class="search-bar mb-3">
            <i class="fa-solid fa-bolt"></i>
            <input type="search" id="liveSearch" placeholder="Instantly filter the results below...">
        </div>

        <p class="text-mute mb-2"><i class="fa-solid fa-book"></i> <?= count($books) ?> result<?= count($books) === 1 ? '' : 's' ?> found</p>

        <?php if ($books): ?>
            <div class="book-grid">
                <?php foreach ($books as $book): ?>
                    <article class="book-card" data-search="<?= e($book['title'] . ' ' . $book['author'] . ' ' . $book['category_name']) ?>">
                        <a href="book.php?id=<?= (int)$book['book_id'] ?>" class="book-cover" style="background:<?= color_from_string($book['title']) ?>">
                            <?php if ($book['available_copies'] > 0): ?>
                                <span class="book-badge badge badge-green"><?= $book['available_copies'] ?> available</span>
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
            <div id="noResults" class="empty-state" style="display:none">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>No matches</h3>
                <p>Try a different keyword.</p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-book-open"></i>
                <h3>No books found</h3>
                <p>Try adjusting your search or filters.</p>
                <a href="catalog.php" class="btn btn-primary mt-2">View all books</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
