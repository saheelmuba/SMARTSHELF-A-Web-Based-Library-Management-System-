<?php
/**
 * header.php
 * -------------------------------------------------------------
 * Shared site header: <head> tags, top navigation bar, theme
 * toggle and the opening <body>/<main> wrappers.
 *
 * Pages should set $pageTitle (optional) before including this.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? SITE_NAME;
// Detect the current file so we can highlight the active nav link.
$current = basename($_SERVER['PHP_SELF']);
$notifCount = is_logged_in() ? unread_notifications($_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(SITE_NAME) ?> - <?= e(SITE_TAGLINE) ?>. Borrow books, reserve titles, read eBooks and manage your library online.">
    <title><?= e($pageTitle) ?> &middot; <?= e(SITE_NAME) ?></title>

    <!-- Favicon (inline SVG book) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- App stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <!-- Apply saved theme before paint to avoid a flash of the wrong theme -->
    <script>
        (function () {
            const saved = localStorage.getItem('theme');
            if (saved) document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>
</head>
<body>
<!-- ============================ NAVBAR ============================ -->
<header class="navbar" id="navbar">
    <div class="nav-inner">
        <a href="<?= BASE_URL ?>index.php" class="brand">
            <span class="brand-logo"><i class="fa-solid fa-book-open-reader"></i></span>
            <span class="brand-text"><?= e(SITE_NAME) ?></span>
        </a>

        <!-- Mobile menu toggle -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="nav-links" id="navLinks">
            <a href="<?= BASE_URL ?>index.php"      class="<?= $current === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="<?= BASE_URL ?>features.php"    class="<?= $current === 'features.php' ? 'active' : '' ?>">Features</a>
            <a href="<?= BASE_URL ?>catalog.php"     class="<?= $current === 'catalog.php' ? 'active' : '' ?>">Catalog</a>
            <a href="<?= BASE_URL ?>About-Us.php"    class="<?= $current === 'About-Us.php' ? 'active' : '' ?>">About</a>
            <a href="<?= BASE_URL ?>Contact-Us.php"  class="<?= $current === 'Contact-Us.php' ? 'active' : '' ?>">Contact</a>

            <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?><?= is_staff() ? 'admin/admin_dashboard.php' : 'dashboard.php' ?>"
                   class="<?= in_array($current, ['dashboard.php','admin_dashboard.php']) ? 'active' : '' ?>">Dashboard</a>
            <?php endif; ?>

            <!-- Theme toggle -->
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark / light mode">
                <i class="fa-solid fa-moon"></i>
            </button>

            <?php if (is_logged_in()): ?>
                <!-- Notification bell -->
                <a href="<?= BASE_URL ?>notifications.php" class="nav-bell" title="Notifications">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($notifCount > 0): ?>
                        <span class="bell-badge"><?= $notifCount ?></span>
                    <?php endif; ?>
                </a>
                <!-- User dropdown -->
                <div class="nav-user" id="navUser">
                    <button class="user-chip" id="userChip">
                        <span class="avatar-sm" style="background:<?= color_from_string($_SESSION['fullname']) ?>">
                            <?= e(initials($_SESSION['fullname'])) ?>
                        </span>
                        <span class="user-name"><?= e(explode(' ', $_SESSION['fullname'])[0]) ?></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="user-menu" id="userMenu">
                        <span class="user-menu-role"><?= e(ucfirst($_SESSION['role'])) ?></span>
                        <a href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user"></i> My Profile</a>
                        <a href="<?= BASE_URL ?><?= is_staff() ? 'admin/admin_dashboard.php' : 'dashboard.php' ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                        <a href="<?= BASE_URL ?>logout.php" class="danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login.php" class="btn btn-ghost btn-sm">Login</a>
                <a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-sm">Join Free</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="page">
