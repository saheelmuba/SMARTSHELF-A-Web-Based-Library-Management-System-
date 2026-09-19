<?php
/**
 * read.php — Digital library eBook reader
 * -------------------------------------------------------------
 * Streams a book's PDF/eBook in an embedded reader for logged-in
 * users. This powers the "Digital Library" feature. If the actual
 * file is missing, a friendly demo placeholder is shown so the
 * feature is always demonstrable.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$bookId = (int) ($_GET['id'] ?? 0);
$book = db_select_one("SELECT * FROM books WHERE book_id = ?", 'i', [$bookId]);

if (!$book || !$book['ebook_file']) {
    set_flash('error', 'No eBook is available for that title.');
    redirect('catalog.php');
}

$pageTitle = 'Reading: ' . $book['title'];
$filePath  = __DIR__ . '/uploads/ebooks/' . $book['ebook_file'];
$fileUrl   = BASE_URL . 'uploads/ebooks/' . rawurlencode($book['ebook_file']);
$fileReady = file_exists($filePath);

require_once __DIR__ . '/includes/header.php';
?>

<section class="section-sm">
    <div class="container">
        <div class="flex between items-center wrap gap-1 mb-2">
            <div>
                <div class="breadcrumb" style="justify-content:flex-start">
                    <a href="catalog.php">Catalog</a> / <a href="book.php?id=<?= $bookId ?>"><?= e($book['title']) ?></a> / <span>Read</span>
                </div>
                <h1 style="font-size:1.5rem;margin-top:6px"><i class="fa-solid fa-tablet-screen-button"></i> <?= e($book['title']) ?></h1>
                <p class="text-soft">by <?= e($book['author']) ?></p>
            </div>
            <?php if ($fileReady): ?>
                <a href="<?= $fileUrl ?>" download class="btn btn-success"><i class="fa-solid fa-download"></i> Download</a>
            <?php endif; ?>
        </div>

        <div class="card" style="overflow:hidden">
            <?php if ($fileReady): ?>
                <!-- Embedded PDF reader -->
                <iframe src="<?= $fileUrl ?>" style="width:100%;height:78vh;border:none" title="eBook reader"></iframe>
            <?php else: ?>
                <!-- Demo placeholder when the sample PDF isn't present -->
                <div class="empty-state" style="padding:80px 24px">
                    <i class="fa-solid fa-book-open-reader" style="color:var(--brand);opacity:1"></i>
                    <h3>eBook preview</h3>
                    <p style="max-width:480px;margin:0 auto 16px">
                        This title is part of the digital library. In a live deployment the PDF
                        (<code><?= e($book['ebook_file']) ?></code>) streams here in an embedded reader.
                        Drop the file into <code>uploads/ebooks/</code> to activate it.
                    </p>
                    <div class="card card-pad" style="max-width:520px;margin:0 auto;text-align:left">
                        <h4 style="margin-bottom:10px"><?= e($book['title']) ?></h4>
                        <p class="text-soft"><?= e($book['description']) ?></p>
                    </div>
                    <a href="book.php?id=<?= $bookId ?>" class="btn btn-ghost mt-3">Back to book</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
