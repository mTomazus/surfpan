<div id="form-container">
<form id="score-submit" style="grid-template-columns: auto;" mx-post="competitions/score_submit" mx-animate-success="true" mx-on-success="#load-on">
    <h2><span>Live</span> Judging</h2>
    <p><?= $heat->division ?> - <?= $heat->round === 'Final' ? 'Final' : 'Heat ' . $heat->heat_number ?></p>
    <input type="hidden" name="heat_id" required value="<?= $heat_id ?>">
    <input type="hidden" name="judge_id" required value="<?= $user->id ?>">

    <!-- Hidden field that carries the actual submitted score (numeric or "L") -->
    <input type="hidden" name="score" id="final_score" value="5">

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

    <!-- ====== DESKTOP: slider + missed button ====== -->
    <div>
        <div class="box">
            <input type="range" class="range_input" id="score_range" min="0" max="10" step="0.2" value="5"
                   onmousemove="desktopSlider(this.value)"
                   ontouchmove="desktopSlider(this.value)"
                   oninput="desktopSlider(this.value)">
            <label for="score_range" class="range_label"><span id="score_value">5</span></label>
            <script>
                // Keep final_score in sync with slider; also clear any missed state
                document.getElementById('score_range').addEventListener('input', function() {
                    desktopSlider(this.value);
                });
                function desktopSlider(value) {
                    document.getElementById('score_value').innerText = parseFloat(value).toFixed(1);
                    document.getElementById('final_score').value = value;
                    clearMissedState();
                }
                function clearMissedState() {
                    var btn = document.getElementById('missed_btn_desktop');
                    if (btn) {
                        btn.style.background = '';
                        btn.style.color = '';
                        btn.textContent = 'L  Missed Wave';
                    }
                }
            </script>
        </div>
        <div style="display:flex;gap:0.5rem;margin-top:1rem;">
            <button type="submit" id="submit_btn_desktop" class="score_label submit_button"
                    style="flex:2;font-size:2rem;"
                    onclick="return prepareDesktopSubmit()">Submit</button>
            <button type="button" id="missed_btn_desktop" class="score_label submit_button"
                    style="flex:1;font-size:1.4rem;background:#e65c00;color:#fff;"
                    onclick="submitMissedWave()">L  Missed Wave</button>
        </div>
        <script>
            function prepareDesktopSubmit() {
                // Make sure final_score holds the current slider value (not 'L')
                var slider = document.getElementById('score_range');
                var isMissed = document.getElementById('final_score').value === 'L';
                if (!isMissed) {
                    document.getElementById('final_score').value = slider.value;
                }
                return true; // allow normal form submit
            }
            function submitMissedWave() {
                document.getElementById('final_score').value = 'L';
                var btn = document.getElementById('missed_btn_desktop');
                btn.style.background = '#ff4500';
                btn.style.color = '#fff';
                btn.textContent = '✓ Missed – submitting…';
                document.getElementById('score-submit').submit();
            }
        </script>
    </div>
    
    <?php } else { ?>

    <!-- ====== MOBILE: stepper + radios + missed button ====== -->
    <div class="radio_score_wrapper">

        <!-- Current score display + fine-tune stepper -->
        <div style="display:flex;align-items:center;justify-content:center;gap:0.5rem;margin-bottom:0.75rem;">
            <button type="button" class="stepper_btn" onclick="mobileAdjust(-0.5)">−0.5</button>
            <button type="button" class="stepper_btn" onclick="mobileAdjust(-0.25)">−0.25</button>
            <span id="mobile_score_display" style="
                min-width:3.5rem;text-align:center;font-size:2.4rem;font-weight:bold;
                color:springgreen;border-bottom:2px solid springgreen;padding:0 0.25rem;">
                5.0
            </span>
            <button type="button" class="stepper_btn" onclick="mobileAdjust(+0.25)">+0.25</button>
            <button type="button" class="stepper_btn" onclick="mobileAdjust(+0.5)">+0.5</button>
        </div>

        <style>
            .stepper_btn {
                font-size: 1.1rem;
                font-weight: bold;
                padding: 0.5rem 0.6rem;
                border-radius: 6px;
                border: 2px solid springgreen;
                background: transparent;
                color: springgreen;
                cursor: pointer;
                min-width: 3.2rem;
                line-height: 1;
            }
            .stepper_btn:active { background: springgreen; color: #000; }
        </style>

        <div class="radio_score">
            <!-- Submit and Missed in top row -->
            <button type="submit" class="submit_button" style="font-size:1.6rem;grid-column:span 2;"
                    onclick="return prepareMobileSubmit()">Submit</button>
            <button type="button" id="missed_btn_mobile" class="submit_button"
                    style="font-size:1.4rem;grid-column:span 2;background:#e65c00;color:#fff;margin-bottom:0.5rem;"
                    onclick="submitMissedMobile()">L  Missed Wave</button>

            <?php
                for ($i = 0; $i <= 10; $i += 0.5) {
                    $id = "score_" . str_replace('.', '_', $i);
                    echo '<input type="radio" class="score_input mobile_radio" name="_score_radio" value="' . $i . '" id="' . $id . '"' . ($i == 5 ? ' checked' : '') . '>';
                    echo '<label for="' . $id . '" class="score_label" onclick="mobileRadioSelect(' . $i . ')">' . number_format($i, 1) . '</label>';
                }
            ?>
        </div>

        <script>
            // Initialise final_score on mobile
            document.getElementById('final_score').value = '5';

            function mobileRadioSelect(val) {
                var v = parseFloat(val);
                document.getElementById('final_score').value = v.toFixed(2);
                document.getElementById('mobile_score_display').textContent = v.toFixed(1);
                clearMissedMobileState();
            }

            function mobileAdjust(delta) {
                var current = parseFloat(document.getElementById('final_score').value);
                if (isNaN(current) || document.getElementById('final_score').value === 'L') {
                    current = 5.0;
                }
                var next = Math.round((current + delta) * 100) / 100;
                next = Math.min(10, Math.max(0, next));
                document.getElementById('final_score').value = next.toFixed(2);
                document.getElementById('mobile_score_display').textContent = next.toFixed(1);

                // Highlight matching radio if it exists (0.5-step grid)
                var matchId = 'score_' + next.toString().replace('.', '_');
                var radios = document.querySelectorAll('.mobile_radio');
                radios.forEach(function(r) { r.checked = false; });
                var match = document.getElementById(matchId);
                if (match) match.checked = true;

                clearMissedMobileState();
            }

            function prepareMobileSubmit() {
                // If it's still showing L from a previous missed tap (shouldn't happen, but guard)
                if (document.getElementById('final_score').value === 'L') {
                    return true;
                }
                return true;
            }

            function submitMissedMobile() {
                document.getElementById('final_score').value = 'L';
                document.getElementById('mobile_score_display').textContent = 'L';
                var btn = document.getElementById('missed_btn_mobile');
                btn.style.background = '#ff4500';
                btn.textContent = '✓ Missed – submitting…';
                // Deselect all radios
                document.querySelectorAll('.mobile_radio').forEach(function(r){ r.checked = false; });
                document.getElementById('score-submit').submit();
            }

            function clearMissedMobileState() {
                var btn = document.getElementById('missed_btn_mobile');
                if (btn) {
                    btn.style.background = '#e65c00';
                    btn.textContent = 'L  Missed Wave';
                }
            }
        </script>
    </div>
    
    <?php } ?>

<?php
echo form_close();
?>
</div>