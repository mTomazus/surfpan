<?php $user_info = Modules::run("competitions/_get_judge_info") ?>

<div class="logo" style="flex: 1.5;text-align: center;">
    <img id="side-menu" src="images/surfpan-hero-2.svg" onclick="toggleScheme()" alt="logo surf club" style="cursor: pointer;">
    <a href="#" style="flex:1"><span style="color: light-dark(black, white);">Surf</span> Panel<br><h6 class="" style="padding: 0; margin: -5px;text-transform:lowercase;font-size: 0.5rem;">ver 1.25</h6></a>
</div>
<div class="d-sm-none" style="flex: 1.5;text-align: center;">
    <?php Template::partial("partials/judges/judges_aside_2") ?>
    <a style="text-transform:uppercase;" href="judges"><?= out(ucwords(str_replace('_', ' ', $user_info->role))) ?> Dashboard</a>
</div>
<div class="d-md-none" style="flex: 1;text-align: center;">
    <?php if (isset($comp_name)) { ?>
        <div>
            <?php if ($user_info->role === 'head_judge') { ?>
                <h5 style="margin: 0;text-align: center;"><?= $user_info->name ?></h5>
                <p style="margin: 0;color: orangered;">- <?= $comp_name ?> -</p>
            <?php } else { ?>
                <h5 style="margin: 0;text-align: center;color:var(--primary-hover);"><?= $user_info->name ?></h5>
                <p style="margin: 0;color: orangered;">- <?= $comp_name ?> -</p>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div style="text-align: center;">
            <h5 style="margin: 0;text-transform:uppercase;"><?= out(ucwords(str_replace('_', ' ', $user_info->role))) ?> Dashboard</h5>
            <p style="margin: 0;color: orangered;">= <?= $user_info->organization ?> =</p>
        </div>
    <?php } ?>
</div>

<div id="hamburger" class="burger col-3" onclick="toggleSideMenu()">
    <div class="line1"></div>
    <div class="line2"></div>
    <div class="line3"></div>
</div>