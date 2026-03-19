<!---------------------------------------------->
<!-------------- SCORE show BELOW -------------->
<!---------------------------------------------->
<h2>Edit Judge Scores</h2>

<form method="GET" action="<?= BASE_URL ?>your_controller/edit_judge_scores">
    <label for="heat_select">Select Heat:</label>
    <select name="heat_id" id="heat_select" onchange="this.form.submit()">
        <?php foreach ($all_heats as $heat): ?>
            <option value="<?= $heat['id'] ?>" <?= $heat_id == $heat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($heat['round']) ?> - Heat <?= $heat['heat_number'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<p>Heat ID: <?= $heat_id ?></p>

<table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Judge ID</th>
            <th>Participant</th>
            <th>Jersey Color</th>
            <th>Wave Number</th>
            <th>Score</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($scores as $score): ?>
            <tr>
                <form action="<?= BASE_URL ?>your_controller/update_score" method="POST">
                    <input type="hidden" name="score_id" value="<?= $score['id'] ?>">
                    <td><?= $score['judge_id'] ?></td>
                    <td><?= htmlspecialchars($score['first_name']) . ' ' . htmlspecialchars($score['last_name']) ?></td>
                    <td style="background-color: <?= htmlspecialchars($score['jersey_color']) ?>; color: #fff; text-transform: uppercase;">
                        <?= htmlspecialchars($score['jersey_color']) ?>
                    </td>
                    <td><?= $score['wave_number'] ?></td>
                    <td>
                        <input type="text" name="score" value="<?= $score['score'] ?>" style="width: 60px;">
                    </td>
                    <td><button type="submit">Update</button></td>
                </form>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>