<?php
$ok   = !empty($success_msg);
$msg  = $ok ? $success_msg : ($error_msg ?? 'Something went wrong.');

// Optional links (controller may set these)
$home_url     = $home_url ?? (defined('BASE_URL') ? BASE_URL : '/');
?>

<div class "container" style="max-width:600px;margin:40px auto;padding:0 20px;max-width: 650px;">
    <section style="padding:20px;">
        <?php if ($ok): ?>
            <div style="font-size:28px;margin-bottom:6px;">
                <h1 style="font-size: 2em; text-transform: uppercase;color: var(--text-light);">congratulations!</h1>
                <h2 style="font-size: 2rem; color: var(--text-dark); margin-top: 0;">✅ Email successfully confirmed</h2>
            </div>
            <p style="margin:3rem 0 0;color:var(--text-dark)"><?= out($msg) ?></p>
        <a href="<?= out($home_url) ?>" class="button" style="margin-top: 3rem;">LOGIN</a>
        <?php else: ?>
            <div style="font-size:28px;margin-bottom:6px;">
                <h1 style="font-size: 2em; text-transform: uppercase;">oops!</h1>
                <h2 style="font-size: 2rem; color: indianred; margin-top: 0;">⚠️ Email confirmation failed</h2>
            </div>
            <p style="margin:3rem 0 0;color:var(--text-dark)"><?= out($msg) ?></p>
        <?php endif; ?>
    </section>
</div>