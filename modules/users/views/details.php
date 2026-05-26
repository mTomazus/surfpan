<?php $user_info = Modules::run("users/_get_user_info") ?>
<!-- ===== Profile ===== -->
<div id="profile-main" style="padding: 2rem 0.2rem;max-width: 800px;margin: auto;">
    <section class="card pad span-12" aria-labelledby="profile-title">
        <div class="section-head">
            <h3 id="profile-title">Profile</h3>
            <span class="subtle">Keep your contact info up to date</span>
        </div>
        <div id="response"><? flashdata() ?></div>

        <?php
        $form_attr = [
            'mx-post' => 'users/submit_update_profile',
            'mx-target' => '#response',
            'mx-close-on-success' => 'true',
            'class' => 'grid',
            'style' => 'gap:12px"'
            ];
        echo form_open('#', $form_attr);
        ?>
        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:12px">
            <label class="field"><span class="subtle">Phone</span><input type="tel" name="phone" value="<?= out($user_info[0]['phone']) ?>" /></label>
            <label class="field"><span class="subtle">Birthday</span><input type="text" class="date-picker" name="dob"value="<?= out($user_info[0]['dob']) ?>" placeholder="1999-09-09" /></label>
            <label class="field"><span class="subtle">Gender</span>
                <select style="appearance:none" name="gender">
                    <option value="female" <?= ($user_info[0]['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                    <option value="male" <?= ($user_info[0]['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                </select>
            </label>
            <label class="field"><span class="subtle">Club</span><input name="club_name" value="<?= out($user_info[0]['club_name']) ?>" placeholder="Enter you surf club" /></label>
            <label class="field" for="country"><span class="subtle">Country</span>
                <select style="appearance:none" name="country" id="country" required>
                    <option value="<?= out($user_info[0]['country']) ?>">-- Select your country --</option>
                    <?php foreach ($countries as $c): ?>
                        <option value="<?= $c->code ?>">
                            <?= out($c->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="controls" style="margin-top:12px">
            <button class="btn" type="submit" name="submit" value="Save">Save</button>
            <a href="<?= BASE_URL ?>pass_reset" class="btn" style="flex:2;text-decoration:none">Change password</a>
            <a href="users" class="btn" style="flex:1;text-decoration: none;">Close</a>
        </div>
        <?= form_close() ?>
    </section>
</div>