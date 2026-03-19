<?php
$user_info = Modules::run("competitions/_get_judge_info");
$comps     = Modules::run("competitions/_get_generated_competitions");

$has_any       = is_array($comps) && count($comps) > 0;
$has_multiple  = is_array($comps) && count($comps) > 1;
$first_comp_id = $has_any ? (int)$comps[0]->id : 0;

function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function render_comp_submenu(array $comps, array $allowed_statuses, string $label, string $mx_get_base, string $mx_target, string $mx_select) {
    echo '<li>';
    echo '  <a class="side-button" onclick="toggleSubMenu(this)"><h4>' . h($label) . '</h4><i class="fa fa-chevron-down" aria-hidden="true"></i></a>';
    echo '  <ul class="sub-menu">';

    foreach ($comps as $comp) {
        $status = $comp->status ?? '';
        if (!in_array($status, $allowed_statuses, true)) continue;

        $id   = (int)($comp->id ?? 0);
        $name = trim(($comp->name ?? '') . ' ' . ($comp->year ?? ''));

        echo '<li><a class="side-button" onclick="toggleSideMenu()" mx-get="' . h($mx_get_base . $id) . '" mx-target="' . h($mx_target) . '" mx-select="' . h($mx_select) . '">';
        echo '<h4 style="text-align:center">' . h($name) . '</h4>';
        echo '</a></li>';
    }

    echo '  </ul>';
    echo '</li>';
}

function render_link(string $label, string $mx_get, string $mx_target, string $mx_select, string $icon_html = '') {
    echo '<a class="side-button" mx-get="' . h($mx_get) . '" mx-target="' . h($mx_target) . '" mx-select="' . h($mx_select) . '">';
    echo $icon_html . '<h4>' . h($label) . '</h4>';
    echo '</a>';
}

?>

<?php if (($user_info->role ?? '') === 'head_judge') { ?>
    <ul class="side-nav side-list">
        <?php render_link('Judging', 'judges/score_heat', '#form-container', '#form-container'); ?>
        <?php render_link('Scores',  'competitions/all_scores', '#form-container', '#form-container'); ?>

        <?php if ($has_multiple) {
            render_comp_submenu($comps, ['generated','running'], 'Heats', 'heats/show_heats/', '#form-container', '#just-heats');
        } elseif ($has_any) {
            render_link('Heats', 'heats/show_heats/' . $first_comp_id, '#form-container', '#just-heats');
        } ?>

        <?php if ($has_multiple) {
            render_comp_submenu($comps, ['generated','open','closed','running'], 'Participants', 'competitions/show_participants/', '#form-container', '#participantsTable');
        } elseif ($has_any) {
            render_link('Participants', 'competitions/show_participants/' . $first_comp_id, '#form-container', '#participantsTable');
        } ?>

    </ul>

<?php } elseif (($user_info->role ?? '') === 'organizer') { ?>
    <ul class="side-list side-nav">
        <?php render_link('Events', 'competitions/create_comp', '#form-container', '#form-table'); ?>

        <?php render_link('Heats', 'heats/heat_schedule_page', '#form-container', '#heat-schedule'); ?>
        <!-- If you NEED mx-after-swap only for this one link, keep it inline as custom markup instead of helper. -->

        <?php if ($has_multiple) {
            render_comp_submenu($comps, ['generated','open','closed','running'], 'Show Heats', 'heats/show_heats/', '#form-container', '#just-heats');
        } ?>

        <?php render_link('Judges', 'competitions/create_judge', '#form-container', '#judge-table'); ?>

        <?php if ($has_multiple) {
            render_comp_submenu($comps, ['generated','open','closed','running'], 'Participants', 'competitions/show_participants/', '#form-container', '#participantsTable');
        } elseif ($has_any) {
            render_link('Participants', 'competitions/show_participants/' . $first_comp_id, '#form-container', '#participantsTable');
        } ?>
    </ul>


<?php } else { ?>
    <ul class="side-list side-nav">
        <?php render_link('Judging', 'judges/score_heat', '#form-container', '#form-container'); ?>

        <?php if ($has_multiple) {
            render_comp_submenu($comps, ['generated','running'], 'Heats', 'heats/show_heats/', '#form-container', '#just-heats');
        } elseif ($has_any) {
            render_link('Heats', 'heats/show_heats/' . $first_comp_id, '#form-container', '#just-heats');
        } ?>

        <?php if ($has_any) {
            render_link('Participants', 'competitions/show_participants/' . $first_comp_id, '#form-container', '#participantsTable');
        } ?>
    </ul>
<?php } ?>