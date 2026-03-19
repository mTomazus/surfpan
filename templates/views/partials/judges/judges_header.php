<?php 
$user_info = Modules::run("competitions/_get_judge_info");
$comps = Modules::run("competitions/_get_generated_competitions");
?>

<div class="logo" style="flex: 1.5;text-align: center;">
    <img id="side-menu" src="images/surfpan-hero-2.svg" onclick="toggleScheme()" alt="logo surf club" style="cursor: pointer;">
    <a href="judges" style="flex:1"><span style="color: light-dark(black, white);">Surf</span> Panel<br><h6 class="" style="padding: 0; margin: -5px;text-transform:lowercase;font-size: 0.5rem;">ver 1.25</h6></a>
</div>
<div class="d-sm-none" style="flex: 1.5;text-align: center;">

    <?= Template::partial("partials/judges/judges_aside_2") ?>

</div>
<div class="d-md-none" style="flex: 1;text-align: center;">
    <?php if (isset($comp_name)) { ?>
        <div>
            <?php if ($user_info->role === 'head_judge') { ?>
                <h5 style="margin: 0;text-align: center;"><?= out(ucwords(str_replace('_', ' ', $user_info->role))) ?> Dashboard</h5>
                <p style="margin: 0;color: orangered;">- <?= $comp_name ?> -</p>
            <?php } else { ?>
                <h5 style="margin: 0;text-align: center;color:var(--primary-hover);"><?= out($user_info->role) ?> Dashboard</h5>
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
<?php if (isset($user_info->role) && $user_info->role === 'organizer') { ?>
<a class="side-button d-sm-none" style="text-d  ecoration:none;" href="" mx-get="organizations/profile" mx-build-modal="profile-modal">
    <i class="fa fa-user" aria-hidden="true"></i>
</a>
<?php } else { ?>
<a class="side-button d-sm-none" mx-get="competitions/judge_profile" mx-build-modal="user-info-modal" mx-select="#judge_profile">
    <i class="fa fa-user" aria-hidden="true"></i>
</a>
<?php } ?>
<a class="side-button d-sm-none" style="text-decoration:none;" href="competitions/logout">
    <i class="fa fa-sign-out" aria-hidden="true"></i>
</a>

<div id="hamburger" class="burger col-3" onclick="toggleSideMenu()">
    <div class="line1"></div>
    <div class="line2"></div>
    <div class="line3"></div>
</div>