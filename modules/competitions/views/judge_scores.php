<!---------------------------------------------->
<!-------------- SCORE show BELOW -------------->
<!---------------------------------------------->
<div id="form-container">
    <h2>My Heat Scores</h2>
<table>
    <thead>
        <tr>
            <th>Jersey</th>

            <?php
            $sql = "SELECT participant_id, jersey_color FROM comp_heat_participants WHERE heat_id = ?";
            $data = [$heat_id];
            $participants = $this->model->query_bind($sql, $data, 'array'); 

            // Get the max wave number for the current heat to set column headers
            $sql = "SELECT MAX(wave_number) AS max_wave FROM comp_judge_scores WHERE heat_id = ?";
            $data2 = [$heat_id];
            $result = $this->model->query_bind($sql, $data2, 'array');
            $max_wave = $result[0]['max_wave'] ?? 1; // Default to 1 if no scores yet
            
            // Print column headers for waves
            for ($i = 1; $i <= $max_wave; $i++): ?>
                <th><?= $i ?></th>
            <?php endfor; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($participants as $participant): 
            $participant_id = $participant['participant_id'];
            $jersey_color = strtoupper($participant['jersey_color']); // Get assigned jersey color
        ?>
            <tr style="background:none">
                <td style="text-transform: uppercase;color:black;background:<?= $jersey_color ?>"><?= $jersey_color ?></td>
                <?php for ($i = 1; $i <= $max_wave; $i++): 

                    // Step 3: Get score for this participant & wave number
                    $sql = "SELECT score FROM comp_judge_scores 
                            WHERE heat_id = ? AND participant_id = ? AND wave_number = ? AND judge_id = ?";
                    
                    $judge_id = (int)$judge->id;

                    $data = [$heat_id, $participant_id, $i, $judge_id];
                    $score = $this->model->query_bind($sql, $data, 'array');
                ?>
                    <td><?= !empty($score) ? $score[0]['score'] : '-' ?></td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>