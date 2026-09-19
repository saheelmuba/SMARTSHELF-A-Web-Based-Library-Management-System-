<?php
/**
 * footer.php
 * -------------------------------------------------------------
 * Shared site footer + closing tags and the global script include.
 * -------------------------------------------------------------
 */
?>
</main><!-- /.page -->

<!-- ============================ FOOTER ============================ -->
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-col footer-brand">
            <a href="<?= BASE_URL ?>index.php" class="brand">
                <span class="brand-logo"><i class="fa-solid fa-book-open-reader"></i></span>
                <span class="brand-text"><?= e(SITE_NAME) ?></span>
            </a>
            <p>A smarter way to run your library &mdash; search, borrow, reserve and read,
               all from one beautiful dashboard.</p>
            <div class="social-row">
                <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Explore</h4>
            <a href="<?= BASE_URL ?>index.php">Home</a>
            <a href="<?= BASE_URL ?>catalog.php">Book Catalog</a>
            <a href="<?= BASE_URL ?>features.php">Features</a>
            <a href="<?= BASE_URL ?>About-Us.php">About Us</a>
        </div>

        <div class="footer-col">
            <h4>Account</h4>
            <a href="<?= BASE_URL ?>login.php">Login</a>
            <a href="<?= BASE_URL ?>register.php">Register</a>
            <a href="<?= BASE_URL ?>dashboard.php">My Dashboard</a>
            <a href="<?= BASE_URL ?>Contact-Us.php">Help &amp; Support</a>
        </div>

        <div class="footer-col">
            <h4>Visit Us</h4>
            <p><i class="fa-solid fa-location-dot"></i> 12 Library Lane, Colombo 03</p>
            <p><i class="fa-solid fa-phone"></i> +94 11 234 5678</p>
            <p><i class="fa-solid fa-envelope"></i> hello@smartshelf.lk</p>
            <p><i class="fa-solid fa-clock"></i> Mon&ndash;Sat, 8am &ndash; 8pm</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. Built with PHP &amp; MySQL. All rights reserved.</p>
        <p>Made for the HDIT 21193 UX &amp; Interface Design project.</p>
    </div>
</footer>

<!-- Scroll-to-top button -->
<button class="to-top" id="toTop" aria-label="Scroll to top"><i class="fa-solid fa-arrow-up"></i></button>

<!-- Global script -->
<script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
