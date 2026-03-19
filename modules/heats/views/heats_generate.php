<div id="form-container">
    <h2><span>Generate</span> Heats</h2>
    <p>Choose elimination method and generate all at once.</p>
    <?php
        echo flashdata('<p style="color: limegreen; font-weight: bold;border: 1px solid;width: fit-content;margin-inline: auto;padding: 0.5rem 1rem;"', '</p>');
    foreach ($competitions as $row) {
        echo '<h3 class="text-center" style="font-weight: bold;font-size: 1.2em;color:light-dark(var(--accent), var(--opposite));">'  . $row['name'] . ' ' . $row['year'] . '</h3>';
        echo '<h3 class="text-center" style="color:light-dark(var(--accent), var(--opposite));">Confirmed Participants: <span>' . $row['total_participants'] . '</span></h3>';
        echo '<table id="generation-table" class="generation-table mt-1 mb-1" mx-get="heats/heat_generation_page" mx-trigger="load" mx-select="#generation-table"><thead>';
        if ($row['status'] === 'generated') {
            echo '<tr><td colspan="6"><h3 class="text-center" style="color: limegreen;">Heats have already been generated for this competition.</h3></td></tr>';
            echo '<tr><td colspan="6"><div style="display: flex;max-width: 500px;margin: auto;"><button class="btn danger" mx-get="heats/delete_modal/' . $row['id'] . '" mx-build-modal="wipe_modal">Wipe Draw Out</button>';
            echo '<button class="btn primary" href="" mx-get="heats/regenerate_modal/' . $row['id'] . '" mx-build-modal="re_generation_modal">Redraw Heats</button>';
            echo '<a class="btn" href="heats/show_heats_draw/' . $row['id'] . '" target="_blank">Heats Draw</a></div>';
        } else {
            switch (true) {
                case $row['total_participants'] <= 12:
                    echo '<h3 class="price text-center">Price: FREE</h3>';
                    break;
                case $row['total_participants'] <= 24:
                    echo '<h3 class="price text-center">Price: 29 <i class="fa fa-eur" aria-hidden="true"></i></h3>';
                    echo '<h5 class="text-center">Payment will be processed on generation.</h5>';
                    break;
                case $row['total_participants'] <= 50:
                    echo '<h3 class="price text-center">Price: 59 <i class="fa fa-eur" aria-hidden="true"></i></h3>';
                    echo '<h5 class="text-center">Payment will be processed on generation.</h5>';
                    break;
                case $row['total_participants'] <= 75:
                    echo '<h3 class="price text-center">Price: 79 <i class="fa fa-eur" aria-hidden="true"></i></h3>';
                    echo '<h5 class="text-center">Payment will be processed on generation.</h5>';
                    break;
                default:
                    echo '<h3 class="price text-center">Price: 99 <i class="fa fa-eur" aria-hidden="true"></i></h3>';
                    echo '<h5 class="text-center">Payment will be processed on generation.</h5>';
            }
        }
            ?>
            <tr style="border-bottom: 1px solid;">
                <th>Division</th>
                <th>Registered</th>
                <th>Confirmed</th>
                <th>Elimination</th>
                <th>Heats</th>
                <th>Status</th>
            </tr></thead><tbody>
    <?php
        foreach ($row['divisions'] as $division) {

            $confirmed = (int)($division['confirmed'] ?? 0);

            // allow per-division override if you want; otherwise use $row['elimination']
            $method = $division['elimination'] ?? $row['elimination'] ?? 'round_robin';

            [$elimination_type, $number_of_heats] = Modules::run('heats/_get_elim_plan', $method, $confirmed);

    ?>
            <tr>
                <td style="text-wrap: nowrap;"><?php echo $division['name']; ?></td>
                <td class="d-sm-none"><?php echo $division['total']; ?></td>
                <td class="d-sm-none"><?php echo $division['confirmed']; ?></td>
                <td>
                    <?php
                        $locked = in_array($row['status'], ['generated','running','finished'], true);
                        $chosen = $elimination_type;        // NULL or 'single'/'double'/...
                        // $effective = $division['effective_format'];  // resolved default
                        // $recommended = $division['recommended_format'];
                    ?>

                    <select id="elimination-format-<?= $division['id'] ?>"
                    name="elimination_format"
                    mx-post="heats/save_division_elimination/<?= $row['id'] ?>/<?= $division['id'] ?>"
                    mx-trigger="change"
                    mx-dom-vals='{"elimination_format": {"selector": "#elimination-format-<?= $division['id'] ?>","property": "value"}}'
                    mx-on-success="#generation-table"
                    style="min-width: 180px;"
                    >
                    <option value="single" <?= $chosen === 'Single Elimination' ? 'selected' : '' ?>>Single Elimination</option>
                    <option value="double" <?= $chosen === 'Double Elimination' ? 'selected' : '' ?>>Double Elimination</option>
                    <option value="second_chance" <?= $chosen === 'Second Chance' ? 'selected' : '' ?>>Second Chance</option>
                    <option disabled value="robin"  <?= $chosen === 'Round Robin'  ? 'selected' : '' ?>>Round Robin</option>
                    </select>
                </td>
                <td style="display: flex;justify-content: center;align-items: center;gap: 0.5rem;"><?php echo $number_of_heats; ?><span class="d-sm-show"> Heats</span></td>
                <td class="d-sm-none">
                    <?php switch (true) {
                        case $row['status'] === 'generated':
                            echo '<span class="generated" style="font-weight: bold;margin: 0;">Generated</span>';
                            break;
                        case $row['status'] === 'closed':
                            echo '<span class="ready" style="font-weight: bold;margin: 0;">Ready</span>';
                            break;
                        default:
                            echo '<span class="pending" style="font-weight: bold;margin: 0;">Not Set</span>';
                    } ?>
                </td>
            </tr>
            <?php   
        }
        echo '</tbody><tfoot><tr><td colspan="6" style="padding: 0;">';
        if ($row['status'] === 'closed') {
            ?>
        <button class="generate-button btn primary mt-1 mb-1" style="margin-inline: auto;display: block;" 
            mx-get="heats/generate_heats/<?= $row['id'] ?>" mx-build-modal="everypay-gateway" mx-close-on-success="true" mx-on-success="#generation-table" mx-target="#result">
            <?= $row['total_participants'] > 12 ? 'Pay & Generate' : 'Generate' ?>
        </button>
    <?php
        } else if ($row['status'] === 'open') {
            echo '<h3 class="text-center" style="color:light-dark(var(--accent), var(--opposite));">Close registration to be able to generate heats.</h3>';
        }
        echo '</td></tr></tfoot></table>';
    }
    ?>
    <div id="result" style="margin-block: 1rem;"></div>
</div>