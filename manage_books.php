<?php
/**
 * admin/manage_books.php — Book inventory list (CRUD: Read)
 * -------------------------------------------------------------
 * Lists every book with live search, availability status and
 * edit/delete/QR actions. Staff only.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Manage Books';

$q = clean($_GET['q'] ?? '');
$sql = "SELECT b.*, c.name AS category_name FROM books b
        LEFT JOIN categories c ON c.category_id = b.category_id";
$types = ''; $params = [];
if ($q !== '') {
    $sql .= " WHERE b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?";
    $like = "%$q%"; $types = 'sss'; $params = [$like, $like, $like];
}
$sql .= " ORDER BY b.book_id DESC";
$books = db_select($sql, $types, $params);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header">
            <div><h1>Manage Books</h1><p class="text-soft"><?= count($books) ?> titles in the catalogue.</p></div>
            <a href="<?= BASE_URL ?>admin/add_book.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add new book</a>
        </div>
        <?php render_flash(); ?>

        <form method="get" class="search-bar mb-3">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search books by title, author or ISBN...">
        </form>

        <div class="panel">
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Book</th><th>Category</th><th>ISBN</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($books as $b): ?>
                        <tr>
                            <td class="flex items-center gap-1">
                                <div class="float-cover" style="background:<?= color_from_string($b['title']) ?>;width:34px;height:46px;border-radius:7px;font-size:.8rem"><i class="fa-solid fa-book"></i></div>
                                <div><strong><?= e($b['title']) ?></strong><br><span class="text-mute" style="font-size:.8rem"><?= e($b['author']) ?></span></div>
                            </td>
                            <td><span class="badge badge-gray"><?= e($b['category_name'] ?? 'None') ?></span></td>
                            <td class="text-mute" style="font-size:.85rem"><?= e($b['isbn'] ?: '—') ?></td>
                            <td><?= (int)$b['available_copies'] ?> / <?= (int)$b['total_copies'] ?></td>
                            <td>
                                <?php if ($b['available_copies'] > 0): ?>
                                    <span class="badge badge-green">Available</span>
                                <?php else: ?>
                                    <span class="badge badge-red">Out</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn" title="QR code" onclick="showQR('SMARTSHELF-BOOK-<?= $b['book_id'] ?>|<?= e($b['isbn']) ?>', '<?= e(addslashes($b['title'])) ?>')"><i class="fa-solid fa-qrcode"></i></button>
                                    <a href="<?= BASE_URL ?>book.php?id=<?= $b['book_id'] ?>" class="icon-btn" title="View"><i class="fa-solid fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>admin/edit_book.php?id=<?= $b['book_id'] ?>" class="icon-btn" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                    <form method="post" action="<?= BASE_URL ?>admin/delete_book.php" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $b['book_id'] ?>">
                                        <button type="submit" class="icon-btn danger" title="Delete" data-confirm="Delete &quot;<?= e(addslashes($b['title'])) ?>&quot;? This cannot be undone."><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$books): ?>
                        <tr><td colspan="6"><div class="empty-state" style="padding:30px"><i class="fa-solid fa-book"></i><h3>No books found</h3></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
