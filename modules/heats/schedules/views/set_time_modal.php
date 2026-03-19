<?php
echo '<h2>Schedule <span>Heat Time</span></h2>';
echo '<p>Set the start time and duration for the heats.</p>';

$form_attr = [
    'mx-post' => 'heats-schedules/set_time/' . $comp_id . '/'. $heat_id,
    'mx-target' => '#result',
    'mx-on-success' => '#heat-list',
    'mx-close-on-success' => 'true',
    'id' => 'auto-schedule-form',
    'style' => 'display: grid; grid-template-columns: 1fr; gap: 1rem;padding: 0 1rem 1rem;'
    ];
echo form_open('#', $form_attr);
echo '<label class="field" style="color: var(--primary);" for="start_time">Start Time:';
echo '<input id="start_time" class="txt" type="datetime-local" name="start_time" required><i style="font-size:1.2rem" class="fa fa-clock-o" aria-hidden="true"></i></label>';
echo validation_errors('start_time');
echo '<label class="field" style="color: var(--primary);" for="duration">Duration:';
$options = [
    '' => '-- Select Duration --',
    '15' => '15 minutes',
    '20' => '20 minutes',
    '25' => '25 minutes',
    '30' => '30 minutes',
    '35' => '35 minutes',
    '40' => '40 minutes',
    '45' => '45 minutes'
];
$attributes = ['required' => 'required', 'id' => 'duration', 'class' => 'txt'];
echo form_dropdown('duration', $options, '20', $attributes);
echo '<i style="font-size:1.2rem"class="fa fa-caret-down" aria-hidden="true"></i></label>';
echo validation_errors('duration');
echo '<label class="field" style="color: var(--primary);" for="changeover">Changeover Time:';
echo form_number('changeover', '5', ['placeholder' => 'Changeover time (min)', 'required' => 'required', 'id' => 'changeover', 'class' => 'txt']);
echo 'minutes </label>';
echo validation_errors('changeover');
echo '<label class="" style="color: var(--primary);margin-inline: auto 1rem;display: flex;align-items: center;padding-block: 0.5rem;" for="reflow">Reflow';
echo form_checkbox('reflow_after', 1, false, ['id' => 'reflow','class' => 'checkbox']);
echo '</label>';
echo validation_errors('reflow_after');
echo '<p style="font-size: 0.9rem;color: var(--text);">Reflow allows the schedule to adjust dynamically based on changes.</p>';
echo '<div style="display: flex; align-items: center; gap: 0.5rem;margin-inline: auto 0;">';
echo '<a class="btn secondary" style="padding: 0.5rem 1rem;" onclick="closeModal()">Cancel</a>';
echo form_submit('submit', 'Set Schedule', ['class' => 'btn primary', 'style' => 'padding: 0.5rem 1rem;']);
echo '</div>';
echo form_close();