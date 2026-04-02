<?php $user_info = Modules::run("users/_get_user_info") ?>

<!-- ===== Profile ===== -->
<div id="profile-main" style="padding: 2rem 0.2rem;max-width: 800px;margin: auto;">
    <h2 class="mt-0"><span>User</span> Profile</h2>
    <p class="mb-0">Keep your contact info up to date</p>

    <!-- Avatar upload -->
    <div class="avatar-wrap">
      <div class="avatar-preview" id="avatar-preview">
        <?php if (!empty($user_info[0]['avatar'])): ?>
          <img id="avatar-img" src="users_module/images/avatars/<?= out($user_info[0]['avatar']) ?>" alt="Avatar">
          <?php else: ?>
            <span id="avatar-initials"><?= mb_strtoupper(mb_substr($user_info[0]['name'] ?? '?', 0, 1)) ?></span>
        <?php endif; ?>
      </div>
      <div>
        <label class="avatar-upload-btn" for="avatar-file-input">
          <i class="fa fa-camera"></i> Change photo
        </label>
        <input type="file" id="avatar-file-input" accept="image/jpeg,image/png,image/webp" style="display:none">
        <div id="avatar-spinner"><i class="fa fa-spinner fa-spin"></i> Uploading…</div>
        <div id="avatar-error"></div>
        <div style="font-size:0.75rem;color:var(--text-light);margin-top:4px;">JPEG, PNG or WEBP · max 400x400px</div>
      </div>
    </div>

    <div id="response"><? flashdata() ?></div>
    <?php
    $form_attr = [
        'mx-post' => 'users/submit_update_profile',
        'mx-target' => '#response',
        'mx-close-on-success' => 'true',
        'class' => 'grid',
        'style' => 'gap:12px'
        ];
    echo form_open_upload('#', $form_attr);
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
<script>
  (function () {
  const input    = document.getElementById('avatar-file-input');
  const spinner  = document.getElementById('avatar-spinner');
  const errEl    = document.getElementById('avatar-error');
  const preview  = document.getElementById('avatar-preview');

  input.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    errEl.textContent = '';

    if (file.size > 5 * 1024 * 1024) {
      errEl.textContent = 'File too large (max 5 MB).';
      this.value = '';
      return;
    }

    const fd = new FormData();
    fd.append('userfile', file);

    spinner.style.display = 'block';

    fetch('<?= BASE_URL ?>users/upload_avatar', { method: 'POST', body: fd })
      .then(r => {
        if (!r.ok && r.status !== 400) throw new Error('HTTP ' + r.status);
        return r.text();
      })
      .then(raw => {
        spinner.style.display = 'none';
        let data;
        try { data = JSON.parse(raw); }
        catch(e) { console.error('Non-JSON response:', raw); errEl.textContent = 'Server error — check console.'; return; }
        if (data.success) {
          preview.innerHTML = '<img id="avatar-img" src="users_module/images/avatars/' + data.avatar + '?t=' + Date.now() + '" alt="Avatar">';
        } else {
          errEl.textContent = data.error || 'Upload failed.';
        }
      })
      .catch(err => {
        spinner.style.display = 'none';
        console.error('Avatar upload error:', err);
        errEl.textContent = 'Upload failed. Please try again.';
      });

    this.value = '';
  });
})();
</script>