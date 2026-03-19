<div id="results-wrapper">
    <div class="results-header container">
        <h1><?= $comp_data->name ?> <?= $comp_data->year ?></h1>
            <h2><?php   $date = new DateTime($comp_data->end_date);
                echo $date->format('F j, Y'); ?></h2>
            <h3>Location: <span><?= $comp_data->location ?></span> • Participants: <span><?= $participant_count?></span> • Divisions: <span><?= $divisions_count ?></span></h3>

    </div>
    <?php if (count($divisions) > 1): // Show divisions nav only if there are multiple divisions ?>
    <div class="nav">
        <?php foreach ($divisions as $division):
            $label = $division;
            $converted = strtolower(str_replace(' ', '_', $label)); ?>
            <button mx-get="results/show/<?= $comp_id ?>/<?= $converted ?>" 
                    mx-target="#results-wrapper" mx-select="#results-wrapper" 
                    mx-push-url="true" class="btn <?= ($active_division === $division) ? 'active' : '' ?>">
                <?= $division ?>
            </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="text-center">
        <h2 class="mb-0">FINAL RESULTS</h2>
        <h3 class="mt-0">Division: <?= $active_division ?></h3>   
    </div>
    <?php if (empty($results)): ?>
        <div class="text-center" style="margin: 2rem 0;">
            <h2>No results available yet for this division.</h2>
            <p>Please check back later.</p>
        </div>
    <?php return; endif; ?>
    <div class="results-content">
        <div class="results-table container">
            <table>
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Surfer</th>
                        <th>Country</th>
                        <th>Best Wave</th>
                        <th>Total Score</th>
                    </tr>
                </thead>
                <tbody class="text-center"><?php $number = 0;
                    foreach ($results as $row): 
                        $number++; // Increment position counter ?>
                        <tr>
                            <td><?= $number ?></td>
                            <td><?= $row['name']; ?></td>
                            <td><?= $row['country']; ?></td>
                            <td class="sm-none"><?= $row['best_wave']; ?></td>
                            <td class="sm-none"><?= $row['total_score']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>