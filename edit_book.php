<?php
/**
 * admin/edit_book.php — Edit an existing book (CRUD: Update)
 * -------------------------------------------------------------
 * Loads a book by id, lets staff update its details and adjusts
 * available_copies sensibly when the total copy count changes.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Edit Book';

$bookId = (int) ($_GET['id'] ?? 0);
$book = db_select_one("SELECT * FROM books WHERE book_id = ?", 'i', [$bookId]);
if (!$book) { set_flash('error', 'Book not found.'); redirect('admin/manage_books.php'); }

$categories = db_select("SELECT * FROM categories ORDER BY name");
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title       = clean($_POST['title'] ?? '');
    $author      = clean($_POST['author'] ?? '');
    $isbn        = clean($_POST['isbn'] ?? '');
    $categoryId  = clean($_POST['category_id'] ?? '');
    $publisher   = clean($_POST['publisher'] ?? '');
    $publishYear = clean($_POST['publish_year'] ?? '');
    $totalCopies = (int) ($_POST['total_copies'] ?? 1);
    $shelf       = clean($_POST['shelf_location'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $ebook       = clean($_POST['ebook_file'] ?? '');

    if (strlen($title) < 2)  $errors['title'] = 'Title is required.';
    if (strlen($author) < 2) $errors['author'] = 'Author is required.';
    if ($totalCopies < 1)    $errors['total_copies'] = 'At least 1 copy required.';

    if (!$errors) {
        // Adjust availability by the same delta as the change in total copies,
        // never letting it drop below 0 or exceed the new total.
        $onLoan = $book['total_copies'] - $book['available_copies'];
        $available = max(0, $totalCopies - $onLoan);

        db_execute(
            "UPDATE books SET title=?, author=?, isbn=?, category_id=?, description=?, publisher=?,
                publish_year=?, total_copies=?, available_copies=?, shelf_location=?, ebook_file=?
             WHERE book_id=?",
            'sssisssiissi',
            [
                $title, $author, $isbn, $categoryId ?: null, $description, $publisher,
                $publishYear ?: null, $totalCopies, $available, $shelf, $ebook ?: null, $bookId
            ]
        );
        log_activity('Updated book "' . $title . '"');
        set_flash('success', 'Book updated successfully.');
        redirect('admin/manage_books.php');
    }
    // Preserve edited values on validation error.
    $book = array_merge($book, [
        'title'=>$title,'author'=>$author,'isbn'=>$isbn,'category_id'=>$categoryId,
        'publisher'=>$publisher,'publish_year'=>$publishYear,'total_copies'=>$totalCopies,
        'shelf_location'=>$shelf,'description'=>$description,'ebook_file'=>$ebook,
    ]);
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header">
            <div><h1>Edit Book</h1><p class="text-soft">Update details for "<?= e($book['title']) ?>".</p></div>
            <a href="<?= BASE_URL ?>admin/manage_books.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <?php render_flash(); ?>
        <div class="panel" style="max-width:760px">
            <div class="panel-body">
                <form method="post" data-validate novalidate>
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="form-group"><label for="title">Title *</label>
                            <input type="text" id="title" name="title" value="<?= e($book['title']) ?>" required>
                            <div class="field-error <?= isset($errors['title']) ? 'show' : '' ?>"><?= e($errors['title'] ?? '') ?></div>
                        </div>
                        <div class="form-group"><label for="author">Author *</label>
                            <input type="text" id="author" name="author" value="<?= e($book['author']) ?>" required>
                            <div class="field-error <?= isset($errors['author']) ? 'show' : '' ?>"><?= e($errors['author'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="isbn">ISBN</label>
                            <input type="text" id="isbn" name="isbn" value="<?= e($book['isbn']) ?>"></div>
                        <div class="form-group"><label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">— Select —</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['category_id'] ?>" <?= $book['category_id'] == $c['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="publisher">Publisher</label>
                            <input type="text" id="publisher" name="publisher" value="<?= e($book['publisher']) ?>"></div>
                        <div class="form-group"><label for="publish_year">Publish year</label>
                            <input type="number" id="publish_year" name="publish_year" value="<?= e($book['publish_year']) ?>"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="total_copies">Total copies *</label>
                            <input type="number" id="total_copies" name="total_copies" value="<?= e($book['total_copies']) ?>" min="1" required>
                            <div class="form-hint">Currently <?= (int)$book['available_copies'] ?> available.</div>
                            <div class="field-error <?= isset($errors['total_copies']) ? 'show' : '' ?>"><?= e($errors['total_copies'] ?? '') ?></div>
                        </div>
                        <div class="form-group"><label for="shelf_location">Shelf location</label>
                            <input type="text" id="shelf_location" name="shelf_location" value="<?= e($book['shelf_location']) ?>"></div>
                    </div>
                    <div class="form-group"><label for="description">Description</label>
                        <textarea id="description" name="description"><?= e($book['description']) ?></textarea></div>
                    <div class="form-group"><label for="ebook_file">eBook filename</label>
                        <input type="text" id="ebook_file" name="ebook_file" value="<?= e($book['ebook_file']) ?>" placeholder="book.pdf"></div>
                    <button class="btn btn-primary btn-lg"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
