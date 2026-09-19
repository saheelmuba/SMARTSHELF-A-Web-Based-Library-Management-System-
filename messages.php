<?php
/**
 * admin/messages.php — Contact form inbox
 * -------------------------------------------------------------
 * Staff view of messages submitted through the public contact
 * form. Mark as read or delete.
 * -------------------------------------------------------------
 */
require_once __DIR__ . '/../includes/functions.php';
require_staff();
$pageTitle = 'Messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['message_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'read') {
        db_execute("UPDATE contact_messages SET is_read=1 WHERE message_id=?", 'i', [$id]);
    } elseif ($action === 'delete') {
        db_execute("DELETE FROM contact_messages WHERE message_id=?", 'i', [$id]);
        set_flash('info', 'Message deleted.');
    }
    redirect('admin/messages.php');
}

$messages = db_select("SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC");

require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash">
    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="dash-main">
        <div class="dash-header"><div><h1>Messages</h1><p class="text-soft">Enquiries from the contact form.</p></div></div>
        <?php render_flash(); ?>
        <?php if ($messages): ?>
            <?php foreach ($messages as $m): ?>
                <div class="card card-pad mb-2" style="<?= $m['is_read'] ? '' : 'border-left:3px solid var(--brand)' ?>">
                    <div class="flex between items-center wrap gap-1 mb-1">
                        <div class="flex items-center gap-1">
                            <span class="avatar-sm" style="background:<?= color_from_string($m['name']) ?>"><?= e(initials($m['name'])) ?></span>
                            <div><strong><?= e($m['name']) ?></strong> <span class="text-mute">&lt;<?= e($m['email']) ?>&gt;</span>
                                <br><span class="text-mute" style="font-size:.8rem"><?= time_ago($m['created_at']) ?></span></div>
                        </div>
                        <?php if (!$m['is_read']): ?><span class="badge badge-brand">New</span><?php endif; ?>
                    </div>
                    <?php if ($m['subject']): ?><strong style="display:block;margin-bottom:4px"><?= e($m['subject']) ?></strong><?php endif; ?>
                    <p class="text-soft mb-2"><?= e($m['message']) ?></p>
                    <div class="flex gap-1">
                        <a href="mailto:<?= e($m['email']) ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-reply"></i> Reply</a>
                        <?php if (!$m['is_read']): ?>
                            <form method="post" style="margin:0"><?= csrf_field() ?><input type="hidden" name="action" value="read"><input type="hidden" name="message_id" value="<?= $m['message_id'] ?>"><button class="btn btn-ghost btn-sm"><i class="fa-solid fa-check"></i> Mark read</button></form>
                        <?php endif; ?>
                        <form method="post" style="margin:0"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="message_id" value="<?= $m['message_id'] ?>"><button class="btn btn-ghost btn-sm" data-confirm="Delete this message?" style="color:var(--red)"><i class="fa-solid fa-trash"></i></button></form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state"><i class="fa-solid fa-envelope-open"></i><h3>Inbox empty</h3><p>No messages yet.</p></div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
