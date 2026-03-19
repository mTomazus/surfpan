<div id="form-container">
<form id="score-submit" style="grid-template-columns: auto;" mx-post="competitions/score_submit" mx-animate-success="true" mx-on-success="#load-on">
    <h2><span>Live</span> Judging</h2>
    <p><?= $heat->division ?> - <?= $heat->round === 'Final' ? 'Final' : 'Heat ' . $heat->heat_number ?></p>
    <input type="hidden" name="heat_id" required value="<?= $heat_id ?>">
    <input type="hidden" name="judge_id" required value="<?= $user->id ?>">
    <div class="heat_info" style="line-height: 1rem;">
        <div style="align-content:center">
            <?php if ($heat->round === 'Final') {
                echo '<h1 class="text-center xl mt-1">' . $heat->round . '</h1>';
            } else {
                echo '<h1 class="text-center xl mt-1">' . $heat->round . ' Heat ' . $heat->heat_number . '</h1>';
            } ?>
            <h3 class="text-center m-0"><?= $heat->division ?></h3>
            <h4 style="margin: 1rem auto;border: none;color: springgreen;font-size: 2rem;">
                <span id="countdown-timer" data-end-time="<?= $heat->end_time ?>"></span>
            </h4>
        </div>
    </div>

    <?= flashdata('<div style="color: black;background: var(--opposite);padding: 0.5rem;text-align: center;margin:1rem">', '</div>') ?>

    <div class="radio">
        <?php 
            $i = 0;
            $colors = ['white', 'red', 'green', 'blue'];
            foreach ($wave_numbers as $participant_id => $wave_number): ?>
                <input type="radio" class="radio_input" name="jersey_color" value="<?= $colors[$i] ?>" id="jersey_<?= $colors[$i] ?>">
                <label for="jersey_<?= $colors[$i] ?>" class="radio_label" style="--var:<?= $colors[$i] ?>">
                    <?= strtoupper($colors[$i]) ?>
                    <p style="padding: 0;margin: 0;font-size: 0.8rem;">Total Waves: <?= $wave_number - 1 ?></p>
                </label>
        <?php $i++;
        endforeach; ?>
    </div>

    <?php if(!preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"])){ ?>

    <div>
        <div class="box">
            <input type="range" class="range_input" name="score" id="score_range" min="0" max="10" step="0.2" value="5" onmousemove="rangeSlider(this.value)" ontouchmove="rangeSlider(this.value)">
            <label for="score_range" class="range_label"><span id="score_value">5</span></label>
            <script>
                function rangeSlider(value) {
                    document.getElementById('score_value').innerText = value;
                }
            </script>        
        </div>
        <button type="submit" class="score_label submit_button" style="width:100%;font-size:2rem;margin-top:1rem;">Submit</button>
    </div>
    
    <?php } else { ?>

    <div class="radio_score">
        <button type="submit" class="submit_button" style="width:100%;font-size:2rem;grid-column:span 2;">Submit</button>
        <?php
            for ($i = 1; $i <= 10; $i += 0.5) {
                $id = "score_" . str_replace('.', '_', $i); // Replace '.' with '_' for a valid ID
                echo '<input type="radio" class="score_input" name="score" value="' . $i . '" id="' . $id . '">';
                echo '<label for="' . $id . '" class="score_label">' . $i . '</label>';
            }
        ?>
    </div>
    
    <?php } ?>

<?php
echo form_close();
?>
</div>
