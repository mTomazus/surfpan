<div id="form-container">

    <h2><span>Show All</span> Scores</h2>
    <p>Select a heat to look judges scores for all participants.</p>
    <label class="field" for="heat-selector">
        <select id="heat-selector" name="heat_id" style="appearance: none;"
                mx-get="competitions/all_scores/${this.value}"
                mx-select-oob="#form-table:#form-table, #heat-selector:#heat-selector"
                mx-push-url="true"
                mx-trigger="change">
                <?php if (empty($all_heats)) { ?>
                    <option value="" selected>No Heats Available</option>
                <?php } else { ?>
                <option value="" selected>-- Select Heat To Look --</option>
            <?php foreach ($all_heats as $heat) { ?>
                <option value="<?= $heat['id'] ?>" <?= $heat_id == $heat['id'] ? 'selected' : '' ?>>
                    =- <?= out($heat['status']) ?> -=- <?= out($heat['name']) ?> - <?= out($heat['division']) ?> - <?= out($heat['round']) ?> - Heat <?= $heat['heat_number'] ?>
                </option>
                <?php } ?> 
            <?php } ?>
        </select>
    </label>

    <div id="form-table" class="edit-scores-table">
        <div id="response"></div>  
        <table id="scores-table" mx-get="competitions/all_scores/<?= $heat_id ?>" mx-select="#scores-table" mx-target="#scores-table" mx-trigger="activate" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Surfer</th>
                    <th>Jersey</th>
                    <th>Wave</th>
                    <th>J1</th>
                    <th>J2</th>
                    <th>J3</th>
                    <th>J4</th>
                    <th>J5</th>
                    <th>AVG</th>
                    <th>Spread</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scores as $score):
                $spread_value = isset($score['spread']) ? (float)$score['spread'] : 0;
                    switch (true){
                        case $spread_value < 0.5:
                            $spread_color = 'var(--ok)';
                            break;
                        case $spread_value < 1:
                            $spread_color = 'var(--warn)';
                            break;
                        default:
                            $spread_color = 'var(--danger)';
                    } ?>

                    <tr style="text-align: center;border-bottom: 0.5px solid;">
                        <td><span>ID: </span><?= $score['participant_id'] ?></td>
                        <td><span class="<?= out($score['jersey_color']) ?> chip"><?= out($score['jersey_color']) ?></span></td>
                        <td><span>Wave </span><?= $score['wave_number'] ?></td>
                        <td class="btn-score" mx-build-modal="modalEditScore" mx-get="competitions/edit_score_modal/<?= $score['J1_id'] ?>"><?= $score['J1'] ?: '' ?></td>
                        <td <?= !empty($score['J2_id']) ? 'class="btn-score" mx-build-modal="modalEditScore" mx-get="competitions/edit_score_modal/'.$score['J2_id'].'"' : '' ?>><?= $score['J2'] == 0 ? '' : $score['J2'] ?></td>
                        <td <?= !empty($score['J3_id']) ? 'class="btn-score" mx-build-modal="modalEditScore" mx-get="competitions/edit_score_modal/'.$score['J3_id'].'"' : '' ?>><?= $score['J3'] == 0 ? '' : $score['J3'] ?></td>
                        <td <?= !empty($score['J4_id']) ? 'class="btn-score" mx-build-modal="modalEditScore" mx-get="competitions/edit_score_modal/'.$score['J4_id'].'"' : '' ?>><?= $score['J4'] == 0 ? '' : $score['J4'] ?></td>
                        <td <?= !empty($score['J5_id']) ? 'class="btn-score" mx-build-modal="modalEditScore" mx-get="competitions/edit_score_modal/'.$score['J5_id'].'"' : '' ?>><?= $score['J5'] == 0 ? '' : $score['J5'] ?></td>
                        <td><?= $score['avg_score'] ?></td>
                        <td><div style="display: flex;justify-content: center;align-items: center;"><span><?= $score['spread'] ?></span> <span class="pan chip" style="background-color: <?= $spread_color ?>;margin: 0 0rem 0 0.5rem;padding: 0;min-width: 20px;height: 20px;"></span></div></td>
                    </tr>
                    
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: left; padding-block: 0;">
                        <p><span>Heat ID:</span> <?= $heat_id ?></p>
                    </td>
                    <td colspan="5" style="text-align: right; padding-block: 0;">
                        <p><span>Total Waves:</span> <?= count($scores) ?></p>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>