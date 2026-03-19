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
    'mx-post' => 'judges/submit_score/',
    'mx-target' => '#response',
    'mx-close-on-success' => 'true',
    'style' => 'display: grid;grid-template-columns: 1fr;margin-block-start:1rem;',    
    'mx-on-success' => '#load-on'
];
echo form_open('#', $form_attr);

echo form_checkbox('score', 'M', false, ['id' => 'modal_missed_btn', 'style' => 'display:none;']);
echo '<label class="missed_label btn danger mb-1" for="modal_missed_btn" style="margin-top:1rem;display:flex;justify-content:space-around;align-items:center;gap:1rem">Missed Wave</label>';

echo '<input type="hidden" name="heat_id" value="' . out($heat->id) . '">';
echo '<input type="hidden" name="participant_id" value="' . out($participant_id) . '">';

if(!preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"])){
?>
    <!-- ====== DESKTOP: slider + missed button ====== -->
    <div class="box" style="background:transparent;grid-template-columns: 1fr;">
        <input type="range" class="range_input" name="score" id="score_range" min="0" max="10" step="0.1" value="5" onmousemove="rangeSlider(this.value)" ontouchmove="rangeSlider(this.value)">
        <label for="score_range" class="mt-1 range_label" style="margin:auto"><span id="score_value">5</span></label>       
    </div>

<?php } else { ?>
    <!-- ====== MOBILE: stepper + radios ====== -->
    <div class="radio_score">
        <?php
            for ($i = 1; $i <= 10; $i += 1) {
                $id = "score_" . str_replace('.', '_', $i); // Replace '.' with '_' for a valid ID
                echo '<input type="radio" class="score_input" name="score" value="' . $i . '" id="' . $id . '">';
                echo '<label for="' . $id . '" class="score_label">' . $i . '</label>';
            }
        ?>
    </div>
    <?php
    echo '<div class="radio_score">';

        echo form_radio('adjustment', '-0.5', false, ['id' => 'minus_05', 'class' => 'score_input']);
        echo form_label('<i class="fa fa-minus"></i>.5', ['for' => 'minus_05', 'class' => 'score_label']);
        echo form_radio('adjustment', '-0.25', false, ['id' => 'minus_025', 'class' => 'score_input']);
        echo form_label('<i class="fa fa-minus"></i>.25', ['for' => 'minus_025', 'class' => 'score_label']);

        echo form_radio('adjustment', '0.25', false,['id' => 'plus_025', 'class' => 'score_input']);
        echo form_label('<i class="fa fa-plus"></i>.25', ['for' => 'plus_025', 'class' => 'score_label']);
        echo form_radio('adjustment', '0.5', false, ['id' => 'plus_05', 'class' => 'score_input']);
        echo form_label('<i class="fa fa-plus"></i>.5', ['for' => 'plus_05', 'class' => 'score_label']);

    echo '</div>'; 
    ?>
    
<?php }
echo '<div style="display:flex;justify-content:space-around;align-items:center;margin-bottom:1rem;gap:1rem">';
echo form_submit('submit', 'Submit', ['style' => 'margin: auto;transition: all ease-in-out 0.3s;', 'class' => 'btn primary']);
echo '<button type="button" class="btn" style="margin:auto" onclick="closeModal()">Close</button>';
echo '</div></label></div>';
echo form_close();
?>
<style>
    #modal_missed_btn:checked ~ .missed_label {
        background: var(--gradient-red);
        box-shadow: 0 0 5px red;
        color: white;
        border: 1px solid white;
        transform: scale(1.15);
    }
    form:has(#modal_missed_btn:checked) .box,
    form:has(#modal_missed_btn:checked) .radio_score {
        opacity: 0.4;
        pointer-events: none;
    }
    .radio_score:nth-of-type(2) {
        grid-template-columns: repeat(4, 1fr);
        margin-block-end: 2rem;
        & label {
            font-size: 1rem;
            padding: 0.5rem;
        }
    }
</style>