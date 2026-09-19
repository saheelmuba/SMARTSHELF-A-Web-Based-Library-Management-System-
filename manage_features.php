<?php
/**
 * admin/manage_features.php — Features list (CRUD: Read)
 * -------------------------------------------------------------
 * Manages the entries shown on the public Features page. Matches
 * the assignment's required `features` table CRUD.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Manage Features';

$features = db_select("SELECT * FROM features ORDER BY feature_id DESC");

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header">
            <div><h1>Manage Features</h1><p class="text-soft">Content shown on the public Features page (<?= count($features) ?> items).</p></div>
            <a href="<?= BASE_URL ?>admin/add_features.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add feature</a>
        </div>
        <?php render_flash(); ?>

        <div class="grid grid-3">
            <?php foreach ($features as $f): ?>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid <?= e($f['icon']) ?>"></i></div>
                    <h3><?= e($f['feature_name']) ?></h3>
                    <p style="font-size:.9rem"><?= e($f['description']) ?></p>
                    <div class="flex gap-1 mt-2">
                        <a href="<?= BASE_URL ?>admin/edit_features.php?id=<?= $f['feature_id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                        <form method="post" action="<?= BASE_URL ?>admin/delete_features.php" style="display:inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $f['feature_id'] ?>">
                            <button type="submit" class="btn btn-ghost btn-sm" data-confirm="Delete this feature?" style="color:var(--red)"><i class="fa-solid fa-trash"></i> Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
