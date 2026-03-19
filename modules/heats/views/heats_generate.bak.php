<div id="form-container">
    <h2><span>Generate</span> Heats</h2>
    <p>Generate by division or all at once.</p>
    <?php
    flashdata('<p style="color: limegreen; font-weight: bold;border: 1px solid;width: fit-content;margin-inline: auto;padding: 0.5rem 1rem;"', '</p>');
    foreach ($competitions as $row) {
        echo '<h3 class="text-center" style="font-weight: bold;font-size: 1.2em;color:light-dark(var(--accent), var(--opposite));">'  . $row['name'] . ' ' . $row['year'] . '</h3>';
        echo '<h3 class="text-center" style="color:light-dark(var(--accent), var(--opposite));">Confirmed Participants: <span>' . $row['total_participants'] . '</span></h3>';
        switch (true) {
            case $row['total_participants'] < 12:
                echo '<h3 class="price text-center">Price: FREE</h3>';
                break;
            case $row['total_participants'] < 24:
                echo '<h3 class="price text-center">Price: 29 <i class="fa fa-eur" aria-hidden="true"></i></h3>';
                echo '<h5 class="text-center">Payment will be processed on generation.</h5>';
                break;
            case $row['total_participants'] < 50:
                echo '<h3 class="price text-center">Price: 59 <i class="fa fa-eur" aria-hidden="true"></i></h3>';
                echo '<h5 class="text-center">Payment will be processed on generation.</h5>';
                break;
            case $row['total_participants'] < 50:
                echo '<h3 class="price text-center">Price: 59 <i class="fa fa-eur" aria-hidden="true"></i></h3>';
                echo '<h5 class="text-center">Payment will be processed on generation.</h5>';
                break;
            default:
                echo '<h3 class="price text-center">Price: 99 <i class="fa fa-eur" aria-hidden="true"></i></h3>';
                echo '<h5 class="text-center">Payment will be processed on generation.</h5>';
        }

    ?>
        <table class="generation-table mt-1 mb-1">
            <tr style="border-bottom: 1px solid;">
                <th>Division</th>
                <th>Registered</th>
                <th>Confirmed</th>
                <th>Elimination</th>
                <th>Heats</th>
                <th>Status</th>
            </tr>
    <?php
        foreach ($row['divisions'] as $division) {
            switch (true) {
                case $division['confirmed'] <= 6:
                    $elimination_type = 'Double';
                    $number_of_heats = 4;
                    break;
                case $division['confirmed'] <= 8:
                    $elimination_type = 'Double';
                    $number_of_heats = 6;
                    break;
                case $division['confirmed'] == 9:
                    $elimination_type = 'Double';
                    $number_of_heats = 8;
                    break;
                case $division['confirmed'] <= 12:
                    $elimination_type = 'Double';
                    $number_of_heats = 8;
                    break;
                case $division['confirmed'] <= 16:
                    $elimination_type = 'Double';
                    $number_of_heats = 11;
                    break;
                default:
                    $elimination_type = 'Round Robin + Knockout';
                    $number_of_heats = ceil($division['confirmed'] / 6);
            }
    ?>
            <tr>
                <td style="text-wrap: nowrap;"><?php echo $division['name']; ?></td>
                <td class="d-sm-none"><?php echo $division['total']; ?></td>
                <td class="d-sm-none"><?php echo $division['confirmed']; ?></td>
                <td><?php echo $elimination_type; ?></td>
                <td class="d-sm-none"><?php echo $number_of_heats; ?></td>
                <td>
                    <?php switch (true) {
                        case $row['status'] === 'generated':
                            echo '<span class="generated chip" style="font-weight: bold;margin: 0;">Generated</span>';
                            break;
                        case $row['status'] === 'closed':
                            echo '<span class="ready chip" style="font-weight: bold;margin: 0;">Ready</span>';
                            break;
                        default:
                            echo '<span class="pending chip" style="font-weight: bold;margin: 0;">Not Set</span>';
                    } ?>
                </td>
            </tr>
            <?php   
        }
        echo '</table>';
        if ($row['status'] === 'closed') {
            ?>
        <button class="generate-button btn primary mt-0 mb-2" style="margin: auto;display: block;" 
            mx-get="heats/generate_heats/<?= $row['id'] ?>" mx-build-modal="everypay-gateway">
            <?= $row['total_participants'] > 12 ? 'Pay & Generate' : 'Generate' ?>
        </button>
    <?php
        } else if ($row['status'] === 'open') {
            echo '<h3 class="text-center" style="color:light-dark(var(--accent), var(--opposite));">Close registration to be able to generate heats.</h3>';
        } else if ($row['status'] === 'generated') {
            echo '<h3 class="text-center" style="color:light-dark(var(--accent), var(--opposite));">Heats have already been generated for this competition.</h3>';
        }
    }
    ?>
    <div id="result"></div>
</div>