<?php
/**
 * admin/add_book.php — Add a new book (CRUD: Create)
 * -------------------------------------------------------------
 * Staff form to add a book to the catalogue. Validates input,
 * handles an optional cover image upload and inserts via a
 * prepared statement.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Add Book';

$categories = db_select("SELECT * FROM categories ORDER BY name");
$errors = [];
$old = ['title'=>'','author'=>'','isbn'=>'','category_id'=>'','publisher'=>'','publish_year'=>'','total_copies'=>'1','shelf_location'=>'','description'=>'','ebook_file'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($old as $k => $v) { $old[$k] = clean($_POST[$k] ?? ''); }

    if (strlen($old['title']) < 2)   $errors['title'] = 'Title is required.';
    if (strlen($old['author']) < 2)  $errors['author'] = 'Author is required.';
    if ((int)$old['total_copies'] < 1) $errors['total_copies'] = 'At least 1 copy required.';

    // Optional cover image upload (sanitised, validated type).
    $coverName = null;
    if (!empty($_FILES['cover']['name']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $coverName = 'cover_' . time() . '_' . random_int(100, 999) . '.' . $ext;
            move_uploaded_file($_FILES['cover']['tmp_name'], __DIR__ . '/../uploads/covers/' . $coverName);
        } else {
            $errors['cover'] = 'Cover must be an image (jpg, png, webp, gif).';
        }
    }

    if (!$errors) {
        $total = (int)$old['total_copies'];
        $newId = db_execute(
            "INSERT INTO books (title, author, isbn, category_id, description, publisher, publish_year,
                total_copies, available_copies, shelf_location, cover_image, ebook_file)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
            'sssisssiisss',
            [
                $old['title'], $old['author'], $old['isbn'],
                $old['category_id'] ?: null, $old['description'], $old['publisher'],
                $old['publish_year'] ?: null, $total, $total,
                $old['shelf_location'], $coverName, $old['ebook_file'] ?: null
            ]
        );
        log_activity('Registered new book "' . $old['title'] . '"');
        set_flash('success', 'Book "' . $old['title'] . '" added to the catalogue.');
        redirect('admin/manage_books.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header">
            <div><h1>Add New Book</h1><p class="text-soft">Register a new title in the library catalogue.</p></div>
            <a href="<?= BASE_URL ?>admin/manage_books.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <?php render_flash(); ?>

        <div class="panel" style="max-width:760px">
            <div class="panel-body">
                <form method="post" enctype="multipart/form-data" data-validate novalidate>
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" value="<?= e($old['title']) ?>" required>
                            <div class="field-error <?= isset($errors['title']) ? 'show' : '' ?>"><?= e($errors['title'] ?? '') ?></div>
                        </div>
                        <div class="form-group">
                            <label for="author">Author *</label>
                            <input type="text" id="author" name="author" value="<?= e($old['author']) ?>" required>
                            <div class="field-error <?= isset($errors['author']) ? 'show' : '' ?>"><?= e($errors['author'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" id="isbn" name="isbn" value="<?= e($old['isbn']) ?>" placeholder="978...">
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">— Select —</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['category_id'] ?>" <?= $old['category_id'] == $c['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="publisher">Publisher</label>
                            <input type="text" id="publisher" name="publisher" value="<?= e($old['publisher']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="publish_year">Publish year</label>
                            <input type="number" id="publish_year" name="publish_year" value="<?= e($old['publish_year']) ?>" min="1000" max="<?= date('Y') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="total_copies">Total copies *</label>
                            <input type="number" id="total_copies" name="total_copies" value="<?= e($old['total_copies']) ?>" min="1" required>
                            <div class="field-error <?= isset($errors['total_copies']) ? 'show' : '' ?>"><?= e($errors['total_copies'] ?? '') ?></div>
                        </div>
                        <div class="form-group">
                            <label for="shelf_location">Shelf location</label>
                            <input type="text" id="shelf_location" name="shelf_location" value="<?= e($old['shelf_location']) ?>" placeholder="A1-23">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?= e($old['description']) ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cover">Cover image <span class="text-mute">(optional)</span></label>
                            <input type="file" id="cover" name="cover" accept="image/*">
                            <div class="field-error <?= isset($errors['cover']) ? 'show' : '' ?>"><?= e($errors['cover'] ?? '') ?></div>
                        </div>
                        <div class="form-group">
                            <label for="ebook_file">eBook filename <span class="text-mute">(in uploads/ebooks/)</span></label>
                            <input type="text" id="ebook_file" name="ebook_file" value="<?= e($old['ebook_file']) ?>" placeholder="book.pdf">
                            <div class="form-hint">Leave blank if no eBook is available.</div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-lg"><i class="fa-solid fa-plus"></i> Add book</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
