<?php $user_info = Modules::run("competitions/_get_judge_info");
$comps = Modules::run("competitions/_get_generated_competitions");
if ($user_info->role === 'head_judge') { ?>

    <div class="side-nav">
        <div class="slide-header">
            <span><?= $user_info->organization ?></span>
            <h3><?= $user_info->role ?></h3>
        </div>
        <a class="side-button" onclick="toggleSideMenu()" mx-get="judges/score_heat" mx-target="#form-container" mx-select="#form-container"><h4><?= t('nav_judging') ?></h4></a>
        <!-- <a class="side-button" onclick="toggleSideMenu()" mx-get="competitions/edit_scores" mx-target="#form-container" mx-select="#form-container"><h4>Edit Scores</h4></a> -->
        <a class="side-button" onclick="toggleSideMenu()" mx-get="competitions/all_scores" mx-target="#form-container" mx-select="#form-container"><h4><?= t('nav_scores') ?></h4></a>
        <?php
        if (!empty($comps[1])) {
        ?>
        <li class=""><a class="side-button" onclick=toggleSubMenu(this)><h4><?= t('nav_heats') ?></h4><i class="fa fa-chevron-down" aria-hidden="true"></i></a>
            <ul class="sub-menu">
                <?php
                foreach ($comps as $comp) {
                    if ($comp->status === 'generated' || $comp->status === 'running') {
                        echo '<li class=""><a class="side-button" onclick="toggleSideMenu()" mx-get="heats/show_heats/' . $comp->id . '" mx-target="#form-container" mx-select="#just-heats"><h4 style="text-align:center">' . $comp->name . ' ' . $comp->year . '</h4></a></li>';
                    }
                }
                ?>
            </ul>
        </li>
        <?php } else { ?>
            <a class="side-button" onclick="toggleSideMenu()" mx-get="heats/show_heats/<?= (int)$comps[0]->id ?>" mx-target="#form-container" mx-select="#just-heats"><h4><?= t('nav_heats') ?></h4></a>
        <?php } ?>
        <!-- <a class="side-button" onclick="toggleSideMenu()" mx-get="heats/heat_schedule_page" mx-target="#form-container" mx-select="#heat-schedule" mx-after-swap="initHeatDragDrop"><i class="fa fa-hourglass-start" aria-hidden="true"></i><h4>Schedule Heats</h4></a> -->
        <?php
        if (!empty($comps[1])) {
        ?>
        <li class=""><a class="side-button" onclick=toggleSubMenu(this)><h4><?= t('nav_participants') ?></h4><i class="fa fa-chevron-down" aria-hidden="true"></i></a>
            <ul class="sub-menu">
                <?php
                foreach ($comps as $comp) {
                    if ($comp->status === 'generated' || $comp->status === 'open' || $comp->status === 'closed' || $comp->status === 'running') {
                        echo '<li class=""><a class="side-button" onclick="toggleSideMenu()" mx-get="competitions/show_participants/' . $comp->id . '" mx-target="#form-container" mx-select="#participantsTable"><h4 style="text-align:center">' . $comp->name . ' ' . $comp->year . '</h4></a></li>';
                    }
                }
                ?>
            </ul>
        </li>
        <?php } else if (!empty($comps)) { ?>
            <a class="side-button" onclick="toggleSideMenu()" mx-get="competitions/show_participants/<?= (int)$comps[0]->id ?>" mx-target="#form-container" mx-select="#participantsTable"><h4><?= t('nav_participants') ?></h4></a>
        <?php } ?>
        <a class="side-button d-sm-none" style="text-decoration: none;" onclick="toggleSideMenu()" href="competitions/logout"><h4><?= t('nav_logout') ?></h4></a>
    </div>

<?php } else if ($user_info->role === 'organizer') { ?>

    <div class="side-nav">
        <a class="side-button" onclick="toggleSideMenu()" mx-get="competitions/create_comp" mx-target="#form-container" mx-select="#form-table"><h4><?= t('nav_events') ?></h4></a>
        <a class="side-button" onclick="toggleSideMenu()" mx-get="heats/heat_schedule_page" mx-target="#form-container" mx-select="#heat-schedule" mx-after-swap="initHeatDragDrop"></i><h4><?= t('nav_heats') ?></h4></a>
        <?php
        if (!empty($comps[1])) { ?>
        <li class=""><a class="side-button" onclick=toggleSubMenu(this)><h4><?= t('nav_show_heats') ?></h4><i class="fa fa-chevron-down" aria-hidden="true"></i></a>
            <ul class="sub-menu">
                <?php
                    foreach ($comps as $comp) {
                        if ($comp->status === 'generated' || $comp->status === 'open' || $comp->status === 'closed' || $comp->status === 'running') {
                            echo '<li class=""><a class="side-button" onclick="toggleSideMenu()" mx-get="heats/show_heats/' . $comp->id . '" mx-target="#form-container" mx-select="#just-heats"><h4 style="text-align:center">' . $comp->name . ' ' . $comp->year . '</h4></a></li>';
                        }
                    }
                ?>
                </ul>
            </li>
        <?php } ?>
        <a class="side-button" onclick="toggleSideMenu()" mx-get="competitions/create_judge" mx-target="#form-container" mx-select="#judge-table"><h4><?= t('nav_judges') ?></h4></a>
        <?php if (!empty($comps[1])) { ?>
        <li class=""><a class="side-button" onclick=toggleSubMenu(this)><h4><?= t('nav_participants') ?></h4><i class="fa fa-chevron-down" aria-hidden="true"></i></a>
            <ul class="sub-menu">
                <?php
                foreach ($comps as $comp) {
                    if ($comp->status === 'generated' || $comp->status === 'open' || $comp->status === 'closed' || $comp->status === 'running') {
                        echo '<li class=""><a class="side-button" onclick="toggleSideMenu()" mx-get="competitions/show_participants/' . $comp->id . '" mx-target="#form-container" mx-select="#participantsTable"><h4 style="text-align:center">' . $comp->name . ' ' . $comp->year . '</h4></a></li>';
                    }
                }
                ?>
            </ul>
        </li>
        <?php } else if (!empty($comps)) { ?>
            <a class="side-button" onclick="toggleSideMenu()" mx-get="competitions/show_participants/<?= (int)$comps[0]->id ?>" mx-target="#form-container" mx-select="#participantsTable"><h4><?= t('nav_participants') ?></h4></a>
        <?php } ?>
        <a class="side-button d-sm-none" style="text-decoration: none;" onclick="toggleSideMenu()" href="competitions/logout"><h4><?= t('nav_logout') ?></h4></a>
    </div>

<?php } else { ?>

    <div class="side-nav">
        <a class="side-button" onclick="toggleSideMenu()" mx-get="judges/score_heat" mx-target="#form-container" mx-select="#form-container"><h4><?= t('nav_judging') ?></h4></a>
        <?php
        if (!empty($comps[1])) { ?>
        <li class=""><a class="side-button" onclick=toggleSubMenu(this)><h4><?= t('nav_heats') ?></h4><i class="fa fa-chevron-down" aria-hidden="true"></i></a>
            <ul class="sub-menu">
                <?php
                foreach ($comps as $comp) {
                    if ($comp->status === 'generated' || $comp->status === 'running') {
                        echo '<li class=""><a class="side-button" onclick="toggleSideMenu()" mx-get="heats/show_heats/' . $comp->id . '" mx-target="#form-container" mx-select="#just-heats"><h4 style="text-align:center">' . $comp->name . ' ' . $comp->year . '</h4></a></li>';
                    }
                }
                ?>
            </ul>
        </li>
        <?php } else if (!empty($comps)) { ?>
            <a class="side-button" onclick="toggleSideMenu()" mx-get="heats/show_heats/<?= (int)$comps[0]->id ?>" mx-target="#form-container" mx-select="#just-heats"><h4><?= t('nav_heats') ?></h4></a>
        <?php } ?>
        <a class="side-button" onclick="toggleSideMenu()" mx-get="competitions/show_participants/<?= (int)$comps[0]->id ?>" mx-target="#form-container" mx-select="#participantsTable"><h4><?= t('nav_participants') ?></h4></a>
        <a class="side-button d-sm-none" style="text-decoration: none;" onclick="toggleSideMenu()" href="competitions/logout"><h4><?= t('nav_logout') ?></h4></a>
    </div>

<?php } ?>