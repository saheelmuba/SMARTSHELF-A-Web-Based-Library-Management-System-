<?php
/**
 * admin/edit_features.php — Edit a feature (CRUD: Update)
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Edit Feature';

$id = (int) ($_GET['id'] ?? 0);
$feature = db_select_one("SELECT * FROM features WHERE feature_id = ?", 'i', [$id]);
if (!$feature) { set_flash('error', 'Feature not found.'); redirect('admin/manage_features.php'); }

$icons = ['fa-star','fa-magnifying-glass','fa-wand-magic-sparkles','fa-qrcode','fa-tablet-screen-button',
          'fa-people-line','fa-coins','fa-bell','fa-chart-pie','fa-boxes-stacked','fa-user-shield','fa-book','fa-bolt'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = clean($_POST['feature_name'] ?? '');
    $desc = clean($_POST['description'] ?? '');
    $fac  = clean($_POST['facilities'] ?? '');
    $icon = clean($_POST['icon'] ?? 'fa-star');

    if (strlen($name) < 3)  $errors['feature_name'] = 'Feature name is required.';
    if (strlen($desc) < 10) $errors['description'] = 'Please add a longer description.';

    if (!$errors) {
        db_execute(
            "UPDATE features SET feature_name=?, description=?, facilities=?, icon=? WHERE feature_id=?",
            'ssssi', [$name, $desc, $fac, $icon, $id]
        );
        log_activity('Updated feature "' . $name . '"');
        set_flash('success', 'Feature updated.');
        redirect('admin/manage_features.php');
    }
    $feature = array_merge($feature, ['feature_name'=>$name,'description'=>$desc,'facilities'=>$fac,'icon'=>$icon]);
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header">
            <div><h1>Edit Feature</h1><p class="text-soft">Update "<?= e($feature['feature_name']) ?>".</p></div>
            <a href="<?= BASE_URL ?>admin/manage_features.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <?php render_flash(); ?>
        <div class="panel" style="max-width:680px">
            <div class="panel-body">
                <form method="post" data-validate novalidate>
                    <?= csrf_field() ?>
                    <div class="form-group"><label for="feature_name">Feature name *</label>
                        <input type="text" id="feature_name" name="feature_name" value="<?= e($feature['feature_name']) ?>" required data-minlength="3">
                        <div class="field-error <?= isset($errors['feature_name']) ? 'show' : '' ?>"><?= e($errors['feature_name'] ?? '') ?></div>
                    </div>
                    <div class="form-group"><label for="description">Description *</label>
                        <textarea id="description" name="description" required data-minlength="10"><?= e($feature['description']) ?></textarea>
                        <div class="field-error <?= isset($errors['description']) ? 'show' : '' ?>"><?= e($errors['description'] ?? '') ?></div>
                    </div>
                    <div class="form-group"><label for="facilities">Facilities</label>
                        <input type="text" id="facilities" name="facilities" value="<?= e($feature['facilities']) ?>"></div>
                    <div class="form-group"><label for="icon">Icon</label>
                        <select id="icon" name="icon">
                            <?php foreach ($icons as $ic): ?>
                                <option value="<?= $ic ?>" <?= $feature['icon'] === $ic ? 'selected' : '' ?>><?= $ic ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-lg"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
