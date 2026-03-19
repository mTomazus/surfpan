<div id="form-container">
    <form style="grid-template-columns: 1fr;">
        <h2><span>Live</span> Judging</h2>
        <?php 
        if (!empty($heat[0])) { 
        ?>
            <p><?= $error ?></p>
            <div>
                <p class="text-center"><?= $heat[0]->division ?> · <?= $heat[0]->round ?> · Heat <?= $heat[0]->heat_number ?></p>
                <h3 class="text-center"><?= $heat[0]->status ?> · <?= date("M d H:i", strtotime($heat[0]->start_time)); ?></h3>

            </div>
            <h3 id="time-now" class="text-center" mx-get="competitions/current_time" mx-trigger="load">00:00</h3>
        <?php 
            } else {
                echo '<p>' . $error . '</p>';
                echo '<h4 class="text-center">There are no scheduled heats available</h4>';
                echo '<p class="blink lg text-center">Come back later</p>';
            }
        ?>
    </form>
</div>
    
