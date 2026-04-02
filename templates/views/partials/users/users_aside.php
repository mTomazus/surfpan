<div class="side-nav">
    <?= anchor('users', '<h4>HOME</h4>', ['class' => 'side-button']) ?>
    <?= anchor('heats', '<h4>RESULTS</h4>', ['class' => 'side-button']) ?>
    <?= anchor('users/athletes', '<h4>ATHLETES</h4>', ['class' => 'side-button']) ?>
    <?php if (isset($judge_info) && isset($judge_info->role) && ($judge_info->role === 'head_judge' || $judge_info->role === 'judge')) { ?>
        <a mx-get="competitions/judge_profile" mx-build-modal="user-info-modal" mx-select="#form-container" mx-target="#user-info-modal .modal-body" class="side-button"><i class="fa fa-user"></i> <h4>PROFILE</h4></a>
    <?php } elseif (isset($user['id'])) { ?>
            <?= anchor('users/profile_info', '<h4>PROFILE</h4>', ['class' => 'side-button']) ?>
    <?php } ?>
    <?= anchor('users/logout', '<h4>LOGOUT</h4>', ['class' => 'side-button']) ?>
</div>
