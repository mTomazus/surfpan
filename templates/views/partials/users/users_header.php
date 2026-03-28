<div class="logo" style="flex: 1.5;text-align: center;">
    <img src="images/surfpan-hero-2.svg" onclick="toggleScheme()" alt="logo surf club">
    <a href="users"><span style="color: light-dark(black, white)">Surf</span> Panel<br><h6>ver 1.25</h6></a>
</div>

<?php 
if (isset($user['name'])) { ?> 

<div class="d-sm-none" style="flex: 1.5;text-align: center;">
    <h3 style="text-transform:uppercase;" ><?= t('dashboard_user') ?></h3>
</div>
<div class="user d-md-none" style="flex: 1;text-align: center;">
    <h5 style="margin: 0;"> = <?= out($user['name']) ?> = </h5>
    <p style="margin: 0;font-size: 0.5em;"><?= t('role_label_user') ?></p>
</div>

<?php } elseif (isset($judge_info)) { ?>

    <div class="d-sm-none" style="flex: 1.5;text-align: center;">
    <?php if ($judge_info->role === 'organizer') { ?>
    <h3 style="text-transform:uppercase;" ><?= t('dashboard_organizer') ?></h3>
    <?php } else { ?>
    <h3 style="text-transform:uppercase;" ><?= t('dashboard_judge') ?></h3>
    <?php } ?>
</div>
<div class="user d-md-none" style="flex: 1;text-align: center;">
    <h5 style="margin: 0;color: orangered;">= <?= $judge_info->name ?> =</h5>
    <p style="margin: 0;font-size: 0.5em;text-transform:uppercase;"><?= str_replace('_', ' ', $judge_info->role) ?></p>
</div>

<?php } ?>

<div class="lang-switcher d-sm-none">
    <?php foreach (['en','ru','de','fr','sv','fi','es','pt'] as $lc): ?>
        <a href="<?= BASE_URL ?>lang/set_lang?lang=<?= $lc ?>"
           class="lang-btn<?= (current_lang() === $lc) ? ' active' : '' ?>">
            <?= strtoupper($lc) ?>
        </a>
    <?php endforeach; ?>
</div>
<div id="hamburger" class="burger col-3" onclick="toggleSideMenu()">
    <div class="line1"></div>
    <div class="line2"></div>
    <div class="line3"></div>
</div>