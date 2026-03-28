<div class="logo">
    <img src="images/surfpan-hero-2.svg" alt="logo surf panel">
    <div>
        <a href="<?= BASE_URL ?>"><span style="color: light-dark(black, white)">Surf</span> Panel<br><h6>ver 1.25</h6></a>
    </div>
</div>
<div>
    <?= Template::partial('partials/public/public_slide', $data) ?>
</div>
<div class="flex-row" style="align-items: center;justify-content: flex-end;gap: 1rem;">
    <select class="lang-switcher"  id="lang-selector" 
    mx-get="lang/set_lang?lang=${this.value}"
    mx-trigger="change"
    mx-target="body">
        <?php foreach (['en','ru','de','fr','sv','fi','es','pt'] as $lc): ?>
            <option class="lang-btn" <?= (current_lang() === $lc) ? 'selected' : '' ?> value="<?= $lc ?>">
                <?= strtoupper($lc) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?= anchor('users', '<i class="fa fa-sign-in"></i>', ['class' => 'login-btn', 'style' => 'margin-inline:1rem']) ?>
</div>
<div id="hamburger" class="burger col-3" onclick="toggleSlideNav()">
    <div class="line1"></div>
    <div class="line2"></div>
    <div class="line3"></div>
</div>