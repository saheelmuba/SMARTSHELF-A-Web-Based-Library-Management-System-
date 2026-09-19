<?php
/**
 * Contact-Us.php — Public contact page with a working form
 * -------------------------------------------------------------
 * Saves submitted messages to the `contact_messages` table using
 * a prepared statement. Includes both server-side (PHP) and
 * client-side (JS) validation and CSRF protection.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contact Us';

$errors = [];
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf(); // CSRF protection

    // Collect + sanitise input.
    $old['name']    = clean($_POST['name'] ?? '');
    $old['email']   = clean($_POST['email'] ?? '');
    $old['subject'] = clean($_POST['subject'] ?? '');
    $old['message'] = clean($_POST['message'] ?? '');

    // Server-side validation.
    if ($old['name'] === '')                  $errors['name'] = 'Please enter your name.';
    if (!valid_email($old['email']))          $errors['email'] = 'Please enter a valid email.';
    if (strlen($old['message']) < 10)         $errors['message'] = 'Message must be at least 10 characters.';

    if (!$errors) {
        // Store the message via a prepared statement (SQL-injection safe).
        db_execute(
            'INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)',
            'ssss',
            [$old['name'], $old['email'], $old['subject'], $old['message']]
        );
        set_flash('success', 'Thanks for reaching out! We\'ll reply to your email shortly.');
        redirect('Contact-Us.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Get in touch</h1>
        <p>Questions about membership, books or your account? We're here to help.</p>
        <div class="breadcrumb"><a href="index.php">Home</a> / <span>Contact</span></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php render_flash(); ?>
        <div class="grid" style="grid-template-columns:1fr 1.3fr;gap:36px;align-items:start">

            <!-- Contact info -->
            <div>
                <h2 style="font-size:1.6rem;margin-bottom:10px">Let's talk</h2>
                <p class="text-soft mb-3">Reach us through any channel below or send a message and
                   we'll get back within one business day.</p>

                <?php
                $contacts = [
                    ['fa-location-dot', 'Visit us', '12 Library Lane, Colombo 03, Sri Lanka'],
                    ['fa-phone', 'Call us', '+94 11 234 5678'],
                    ['fa-envelope', 'Email us', 'hello@smartshelf.lk'],
                    ['fa-clock', 'Opening hours', 'Mon – Sat, 8:00 am – 8:00 pm'],
                ];
                foreach ($contacts as $c): ?>
                    <div class="float-book" style="border-color:var(--border)">
                        <div class="float-cover" style="background:var(--brand-soft);color:var(--brand);width:46px;height:46px;border-radius:12px">
                            <i class="fa-solid <?= $c[0] ?>"></i>
                        </div>
                        <div class="meta">
                            <strong><?= $c[1] ?></strong>
                            <span><?= $c[2] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Contact form -->
            <div class="card card-pad">
                <h2 style="font-size:1.4rem;margin-bottom:20px">Send us a message</h2>
                <form method="post" action="Contact-Us.php" data-validate novalidate>
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full name</label>
                            <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" placeholder="Jane Doe" required>
                            <div class="field-error <?= isset($errors['name']) ? 'show' : '' ?>"><?= e($errors['name'] ?? '') ?></div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" placeholder="jane@example.com" required>
                            <div class="field-error <?= isset($errors['email']) ? 'show' : '' ?>"><?= e($errors['email'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" value="<?= e($old['subject']) ?>" placeholder="How can we help?">
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Write your message..." required data-minlength="10"><?= e($old['message']) ?></textarea>
                        <div class="field-error <?= isset($errors['message']) ? 'show' : '' ?>"><?= e($errors['message'] ?? '') ?></div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fa-solid fa-paper-plane"></i> Send message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
