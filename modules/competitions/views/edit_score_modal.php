<?php
echo '<h2 style="margin-top:0;"><span>Edit Score</span> id ' . out($score_record->id) . '</h2>';
// Edit Score Modal
$form_attr = [
    'id' => 'modalEditScoreForm',
    'class' => 'mt-2',
    'mx-post' => 'competitions/submit_edit_score/' . $score_record->id,
    'mx-target' => '#response',
    'mx-close-on-success' => 'true',
    'mx-on-success' => '#scores-table',
    'style' => 'color:var(--primary);'
];
echo '<p>Surfer ' . out($score_record->participant_name) . '</p>';
echo '<p>Wave No. ' . out($score_record->wave_number) . '</p>';
echo '<p>Judge ' . out($score_record->judge_name) . '</p>';
echo form_open('#', $form_attr);
echo '<div style="grid-column:1/-1;"><label for="edit-score" class="field" style="margin: auto;padding: 0;width: fit-content;">';
echo '<span style="text-wrap: nowrap;padding-left: 1rem;color: var(--white);">Edit score:</span>';
echo form_input('score', $score_record->score, [ 'id' => 'edit-score', 'type' => 'number', 'min' => '0', 'max' => '10', 'step' => '0.1', 'required' => 'required', 'style' => 'appearance: none;text-align: center;font-weight: 900;font-size: 1.2rem;']);
// Form fields go here.
echo '</label></div><div style="display: flex;grid-column: 1/-1;;justify-content: center;gap: 1rem;margin-top: 1rem;">';
echo form_submit('submit', 'Update', ['class' => 'btn primary']);
echo'<button type="button" class="btn" onclick="closeModal()">Close</button></div>';
echo form_close();
?>