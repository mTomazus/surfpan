<div class="side-nav">
    <?= anchor('users', '<i class="fa fa-home"></i> <h4>' . t('nav_home') . '</h4>', ['class' => 'side-button']) ?>
    <?= anchor('heats', '<i class="fa fa-binoculars"></i> <h4>' . t('nav_results') . '</h4>', ['class' => 'side-button']) ?>
    <?php if (isset($judge_info) && isset($judge_info->role) && ($judge_info->role === 'head_judge' || $judge_info->role === 'judge')) { ?>
        <a mx-get="competitions/judge_profile" mx-build-modal="user-info-modal" mx-select="#form-container" mx-target="#user-info-modal .modal-body" class="side-button"><i class="fa fa-user"></i> <h4><?= t('nav_profile') ?></h4></a>
    <?php } elseif (isset($user['id'])) { ?>
            <?= anchor('users/profile_info', '<i class="fa fa-user"></i> <h4>' . t('nav_profile') . '</h4>', ['class' => 'side-button']) ?>
    <?php } ?>
    <?= anchor('users/logout', '<i class="fa fa-sign-out"></i> <h4>' . t('nav_logout') . '</h4>', ['class' => 'side-button']) ?>
    <div class="lang-switcher" style="padding: 1rem 0;">
        <?php foreach (['en','ru','de','fr','sv','fi','es','pt'] as $lc): ?>
            <a href="<?= BASE_URL ?>lang/set_lang?lang=<?= $lc ?>"
               class="lang-btn<?= (current_lang() === $lc) ? ' active' : '' ?>">
                <?= strtoupper($lc) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
