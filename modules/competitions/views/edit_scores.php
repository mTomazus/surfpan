<div id="form-container">

    <h2><span>Edit All</span> Scores</h2>
    <p>Select a heat to edit the scores for all participants.</p>
    <label for="heat-selector" class="field" style="margin: auto;width: fit-content;">
        <select id="heat-selector" name="heat_id" style="appearance: none;"
                mx-get="competitions/edit_scores/${this.value}"
                mx-select-oob="#form-table:#form-table, #heat-selector:#heat-selector"
                mx-push-url="true"
                mx-trigger="change">
            <option value="" selected>-- Select Heat To Edit --</option>
            <?php foreach ($all_heats as $heat): ?>
                <option value="<?= $heat['id'] ?>" <?= $heat_id == $heat['id'] ? 'selected' : '' ?>>
                    <?= out($heat['id']) ?> -=- <?= out($heat['status']) ?> -=- <?= out($heat['division']) ?> - <?= out($heat['round']) ?> - Heat <?= $heat['heat_number'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>

    <?php
        // $scores = [...] // your array

        // 1) Group scores by wave -> jersey -> judge
        $data = [];
        $jerseys = [];
        $judges = [];

        foreach ($scores as $row) {
            $wave   = (int)$row['wave_number'];
            $jersey = strtolower($row['jersey_color']);
            $judge  = $row['judge_name'];

            $data[$wave][$jersey][$judge] = [
                'score' => $row['score'],
                'id'    => $row['score_id']
            ];

            $jerseys[$jersey] = true;
            $judges[$judge] = true;
        }

        $jerseys = array_keys($jerseys);
        $judges  = array_keys($judges);

        // Optional: order jerseys by heat order
        $jersey_order = ['white','red','green','blue'];
        usort($jerseys, function($a,$b) use($jersey_order){
            $ia = array_search($a, $jersey_order);
            $ib = array_search($b, $jersey_order);
            $ia = $ia === false ? PHP_INT_MAX : $ia;
            $ib = $ib === false ? PHP_INT_MAX : $ib;
            return $ia <=> $ib ?: strcmp($a, $b);
        });
    ?>
    <div id="form-table" mx-get="competitions/edit_scores/<?= $heat_id ?>" mx-select="#form-table" mx-target="#form-table" mx-trigger="activate" class="edit-scores-table">
        <div id="response"></div>
        <table border="1" cellpadding="6" cellspacing="0" style="border:none;">
            <thead>
                <tr>
                <th style="border-bottom: 2px solid var(--white);" rowspan="2">Wave</th>
                <?php foreach ($jerseys as $jersey): ?>
                    <th colspan="<?= count($judges) ?>" style="text-transform:capitalize;color:black; text-align:center;background: <?= out($jersey) ?>;">
                    <?= out($jersey) ?>
                    </th>
                <?php endforeach; ?>
                </tr>
                <tr>
                <?php foreach ($jerseys as $jersey): ?>
                    <?php foreach ($judges as $j): ?>
                    <th style="border-bottom: 2px solid var(--white);">J<?= out($j) ?></th>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $wave => $jerseyData): ?>
                <tr>
                    <td><?= $wave ?></td>
                    <?php foreach ($jerseys as $jersey): ?>
                    <?php foreach ($judges as $j): ?>
                        <td style="text-align:center">
                        <?php if (isset($jerseyData[$jersey][$j])): ?>
                            <?php 
                            $score  = number_format((float)$jerseyData[$jersey][$j]['score'], 2);
                            $scoreId = $jerseyData[$jersey][$j]['id'];
                            ?>
                            <a href="" mx-build-modal="modalEditScore" mx-get="competitions/edit_score_modal/<?= $scoreId ?>" 
                            class="btn-score">
                            <?= $score ?>
                            </a>
                        <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: left; padding: 0.5rem; font-size: 0.9rem;">
                        <p>Heat ID: <?= $heat_id ?> | Total Waves: <?= count($data) ?></p>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>