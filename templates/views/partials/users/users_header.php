<div class="logo" style="flex: 1.5;text-align: center;">
    <img src="images/surfpan-hero-2.svg" onclick="toggleScheme()" alt="logo surf club">
    <a href="users"><span style="color: light-dark(black, white)">Surf</span> Panel<br><h6>ver 1.25</h6></a>
</div>
<div class="d-md-none" style="flex: 1.5;text-align: center;">

    <?= Template::partial('partials/slide_nav', ['slide_type' => 'users']) ?>

</div>
<?php 
if (isset($user['name'])) { ?> 

<div class="user d-sm-none" style="flex: 1;text-align: center;">
    <h5 style="margin: 0; text-transform:uppercase;">User's Dashboard</h5>
    <p style="margin: 0;color: var(--ok);"> = <?= out($user['name']) ?> = </p>
</div>

<?php } elseif (isset($judge_info)) { ?>

<div class="d-sm-none" style="flex: 1.5;text-align: center;">

    <?php if ($judge_info->role === 'organizer') { ?>
    <h3 style="text-transform:uppercase;" >Organizer's Dashboard</h3>
    <?php } else { ?>
    <h3 style="text-transform:uppercase;" >Judge's Dashboard</h3>
    <?php } ?>

</div>

<div class="user d-sm-none" style="flex: 1;text-align: center;">
    <h5 style="margin: 0;color: orangered;">= <?= $judge_info->name ?> =</h5>
    <p style="margin: 0;font-size: 0.5em;text-transform:uppercase;"><?= str_replace('_', ' ', $judge_info->role) ?></p>
</div>

<?php } ?>
<a class="side-button d-md-none mr-1" mx-get="users/profile_info" mx-build-modal="user-info-modal" mx-select="#profile-main">
    <i class="fa fa-user" aria-hidden="true"></i>
</a>
<a class="side-button d-md-none mr-1" style="text-decoration:none;" href="users/logout">
    <i class="fa fa-sign-out" aria-hidden="true"></i>
</a>
<div id="hamburger" class="burger col-3" onclick="toggleSideMenu()">
    <div class="line1"></div>
    <div class="line2"></div>
    <div class="line3"></div>
</div>