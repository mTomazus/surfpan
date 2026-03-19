<div id="just-heats">
    <h2><span>Competition</span> Heats</h2>
    <p>All heats for the selected competition are displayed here.<br>Total heats: <?= count($heats) ?></p>

    <div class="legend mt-1 mb-1">
        <h4>finished</h4>
        <h4>running</h4>
        <h4>scheduled</h4>
        <h4>pending</h4>
    </div>

    <div class="wrapper" >
        <?php foreach ($heats as $heat): ?>
            <div class="heat <?= out($heat['status']) ?>">
                <h4><?= out($heat['division']) ?> | <?= out($heat['round']) ?> | Heat <?= out($heat['heat_number']) ?></h4>
                <hr>
            </div>
        <?php endforeach; ?>
    </div>
</div>
