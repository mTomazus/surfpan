<?php $user_info = Modules::run("users/_get_user_info") ?>
<!-- ===== Profile ===== -->
<div id="profile-main" style="padding: 2rem 0.2rem;max-width: 800px;margin: auto;">
    <h2 class="mt-0"><span>User</span> Profile</h2>
    <p>Keep your contact info up to date</p>
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
    <label class="field"><span class="subtle">Name:</span><input name="name" value="<?= out($user_info[0]['name'])?>" placeholder="Vardas Pavardenis" /></label>
    <label class="field"><span class="subtle">Email:</span><input type="email" name="email" value="<?= out($user_info[0]['email']) ?>" /></label>
    <label class="field"><span class="subtle">Phone:</span><input type="tel" name="phone" value="<?= out($user_info[0]['phone']) ?>" /></label>
    <label class="field"><span class="subtle">Birthday:</span><input type="text" class="date-picker" name="dob"value="<?= out($user_info[0]['dob']) ?>" placeholder="1999-09-09" /></label>
    <label class="field"><span class="subtle">Gender:</span>
        <select name="gender">
            <option value="female" <?= ($user_info[0]['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
            <option value="male" <?= ($user_info[0]['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
        </select>
    </label>
    <label class="field"><span class="subtle">Club:</span><input name="club_name" value="<?= out($user_info[0]['club_name']) ?>" placeholder="Enter you surf club" /></label>
    <label class="field" for="country">
        <span class="subtle">Country:</span>
        <select name="country" id="country" required>
            <?php foreach ($countries as $c): ?>
                <option value="<?= $c->code ?>" <?= ($c->code == $user_info[0]['country']) ? 'selected' : '' ?>>
                    <?= out($c->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <div class="controls" style="margin-top:12px">
        <button class="btn" type="submit" name="submit" value="Save">Save</button>
        <button class="btn" style="flex:2" onclick="alert('Change password…')">Change password</button>
        <a href="users" class="btn" style="flex:1;text-decoration: none;">Close</a>
    </div>
    <?= form_close() ?>
</div>