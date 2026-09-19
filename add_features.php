<?php
/**
 * admin/add_features.php — Add a feature (CRUD: Create)
 * -------------------------------------------------------------
 * Creates a new entry in the `features` table with validation,
 * CSRF protection and a prepared statement.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Add Feature';

$icons = ['fa-star','fa-magnifying-glass','fa-wand-magic-sparkles','fa-qrcode','fa-tablet-screen-button',
          'fa-people-line','fa-coins','fa-bell','fa-chart-pie','fa-boxes-stacked','fa-user-shield','fa-book','fa-bolt'];
$errors = [];
$old = ['feature_name'=>'','description'=>'','facilities'=>'','icon'=>'fa-star'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($old as $k => $v) { $old[$k] = clean($_POST[$k] ?? ''); }

    if (strlen($old['feature_name']) < 3)  $errors['feature_name'] = 'Feature name is required.';
    if (strlen($old['description']) < 10)  $errors['description'] = 'Please add a longer description.';

    if (!$errors) {
        db_execute(
            "INSERT INTO features (feature_name, description, facilities, user, icon) VALUES (?,?,?,?,?)",
            'sssss',
            [$old['feature_name'], $old['description'], $old['facilities'], $_SESSION['fullname'], $old['icon']]
        );
        log_activity('Added feature "' . $old['feature_name'] . '"');
        set_flash('success', 'Feature added.');
        redirect('admin/manage_features.php');
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header">
            <div><h1>Add Feature</h1><p class="text-soft">Showcase a new capability on the public site.</p></div>
            <a href="<?= BASE_URL ?>admin/manage_features.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <?php render_flash(); ?>
        <div class="panel" style="max-width:680px">
            <div class="panel-body">
                <form method="post" data-validate novalidate>
                    <?= csrf_field() ?>
                    <div class="form-group"><label for="feature_name">Feature name *</label>
                        <input type="text" id="feature_name" name="feature_name" value="<?= e($old['feature_name']) ?>" required data-minlength="3">
                        <div class="field-error <?= isset($errors['feature_name']) ? 'show' : '' ?>"><?= e($errors['feature_name'] ?? '') ?></div>
                    </div>
                    <div class="form-group"><label for="description">Description *</label>
                        <textarea id="description" name="description" required data-minlength="10"><?= e($old['description']) ?></textarea>
                        <div class="field-error <?= isset($errors['description']) ? 'show' : '' ?>"><?= e($errors['description'] ?? '') ?></div>
                    </div>
                    <div class="form-group"><label for="facilities">Facilities <span class="text-mute">(comma separated)</span></label>
                        <input type="text" id="facilities" name="facilities" value="<?= e($old['facilities']) ?>" placeholder="Live search, filters, sorting">
                    </div>
                    <div class="form-group"><label for="icon">Icon</label>
                        <select id="icon" name="icon">
                            <?php foreach ($icons as $ic): ?>
                                <option value="<?= $ic ?>" <?= $old['icon'] === $ic ? 'selected' : '' ?>><?= $ic ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-hint">FontAwesome solid icon class.</div>
                    </div>
                    <button class="btn btn-primary btn-lg"><i class="fa-solid fa-plus"></i> Add feature</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
