<?php $user_info = Modules::run("users/_get_user_info") ?>
<!-- ===== Profile ===== -->
<div id="profile-main" style="padding: 2rem 0.2rem;max-width: 800px;margin: auto;">
    <h2 class="mt-0"><span><?= t('user_profile_title') ?></span></h2>
    <p><?= t('user_profile_desc') ?></p>
    <div id="response"><? flashdata() ?></div>
    <?php
    $form_attr = [
        'mx-post' => 'users/submit_update_profile',
        'mx-target' => '#response',
        'mx-close-on-success' => 'true',
        'class' => 'grid',
        'style' => 'gap:12px'
        ];
    echo form_open('#', $form_attr);
    ?>
    <label class="field"><span class="subtle"><?= t('user_profile_name') ?></span><input name="name" value="<?= out($user_info[0]['name'])?>" placeholder="Vardas Pavardenis" /></label>
    <label class="field"><span class="subtle"><?= t('user_profile_email') ?></span><input type="email" name="email" value="<?= out($user_info[0]['email']) ?>" /></label>
    <label class="field"><span class="subtle"><?= t('user_profile_phone') ?></span><input type="tel" name="phone" value="<?= out($user_info[0]['phone']) ?>" /></label>
    <label class="field"><span class="subtle"><?= t('user_profile_birthday') ?></span><input type="text" class="date-picker" name="dob" value="<?= out($user_info[0]['dob']) ?>" placeholder="1999-09-09" /></label>
    <label class="field"><span class="subtle"><?= t('user_profile_gender') ?></span>
        <select name="gender">
            <option value="female" <?= ($user_info[0]['gender'] === 'female') ? 'selected' : '' ?>><?= t('user_profile_gender_female') ?></option>
            <option value="male" <?= ($user_info[0]['gender'] === 'male') ? 'selected' : '' ?>><?= t('user_profile_gender_male') ?></option>
        </select>
    </label>
    <label class="field"><span class="subtle"><?= t('user_profile_club') ?></span><input name="club_name" value="<?= out($user_info[0]['club_name']) ?>" placeholder="Enter you surf club" /></label>
    <label class="field" for="country">
        <span class="subtle"><?= t('user_profile_country') ?></span>
        <select name="country" id="country" required>
            <?php foreach ($countries as $c): ?>
                <option value="<?= $c->code ?>" <?= ($c->code == $user_info[0]['country']) ? 'selected' : '' ?>>
                    <?= out($c->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <div class="controls" style="margin-top:12px">
        <button class="btn" type="submit" name="submit" value="Save"><?= t('user_profile_save') ?></button>
        <button class="btn" style="flex:2" onclick="alert('Change password…')"><?= t('user_profile_change_pass') ?></button>
        <a href="users" class="btn" style="flex:1;text-decoration: none;"><?= t('user_profile_close') ?></a>
    </div>
    <?= form_close() ?>
</div>