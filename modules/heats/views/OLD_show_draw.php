<div id="full-draw">
    <div id="heat-draw" style="margin: 0 0.5rem;">
        <h1 style="font-size: 2.5rem;
                    text-align: center;
                    background: linear-gradient(to right, red, rgb(152, 0, 104) 52.1%, purple);
                    background-clip: text;
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    font-family: Impact;
                    margin: 1rem;
                    margin-bottom: 0!important;"><?= out($comp_name) ?></h1>
        <a href="<?= BASE_URL ?>organizations/show/<?= $org['slug'] ?>" style="text-align: center;margin: 1rem auto;
                                                                                display: flex;justify-content: center;padding: 0.5rem 2rem;border: 1px solid;
                                                                                width: -webkit-fit-content;text-decoration: none;
                                                                                box-shadow: 0 2px 5px;text-transform: uppercase;"><?= out($org['organization']) ?></a>
        <h3 style="text-align: center; margin:0;">Complete Heat Draw</h3>
    <div class="container-xxl">
        <div class="nav d-sm-none">
            <?php
                // Show divisions nav only if there are multiple divisions -->
                if (count($divisions) > 1): 
                    foreach ($divisions as $label => $value): ?>
                        <button mx-get="heats/show_heats_draw/<?= $comp_id ?>/<?= $value ?>" 
                                mx-target=".wrapper" mx-select=".wrapper" 
                                mx-push-url="true" class="btn">
                            <?= out($label) ?>
                        </button>
                    <?php endforeach;
                endif; ?>
        </div>
        <label for="division-selector" class="field d-md-none" style="width: auto;margin: 1rem auto;align-items: center;justify-content: center;background: var(--bg-dark);">
            <i class="fa fa-arrow-right" style="padding-inline: 0.5rem; color: light-dark(var(--accent), var(--opposite));"></i>
            <select id="division-selector" 
                mx-get="heats/show_heats_draw/<?= $comp_id ?>/${this.value}" 
                mx-target=".wrapper" mx-select=".wrapper"
                mx-trigger="change">
                <?php if (count($divisions) > 1): 
                        foreach ($divisions as $label => $value): ?>
                            <option value="<?= $value ?>"><?= out($label) ?></option>
                        <?php endforeach;
                endif; ?>
            </select>
        </label>
        
        <div class="wrapper">
            <?php foreach ($heats as $heat):
                    if ($heat['status'] === 'running' || $heat['status'] === 'finished' ) {
                    echo '<div class="card-heat" mx-build-modal="heatScore" mx-get="heats/heat_scores/';
                    echo $heat['id'];
                    echo '" >'; }
                    else { echo '<div class="card-heat">';} ?>
                        <div class="heat-header">
                            <?php 
                                if ($heat['round'] === 'Final') {
                                    echo '<h2>' . out($heat['round']) . '</h2>';
                                } else {
                                    echo '<h2>' . out($heat['round']) . ' - Heat ' . out($heat['heat_number']) . '</h2>';
                                }

                                if ($heat['status'] === 'finished') {
                                    echo '<p>' . out($heat['division']) . '</p>';
                                    echo '<p class="status status--ended">ended</p>';
                                } elseif ($heat['status'] === 'pending') { 
                                    echo '<p>' . out($heat['division']) . '</p>';
                                    echo '<p class="status status--soon">soon</p>';   
                                } elseif ($heat['status'] === 'scheduled') {
                                    echo '<p>' . out($heat['division']) . '</p>';
                                    $orgTz = new DateTimeZone($timezone);
                                    $dt = new DateTimeImmutable($heat['start_time'], new DateTimeZone('UTC'));
                                    echo '<p style="text-align:right;color:yellow;">'.$dt->setTimezone($orgTz)->format('H:i').'</p>';

                                } elseif ($heat['status'] === 'running') {
                                    // changing time zones to UTC from org settings
                                    // date_default_timezone_set($timezone); // Set your timezone
                                    $end_time_str = $heat['end_time']; // Example format from DB
                                    $end_time    = new DateTimeImmutable($heat['end_time'], new DateTimeZone('UTC'));
                                    $current_time = new DateTimeImmutable('now', new DateTimeZone('UTC'));
                                    if ($end_time > $current_time) {
                                        $interval = $current_time->diff($end_time);
                                        $minutes = $interval->i + ($interval->h * 60) + ($interval->d * 1440); // convert hours/days to minutes
                                        $seconds = $interval->s;
                                        echo '<p>' . out($heat['division']) . '</p>';
                                        echo '<div style="text-align: right;display: flex;justify-content: right;align-items: center;font-size: 16px;">
                                                <h3 class="status status--live" style="text-align: center;margin: 0;">live</h3>';
                                        echo '<p style="margin-left: 1rem;color: greenyellow;">' . sprintf('%02d:%02d', $minutes, $seconds) . '</p></div>'; // Output like 12:34
                                    } else {
                                        echo '<p>' . out($heat['division']) . '</p>';
                                        echo '<p class="status status--ended">ended</p>';
                                    }
                                }
                            ?>
                        </div>
                    <table>
                    <!--- <?php $counter = 1; // Initialize auto-numbering ?> -->
                        <?php foreach ($heat['participants'] as $participant): ?>
                            <tr>
                            <!---    <td><?= $counter++ ?></td> <!-- Auto-numbering --> 
                                <td><div class="jersey <?= out($participant['jersey_color']) ?>"></div></td>
                                <td><?= out($participant['name']) ?></td>
                                <td><?= out($participant['result']['total_score'] ?? '') ?></td>
                                <?php if ($heat['round'] === 'Final') { 
                                    if (!empty($participant['result']['total_score'])) { ?>
                                    <td style="font-size: 1rem;background:crimson;color:white;margin-left: 1rem;"><?= out($participant['result']['rank']) ?></td>
                                    <?php } else { ?>
                                    <td></td>
                                    <?php }
                                } else { if (!empty($participant['result']['total_score'])) { ?>
                                    <td style="font-size: 1rem;background:cornflowerblue;color:white;margin-left:1rem;"><?= out($participant['result']['rank']) ?></td>
                                    <?php } else { ?>
                                    <td></td>
                                    <?php }
                                } ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    * {
        box-sizing: border-box;
    }
    h1 {
        margin-bottom:2rem!important;
    }
    p {
        margin-top:0;
    }
    table {
        & td {
            border:none;
            text-transform: uppercase;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }
    }
    tr td:first-child {
        font-size-adjust: 0.5;
    }
    .wrapper {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        margin-bottom: 2rem;
    }
    .jersey {
        width: 30px;
        height: 30px;
        border-radius: 15px;
        margin:auto;
    }
    .white {
        background: white;
        border: 1px solid black;
        box-shadow: 0 0 5px;
    }
    .red {
        background: radial-gradient(circle at 100px 100px, rgba(251, 80, 54, 0.8) 0.42%, rgb(255, 51, 51) 81.93%, rgb(152, 2, 2));
        color:white;
        border: 1px solid black;
        box-shadow: 0 0 5px red;
    }
    .green {
        background: radial-gradient(circle at 100px 100px, #01d42c 0.42%, rgb(77, 144, 10) 78.99%, #044700);
        color:white;
        border: 1px solid black;
        box-shadow: 0 0 5px green;
    }
    .blue {
        background: radial-gradient(circle at 100px 100px, #5cabff, rgb(10, 118, 233) 78.99%, #2216a6);
        color:white;
        border: 1px solid black;
        box-shadow: 0 0 5px blue;
    }
    .heat {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 5px;
        padding: 1rem;
        & div {
            display: grid;
            grid-template-columns: auto auto;
            justify-content: space-between;
            align-items: center;
            & p {
                font-weight: 900;
                font-family: Krungthep, sans-serif;
            }
        }
    }
    tbody {
        font-weight: 900;
        font-family: Baskerville;
        text-align: center;
    }
    .nav {
        padding: 1rem 0;
        margin: auto 2rem;
        justify-content: center;
        gap:1rem;
        & button {
            background: var(--bg);
            box-shadow: 0 2px 6px var(--text);
            border: 1px solid;
            color: var(--text);
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s;
        }
        & button:hover{
            background: var(--text);
            color:var(--bg);
            border: none;
            box-shadow: 0 2px 8px var(--text);
        }
    }
    /* Tooltip container */
    .tooltip {
    position: relative;
    display: inline-block;
    }

    /* Tooltip text */
    .tooltip .tooltiptext {
    visibility: hidden;
    width: 40px;
    background-color: grey;
    color: #fff;
    text-align: center;
    padding: 2px 10px;
    border-radius: 6px;
    
    /* Position the tooltip text - see examples below! */
    position: absolute;
    z-index: 1;
    }

    /* Show the tooltip text when you mouse over the tooltip container */
    .tooltip:hover .tooltiptext {
        visibility: visible;
    }
    .status--ended  { color: red; margin: 0; }
    .status--live   { color: greenyellow; }
    .status--soon   { color: var(--white); }
</style>