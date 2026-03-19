<?php
echo '<h2 style="margin-top:0;"><span>Enter Wave</span> ' . $wave_numbers[$participant_id] . ' Score</h2>';
echo '<p>' . $heat->division . ' - ' . ($heat->round === 'Final' ? 'Final' : $heat->round . ' Heat ' . $heat->heat_number) . '</p>';
// Edit Score Modal
echo '<div id="response"></div>';
?>
<div class="heat_info" style="line-height: 1rem;">
    <?php switch ($data['jersey_color']) {
        case 'white':
            $color = 'black';
            break;
        default:
            $color = 'white';
            break;
    } ?>
    <div style="text-transform:uppercase;height: 60px;opacity: 0.8;color: <?= $color ?>;background-image: var(--gradient-<?= $jersey_color ?>);box-shadow:0 0 10px <?= $jersey_color ?>;"><span>Jersey</span> <?= $jersey_color ?></div>
</div>
<?php
$form_attr = [
    'id' => 'modalEditScoreForm',
    'class' => 'mt-2',
    'mx-post' => 'judges/submit_score/',
    'mx-target' => '#response',
    'mx-close-on-success' => 'true',
    'style' => 'display: grid;grid-template-columns: 1fr;',    
    'mx-on-success' => '#load-on'
];
echo form_open('#', $form_attr);
echo '<input type="hidden" name="heat_id" value="' . out($heat->id) . '">';
echo '<input type="hidden" name="participant_id" value="' . out($participant_id) . '">';
 
// Single hidden field that carries the actual submitted value (numeric or "L")
echo '<input type="hidden" name="score" id="modal_final_score" value="5">';
 
if(!preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"])){
?>
    <!-- ====== DESKTOP: slider + missed button ====== -->
    <div class="box" style="background:transparent;grid-template-columns: 1fr;">
        <input type="range" class="range_input" id="modal_score_range" min="0" max="10" step="0.1" value="5"
               oninput="modalSlider(this.value)"
               onmousemove="modalSlider(this.value)"
               ontouchmove="modalSlider(this.value)">
        <label for="modal_score_range" class="mt-1 range_label" style="margin:auto">
            <span id="modal_score_value">5.0</span>
        </label>
        <script>
            function modalSlider(value) {
                document.getElementById('modal_score_value').innerText = parseFloat(value).toFixed(1);
                document.getElementById('modal_final_score').value = value;
                resetModalMissed();
            }
            function resetModalMissed() {
                var btn = document.getElementById('modal_missed_btn');
                if (btn) { btn.style.background = '#e65c00'; btn.textContent = 'L  Missed Wave'; }
            }
        </script>
    </div>
 
<?php } else { ?>
 
    <!-- ====== MOBILE: stepper + radios ====== -->
    <div>
        <!-- Fine-tune stepper row -->
        <div style="display:flex;align-items:center;justify-content:center;gap:0.5rem;margin:0.75rem 0;">
            <button type="button" class="modal_stepper_btn" onclick="modalAdjust(-0.5)">−0.5</button>
            <button type="button" class="modal_stepper_btn" onclick="modalAdjust(-0.25)">−0.25</button>
            <span id="modal_score_display" style="
                min-width:3.5rem;text-align:center;font-size:2.4rem;font-weight:bold;
                color:springgreen;border-bottom:2px solid springgreen;padding:0 0.25rem;">
                5.0
            </span>
            <button type="button" class="modal_stepper_btn" onclick="modalAdjust(+0.25)">+0.25</button>
            <button type="button" class="modal_stepper_btn" onclick="modalAdjust(+0.5)">+0.5</button>
        </div>
 
        <style>
            .modal_stepper_btn {
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
            .modal_stepper_btn:active { background: springgreen; color: #000; }
        </style>
 
        <div class="radio_score">
            <?php
                for ($i = 0.5; $i <= 10; $i += 0.5) {
                    $id = "modal_score_" . str_replace('.', '_', $i);
                    $checked = ($i == 5) ? ' checked' : '';
                    echo '<input type="radio" class="score_input modal_radio" name="_modal_radio" value="' . $i . '" id="' . $id . '"' . $checked . '>';
                    echo '<label for="' . $id . '" class="score_label" onclick="modalRadioSelect(' . $i . ')">' . number_format($i, 1) . '</label>';
                }
            ?>
        </div>
 
        <script>
            document.getElementById('modal_final_score').value = '5';
 
            function modalRadioSelect(val) {
                var v = parseFloat(val);
                document.getElementById('modal_final_score').value = v.toFixed(2);
                document.getElementById('modal_score_display').textContent = v.toFixed(1);
                resetModalMissed();
            }
 
            function modalAdjust(delta) {
                var current = parseFloat(document.getElementById('modal_final_score').value);
                if (isNaN(current) || document.getElementById('modal_final_score').value === 'L') {
                    current = 5.0;
                }
                var next = Math.round((current + delta) * 100) / 100;
                next = Math.min(10, Math.max(0, next));
                document.getElementById('modal_final_score').value = next.toFixed(2);
                document.getElementById('modal_score_display').textContent = next.toFixed(1);
 
                // Tick matching radio if it exists
                var matchId = 'modal_score_' + next.toString().replace('.', '_');
                document.querySelectorAll('.modal_radio').forEach(function(r) { r.checked = false; });
                var match = document.getElementById(matchId);
                if (match) match.checked = true;
 
                resetModalMissed();
            }
 
            function resetModalMissed() {
                var btn = document.getElementById('modal_missed_btn');
                if (btn) { btn.style.background = '#e65c00'; btn.textContent = 'L  Missed Wave'; }
                // Reset display if it was showing L
                var disp = document.getElementById('modal_score_display');
                if (disp && disp.textContent === 'L') {
                    disp.textContent = parseFloat(document.getElementById('modal_final_score').value || 5).toFixed(1);
                }
            }
        </script>
    </div>
 
<?php }
?>
 
<div style="display:flex;justify-content:space-around;align-items:center;margin-bottom:1rem;gap:0.75rem;flex-wrap:wrap;">
    <?php echo form_submit('submit', 'Submit', [
        'style' => 'transition: all ease-in-out 0.3s;flex:2;min-width:7rem;',
        'class' => 'btn primary',
        'onclick' => 'return prepareModalSubmit()'
    ]); ?>
    <button type="button" id="modal_missed_btn"
            style="flex:1;min-width:7rem;font-size:1.1rem;background:#e65c00;color:#fff;border:none;
                   padding:0.6rem 1rem;border-radius:6px;cursor:pointer;transition:background 0.2s;"
            onclick="submitModalMissed()">L&nbsp; Missed Wave</button>
    <button type="button" class="btn" style="flex:1;min-width:6rem;" onclick="closeModal()">Close</button>
</div>
 
<script>
    function prepareModalSubmit() {
        // Ensure the final_score is the slider value if not already set to L
        var fs = document.getElementById('modal_final_score');
        if (fs.value !== 'L') {
            var slider = document.getElementById('modal_score_range');
            if (slider) fs.value = slider.value;
        }
        return true;
    }
 
    function submitModalMissed() {
        document.getElementById('modal_final_score').value = 'L';
        var btn = document.getElementById('modal_missed_btn');
        btn.style.background = '#ff4500';
        btn.textContent = '✓ Missed – submitting…';
        // Update score display if mobile
        var disp = document.getElementById('modal_score_display');
        if (disp) disp.textContent = 'L';
        // Deselect all radios
        document.querySelectorAll('.modal_radio').forEach(function(r) { r.checked = false; });
        document.getElementById('modalEditScoreForm').submit();
    }
</script>
 
<?php
echo form_close();
?>