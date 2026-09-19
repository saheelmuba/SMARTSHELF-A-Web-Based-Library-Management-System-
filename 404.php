<?php
/**
 * 404.php — Friendly "page not found" page
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle = 'Page Not Found';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container empty-state" style="padding:80px 20px">
        <div style="font-size:5rem;font-weight:800;background:var(--brand-grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent">404</div>
        <h3 style="font-size:1.6rem;margin-top:10px">Page not found</h3>
        <p style="max-width:440px;margin:8px auto 24px">The page you're looking for has been moved, removed or never existed. Let's get you back on track.</p>
        <div class="flex gap-1 wrap" style="justify-content:center">
            <a href="<?= BASE_URL ?>index.php" class="btn btn-primary"><i class="fa-solid fa-house"></i> Go home</a>
            <a href="<?= BASE_URL ?>catalog.php" class="btn btn-ghost"><i class="fa-solid fa-magnifying-glass"></i> Browse catalog</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
