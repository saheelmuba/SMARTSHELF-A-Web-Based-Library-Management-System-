<?php
/**
 * admin/manage_categories.php — Category CRUD (single page)
 * -------------------------------------------------------------
 * Add, edit and delete book categories from one screen.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $name = clean($_POST['name'] ?? '');
    $desc = clean($_POST['description'] ?? '');
    $icon = clean($_POST['icon'] ?? 'fa-book');

    if ($action === 'add' && $name !== '') {
        db_execute("INSERT INTO categories (name, description, icon) VALUES (?,?,?)", 'sss', [$name, $desc, $icon]);
        set_flash('success', 'Category added.');
    } elseif ($action === 'edit') {
        $cid = (int) ($_POST['category_id'] ?? 0);
        db_execute("UPDATE categories SET name=?, description=?, icon=? WHERE category_id=?", 'sssi', [$name, $desc, $icon, $cid]);
        set_flash('success', 'Category updated.');
    } elseif ($action === 'delete') {
        $cid = (int) ($_POST['category_id'] ?? 0);
        db_execute("DELETE FROM categories WHERE category_id=?", 'i', [$cid]);
        set_flash('info', 'Category deleted. Its books are now uncategorised.');
    }
    redirect('admin/manage_categories.php');
}

$categories = db_select(
    "SELECT c.*, (SELECT COUNT(*) FROM books b WHERE b.category_id=c.category_id) AS book_count
     FROM categories c ORDER BY c.name"
);
$editId = (int) ($_GET['edit'] ?? 0);
$editing = $editId ? db_select_one("SELECT * FROM categories WHERE category_id=?", 'i', [$editId]) : null;
$icons = ['fa-book','fa-feather','fa-microchip','fa-landmark','fa-chart-line','fa-child','fa-brain','fa-magnifying-glass','fa-rocket','fa-flask','fa-music','fa-palette'];

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header"><div><h1>Categories</h1><p class="text-soft">Organise the catalogue.</p></div></div>
        <?php render_flash(); ?>
        <div class="grid" style="grid-template-columns:1fr 1.6fr;gap:24px;align-items:start">
            <!-- Add / edit form -->
            <div class="panel">
                <div class="panel-head"><h2><?= $editing ? 'Edit category' : 'Add category' ?></h2></div>
                <div class="panel-body">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
                        <?php if ($editing): ?><input type="hidden" name="category_id" value="<?= $editing['category_id'] ?>"><?php endif; ?>
                        <div class="form-group"><label for="name">Name *</label>
                            <input type="text" id="name" name="name" value="<?= e($editing['name'] ?? '') ?>" required></div>
                        <div class="form-group"><label for="description">Description</label>
                            <textarea id="description" name="description" style="min-height:70px"><?= e($editing['description'] ?? '') ?></textarea></div>
                        <div class="form-group"><label for="icon">Icon</label>
                            <select id="icon" name="icon">
                                <?php foreach ($icons as $ic): ?><option value="<?= $ic ?>" <?= ($editing['icon'] ?? '')===$ic?'selected':'' ?>><?= $ic ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-block"><i class="fa-solid fa-<?= $editing ? 'floppy-disk' : 'plus' ?>"></i> <?= $editing ? 'Save' : 'Add' ?></button>
                        <?php if ($editing): ?><a href="manage_categories.php" class="btn btn-ghost btn-block mt-1">Cancel</a><?php endif; ?>
                    </form>
                </div>
            </div>
            <!-- List -->
            <div class="panel">
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>Category</th><th>Books</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td class="flex items-center gap-1">
                                    <span class="feature-icon" style="width:38px;height:38px;font-size:1rem;margin:0"><i class="fa-solid <?= e($c['icon']) ?>"></i></span>
                                    <div><strong><?= e($c['name']) ?></strong><br><span class="text-mute" style="font-size:.8rem"><?= e($c['description']) ?></span></div>
                                </td>
                                <td><span class="badge badge-brand"><?= (int)$c['book_count'] ?></span></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="?edit=<?= $c['category_id'] ?>" class="icon-btn"><i class="fa-solid fa-pen"></i></a>
                                        <form method="post" style="margin:0"><?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete"><input type="hidden" name="category_id" value="<?= $c['category_id'] ?>">
                                            <button class="icon-btn danger" data-confirm="Delete this category?"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
