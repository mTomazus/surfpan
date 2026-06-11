<div id="form-container">
    <div id="load-on" mx-get="judges/score_heat" mx-target="#form-container" mx-select="#form-container" mx-trigger="load"></div>
    <h2><span>Live</span> Judging</h2>
    <p><?= $heat->division ?> - <?= $heat->round === 'Final' ? 'Final' : $heat->round . ' Heat ' . $heat->heat_number ?></p>

    <input type="hidden" name="heat_id" required value="<?= $heat_id ?>">
    <input type="hidden" name="judge_id" required value="<?= $user->id ?>">
    
    <div class="heat_info" style="line-height: 1rem;">
        <div>
            <p>time remaining</p>
            <h4>
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
                <label class="radio_label" style="--var:<?= $colors[$i] ?>; background:var(--gradient-<?= $colors[$i] ?>);" mx-build-modal="modalScoring" mx-get="judges/scores_modal/<?= $heat_id ?>/<?= $participant_id ?>">
                    <?= strtoupper($colors[$i]) ?>
                    <p style="padding: 0;margin: 0;font-size: 0.8rem;">Scored: <?= $wave_number - 1 ?></p>
                    <?php foreach ($missing_scores as $score): 
                        if ($score['jersey_color'] === $colors[$i]): ?>
                            <p class="text-center blink" style="padding: 0;margin: 0;font-size: 0.8rem;">wave  <?= $score['wave_number'] ?? '' ?> score missing</p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </label>
        <?php $i++;
        endforeach; ?>
    </div>

    <div id="scores-table" class="card pad mt-2 mb-2">
        <table>
            <thead>
                <tr>
                    <th>Jrs/Wvs</th>
                    <?php $max_wave_number = max($wave_numbers);
                    for ($i = 1; $i < $max_wave_number; $i++): ?>
                        <th style="text-align:center;"><?= $i ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($scores as $row): ?>
                    <tr>
                        <td class="chip <?= strtolower($row['jersey_color']) ?>"><?= strtoupper($row['jersey_color']) ?></td>
                        <?php for ($i = 0; $i < $max_wave_number - 1; $i++):
                            if (!isset($row['waves'][$i]['score_id'])) {
                                echo '<td id="score_" class="btn-score" style="opacity:0.5;">-</td>';
                            } else { ?>
                            <td id="score_<?= $row['waves'][$i]['score_id'] ?? '' ?>"  mx-build-modal="modalEditScore" mx-get="competitions/edit_score_modal/<?= $row['waves'][$i]['score_id'] ?? '' ?>" class="btn-score"><?= out($row['waves'][$i]['score'] ?? 'M') ?></td>
                            <?php }
                        endfor;
                    echo '</tr>';
                endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
<script>
    function updateRangeSliderBackground() {
        var rangeInput = document.getElementById('score_range');
        if (!rangeInput) return;
        var value = rangeInput.value;
        var min = rangeInput.min ? rangeInput.min : 0;
        var max = rangeInput.max ? rangeInput.max : 10;
        var percent = ((value - min) / (max - min)) * 100;
        var color = 'linear-gradient(to right, rgb(0, 247, 148) ' + percent + '%, rgb(63, 63, 63) ' + percent + '%)';
        rangeInput.style.background = color;
    }

    function rangeSlider(value) {
        document.getElementById('score_value').innerText = value;
        updateRangeSliderBackground();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var rangeInput = document.getElementById('score_range');
        if (rangeInput) {
            updateRangeSliderBackground();
            rangeInput.addEventListener('input', function() {
                rangeSlider(this.value);
            });
            // Add touch event listeners for better mobile support
            rangeInput.addEventListener('touchstart', function() {
                updateRangeSliderBackground();
            });
            rangeInput.addEventListener('touchmove', function() {
                updateRangeSliderBackground();
            });
            rangeInput.addEventListener('touchend', function() {
                updateRangeSliderBackground();
            });
        }
    });
    (function countdownTimerWatcher() {
    let transitioned = false;
    function updateCountdown() {
        const timerEl = document.querySelector('[data-end-time]');
        if (timerEl) {
            const endTimeStr = timerEl.getAttribute('data-end-time');
            const endTime = new Date(endTimeStr.replace(' ', 'T') + 'Z').getTime();
            const now = new Date().getTime();
            let diff = Math.floor((endTime - now) / 1000);
            if (diff > 0) {
                const minutes = String(Math.floor(diff / 60)).padStart(2, '0');
                const seconds = String(diff % 60).padStart(2, '0');
                timerEl.textContent = `${minutes}:${seconds}`;
            } else {
                timerEl.textContent = "00:00";
                if (!transitioned) {
                    transitioned = true;
                    // Give the server 3 s to process results, then reload scoring state
                    setTimeout(function() {
                        const container = document.getElementById('form-container');
                        if (container && window.trongateMX) {
                            trongateMX.get('judges/score_heat', { target: '#form-container', select: '#form-container' });
                        } else {
                            window.location.href = '<?= BASE_URL ?>judges/score_heat';
                        }
                    }, 3000);
                }
            }
        }
    }
    setInterval(updateCountdown, 500);
    updateCountdown();
})();
</script>
<style>
    .modal-body {
        border:1px solid light-dark(var(--accent), var(--opposite));
        border-radius: var(--radius);
        padding:1rem;
    }
</style>